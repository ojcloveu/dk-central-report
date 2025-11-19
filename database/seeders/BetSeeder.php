<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Bet;
use App\Models\Channel;
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
        $masters = [
            'DKDK',
            'DKAO',
        ];

        // Define account prefixes for different regions
        $masterPrefixes = ['DKDK', 'DKAO'];
        $channelPrefixes = ['DK', 'VN', 'TH', 'PH'];

        // Generate full list of master+channel prefixes (e.g., DKDKDK, DKAOVN)
        $masterChannelPrefixes = [];
        foreach ($masterPrefixes as $mp) {
            foreach ($channelPrefixes as $cp) {
                // The structure is Master + Channel (e.g., DKDK + DK)
                $masterChannelPrefixes[$cp][] = $mp . $cp;
            }
        }

        // Generate data for the last 3 months
        $startDate = Carbon::now()->subMonths(3)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        echo "Generating bet data from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}\n";

        $totalRecords = 0;
        $batchSize = 1000;
        $batchData = [];

        // Iterate through each day in the date range
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {

            // Skip some days randomly to simulate realistic data patterns
            if (rand(1, 100) <= 5) { // 5% chance to skip a day
                continue;
            }

            foreach ($channels as $channel) {
                foreach ($masters as $master) {

                    // Records per day minimum.
                    $recordsPerDay = rand(2, 5);

                    for ($i = 0; $i < $recordsPerDay; $i++) {
                        // Select the appropriate master-channel prefixes list
                        $availablePrefixes = $masterChannelPrefixes[$channel] ?? [];

                        if (empty($availablePrefixes)) {
                            // Fallback if combination is unexpected, though should not happen with new logic
                            $account = $master . $channel . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
                        } else {
                            // Select a prefix that matches the current master type (e.g., DKDK)
                            $masterSpecificPrefixes = array_filter($availablePrefixes, function ($p) use ($master) {
                                return str_starts_with($p, $master);
                            });

                            $prefix = $masterSpecificPrefixes ? $masterSpecificPrefixes[array_rand($masterSpecificPrefixes)] : $availablePrefixes[array_rand($availablePrefixes)];

                            // Generate the final account number: PREFIX + AccountType (AABA) + ID (000-999)
                            // Using a fixed suffix 'BA' for the example, adjust as needed.
                            $suffix = ['BA', 'AA', 'CA'][array_rand(['BA', 'AA', 'CA'])];
                            $account = $prefix . $suffix . str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);
                        }


                        // Generate realistic betting amounts and counts
                        $minBet = $this->generateMinBet($master);
                        $maxBet = $this->generateMaxBet($master, $minBet);
                        $betCount = rand(50, 500);

                        // Calculate turnover (total bet amount)
                        $baseTurnover = rand(10000, 100000);
                        $turnover = $this->adjustTurnoverByMaster($master, $baseTurnover);

                        // Calculate win/lose (house edge varies by game type)
                        $winlose = $this->calculateWinLose($master, $turnover);

                        $lp = 0.00;
                        if ($turnover > 0) {
                            // Calculate Win/Loss as a percentage of Turnover and cap the result
                            $lp = round(($winlose / $turnover) * 100, 2);
                            $lp = max(-100.00, min(100.00, $lp));
                        }


                        $batchData[] = [
                            'account' => $account,
                            'channel' => $channel,
                            'trandate' => $date->format('Y-m-d'),
                            'master' => $master,
                            'min' => $minBet,
                            'max' => $maxBet,
                            'count' => $betCount,
                            'turnover' => $turnover,
                            'winlose' => $winlose,
                            'lp' => $lp,
                            'created_at' => $date->copy()->addHours(rand(0, 23))->addMinutes(rand(0, 59)),
                            'updated_at' => $date->copy()->addHours(rand(0, 23))->addMinutes(rand(0, 59)),
                        ];

                        $totalRecords++;

                        // Insert in batches for better performance
                        if (count($batchData) >= $batchSize) {
                            DB::table('bets')->insert($batchData);
                            $batchData = [];
                            echo "Inserted {$totalRecords} records...\n";
                        }
                    }
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
            'DKDK' => rand(10, 50),
            'DKAO' => rand(25, 100),
            default => rand(10, 50),
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
            'DKDK' => rand(80, 120) / 100,
            'DKAO' => rand(90, 130) / 100,
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
            'DKAO' => rand(-5, -1) / 100,
            default => rand(-10, -5) / 100,
        };

        // Add some variance - sometimes players win
        $variance = (rand(-50, 100) / 100); // -50% to +100% variance
        $actualEdge = $houseEdge * (1 + $variance);

        // Ensure the result is within reasonable bounds
        $winlose = $turnover * $actualEdge;

        // Cap extreme wins/losses
        $maxWin = $turnover * 0.5; // Maximum win is 50% of turnover
        $maxLoss = $turnover * -0.8; // Maximum loss is 80% of turnover

        return round(max($maxLoss, min($maxWin, $winlose)), 2);
    }
}