<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\BetReportPeriodResource;
use App\Http\Resources\BetReportResource;
use App\Models\Bet;
use App\Services\DkApiService;
use App\Traits\MapDatetimeTrait;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\Response;
use Backpack\Settings\app\Models\Setting;

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

        // Filter by date range (start_date and end_date)
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereDate('trandate', '>=', $request->get('start_date'));
            $query->whereDate('trandate', '<=', $request->get('end_date'));
        } elseif ($request->has('start_date')) {
            $query->whereDate('trandate', '>=', $request->get('start_date'));
        } elseif ($request->has('end_date')) {
            $query->whereDate('trandate', '<=', $request->get('end_date'));
        }

        // Filter by 'master' with OR for multiple selections
        if ($request->filled('master')) {
            $masters = $request->get('master');

            // Handle both array and comma-separated string
            if (is_array($masters)) {
                $mastersArray = array_map('trim', $masters);
            } else {
                $mastersArray = array_map('trim', explode(',', $masters));
            }

            if (!empty($mastersArray)) {
                $query->whereIn('master', $mastersArray);
            }
        }
        // Filter by 'account'
        if ($request->filled('account')) {
            $query->where('account', 'LIKE', '%' . $request->get('account') . '%');
        }
        // Filter by 'channel'
        if ($request->filled('channel')) {
            $query->where('channel', $request->get('channel'));
        }

        // Filter by "count" range
        if ($request->filled('count_min')) {
            $query->where('count', '>=', $request->get('count_min'));
        }
        if ($request->filled('count_max')) {
            $query->where('count', '<=', $request->get('count_max'));
        }

        // Filter by "turnover" range
        if ($request->filled('turnover_min')) {
            $query->where('turnover', '>=', $request->get('turnover_min'));
        }
        if ($request->filled('turnover_max')) {
            $query->where('turnover', '<=', $request->get('turnover_max'));
        }

        // Filter by "winlose" range
        if ($request->filled('winlose_min')) {
            $winloseMin = $request->get('winlose_min');
            if ($winloseMin < 0) {
                $winloseMin = $winloseMin - 0.99;
            }
            $query->where('winlose', '>=', $winloseMin);
        }
        if ($request->filled('winlose_max')) {
            $winloseMax = $request->get('winlose_max');
            if ($winloseMax > 0) {
                $winloseMax = $winloseMax + 0.99;
            }
            $query->where('winlose', '<=', $winloseMax);
        }

        // Filter by "lp" range
        if ($request->filled('lp_min')) {
            $lpMin = $request->get('lp_min') / 100;
            $query->where('lp', '>=', $lpMin);
        }
        if ($request->filled('lp_max')) {
            $lpMax = $request->get('lp_max') / 100;
            $query->where('lp', '<=', $lpMax);
        }

        // Filter out ignored accounts
        $ignoreAccount = config('settings.ignore_account');
        if (!empty($ignoreAccount)) {
            $ignoredAccounts = array_map('trim', explode(',', $ignoreAccount));
            $query->whereNotIn('account', $ignoredAccounts);
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
        // Set default sorting to total_turnover DESC
        $sortBy = $request->input('sort_by', 'total_turnover');
        $sortDir = $request->input('sort_dir', 'desc');

        $startDate = null;
        $period = $request->input('period');

        $getStartDate = $this->getStartDate($period);
        $startDate = $getStartDate->start_date;
        $startEnd = $getStartDate->end_date;

        $query = Bet::query();

        // Apply date range filter
        if ($startDate && $startEnd) {
            $query->whereDate('trandate', '>=', $startDate);
            $query->whereDate('trandate', '<=', $startEnd);
        }

        // Handle accounts from POST body
        if ($request->filled('accounts')) {
            $accounts = $request->input('accounts');

            // Handle both array JSON body and string query params
            if (is_array($accounts)) {
                $accountsArray = array_map('trim', $accounts);
            } else {
                $accountsArray = array_map('trim', explode(',', $accounts));
            }

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

        $betPeriod = $query->get();

        return BetReportPeriodResource::collection($betPeriod)
            ->additional([
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $startEnd,
                ],
            ])
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Fetches external summary data (deposit/withdraw) from DK API
     */
    public function getExternalSummary(Request $request, DkApiService $dkApiService)
    {
        try {
            // Get accounts param
            $accountsString = $request->get('accounts', '');
            if (empty($accountsString)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accounts parameter is required'
                ], 400);
            }

            // Parse and deduplicate accounts to ensure unique
            $accountsArray = array_map('trim', explode(',', $accountsString));
            $uniqueAccounts = array_unique(array_filter($accountsArray));
            if (empty($uniqueAccounts)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No valid accounts provided'
                ], 400);
            }

            // Fetch summary data from external DK API
            $summaryData = $dkApiService->getSummary($uniqueAccounts);

            return response()->json($summaryData, Response::HTTP_OK);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            \Log::error('Error fetching external summary: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch external summary data',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
