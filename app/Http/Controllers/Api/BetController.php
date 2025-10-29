<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SyncBetsRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BetController extends Controller
{
    /**
     * Sync bets data for today
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function syncBets(Request $request): JsonResponse
    {
        try {
            $start = now()->startOfDay();          // server timezone
            $end   = (clone $start)->addDay();     // start of next day

            $rows = DB::table('bets as b')
                ->join('v_user as u', 'u.id', '=', 'b.created_by')
                ->join('channels as c', 'c.id', '=', 'b.channel_id')
                ->where('c.channel_name', 'VN')
                // index-friendly "today" range
                ->where('b.created_at', '>=', $start)
                ->where('b.created_at', '<',  $end)
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
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Bets synced successfully',
                'data' => [
                    'sync_date' => $start->format('Y-m-d'),
                    'total_records' => $rows->count(),
                    'bets' => $rows
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
     * Sync bets data for a specific date range
     * 
     * @param SyncBetsRequest $request
     * @return JsonResponse
     */
    public function syncBetsByDateRange(SyncBetsRequest $request): JsonResponse
    {
        try {
            $start = Carbon::parse($request->start_date)->startOfDay();
            $end = Carbon::parse($request->end_date)->endOfDay();
            $channel = $request->get('channel', 'VN');

            $query = DB::table('bets as b')
                ->join('v_user as u', 'u.id', '=', 'b.created_by')
                ->join('channels as c', 'c.id', '=', 'b.channel_id')
                ->where('c.channel_name', $channel)
                ->where('b.created_at', '>=', $start)
                ->where('b.created_at', '<=', $end)
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
                ->groupBy('b.created_by', 'u.fullusername', 'c.id', 'c.channel_name', DB::raw('DATE(b.created_at)'));

            $rows = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Bets synced successfully',
                'data' => [
                    'start_date' => $start->format('Y-m-d'),
                    'end_date' => $end->format('Y-m-d'),
                    'channel' => $channel,
                    'total_records' => $rows->count(),
                    'bets' => $rows
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
}