<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bet;
use App\Models\Channel;
use App\Models\Master;
use App\Traits\MapDatetimeTrait;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * Class MasterReportController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class MasterReportController extends Controller
{
    use MapDatetimeTrait;

    public function index()
    {
        return Inertia::render('MasterReport', []);
    }

    /*
     * Fet master report
     */
    public function getMasterReport(Request $request)
    {
        // Get date ranges using the trait
        $lastMonthRange = $this->getStartDate('1m');
        $thisMonthRange = $this->getStartDate('tm');

        // Get channels (Query 1)
        $channels = Channel::where('is_active', true)
            ->orderBy('channel_name')
            ->pluck('channel_name');

        // Get masters (Query 2)
        $masters = Master::where('is_active', true)
            ->orderBy('name')
            ->pluck('name');

        // Get all last month stats in one query (Query 3)
        $lastMonthStats = Bet::query()
            ->whereDate('trandate', '>=', $lastMonthRange->start_date)
            ->whereDate('trandate', '<=', $lastMonthRange->end_date)
            ->whereIn('channel', $channels)
            ->whereIn('master', $masters)
            ->select(
                'channel',
                'master',
                DB::raw('SUM(winlose) as total_winlose'),
                DB::raw('SUM(turnover) as total_turnover')
            )
            ->groupBy('channel', 'master')
            ->get()
            ->keyBy(fn($item) => $item->channel . '|' . $item->master);

        // Get all this month stats in one query (Query 4)
        $thisMonthStats = Bet::query()
            ->whereDate('trandate', '>=', $thisMonthRange->start_date)
            ->whereDate('trandate', '<=', $thisMonthRange->end_date)
            ->whereIn('channel', $channels)
            ->whereIn('master', $masters)
            ->select(
                'channel',
                'master',
                DB::raw('SUM(winlose) as total_winlose'),
                DB::raw('SUM(turnover) as total_turnover')
            )
            ->groupBy('channel', 'master')
            ->get()
            ->keyBy(fn($item) => $item->channel . '|' . $item->master);

        // Initialize result structure
        $data = [];
        $totals = [];

        // Build data structure from cached results
        foreach ($channels as $channel) {
            $channelData = ['channel' => $channel];

            foreach ($masters as $master) {
                $key = $channel . '|' . $master;

                $lastMonth = $lastMonthStats->get($key);
                $thisMonth = $thisMonthStats->get($key);

                $channelData[$master] = [
                    'last_month' => [
                        'winlose' => (float) ($lastMonth->total_winlose ?? 0),
                        'turnover' => (float) ($lastMonth->total_turnover ?? 0),
                    ],
                    'this_month' => [
                        'winlose' => (float) ($thisMonth->total_winlose ?? 0),
                        'turnover' => (float) ($thisMonth->total_turnover ?? 0),
                    ],
                ];

                // Accumulate totals
                if (!isset($totals[$master])) {
                    $totals[$master] = [
                        'last_month' => ['winlose' => 0, 'turnover' => 0],
                        'this_month' => ['winlose' => 0, 'turnover' => 0],
                    ];
                }

                $totals[$master]['last_month']['winlose'] += $channelData[$master]['last_month']['winlose'];
                $totals[$master]['last_month']['turnover'] += $channelData[$master]['last_month']['turnover'];
                $totals[$master]['this_month']['winlose'] += $channelData[$master]['this_month']['winlose'];
                $totals[$master]['this_month']['turnover'] += $channelData[$master]['this_month']['turnover'];
            }

            $data[] = $channelData;
        }

        return response()->json([
            'data' => $data,
            'totals' => $totals,
            'channels' => $channels,
            'masters' => $masters,
            'date_ranges' => [
                'last_month' => [
                    'start' => $lastMonthRange->start_date,
                    'end' => $lastMonthRange->end_date,
                ],
                'this_month' => [
                    'start' => $thisMonthRange->start_date,
                    'end' => $thisMonthRange->end_date,
                ],
            ],
        ]);
    }

    /**
     * Get master turnover totals (Pure Eloquent)
     */
    public function getMasterTurnover(Request $request)
    {
        // Get date ranges using the trait
        $lastMonthRange = $this->getStartDate('1m');
        $thisMonthRange = $this->getStartDate('tm');

        // Get masters
        $masters = Master::where('is_active', true)
            ->orderBy('name')
            ->pluck('name');

        // Get last month turnover
        $lastMonthStats = Bet::whereIn('master', $masters)
            ->whereBetween('trandate', [$lastMonthRange->start_date, $lastMonthRange->end_date])
            ->selectRaw('master, SUM(turnover) as total_turnover')
            ->groupBy('master')
            ->pluck('total_turnover', 'master');

        // Get this month turnover
        $thisMonthStats = Bet::whereIn('master', $masters)
            ->whereBetween('trandate', [$thisMonthRange->start_date, $thisMonthRange->end_date])
            ->selectRaw('master, SUM(turnover) as total_turnover')
            ->groupBy('master')
            ->pluck('total_turnover', 'master');

        // Build turnover array
        $turnover = [];
        foreach ($masters as $master) {
            $turnover[$master] = [
                'last_month' => (float) ($lastMonthStats->get($master) ?? 0),
                'this_month' => (float) ($thisMonthStats->get($master) ?? 0),
            ];
        }

        return response()->json([
            'turnover' => $turnover,
            'masters' => $masters,
            'date_ranges' => [
                'last_month' => [
                    'start' => $lastMonthRange->start_date,
                    'end' => $lastMonthRange->end_date,
                ],
                'this_month' => [
                    'start' => $thisMonthRange->start_date,
                    'end' => $thisMonthRange->end_date,
                ],
            ],
        ]);
    }

    /**
     * Alternative: Using Eloquent Relationships (if you have Master model relationship)
     */
    // public function getMasterTurnoverWithRelationships(Request $request)
    // {
    //     // Get date ranges using the trait
    //     $lastMonthRange = $this->getStartDate('1m');
    //     $thisMonthRange = $this->getStartDate('tm');

    //     // Get masters with their bets aggregated
    //     $masters = Master::where('is_active', true)
    //         ->orderBy('name')
    //         ->withSum([
    //             'bets as last_month_turnover' => function ($query) use ($lastMonthRange) {
    //                 $query->whereBetween('trandate', [$lastMonthRange->start_date, $lastMonthRange->end_date]);
    //             }
    //         ], 'turnover')
    //         ->withSum([
    //             'bets as this_month_turnover' => function ($query) use ($thisMonthRange) {
    //                 $query->whereBetween('trandate', [$thisMonthRange->start_date, $thisMonthRange->end_date]);
    //             }
    //         ], 'turnover')
    //         ->get();

    //     // Build turnover array
    //     $turnover = [];
    //     $masterNames = [];

    //     foreach ($masters as $master) {
    //         $masterNames[] = $master->name;
    //         $turnover[$master->name] = [
    //             'last_month' => (float) ($master->last_month_turnover ?? 0),
    //             'this_month' => (float) ($master->this_month_turnover ?? 0),
    //         ];
    //     }

    //     return response()->json([
    //         'turnover' => $turnover,
    //         'masters' => $masterNames,
    //         'date_ranges' => [
    //             'last_month' => [
    //                 'start' => $lastMonthRange->start_date,
    //                 'end' => $lastMonthRange->end_date,
    //             ],
    //             'this_month' => [
    //                 'start' => $thisMonthRange->start_date,
    //                 'end' => $thisMonthRange->end_date,
    //             ],
    //         ],
    //     ]);
    // }
}
