<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncBetsStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:bets-status {jobId : The job ID to check status for}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the status of a background bet sync job';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $jobId = $this->argument('jobId');
        
        $this->info("Checking status for job: {$jobId}");
        
        $status = cache()->get("sync_job_status:{$jobId}");
        
        if (!$status) {
            $this->error('Job not found or expired.');
            return 1;
        }
        
        $this->displayJobStatus($status);
        
        // If job is still processing, offer to monitor
        if ($status['status'] === 'processing') {
            if ($this->confirm('Job is still processing. Would you like to monitor progress?')) {
                $this->monitorJob($jobId);
            }
        }
        
        return 0;
    }
    
    /**
     * Display job status information
     */
    private function displayJobStatus(array $status): void
    {
        $this->info('Job Status Information:');
        $this->newLine();
        
        $tableData = [
            ['Job ID', $status['job_id']],
            ['Status', strtoupper($status['status'])],
            ['Updated At', $status['updated_at'] ?? 'N/A'],
        ];
        
        // Add status-specific information
        switch ($status['status']) {
            case 'processing':
                $tableData[] = ['Started At', $status['started_at'] ?? 'N/A'];
                $tableData[] = ['Estimated Records', number_format($status['estimated_records'] ?? 0)];
                $tableData[] = ['Processed Records', number_format($status['processed_records'] ?? 0)];
                if (isset($status['progress_percentage'])) {
                    $tableData[] = ['Progress', $status['progress_percentage'] . '%'];
                }
                break;
                
            case 'completed':
                $tableData[] = ['Started At', $status['started_at'] ?? 'N/A'];
                $tableData[] = ['Completed At', $status['completed_at'] ?? 'N/A'];
                $tableData[] = ['Processed Records', number_format($status['processed_records'] ?? 0)];
                $tableData[] = ['Execution Time', ($status['execution_time_seconds'] ?? 0) . ' seconds'];
                $tableData[] = ['Records/Second', number_format($status['records_per_second'] ?? 0, 2)];
                break;
                
            case 'failed':
                $tableData[] = ['Started At', $status['started_at'] ?? 'N/A'];
                $tableData[] = ['Failed At', $status['failed_at'] ?? 'N/A'];
                $tableData[] = ['Processed Records', number_format($status['processed_records'] ?? 0)];
                $tableData[] = ['Error', $status['error'] ?? 'Unknown error'];
                break;
        }
        
        $this->table(['Property', 'Value'], $tableData);
        
        // Display status-specific messages
        switch ($status['status']) {
            case 'processing':
                $this->comment('✋ Job is currently processing...');
                break;
            case 'completed':
                $this->info('✅ Job completed successfully!');
                break;
            case 'failed':
                $this->error('❌ Job failed!');
                break;
        }
    }
    
    /**
     * Monitor job progress in real-time
     */
    private function monitorJob(string $jobId): void
    {
        $this->info('Monitoring job progress (Press Ctrl+C to stop monitoring)...');
        $this->newLine();
        
        $lastProcessedRecords = 0;
        $progressBar = null;
        
        while (true) {
            $status = cache()->get("sync_job_status:{$jobId}");
            
            if (!$status) {
                $this->error('Job status no longer available.');
                break;
            }
            
            if ($status['status'] !== 'processing') {
                if ($progressBar) {
                    $progressBar->finish();
                    $this->newLine(2);
                }
                
                $this->displayJobStatus($status);
                break;
            }
            
            $processedRecords = $status['processed_records'] ?? 0;
            $estimatedRecords = $status['estimated_records'] ?? 0;
            
            if (!$progressBar && $estimatedRecords > 0) {
                $progressBar = $this->output->createProgressBar($estimatedRecords);
                $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% (%elapsed:6s%/%estimated:-6s%) %memory:6s%');
                $progressBar->start();
            }
            
            // Update progress display
            if ($progressBar) {
                $progressBar->setProgress($processedRecords);
            }
            
            sleep(2); // Update every 2 seconds
        }
    }
}