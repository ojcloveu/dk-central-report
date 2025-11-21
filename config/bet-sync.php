<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bet Sync Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration options for the enhanced bet synchronization system
    | optimized for handling large datasets with millions of records.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Performance Settings
    |--------------------------------------------------------------------------
    */
    
    // Chunk size for processing large datasets in memory-efficient chunks
    'chunk_size' => env('BET_SYNC_CHUNK_SIZE', 1000),
    
    // Batch size for bulk database operations
    'batch_size' => env('BET_SYNC_BATCH_SIZE', 500),
    
    // Threshold for automatically suggesting background processing
    'background_threshold' => env('BET_SYNC_BACKGROUND_THRESHOLD', 10000),
    
    // Memory limit warning threshold (in MB)
    'memory_warning_mb' => env('BET_SYNC_MEMORY_WARNING', 256),

    /*
    |--------------------------------------------------------------------------
    | Database Settings
    |--------------------------------------------------------------------------
    */
    
    // Source database connection name
    'source_connection' => env('BET_SYNC_SOURCE_CONNECTION', 'dk'),
    
    // Destination table name
    'destination_table' => env('BET_SYNC_DESTINATION_TABLE', 'bets'),
    
    // Enable database query logging for debugging
    'enable_query_log' => env('BET_SYNC_ENABLE_QUERY_LOG', false),

    /*
    |--------------------------------------------------------------------------
    | Job Settings
    |--------------------------------------------------------------------------
    */
    
    // Default queue for background jobs
    'queue_name' => env('BET_SYNC_QUEUE', 'default'),
    
    // Job timeout in seconds
    'job_timeout' => env('BET_SYNC_JOB_TIMEOUT', 3600),
    
    // Number of job retry attempts
    'job_tries' => env('BET_SYNC_JOB_TRIES', 3),
    
    // Job status cache duration in hours
    'status_cache_hours' => env('BET_SYNC_STATUS_CACHE_HOURS', 24),

    /*
    |--------------------------------------------------------------------------
    | Logging Settings
    |--------------------------------------------------------------------------
    */
    
    // Enable progress logging
    'enable_progress_logging' => env('BET_SYNC_ENABLE_PROGRESS_LOG', true),
    
    // Progress logging interval (every N records)
    'progress_log_interval' => env('BET_SYNC_PROGRESS_INTERVAL', 5000),
    
    // Log level for sync operations
    'log_level' => env('BET_SYNC_LOG_LEVEL', 'info'),

    /*
    |--------------------------------------------------------------------------
    | Data Processing Settings
    |--------------------------------------------------------------------------
    */
    
    // Enable data validation before processing
    'enable_validation' => env('BET_SYNC_ENABLE_VALIDATION', true),
    
    // Skip invalid records instead of failing
    'skip_invalid_records' => env('BET_SYNC_SKIP_INVALID', false),
    
    // Default date range in days when not specified
    'default_date_range_days' => env('BET_SYNC_DEFAULT_DAYS', 1),

    /*
    |--------------------------------------------------------------------------
    | Monitoring Settings
    |--------------------------------------------------------------------------
    */
    
    // Enable performance monitoring
    'enable_monitoring' => env('BET_SYNC_ENABLE_MONITORING', true),
    
    // Alert thresholds
    'alerts' => [
        // Alert if sync takes longer than this (in seconds)
        'slow_sync_threshold' => env('BET_SYNC_SLOW_THRESHOLD', 300),
        
        // Alert if memory usage exceeds this (in MB)
        'memory_alert_threshold' => env('BET_SYNC_MEMORY_ALERT', 512),
        
        // Alert if error rate exceeds this percentage
        'error_rate_threshold' => env('BET_SYNC_ERROR_RATE_THRESHOLD', 5),
    ],

    /*
    |--------------------------------------------------------------------------
    | Channel Settings
    |--------------------------------------------------------------------------
    */
    
    // Default channel filter (null = all channels)
    'default_channel' => env('BET_SYNC_DEFAULT_CHANNEL'),
    
    // Allowed channels for filtering
    'allowed_channels' => array_filter(explode(',', env('BET_SYNC_ALLOWED_CHANNELS', 'VN,TH,MY,PH'))),

    /*
    |--------------------------------------------------------------------------
    | Optimization Settings
    |--------------------------------------------------------------------------
    */
    
    // Enable query optimization hints
    'enable_query_hints' => env('BET_SYNC_ENABLE_QUERY_HINTS', true),
    
    // Use parallel processing when available
    'enable_parallel_processing' => env('BET_SYNC_ENABLE_PARALLEL', false),
    
    // Number of parallel workers
    'parallel_workers' => env('BET_SYNC_PARALLEL_WORKERS', 4),
];