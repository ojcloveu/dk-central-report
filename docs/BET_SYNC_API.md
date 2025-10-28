# Bet Sync API Documentation

## Overview
The Bet Sync API provides endpoints to synchronize and retrieve betting data with aggregated statistics.

## Endpoints

### 1. Sync Today's Bets
**POST** `/api/sync-bets`

Retrieves and syncs betting data for the current day (server timezone) from the VN channel.

#### Response
```json
{
    "success": true,
    "message": "Bets synced successfully",
    "data": {
        "sync_date": "2025-10-27",
        "total_records": 150,
        "bets": [
            {
                "total_bets": 25,
                "min_bet": 100.00,
                "max_bet": 5000.00,
                "turnover": 125000.00,
                "total_win_lose_amount": -15000.00,
                "channel_id": 1,
                "channel_name": "VN",
                "created_by": 123,
                "fullusername": "John Doe",
                "bet_date": "2025-10-27"
            }
        ]
    }
}
```

### 2. Sync Bets by Date Range
**POST** `/api/sync-bets/date-range`

Retrieves and syncs betting data for a specific date range and channel.

#### Request Body
```json
{
    "start_date": "2025-10-25",
    "end_date": "2025-10-27",
    "channel": "VN"
}
```

#### Parameters
- `start_date` (required): Start date in YYYY-MM-DD format
- `end_date` (required): End date in YYYY-MM-DD format (must be after or equal to start_date)
- `channel` (optional): Channel name (defaults to "VN")

#### Response
```json
{
    "success": true,
    "message": "Bets synced successfully",
    "data": {
        "start_date": "2025-10-25",
        "end_date": "2025-10-27",
        "channel": "VN",
        "total_records": 450,
        "bets": [...]
    }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Failed to sync bets",
    "error": "Error details (only in debug mode)"
}
```

## Data Structure

### Aggregated Bet Data
- `total_bets`: Total number of bets
- `min_bet`: Minimum bet amount
- `max_bet`: Maximum bet amount  
- `turnover`: Total bet amount (sum of all bets)
- `total_win_lose_amount`: Net win/lose amount calculated as:
  - If win_lose = 0 (lose): add bet_amount
  - If win_lose = 1 (win): subtract (bet_amount * payout)
- `channel_id`: Channel ID
- `channel_name`: Channel name
- `created_by`: User ID who created the bets
- `fullusername`: Full username
- `bet_date`: Date of the bets

## Database Requirements

Before using these endpoints, ensure you have run the migrations:

```bash
php artisan migrate
```

This will create the necessary tables:
- `channels`: Channel information
- `v_user`: User view with fullusername
- Updates to `bets` table with new columns

## Notes

- All dates are handled in server timezone
- The query uses efficient index-friendly date ranges
- Results are grouped by user, channel, and date
- Error logging is implemented for debugging