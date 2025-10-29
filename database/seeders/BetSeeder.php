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
     */
    public function run(): void
    {
        // Clear existing bet data
        DB::table('bets')->truncate();

        // Get all channels
        $channels = ['VN', 'SG', 'MY', 'TH'];
        
        // Define masters (betting types/games)
        $masters = [
            'SPORTBOOK',
            'LIVECASINO',
            'SLOTS',
            'POKER',
            'LOTTERY',
            'ESPORTS',
            'VIRTUAL'
        ];

        // Define account prefixes for different regions
        $accountPrefixes = [
            'VN' => ['VN', 'VNN', 'VNA'],
            'SG' => ['SG', 'SGP', 'SGA'],
            'MY' => ['MY', 'MYS', 'MYA'],
            'TH' => ['TH', 'THA', 'THB']
        ];

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
                    
                    // Generate 1-5 records per channel per master per day
                    $recordsPerDay = rand(1, 5);
                    
                    for ($i = 0; $i < $recordsPerDay; $i++) {
                        
                        // Generate realistic account number
                        $prefix = $accountPrefixes[$channel][array_rand($accountPrefixes[$channel])];
                        $account = $prefix . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);

                        // Generate realistic betting amounts and counts
                        $minBet = $this->generateMinBet($master);
                        $maxBet = $this->generateMaxBet($master, $minBet);
                        $betCount = rand(50, 500);
                        
                        // Calculate turnover (total bet amount)
                        $baseTurnover = rand(10000, 100000);
                        $turnover = $this->adjustTurnoverByMaster($master, $baseTurnover);
                        
                        // Calculate win/lose (house edge varies by game type)
                        $winlose = $this->calculateWinLose($master, $turnover);
                        
                        // Generate LP (loyalty points) - usually 0.1% to 1% of turnover
                        $lp = round($turnover * (rand(10, 100) / 10000), 2);

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
        return match($master) {
            'SPORTBOOK' => rand(10, 50),
            'LIVECASINO' => rand(25, 100),
            'SLOTS' => rand(1, 10),
            'POKER' => rand(50, 200),
            'LOTTERY' => rand(5, 20),
            'ESPORTS' => rand(10, 30),
            'VIRTUAL' => rand(5, 25),
            default => rand(10, 50),
        };
    }

    /**
     * Generate maximum bet amount based on master type and min bet
     */
    private function generateMaxBet(string $master, int $minBet): int
    {
        $multiplier = match($master) {
            'SPORTBOOK' => rand(20, 100),
            'LIVECASINO' => rand(40, 200),
            'SLOTS' => rand(10, 50),
            'POKER' => rand(50, 300),
            'LOTTERY' => rand(10, 100),
            'ESPORTS' => rand(15, 80),
            'VIRTUAL' => rand(20, 100),
            default => rand(20, 100),
        };

        return $minBet * $multiplier;
    }

    /**
     * Adjust turnover based on master type
     */
    private function adjustTurnoverByMaster(string $master, float $baseTurnover): float
    {
        $multiplier = match($master) {
            'SPORTBOOK' => rand(80, 120) / 100, // Sports betting is popular
            'LIVECASINO' => rand(90, 130) / 100, // High-value games
            'SLOTS' => rand(60, 110) / 100, // Many small bets
            'POKER' => rand(70, 140) / 100, // Variable based on tournaments
            'LOTTERY' => rand(40, 80) / 100, // Lower frequency, higher amounts
            'ESPORTS' => rand(50, 90) / 100, // Growing but still niche
            'VIRTUAL' => rand(30, 70) / 100, // Least popular
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
        $houseEdge = match($master) {
            'SPORTBOOK' => rand(-8, -3) / 100, // 3-8% house edge
            'LIVECASINO' => rand(-5, -1) / 100, // 1-5% house edge
            'SLOTS' => rand(-15, -5) / 100, // 5-15% house edge
            'POKER' => rand(-10, -3) / 100, // 3-10% rake
            'LOTTERY' => rand(-30, -15) / 100, // 15-30% house edge
            'ESPORTS' => rand(-8, -4) / 100, // 4-8% house edge
            'VIRTUAL' => rand(-12, -6) / 100, // 6-12% house edge
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