<?php

namespace App\Console\Commands;

use App\Jobs\SyncBetsJob;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncBetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:bets 
                            {--start-date= : Start date (Y-m-d format)}
                            {--end-date= : End date (Y-m-d format)}
                            {--channel= : Channel name filter}
                            {--background : Run as background job}
                            {--chunk-size=1000 : Chunk size for processing}
                            {--days-back=1 : Number of days back from today if no dates provided}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync bets data from the source database with optimized performance for large datasets';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting bet sync operation...');
        
        try {
            // Parse date parameters
            $startDate = $this->getStartDate();
            $endDate = $this->getEndDate();
            $channel = $this->option('channel');
            $background = $this->option('background');
            
            $this->info("Date range: {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");
            if ($channel) {
                $this->info("Channel filter: {$channel}");
            }
            
            // Estimate record count
            $estimatedRecords = $this->getEstimatedRecordCount($startDate, $endDate, $channel);
            $this->info("Estimated records to process: {$estimatedRecords}");
            
            if ($estimatedRecords > 50000 && !$background) {
                if (!$this->confirm('Large dataset detected. Consider using --background option. Continue with synchronous processing?')) {
                    $this->info('Operation cancelled.');
                    return 1;
                }
            }
            
            if ($background) {
                return $this->runBackgroundSync($startDate, $endDate, $channel);
            } else {
                return $this->runSynchronousSync($startDate, $endDate, $channel);
            }
            
        } catch (\Exception $e) {
            $this->error('Sync operation failed: ' . $e->getMessage());
            if ($this->output->isVerbose()) {
                $this->error($e->getTraceAsString());
            }
            return 1;
        }
    }
    
    /**
     * Get start date from options
     */
    private function getStartDate(): Carbon
    {
        if ($this->option('start-date')) {
            return Carbon::parse($this->option('start-date'))->startOfDay();
        }
        
        $daysBack = (int) $this->option('days-back');
        return now()->subDays($daysBack)->startOfDay();
    }
    
    /**
     * Get end date from options
     */
    private function getEndDate(): Carbon
    {
        if ($this->option('end-date')) {
            return Carbon::parse($this->option('end-date'))->endOfDay();
        }
        
        return $this->getStartDate()->addDay()->subSecond();
    }
    
    /**
     * Run synchronous sync
     */
    private function runSynchronousSync(Carbon $startDate, Carbon $endDate, ?string $channel): int
    {
        $this->info('Running synchronous sync...');
        
        $startTime = microtime(true);
        $processedRecords = 0;
        $batchData = [];
        $chunkSize = (int) $this->option('chunk-size');
        
        // Progress bar
        $progressBar = null;
        if (!$this->output->isVerbose()) {
            $progressBar = $this->output->createProgressBar($this->getEstimatedRecordCount($startDate, $endDate, $channel));
            $progressBar->start();
        }
        
        // Get the base query
        $query = $this->getBaseBetQuery($startDate, $endDate, $channel);
        
        // Process data in chunks
        $query->chunk($chunkSize, function ($rows) use (&$processedRecords, &$batchData, $progressBar) {
            foreach ($rows as $row) {
                $batchData[] = $this->prepareBetData($row);
                
                if (count($batchData) >= 500) {
                    $this->processBatch($batchData);
                    $processedRecords += count($batchData);
                    $batchData = [];
                    
                    if ($progressBar) {
                        $progressBar->advance(count($batchData));
                    } elseif ($this->output->isVerbose()) {
                        $this->info("Processed {$processedRecords} records...");
                    }
                }
            }
        });
        
        // Process remaining data
        if (!empty($batchData)) {
            $this->processBatch($batchData);
            $processedRecords += count($batchData);
        }
        
        if ($progressBar) {
            $progressBar->finish();
            $this->newLine();
        }
        
        $executionTime = round(microtime(true) - $startTime, 2);
        $recordsPerSecond = $executionTime > 0 ? round($processedRecords / $executionTime, 2) : 0;
        
        $this->info("✅ Sync completed successfully!");
        $this->table([
            'Metric', 'Value'
        ], [
            ['Records Processed', number_format($processedRecords)],
            ['Execution Time', "{$executionTime} seconds"],
            ['Records/Second', $recordsPerSecond],
            ['Memory Peak', $this->formatBytes(memory_get_peak_usage(true))],
        ]);
        
        return 0;
    }
    
    /**
     * Run background sync
     */
    private function runBackgroundSync(Carbon $startDate, Carbon $endDate, ?string $channel): int
    {
        $jobId = uniqid('sync_bets_');
        
        $this->info('Dispatching background job...');
        SyncBetsJob::dispatch($startDate, $endDate, $channel, $jobId);
        
        $this->info("✅ Background job dispatched successfully!");
        $this->info("Job ID: {$jobId}");
        $this->info("Monitor progress with: php artisan sync:bets-status {$jobId}");
        
        return 0;
    }
    
    /**
     * Get estimated record count
     */
    private function getEstimatedRecordCount(Carbon $startDate, Carbon $endDate, ?string $channel): int
    {
        $query = DB::connection('dk')->table('bets as b')
            ->join('v_user as u', 'u.id', '=', 'b.created_by')
            ->join('channels as c', 'c.id', '=', 'b.channel_id')
            ->where('b.created_at', '>=', $startDate)
            ->where('b.created_at', '<=', $endDate);
            
        if ($channel) {
            $query->where('c.channel_name', $channel);
        }
        
        // Use a faster count estimation for very large datasets
        try {
            return $query->groupBy('b.created_by', 'u.fullusername', 'c.id', 'c.channel_name', DB::raw('DATE(b.created_at)'))
                        ->orderBy('b.created_by')
                        ->orderBy('c.id')
                        ->orderBy(DB::raw('DATE(b.created_at)'))
                        ->selectRaw('COUNT(*) as count')
                        ->get()
                        ->count();
        } catch (\Exception $e) {
            // Fallback to approximate count
            return $query->count();
        }
    }
    
    /**
     * Get the base query for bet data aggregation
     */
    private function getBaseBetQuery(Carbon $startDate, Carbon $endDate, ?string $channel)
    {
        $query = DB::connection('dk')->table('bets as b')
            ->join('v_user as u', 'u.id', '=', 'b.created_by')
            ->join('channels as c', 'c.id', '=', 'b.channel_id')
            ->where('b.created_at', '>=', $startDate)
            ->where('b.created_at', '<=', $endDate)
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
            
        if ($channel) {
            $query->where('c.channel_name', $channel);
        }
        
        return $query;
    }
    
    /**
     * Prepare bet data for batch processing
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
     * Process a batch of bet data
     */
    private function processBatch(array $batchData): void
    {
        if (empty($batchData)) {
            return;
        }

        DB::transaction(function () use ($batchData) {
            $groupedData = collect($batchData)->groupBy(function ($item) {
                return $item['account'] . '|' . $item['channel'] . '|' . $item['trandate'];
            });

            foreach ($groupedData as $key => $items) {
                $data = $items->last();
                [$account, $channel, $trandate] = explode('|', $key);
                
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
     * Format bytes to human readable format
     */
    private function formatBytes($size, $precision = 2): string
    {
        if ($size === 0) return '0 B';
        
        $base = log($size, 1024);
        $suffixes = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
    }
}