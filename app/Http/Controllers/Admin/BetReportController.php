<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\BetReportPeriodResource;
use App\Http\Resources\BetReportResource;
use App\Models\Bet;
use App\Traits\MapDatetimeTrait;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ReportController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class BetReportController extends Controller
{
    use MapDatetimeTrait;

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

        $bets = $query->paginate($perPage);

        return BetReportResource::collection($bets)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Fetches summarized bet reports grouped by account and time period.
     */
    public function betReportPeriod(Request $request)
    {
        // Get Pagination parameter
        $perPage = $request->get('per_page', 10);
        // Set default sorting to total_turnover DESC
        $sortBy = $request->get('sort_by', 'total_turnover');
        $sortDir = $request->get('sort_dir', 'desc');

        $startDate = null;
        $period = $request->get('period');

        $getStartDate = $this->getStartDate($period);
        $startDate = $getStartDate->start_date;
        $startEnd = $getStartDate->end_date;

        $query = Bet::query();

        // Apply date range filter
        if ($startDate && $startEnd) {
            $query->whereDate('trandate', '>=', $startDate);
            $query->whereDate('trandate', '<=', $startEnd);
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
            ->selectRaw('(SUM(winlose) / SUM(turnover) * 100) as total_lp')
            ->groupBy('account');

        // Sorting based on request param
        $query->orderBy($sortBy, $sortDir);

        $betPeriod = $query->paginate($perPage);

        return BetReportPeriodResource::collection($betPeriod)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
