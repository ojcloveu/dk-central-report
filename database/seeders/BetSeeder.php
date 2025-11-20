<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Bet;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * php artisan db:seed --class="Database\\Seeders\\BetSeeder"
     */
    public function run(): void
    {
        // Clear existing bet data
        DB::table('bets')->truncate();

        // Get all channels
        $channels = ['DK', 'VN', 'TH', 'PH'];

        // Define masters (betting types/games)
        $masters = ['DKDK', 'DKAO'];

        // how many accounts per (master + channel prefix)
        $accountsPerGroup = 40;

        // chance (percentage) an account will switch master/channel on a given day
        $switchMasterChance = 20; // 20% chance to use a different master that day
        $switchChannelChance = 25; // 25% chance to use a different channel that day

        // generate fixed accounts (stable account string)
        $accounts = [];
        foreach ($channels as $channel) {
            foreach ($masters as $master) {
                for ($i = 0; $i < $accountsPerGroup; $i++) {
                    $suffix = ['BA', 'AA', 'CA'][array_rand(['BA', 'AA', 'CA'])];
                    $id = str_pad($i, 3, '0', STR_PAD_LEFT);

                    $accounts[] = [
                        'account' => $master . $channel . $suffix . $id, // stable string
                        'default_master' => $master,
                        'default_channel' => $channel,
                    ];
                }
            }
        }

        $startDate = Carbon::now()->subMonths(3)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        echo "Generating bets from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}\n";

        $totalRecords = 0;
        $batchSize = 1000;
        $batchData = [];

        foreach ($accounts as $acc) {
            // Chance this account has a record on a given day (80-100% => some gaps)
            $dailyChance = rand(80, 100);

            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                // skip some days to create gaps
                if (rand(1, 100) > $dailyChance) {
                    continue;
                }

                // --- choose master/channel for this record ---
                $master = $acc['default_master'];
                if (rand(1, 100) <= $switchMasterChance) {
                    // pick a different master occasionally to create diversity
                    $otherMasters = array_values(array_diff($masters, [$acc['default_master']]));
                    if (!empty($otherMasters)) {
                        $master = $otherMasters[array_rand($otherMasters)];
                    }
                }

                $channel = $acc['default_channel'];
                if (rand(1, 100) <= $switchChannelChance) {
                    $otherChannels = array_values(array_diff($channels, [$acc['default_channel']]));
                    if (!empty($otherChannels)) {
                        $channel = $otherChannels[array_rand($otherChannels)];
                    }
                }

                // ONE record per account per day
                $min = $this->generateMinBet($master);
                $max = $this->generateMaxBet($master, $min);

                // Base turnover depends on master and channel slightly to diversify
                $baseTurnover = rand(8000, 120000);

                // bump or reduce based on channel popularity (simple heuristic)
                $channelMultiplier = match ($channel) {
                    'DK' => 1.05,
                    'VN' => 0.95,
                    'TH' => 1.00,
                    'PH' => 0.9,
                    default => 1.0,
                };

                $baseTurnover = (int) round($baseTurnover * $channelMultiplier);

                $turnover = $this->adjustTurnoverByMaster($master, $baseTurnover);
                $winlose = $this->calculateWinLose($master, $turnover);

                $lp = 0.00;
                if ($turnover > 0) {
                    // Calculate Win/Loss as a percentage of Turnover and cap the result
                    $lp = round(($winlose / $turnover) * 100, 2);
                    $lp = max(-100.00, min(100.00, $lp));
                }

                $createdAt = $date->copy()->addHours(rand(0, 23))->addMinutes(rand(0, 59));

                $batchData[] = [
                    'account' => $acc['account'],
                    'channel' => $channel,
                    'master' => $master,
                    'trandate' => $date->format('Y-m-d'),
                    'min' => $min,
                    'max' => $max,
                    'count' => rand(30, 600),
                    'turnover' => $turnover,
                    'winlose' => $winlose,
                    'lp' => $lp,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ];

                $totalRecords++;

                if (count($batchData) >= $batchSize) {
                    DB::table('bets')->insert($batchData);
                    $batchData = [];
                    echo "Inserted {$totalRecords} records...\n";
                }
            }
        }

        // Insert remaining records
        if (!empty($batchData)) {
            DB::table('bets')->insert($batchData);
        }

        echo "Bet seeder completed! Generated {$totalRecords} bet records.\n";
    }

    /**
     * Generate minimum bet amount based on master type
     */
    private function generateMinBet(string $master): int
    {
        return match ($master) {
            'DKDK' => rand(10, 60),
            'DKAO' => rand(20, 120),
            default => rand(10, 60),
        };
    }

    /**
     * Generate maximum bet amount based on master type and min bet
     */
    private function generateMaxBet(string $master, int $minBet): int
    {
        $multiplier = match ($master) {
            'DKDK' => rand(20, 100),
            'DKAO' => rand(40, 200),
            default => rand(20, 100),
        };

        return $minBet * $multiplier;
    }

    /**
     * Adjust turnover based on master type
     */
    private function adjustTurnoverByMaster(string $master, float $baseTurnover): float
    {
        $multiplier = match ($master) {
            'DKDK' => rand(80, 140) / 100,
            'DKAO' => rand(85, 135) / 100,
            default => 1,
        };

        return round($baseTurnover * $multiplier, 2);
    }

    /**
     * Calculate win/lose based on house edge for different game types
     */
    private function calculateWinLose(string $master, float $turnover): float
    {
        // House edge percentages (negative means house wins)
        $houseEdge = match ($master) {
            'DKDK' => rand(-8, -3) / 100,
            'DKAO' => rand(-6, -1) / 100,
            default => rand(-10, -5) / 100,
        };

        // Add some variance - sometimes players win
        $variance = (rand(-60, 120) / 100); // wider variance for realism
        $actualEdge = $houseEdge * (1 + $variance);

        // Ensure the result is within reasonable bounds
        $winlose = $turnover * $actualEdge;

        // Cap extreme wins/losses
        $maxWin = $turnover * 0.6; // Maximum win is 50% of turnover
        $maxLoss = $turnover * -0.9; // Maximum loss is 80% of turnover

        return round(max($maxLoss, min($maxWin, $winlose)), 2);
    }
}
