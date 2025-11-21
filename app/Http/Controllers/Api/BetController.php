<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SyncBetsRequest;
use App\Jobs\SyncBetsJob;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Carbon\Carbon;

class BetController extends Controller
{
    /**
     * Chunk size for processing large datasets
     */
    private function getChunkSize(): int
    {
        return config('bet-sync.chunk_size', 1000);
    }
    
    /**
     * Batch size for bulk operations
     */
    private function getBatchSize(): int
    {
        return config('bet-sync.batch_size', 500);
    }
    
    /**
     * Background processing threshold
     */
    private function getBackgroundThreshold(): int
    {
        return config('bet-sync.background_threshold', 10000);
    }

    /**
     * Sync bets data for today with optimized performance for large datasets
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function syncBets(Request $request): JsonResponse
    {
        try {
            $start = now()->startOfDay();
            $end   = (clone $start)->addDay();
            
            // Start time for performance tracking
            $startTime = microtime(true);
            $processedRecords = 0;
            $batchData = [];
            
            Log::info('Starting bet sync process', [
                'date_range' => [$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')]
            ]);

            // Get the base query
            $query = $this->getBaseBetQuery($start, $end);

            // Process data in chunks to avoid memory issues
            $query->chunk($this->getChunkSize(), function (Collection $rows) use (&$processedRecords, &$batchData) {
                foreach ($rows as $row) {
                    // Prepare data for batch insert/update
                    $batchData[] = $this->prepareBetData($row);
                    
                    // Process in batches
                    if (count($batchData) >= $this->getBatchSize()) {
                        $this->processBatch($batchData);
                        $processedRecords += count($batchData);
                        $batchData = []; // Reset batch
                        
                        // Log progress for long-running operations
                        if ($processedRecords % 5000 === 0) {
                            Log::info("Processed {$processedRecords} records");
                        }
                    }
                }
            });

            // Process any remaining data
            if (!empty($batchData)) {
                $this->processBatch($batchData);
                $processedRecords += count($batchData);
            }

            $executionTime = round(microtime(true) - $startTime, 2);
            
            Log::info('Bet sync completed', [
                'processed_records' => $processedRecords,
                'execution_time_seconds' => $executionTime,
                'records_per_second' => $executionTime > 0 ? round($processedRecords / $executionTime, 2) : 0
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bets synced successfully',
                'data' => [
                    'date' => $start->format('Y-m-d'),
                    'total_records' => $processedRecords,
                    'execution_time_seconds' => $executionTime,
                    'performance' => [
                        'records_per_second' => $executionTime > 0 ? round($processedRecords / $executionTime, 2) : 0,
                        'chunk_size' => $this->getChunkSize(),
                        'batch_size' => $this->getBatchSize()
                    ]
                ]
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('Sync bets failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync bets',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Sync bets data for a specific date range with optimized performance
     * 
     * @param SyncBetsRequest $request
     * @return JsonResponse
     */
    public function syncBetsByDateRange(SyncBetsRequest $request): JsonResponse
    {
        try {
            $start = Carbon::parse($request->start_date)->startOfDay();
            $end = Carbon::parse($request->end_date)->endOfDay();
            $channel = $request->get('channel');
            
            $startTime = microtime(true);
            $processedRecords = 0;
            $batchData = [];
            
            Log::info('Starting bet sync by date range', [
                'date_range' => [$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')],
                'channel' => $channel
            ]);

            // Get the base query with optional channel filter
            $query = $this->getBaseBetQuery($start, $end, $channel);

            // Process data in chunks
            $query->chunk($this->getChunkSize(), function (Collection $rows) use (&$processedRecords, &$batchData) {
                foreach ($rows as $row) {
                    $batchData[] = $this->prepareBetData($row);
                    
                    if (count($batchData) >= $this->getBatchSize()) {
                        $this->processBatch($batchData);
                        $processedRecords += count($batchData);
                        $batchData = [];
                        
                        if ($processedRecords % 5000 === 0) {
                            Log::info("Processed {$processedRecords} records");
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
            
            Log::info('Bet sync by date range completed', [
                'processed_records' => $processedRecords,
                'execution_time_seconds' => $executionTime
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bets synced successfully',
                'data' => [
                    'start_date' => $start->format('Y-m-d'),
                    'end_date' => $end->format('Y-m-d'),
                    'channel' => $channel,
                    'total_records' => $processedRecords,
                    'execution_time_seconds' => $executionTime,
                    'performance' => [
                        'records_per_second' => $executionTime > 0 ? round($processedRecords / $executionTime, 2) : 0,
                        'chunk_size' => $this->getChunkSize(),
                        'batch_size' => $this->getBatchSize()
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            Log::error('Sync bets by date range failed: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync bets',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get the base query for bet data aggregation
     * 
     * @param Carbon $start
     * @param Carbon $end
     * @param string|null $channel
     * @return \Illuminate\Database\Query\Builder
     */
    private function getBaseBetQuery(Carbon $start, Carbon $end, ?string $channel = null)
    {
        $query = DB::connection('dk')->table('bets as b')
            ->join('v_user as u', 'u.id', '=', 'b.created_by')
            ->join('channels as c', 'c.id', '=', 'b.channel_id')
            ->where('b.created_at', '>=', $start)
            ->where('b.created_at', '<', $end)
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
        if ($channel) {
            $query->where('c.channel_name', $channel);
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
     * Get memory usage statistics
     * 
     * @return array
     */
    private function getMemoryStats(): array
    {
        return [
            'current_usage_mb' => round(memory_get_usage() / 1024 / 1024, 2),
            'peak_usage_mb' => round(memory_get_peak_usage() / 1024 / 1024, 2),
            'current_usage_real_mb' => round(memory_get_usage(true) / 1024 / 1024, 2),
            'peak_usage_real_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2)
        ];
    }

    /**
     * Start a background sync job for very large datasets
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function startBackgroundSync(Request $request): JsonResponse
    {
        try {
            $startDate = $request->has('start_date') 
                ? Carbon::parse($request->start_date)->startOfDay()
                : now()->startOfDay();
                
            $endDate = $request->has('end_date')
                ? Carbon::parse($request->end_date)->endOfDay()
                : (clone $startDate)->addDay();
                
            $channel = $request->get('channel');
            $jobId = uniqid('sync_bets_');

            // Estimate the dataset size
            $estimatedRecords = $this->getEstimatedRecordCount($startDate, $endDate, $channel);
            
            // If dataset is small enough, process synchronously
            if ($estimatedRecords <= $this->getBackgroundThreshold()) {
                // Create a proper SyncBetsRequest for the method
                $syncRequest = new SyncBetsRequest();
                $syncRequest->replace([
                    'start_date' => $startDate->format('Y-m-d'),
                    'end_date' => $endDate->format('Y-m-d'),
                    'channel' => $channel
                ]);
                return $this->syncBetsByDateRange($syncRequest);
            }

            // Dispatch background job for large datasets
            SyncBetsJob::dispatch($startDate, $endDate, $channel, $jobId);

            Log::info('Background sync job dispatched', [
                'job_id' => $jobId,
                'estimated_records' => $estimatedRecords,
                'date_range' => [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')],
                'channel' => $channel
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Background sync job started successfully',
                'data' => [
                    'job_id' => $jobId,
                    'estimated_records' => $estimatedRecords,
                    'status_check_url' => route('api.bets.job-status', ['jobId' => $jobId]),
                    'note' => 'Use the status_check_url to monitor progress'
                ]
            ], 202); // Accepted

        } catch (\Exception $e) {
            Log::error('Failed to start background sync: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to start background sync',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Check the status of a background sync job
     * 
     * @param string $jobId
     * @return JsonResponse
     */
    public function getJobStatus(string $jobId): JsonResponse
    {
        try {
            $status = cache()->get("sync_job_status:{$jobId}");
            
            if (!$status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job not found or expired'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $status
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to get job status: ' . $e->getMessage(), [
                'job_id' => $jobId
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get job status',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Get estimated record count for a date range
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string|null $channel
     * @return int
     */
    private function getEstimatedRecordCount(Carbon $startDate, Carbon $endDate, ?string $channel = null): int
    {
        $query = DB::connection('dk')->table('bets as b')
            ->join('v_user as u', 'u.id', '=', 'b.created_by')
            ->join('channels as c', 'c.id', '=', 'b.channel_id')
            ->where('b.created_at', '>=', $startDate)
            ->where('b.created_at', '<', $endDate);
            
        if ($channel) {
            $query->where('c.channel_name', $channel);
        }
        
        return $query->groupBy('b.created_by', 'u.fullusername', 'c.id', 'c.channel_name', DB::raw('DATE(b.created_at)'))
                    ->orderBy('b.created_by')
                    ->orderBy('c.id')
                    ->orderBy(DB::raw('DATE(b.created_at)'))
                    ->selectRaw('COUNT(*) as count')
                    ->get()
                    ->count();
    }
}