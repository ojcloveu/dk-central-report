<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class SyncBetsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The maximum number of seconds the job can run.
     *
     * @var int
     */
    public $timeout = 3600; // 1 hour

    /**
     * Chunk size for processing large datasets
     */
    private const CHUNK_SIZE = 2000;
    
    /**
     * Batch size for bulk operations
     */
    private const BATCH_SIZE = 1000;

    /**
     * @var Carbon
     */
    private $startDate;

    /**
     * @var Carbon
     */
    private $endDate;

    /**
     * @var string|null
     */
    private $channel;

    /**
     * @var string
     */
    private $jobId;

    /**
     * Create a new job instance.
     *
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string|null $channel
     * @param string|null $jobId
     */
    public function __construct(Carbon $startDate, Carbon $endDate, ?string $channel = null, ?string $jobId = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->channel = $channel;
        $this->jobId = $jobId ?? uniqid('sync_bets_');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $startTime = microtime(true);
        $processedRecords = 0;
        $batchData = [];
        
        Log::info("Starting background bet sync job", [
            'job_id' => $this->jobId,
            'date_range' => [$this->startDate->format('Y-m-d H:i:s'), $this->endDate->format('Y-m-d H:i:s')],
            'channel' => $this->channel
        ]);

        try {
            // Update job status to processing
            $this->updateJobStatus('processing', [
                'started_at' => now(),
                'estimated_records' => $this->getEstimatedRecordCount()
            ]);

            // Get the base query
            $query = $this->getBaseBetQuery();

            // Process data in chunks
            $query->chunk(self::CHUNK_SIZE, function (Collection $rows) use (&$processedRecords, &$batchData) {
                foreach ($rows as $row) {
                    $batchData[] = $this->prepareBetData($row);
                    
                    if (count($batchData) >= self::BATCH_SIZE) {
                        $this->processBatch($batchData);
                        $processedRecords += count($batchData);
                        $batchData = [];
                        
                        // Update progress every 10k records
                        if ($processedRecords % 10000 === 0) {
                            $this->updateJobProgress($processedRecords);
                            Log::info("Job {$this->jobId}: Processed {$processedRecords} records");
                        }
                    }
                }
            });

            // Process remaining data
            if (!empty($batchData)) {
                $this->processBatch($batchData);
                $processedRecords += count($batchData);
            }

            $executionTime = round(microtime(true) - $startTime, 2);
            
            // Update job status to completed
            $this->updateJobStatus('completed', [
                'completed_at' => now(),
                'processed_records' => $processedRecords,
                'execution_time_seconds' => $executionTime,
                'records_per_second' => $executionTime > 0 ? round($processedRecords / $executionTime, 2) : 0
            ]);

            Log::info("Background bet sync job completed", [
                'job_id' => $this->jobId,
                'processed_records' => $processedRecords,
                'execution_time_seconds' => $executionTime
            ]);

        } catch (\Exception $e) {
            $this->updateJobStatus('failed', [
                'failed_at' => now(),
                'error' => $e->getMessage(),
                'processed_records' => $processedRecords
            ]);

            Log::error("Background bet sync job failed", [
                'job_id' => $this->jobId,
                'error' => $e->getMessage(),
                'processed_records' => $processedRecords
            ]);

            throw $e;
        }
    }

    /**
     * Get the base query for bet data aggregation
     * 
     * @return \Illuminate\Database\Query\Builder
     */
    private function getBaseBetQuery()
    {
        $query = DB::connection('dk')->table('bets as b')
            ->join('v_user as u', 'u.id', '=', 'b.created_by')
            ->join('channels as c', 'c.id', '=', 'b.channel_id')
            ->where('b.created_at', '>=', $this->startDate)
            ->where('b.created_at', '<', $this->endDate)
            ->selectRaw("
                COUNT(b.id)                                 as total_bets,
                MIN(b.bet_amount)                           as min_bet,
                MAX(b.bet_amount)                           as max_bet,
                SUM(b.bet_amount)                           as turnover,
                SUM(CASE
                      WHEN b.win_lose = 0 THEN b.bet_amount
                      WHEN b.win_lose = 1 THEN -(b.bet_amount * b.payout)
                      ELSE 0
                    END)                                    as total_win_lose_amount,
                c.id                                        as channel_id,
                c.channel_name                              as channel_name,
                b.created_by,
                u.fullusername,
                DATE(b.created_at)                          as bet_date
            ")
            ->groupBy('b.created_by', 'u.fullusername', 'c.id', 'c.channel_name', DB::raw('DATE(b.created_at)'))
            ->orderBy('b.created_by')
            ->orderBy('c.id')
            ->orderBy(DB::raw('DATE(b.created_at)'));
            
        // Add channel filter if specified
        if ($this->channel) {
            $query->where('c.channel_name', $this->channel);
        }
        
        return $query;
    }

    /**
     * Prepare bet data for batch processing
     * 
     * @param \stdClass $row
     * @return array
     */
    private function prepareBetData(\stdClass $row): array
    {
        return [
            'account'    => $row->fullusername,
            'master'     => substr($row->fullusername, 0, 4),
            'channel'    => $row->channel_name,
            'trandate'   => $row->bet_date,
            'min'        => $row->min_bet,
            'max'        => $row->max_bet,
            'count'      => $row->total_bets,
            'turnover'   => $row->turnover,
            'winlose'    => $row->total_win_lose_amount,
            'lp'         => $row->turnover > 0 ? $row->total_win_lose_amount / $row->turnover : 0,
            'updated_at' => now(),
            'created_at' => now()
        ];
    }

    /**
     * Process a batch of bet data using bulk operations
     * 
     * @param array $batchData
     * @return void
     */
    private function processBatch(array $batchData): void
    {
        if (empty($batchData)) {
            return;
        }

        // Use database transaction for better performance and data consistency
        DB::transaction(function () use ($batchData) {
            // Group data by unique key for upsert operations
            $groupedData = collect($batchData)->groupBy(function ($item) {
                return $item['account'] . '|' . $item['channel'] . '|' . $item['trandate'];
            });

            foreach ($groupedData as $key => $items) {
                // Take the last item if there are duplicates
                $data = $items->last();
                [$account, $channel, $trandate] = explode('|', $key);
                
                // Use more efficient upsert operation
                DB::table('bets')->updateOrInsert(
                    [
                        'account' => $account,
                        'channel' => $channel,
                        'trandate' => $trandate,
                    ],
                    $data
                );
            }
        });
    }

    /**
     * Get estimated record count for progress tracking
     * 
     * @return int
     */
    private function getEstimatedRecordCount(): int
    {
        $query = DB::connection('dk')->table('bets as b')
            ->join('v_user as u', 'u.id', '=', 'b.created_by')
            ->join('channels as c', 'c.id', '=', 'b.channel_id')
            ->where('b.created_at', '>=', $this->startDate)
            ->where('b.created_at', '<', $this->endDate);
            
        if ($this->channel) {
            $query->where('c.channel_name', $this->channel);
        }
        
        return $query->groupBy('b.created_by', 'u.fullusername', 'c.id', 'c.channel_name', DB::raw('DATE(b.created_at)'))
                    ->orderBy('b.created_by')
                    ->orderBy('c.id')
                    ->orderBy(DB::raw('DATE(b.created_at)'))
                    ->selectRaw('COUNT(*) as count')
                    ->get()
                    ->count();
    }

    /**
     * Update job status in cache/database
     * 
     * @param string $status
     * @param array $data
     * @return void
     */
    private function updateJobStatus(string $status, array $data = []): void
    {
        $statusData = array_merge([
            'job_id' => $this->jobId,
            'status' => $status,
            'updated_at' => now()
        ], $data);

        // Store in cache for quick access
        cache()->put("sync_job_status:{$this->jobId}", $statusData, now()->addHours(24));
        
        // Also store in database for persistence (optional)
        // DB::table('job_statuses')->updateOrInsert(['job_id' => $this->jobId], $statusData);
    }

    /**
     * Update job progress
     * 
     * @param int $processedRecords
     * @return void
     */
    private function updateJobProgress(int $processedRecords): void
    {
        $statusData = cache()->get("sync_job_status:{$this->jobId}", []);
        $estimatedRecords = $statusData['estimated_records'] ?? 0;
        
        $progress = $estimatedRecords > 0 ? round(($processedRecords / $estimatedRecords) * 100, 2) : 0;
        
        $statusData['processed_records'] = $processedRecords;
        $statusData['progress_percentage'] = $progress;
        $statusData['updated_at'] = now();
        
        cache()->put("sync_job_status:{$this->jobId}", $statusData, now()->addHours(24));
    }
}