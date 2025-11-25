<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        // Initial static data for the dashboard with min/max values
        $lines = [
            'die_casting' => [
                ['name' => 'DI01', 'stock' => 2450, 'min_stock' => 1500, 'max_stock' => 3000, 'trend' => 'up', 'updated' => now()->subMinutes(2), 'status' => 'active'],
                ['name' => 'DI02', 'stock' => 1890, 'min_stock' => 1200, 'max_stock' => 2500, 'trend' => 'down', 'updated' => now()->subMinutes(1), 'status' => 'active'],
            ],
            'machining' => [
                ['name' => 'EI12', 'stock' => 3200, 'min_stock' => 2000, 'max_stock' => 4000, 'trend' => 'up', 'updated' => now()->subMinutes(3), 'status' => 'active'],
                ['name' => 'EI13', 'stock' => 2750, 'min_stock' => 1800, 'max_stock' => 3500, 'trend' => 'up', 'updated' => now()->subMinutes(5), 'status' => 'active'],
                ['name' => 'EI14', 'stock' => 1980, 'min_stock' => 1500, 'max_stock' => 2800, 'trend' => 'down', 'updated' => now()->subMinutes(2), 'status' => 'active'],
                ['name' => 'EI15', 'stock' => 2100, 'min_stock' => 1600, 'max_stock' => 3000, 'trend' => 'up', 'updated' => now()->subMinutes(4), 'status' => 'active'],
                ['name' => 'EI16', 'stock' => 1850, 'min_stock' => 1400, 'max_stock' => 2700, 'trend' => 'down', 'updated' => now()->subMinutes(1), 'status' => 'active'],
                ['name' => 'EI17', 'stock' => 2300, 'min_stock' => 1700, 'max_stock' => 3200, 'trend' => 'up', 'updated' => now()->subMinutes(6), 'status' => 'active'],
                ['name' => 'EI18', 'stock' => 1950, 'min_stock' => 1500, 'max_stock' => 2900, 'trend' => 'down', 'updated' => now()->subMinutes(2), 'status' => 'active'],
            ],
            'assembling' => [
                ['name' => 'CI12', 'stock' => 1500, 'min_stock' => 1000, 'max_stock' => 2200, 'trend' => 'up', 'updated' => now()->subMinutes(3), 'status' => 'active'],
                ['name' => 'CI13', 'stock' => 1650, 'min_stock' => 1100, 'max_stock' => 2400, 'trend' => 'down', 'updated' => now()->subMinutes(1), 'status' => 'active'],
                ['name' => 'CI14', 'stock' => 1420, 'min_stock' => 900, 'max_stock' => 2000, 'trend' => 'up', 'updated' => now()->subMinutes(4), 'status' => 'active'],
                ['name' => 'CI15', 'stock' => 1780, 'min_stock' => 1200, 'max_stock' => 2600, 'trend' => 'up', 'updated' => now()->subMinutes(2), 'status' => 'active'],
                ['name' => 'CI16', 'stock' => 1920, 'min_stock' => 1300, 'max_stock' => 2800, 'trend' => 'down', 'updated' => now()->subMinutes(5), 'status' => 'active'],
                ['name' => 'CI17', 'stock' => 1350, 'min_stock' => 800, 'max_stock' => 1900, 'trend' => 'up', 'updated' => now()->subMinutes(3), 'status' => 'active'],
                ['name' => 'CI18', 'stock' => 1680, 'min_stock' => 1100, 'max_stock' => 2500, 'trend' => 'down', 'updated' => now()->subMinutes(1), 'status' => 'active'],
            ]
        ];

        // Pre-calculate utilization and status for each line
        foreach ($lines as &$section) {
            foreach ($section as &$line) {
                $line['utilization_percent'] = $this->calculateUtilization($line['stock'], $line['min_stock'], $line['max_stock']);
                $line['status_class'] = $this->getStockStatusClass($line['stock'], $line['min_stock'], $line['max_stock']);
                $line['utilization_class'] = $this->getUtilizationClass($line['stock'], $line['min_stock'], $line['max_stock']);
            }
        }

        return view('pages.stocks.dashboard', compact('lines'));
    }

    public function mockData()
    {
        // Generate slightly modified data for the "real-time" effect
        $lines = [
            'die_casting' => [
                ['name' => 'DI01', 'stock' => $this->randomizeStock(2450), 'min_stock' => 1500, 'max_stock' => 3000, 'trend' => $this->randomTrend(), 'updated' => now(), 'status' => 'active'],
                ['name' => 'DI02', 'stock' => $this->randomizeStock(1890), 'min_stock' => 1200, 'max_stock' => 2500, 'trend' => $this->randomTrend(), 'updated' => now(), 'status' => 'active'],
            ],
            'machining' => [
                ['name' => 'EI12', 'stock' => $this->randomizeStock(3200), 'min_stock' => 2000, 'max_stock' => 4000, 'trend' => $this->randomTrend(), 'updated' => now(), 'status' => 'active'],
                ['name' => 'EI13', 'stock' => $this->randomizeStock(2750), 'min_stock' => 1800, 'max_stock' => 3500, 'trend' => $this->randomTrend(), 'updated' => now(), 'status' => 'active'],
                ['name' => 'EI14', 'stock' => $this->randomizeStock(1980), 'min_stock' => 1500, 'max_stock' => 2800, 'trend' => $this->randomTrend(), 'updated' => now(), 'status' => 'active'],
                ['name' => 'EI15', 'stock' => $this->randomizeStock(2100), 'min_stock' => 1600, 'max_stock' => 3000, 'trend' => $this->randomTrend(), 'updated' => now(), 'status' => 'active'],
                ['name' => 'EI16', 'stock' => $this->randomizeStock(1850), 'min_stock' => 1400, 'max_stock' => 2700, 'trend' => $this->randomTrend(), 'updated' => now(), 'status' => 'active'],
                ['name' => 'EI17', 'stock' => $this->randomizeStock(2300), 'min_stock' => 1700, 'max_stock' => 3200, 'trend' => $this->randomTrend(), 'updated' => now(), 'status' => 'active'],
                ['name' => 'EI18', 'stock' => $this->randomizeStock(1950), 'min_stock' => 1500, 'max_stock' => 2900, 'trend' => $this->randomTrend(), 'updated' => now(), 'status' => 'active'],
            ],
            'assembling' => [
                ['name' => 'CI12', 'stock' => $this->randomizeStock(1500), 'min_stock' => 1000, 'max_stock' => 2200, 'trend' => $this->randomTrend(), 'updated' => now(), 'status' => 'active'],
                ['name' => 'CI13', 'stock' => $this->randomizeStock(1650), 'min_stock' => 1100, 'max_stock' => 2400, 'trend' => $this->randomTrend(), 'updated' => now(), 'status' => 'active'],
                ['name' => 'CI14', 'stock' => $this->randomizeStock(1420), 'min_stock' => 900, 'max_stock' => 2000, 'trend' => $this->randomTrend(), 'updated' => now(), 'status' => 'active'],
                ['name' => 'CI15', 'stock' => $this->randomizeStock(1780), 'min_stock' => 1200, 'max_stock' => 2600, 'trend' => $this->randomTrend(), 'updated' => now(), 'status' => 'active'],
                ['name' => 'CI16', 'stock' => $this->randomizeStock(1920), 'min_stock' => 1300, 'max_stock' => 2800, 'trend' => $this->randomTrend(), 'updated' => now(), 'status' => 'active'],
                ['name' => 'CI17', 'stock' => $this->randomizeStock(1350), 'min_stock' => 800, 'max_stock' => 1900, 'trend' => $this->randomTrend(), 'updated' => now(), 'status' => 'active'],
                ['name' => 'CI18', 'stock' => $this->randomizeStock(1680), 'min_stock' => 1100, 'max_stock' => 2500, 'trend' => $this->randomTrend(), 'updated' => now(), 'status' => 'active'],
            ]
        ];

        return response()->json($lines);
    }

    public function mockLineData($line)
    {
        // Base values for each line with min/max
        $baseValues = [
            'DI01' => ['stock' => 2450, 'min' => 1500, 'max' => 3000],
            'DI02' => ['stock' => 1890, 'min' => 1200, 'max' => 2500],
            'EI12' => ['stock' => 3200, 'min' => 2000, 'max' => 4000],
            'EI13' => ['stock' => 2750, 'min' => 1800, 'max' => 3500],
            'EI14' => ['stock' => 1980, 'min' => 1500, 'max' => 2800],
            'EI15' => ['stock' => 2100, 'min' => 1600, 'max' => 3000],
            'EI16' => ['stock' => 1850, 'min' => 1400, 'max' => 2700],
            'EI17' => ['stock' => 2300, 'min' => 1700, 'max' => 3200],
            'EI18' => ['stock' => 1950, 'min' => 1500, 'max' => 2900],
            'CI12' => ['stock' => 1500, 'min' => 1000, 'max' => 2200],
            'CI13' => ['stock' => 1650, 'min' => 1100, 'max' => 2400],
            'CI14' => ['stock' => 1420, 'min' => 900, 'max' => 2000],
            'CI15' => ['stock' => 1780, 'min' => 1200, 'max' => 2600],
            'CI16' => ['stock' => 1920, 'min' => 1300, 'max' => 2800],
            'CI17' => ['stock' => 1350, 'min' => 800, 'max' => 1900],
            'CI18' => ['stock' => 1680, 'min' => 1100, 'max' => 2500]
        ];

        $baseData = $baseValues[$line] ?? ['stock' => 2000, 'min' => 1000, 'max' => 3000];
        $stock = $this->randomizeStock($baseData['stock']);
        
        return response()->json([
            'name' => $line,
            'stock' => $stock,
            'min_stock' => $baseData['min'],
            'max_stock' => $baseData['max'],
            'trend' => $this->randomTrend(),
            'updated' => now()->toISOString(),
            'status' => 'active'
        ]);
    }

    private function randomizeStock($baseValue)
    {
        // Randomly adjust stock by ±30 units (smaller range for more realistic changes)
        $variation = rand(-30, 30);
        return max(0, $baseValue + $variation); // Ensure stock doesn't go negative
    }

    private function randomTrend()
    {
        return rand(0, 1) ? 'up' : 'down';
    }

    private function calculateUtilization($stock, $minStock, $maxStock)
    {
        if ($maxStock - $minStock === 0) return 0;
        return round((($stock - $minStock) / ($maxStock - $minStock)) * 100);
    }

    private function getStockStatusClass($stock, $minStock, $maxStock)
    {
        $utilization = ($stock - $minStock) / ($maxStock - $minStock);
        if ($utilization < 0.3) return 'danger';
        if ($utilization < 0.7) return 'warning';
        return '';
    }

    private function getUtilizationClass($stock, $minStock, $maxStock)
    {
        $utilization = ($stock - $minStock) / ($maxStock - $minStock);
        if ($utilization < 0.3) return 'low';
        if ($utilization < 0.7) return 'medium';
        return 'high';
    }
}