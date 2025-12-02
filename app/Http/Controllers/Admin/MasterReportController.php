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

    /**
     * Get master report data grouped by channel and master
     */
    public function getMasterReport(Request $request)
    {
        // Get date ranges using the trait
        $lastMonthRange = $this->getStartDate('1m');
        $thisMonthRange = $this->getStartDate('tm');

        // Get channels
        $channels = Channel::where('is_active', true)
            ->orderBy('channel_name')
            ->pluck('channel_name');

        // Get masters
        $masters = Master::where('is_active', true)
            ->orderBy('name')
            ->pluck('name');

        // Initialize result structure
        $data = [];
        $totals = [];
        $turnover = [];

        // Calculate data for each channel and master combination
        foreach ($channels as $channel) {
            $channelData = [
                'channel' => $channel,
            ];

            foreach ($masters as $master) {
                // Last month data
                $lastMonthStats = Bet::where('channel', $channel)
                    ->where('master', $master)
                    ->whereDate('trandate', '>=', $lastMonthRange->start_date)
                    ->whereDate('trandate', '<=', $lastMonthRange->end_date)
                    ->select(
                        DB::raw('SUM(winlose) as total_winlose'),
                        DB::raw('SUM(turnover) as total_turnover')
                    )
                    ->first();

                // This month data
                $thisMonthStats = Bet::where('channel', $channel)
                    ->where('master', $master)
                    ->whereDate('trandate', '>=', $thisMonthRange->start_date)
                    ->whereDate('trandate', '<=', $thisMonthRange->end_date)
                    ->select(
                        DB::raw('SUM(winlose) as total_winlose'),
                        DB::raw('SUM(turnover) as total_turnover')
                    )
                    ->first();

                $channelData[$master] = [
                    'last_month' => [
                        'winlose' => (float) ($lastMonthStats->total_winlose ?? 0),
                        'turnover' => (float) ($lastMonthStats->total_turnover ?? 0),
                    ],
                    'this_month' => [
                        'winlose' => (float) ($thisMonthStats->total_winlose ?? 0),
                        'turnover' => (float) ($thisMonthStats->total_turnover ?? 0),
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

        // Calculate overall turnover for each master
        foreach ($masters as $master) {
            $lastMonthTurnover = Bet::where('master', $master)
                ->whereDate('trandate', '>=', $lastMonthRange->start_date)
                ->whereDate('trandate', '<=', $lastMonthRange->end_date)
                ->sum('turnover');

            $thisMonthTurnover = Bet::where('master', $master)
                ->whereDate('trandate', '>=', $thisMonthRange->start_date)
                ->whereDate('trandate', '<=', $thisMonthRange->end_date)
                ->sum('turnover');

            $turnover[$master] = [
                'last_month' => (float) $lastMonthTurnover,
                'this_month' => (float) $thisMonthTurnover,
            ];
        }

        return response()->json([
            'data' => $data,
            'totals' => $totals,
            'turnover' => $turnover,
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
}
