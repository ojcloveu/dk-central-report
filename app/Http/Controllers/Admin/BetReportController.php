<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bet;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Carbon\Carbon;

/**
 * Class ReportController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class BetReportController extends Controller
{
    public function index()
    {
        return Inertia::render('BetReport', []);
    }

    /**
     * Fetches detailed bet reports with standard filters (no grouping).
     */
    public function betReports(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $sortBy = $request->get('sort_by', 'trandate');
        $sortDir = $request->get('sort_dir', 'desc');

        $query = Bet::query();

        // Filter by 'trandate'
        if ($request->has('trandate')) {
            $query->whereDate('trandate', $request->get('trandate'));
        }
        // Filter by 'master'
        if ($request->filled('master')) {
            $query->where('master', 'LIKE', '%' . $request->get('master') . '%');
        }
        // Filter by 'account'
        if ($request->filled('account')) {
            $query->where('account', 'LIKE', '%' . $request->get('account') . '%');
        }
        // Filter by 'channel'
        if ($request->filled('channel')) {
            $query->where('channel', $request->get('channel'));
        }

        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($perPage);
    }

    /**
     * Fetches summarized bet reports grouped by account and time period.
     */
    public function betReportPeriod(Request $request)
    {
        // Get Pagination parameter
        $perPage = $request->get('per_page', 10);

        $startDate = null;
        $period = $request->get('period');
        
        switch ($period) {
            case '7days':
                $startDate = Carbon::now()->subDays(7);
                break;
            case '1month':
                $startDate = Carbon::now()->subMonth();
                break;
            case '3months':
                $startDate = Carbon::now()->subMonths(3);
                break;
            default:
                // If no valid period is provided, return an empty
                return response()->json([
                    'data' => [], 
                    'current_page' => 1, 
                    'per_page' => $perPage, 
                    'total' => 0
                ], 200);
        }

        $query = Bet::query();

        // Apply date range filter
        if ($startDate) {
            $query->whereDate('trandate', '>=', $startDate->toDateString());
        }

        if ($request->filled('accounts')) {
            $accountsString = $request->get('accounts');
            $accountsArray = array_map('trim', explode(',', $accountsString));
            
            if (!empty($accountsArray)) {
                $query->whereIn('account', $accountsArray);
            }
        }

        $query
            ->select('account')
            ->selectRaw('SUM(count) as total_count')
            ->selectRaw('SUM(turnover) as total_turnover')
            ->selectRaw('SUM(winlose) as total_winlose')
            ->selectRaw('SUM(lp) as total_lp')
            ->groupBy('account')
            ->orderBy('total_turnover', 'DESC');
        
        return $query->paginate($perPage);
    }
}
