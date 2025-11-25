<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Stock Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #00d4aa;
            --primary-dark: #00b894;
            --secondary: #2d3436;
            --accent: #ffeaa7;
            --warning: #fdcb6e;
            --danger: #e74c3c;
            --text: #f5f6fa;
            --text-muted: #b2bec3;
            --bg-dark: #0f1419;
            --bg-card: rgba(255, 255, 255, 0.08);
            --bg-card-hover: rgba(255, 255, 255, 0.12);
            --border: rgba(255, 255, 255, 0.1);
            --radius: 12px;
            --shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background: var(--bg-dark);
            color: var(--text);
            min-height: 100vh;
            padding: 16px;
            line-height: 1.5;
            /* REMOVED: overflow: hidden */
        }

        .dashboard {
            max-width: 100%;
            min-height: 100vh;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            flex-shrink: 0;
        }

        .header-content h1 {
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .header-content .subtitle {
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .header-status {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: rgba(0, 212, 170, 0.1);
            border-radius: 16px;
            border: 1px solid rgba(0, 212, 170, 0.3);
            font-size: 0.85rem;
        }

        .status-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--primary);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        /* Main Content Grid */
        .main-content {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 16px;
            flex: 1;
            min-height: 600px;
            /* Minimum height instead of fixed */
        }

        /* Production Lines */
        .production-lines {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            height: 100%;
        }

        .line-section {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            padding: 16px;
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            min-height: 400px;
            /* Minimum height for sections */
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary);
        }

        .line-count {
            background: var(--secondary);
            padding: 3px 6px;
            border-radius: 10px;
            font-size: 0.75rem;
            color: var(--text-muted);
        }

        .lines-grid {
            display: flex;
            flex-direction: column;
            gap: 8px;
            overflow-y: auto;
            flex: 1;
            padding-right: 4px;
            max-height: 500px;
            /* Maximum height with scroll */
        }

        /* Custom scrollbar */
        .lines-grid::-webkit-scrollbar {
            width: 6px;
        }

        .lines-grid::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 3px;
        }

        .lines-grid::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 3px;
        }

        .lines-grid::-webkit-scrollbar-thumb:hover {
            background: var(--primary-dark);
        }

        .line-card {
            display: grid;
            grid-template-columns: 1fr auto auto;
            align-items: center;
            gap: 8px;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            border: 1px solid transparent;
            transition: all 0.2s ease;
            cursor: pointer;
            position: relative;
            flex-shrink: 0;
            /* Prevent cards from shrinking */
        }

        .line-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--border);
        }

        .line-card.updating {
            animation: highlight 1s ease;
        }

        .line-card.danger {
            border-left: 3px solid var(--danger);
        }

        .line-card.warning {
            border-left: 3px solid var(--warning);
        }

        @keyframes highlight {
            0% {
                background: rgba(0, 212, 170, 0.2);
            }

            100% {
                background: rgba(255, 255, 255, 0.05);
            }
        }

        .line-name {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .stock-value {
            font-size: 1.1rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            transition: all 0.3s ease;
        }

        .trend {
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 0.8rem;
            padding: 3px 6px;
            border-radius: 6px;
        }

        .trend.up {
            color: var(--primary);
            background: rgba(0, 212, 170, 0.1);
        }

        .trend.down {
            color: var(--danger);
            background: rgba(231, 76, 60, 0.1);
        }

        .stock-range {
            grid-column: 1 / -1;
            margin-top: 6px;
        }

        .range-bar {
            height: 4px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            position: relative;
            overflow: hidden;
            margin-bottom: 4px;
        }

        .range-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--danger), var(--warning), var(--primary));
            border-radius: 2px;
            transition: all 0.5s ease;
        }

        .range-labels {
            display: flex;
            justify-content: space-between;
            font-size: 0.65rem;
            color: var(--text-muted);
        }

        .range-min,
        .range-max {
            font-weight: 500;
        }

        .range-current {
            position: absolute;
            top: -14px;
            transform: translateX(-50%);
            font-size: 0.65rem;
            font-weight: 600;
            color: var(--text);
            background: var(--secondary);
            padding: 1px 4px;
            border-radius: 3px;
            white-space: nowrap;
        }

        .line-meta {
            grid-column: 1 / -1;
            display: flex;
            justify-content: space-between;
            font-size: 0.7rem;
            color: var(--text-muted);
            margin-top: 6px;
        }

        .utilization {
            font-weight: 600;
        }

        .utilization.low {
            color: var(--danger);
        }

        .utilization.medium {
            color: var(--warning);
        }

        .utilization.high {
            color: var(--primary);
        }

        /* Charts Panel */
        .charts-panel {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            padding: 16px;
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
            min-height: 400px;
        }

        .chart-header {
            margin-bottom: 16px;
            flex-shrink: 0;
        }

        .chart-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .chart-subtitle {
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .chart-container {
            flex: 1;
            min-height: 200px;
            position: relative;
        }

        /* KPI Cards */
        .kpi-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 16px;
            flex-shrink: 0;
        }

        .kpi-card {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border-radius: var(--radius);
            padding: 16px;
            border: 1px solid var(--border);
        }

        .kpi-label {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .kpi-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
        }

        .kpi-subtext {
            font-size: 0.7rem;
            color: var(--text-muted);
            margin-top: 2px;
        }

        /* Responsive Design */
        @media (max-width: 1600px) {
            .main-content {
                grid-template-columns: 1fr 350px;
            }

            .production-lines {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 1440px) {
            .main-content {
                grid-template-columns: 1fr 320px;
            }

            .line-card {
                grid-template-columns: 1fr auto;
            }

            .trend {
                grid-column: 2;
                grid-row: 1;
            }
        }

        @media (max-width: 1366px) {
            .main-content {
                grid-template-columns: 1fr 300px;
            }

            .production-lines {
                gap: 12px;
            }

            .line-section {
                padding: 12px;
            }

            .lines-grid {
                max-height: 450px;
            }
        }

        @media (max-width: 1280px) {
            .main-content {
                grid-template-columns: 1fr;
                grid-template-rows: auto auto;
            }

            .production-lines {
                order: 1;
            }

            .charts-panel {
                order: 2;
                min-height: 300px;
            }

            .lines-grid {
                max-height: none;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 12px;
            }
        }

        @media (max-width: 1024px) {
            .production-lines {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .lines-grid {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            }

            .line-card {
                min-width: 0;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 12px;
            }

            .header {
                flex-direction: column;
                gap: 10px;
                text-align: center;
                padding: 12px;
            }

            .header-content h1 {
                font-size: 1.2rem;
            }

            .kpi-grid {
                grid-template-columns: 1fr;
            }

            .lines-grid {
                grid-template-columns: 1fr;
            }

            .main-content {
                min-height: auto;
            }
        }

        /* High DPI Optimizations */
        @media (min-width: 1920px) {
            .dashboard {
                max-width: 1800px;
            }

            .main-content {
                grid-template-columns: 1fr 420px;
            }

            .line-section {
                padding: 20px;
            }

            .lines-grid {
                gap: 12px;
                max-height: 600px;
            }

            .line-card {
                padding: 16px;
            }
        }

        /* Ensure body can scroll if content overflows */
        @media (max-height: 800px) {
            .dashboard {
                min-height: auto;
                height: auto;
            }

            .main-content {
                min-height: auto;
            }

            .lines-grid {
                max-height: 300px;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <h1>Production Stock Dashboard</h1>
                <div class="subtitle">Real-time monitoring with stock range indicators</div>
            </div>
            <div class="header-status">
                <div class="status-indicator">
                    <div class="status-dot"></div>
                    <span>LIVE</span>
                </div>
                <div id="current-time" class="datetime"></div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Production Lines -->
            <div class="production-lines">
                <!-- Die Casting -->
                <div class="line-section">
                    <div class="section-header">
                        <h2 class="section-title">Die Casting</h2>
                        <span class="line-count">{{ count($lines['die_casting']) }} lines</span>
                    </div>
                    <div class="lines-grid" id="die-casting-lines">
                        @foreach ($lines['die_casting'] as $line)
                            <div class="line-card {{ $line['status_class'] }}" data-line="{{ $line['name'] }}"
                                data-min="{{ $line['min_stock'] }}" data-max="{{ $line['max_stock'] }}">
                                <div class="line-name">{{ $line['name'] }}</div>
                                <div class="stock-value">{{ number_format($line['stock']) }}</div>
                                <div class="trend {{ $line['trend'] }}">
                                    @if ($line['trend'] == 'up')
                                        ↗
                                    @else
                                        ↘
                                    @endif
                                </div>

                                <div class="stock-range">
                                    <div class="range-bar">
                                        @php
                                            $utilizationPercent =
                                                (($line['stock'] - $line['min_stock']) /
                                                    ($line['max_stock'] - $line['min_stock'])) *
                                                100;
                                            $fillWidth = min(max($utilizationPercent, 0), 100);
                                        @endphp
                                        <div class="range-fill" style="width: {{ $fillWidth }}%">
                                            <div class="range-current" style="left: {{ $fillWidth }}%">
                                                {{ number_format($line['stock']) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="range-labels">
                                        <span class="range-min">Min: {{ number_format($line['min_stock']) }}</span>
                                        <span class="range-max">Max: {{ number_format($line['max_stock']) }}</span>
                                    </div>
                                </div>

                                <div class="line-meta">
                                    <span class="utilization {{ $line['utilization_class'] }}">
                                        {{ $line['utilization_percent'] }}% utilized
                                    </span>
                                    <span>Updated: {{ $line['updated']->format('H:i:s') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Machining -->
                <div class="line-section">
                    <div class="section-header">
                        <h2 class="section-title">Machining</h2>
                        <span class="line-count">{{ count($lines['machining']) }} lines</span>
                    </div>
                    <div class="lines-grid" id="machining-lines">
                        @foreach ($lines['machining'] as $line)
                            <div class="line-card {{ $line['status_class'] }}" data-line="{{ $line['name'] }}"
                                data-min="{{ $line['min_stock'] }}" data-max="{{ $line['max_stock'] }}">
                                <div class="line-name">{{ $line['name'] }}</div>
                                <div class="stock-value">{{ number_format($line['stock']) }}</div>
                                <div class="trend {{ $line['trend'] }}">
                                    @if ($line['trend'] == 'up')
                                        ↗
                                    @else
                                        ↘
                                    @endif
                                </div>

                                <div class="stock-range">
                                    <div class="range-bar">
                                        @php
                                            $utilizationPercent =
                                                (($line['stock'] - $line['min_stock']) /
                                                    ($line['max_stock'] - $line['min_stock'])) *
                                                100;
                                            $fillWidth = min(max($utilizationPercent, 0), 100);
                                        @endphp
                                        <div class="range-fill" style="width: {{ $fillWidth }}%">
                                            <div class="range-current" style="left: {{ $fillWidth }}%">
                                                {{ number_format($line['stock']) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="range-labels">
                                        <span class="range-min">Min: {{ number_format($line['min_stock']) }}</span>
                                        <span class="range-max">Max: {{ number_format($line['max_stock']) }}</span>
                                    </div>
                                </div>

                                <div class="line-meta">
                                    <span class="utilization {{ $line['utilization_class'] }}">
                                        {{ $line['utilization_percent'] }}% utilized
                                    </span>
                                    <span>Updated: {{ $line['updated']->format('H:i:s') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Assembling -->
                <div class="line-section">
                    <div class="section-header">
                        <h2 class="section-title">Assembling</h2>
                        <span class="line-count">{{ count($lines['assembling']) }} lines</span>
                    </div>
                    <div class="lines-grid" id="assembling-lines">
                        @foreach ($lines['assembling'] as $line)
                            <div class="line-card {{ $line['status_class'] }}" data-line="{{ $line['name'] }}"
                                data-min="{{ $line['min_stock'] }}" data-max="{{ $line['max_stock'] }}">
                                <div class="line-name">{{ $line['name'] }}</div>
                                <div class="stock-value">{{ number_format($line['stock']) }}</div>
                                <div class="trend {{ $line['trend'] }}">
                                    @if ($line['trend'] == 'up')
                                        ↗
                                    @else
                                        ↘
                                    @endif
                                </div>

                                <div class="stock-range">
                                    <div class="range-bar">
                                        @php
                                            $utilizationPercent =
                                                (($line['stock'] - $line['min_stock']) /
                                                    ($line['max_stock'] - $line['min_stock'])) *
                                                100;
                                            $fillWidth = min(max($utilizationPercent, 0), 100);
                                        @endphp
                                        <div class="range-fill" style="width: {{ $fillWidth }}%">
                                            <div class="range-current" style="left: {{ $fillWidth }}%">
                                                {{ number_format($line['stock']) }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="range-labels">
                                        <span class="range-min">Min: {{ number_format($line['min_stock']) }}</span>
                                        <span class="range-max">Max: {{ number_format($line['max_stock']) }}</span>
                                    </div>
                                </div>

                                <div class="line-meta">
                                    <span class="utilization {{ $line['utilization_class'] }}">
                                        {{ $line['utilization_percent'] }}% utilized
                                    </span>
                                    <span>Updated: {{ $line['updated']->format('H:i:s') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Charts Panel -->
            <div class="charts-panel">
                <div class="chart-header">
                    <h2 class="chart-title">Stock Overview</h2>
                    <div class="chart-subtitle">Current stock levels with min/max ranges</div>
                </div>
                <div class="chart-container">
                    <canvas id="stockChart"></canvas>
                </div>

                <div class="kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-label">Total Active Lines</div>
                        <div class="kpi-value" id="total-lines">
                            {{ count($lines['die_casting']) + count($lines['machining']) + count($lines['assembling']) }}
                        </div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-label">Avg Utilization</div>
                        <div class="kpi-value" id="avg-utilization">
                            @php
                                $allUtilizations = [];
                                foreach ($lines as $section) {
                                    foreach ($section as $line) {
                                        $allUtilizations[] = $line['utilization_percent'];
                                    }
                                }
                                $avgUtilization =
                                    count($allUtilizations) > 0
                                        ? round(array_sum($allUtilizations) / count($allUtilizations))
                                        : 0;
                            @endphp
                            {{ $avgUtilization }}%
                        </div>
                        <div class="kpi-subtext">Across all production lines</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize date and time
        function updateDateTime() {
            const now = new Date();
            document.getElementById('current-time').textContent =
                now.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric'
                }) + ' • ' +
                now.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit'
                });
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Helper functions for stock status
        function getStockStatusClass(stock, minStock, maxStock) {
            const utilization = (stock - minStock) / (maxStock - minStock);
            if (utilization < 0.3) return 'danger';
            if (utilization < 0.7) return 'warning';
            return '';
        }

        function getUtilizationClass(stock, minStock, maxStock) {
            const utilization = (stock - minStock) / (maxStock - minStock);
            if (utilization < 0.3) return 'low';
            if (utilization < 0.7) return 'medium';
            return 'high';
        }

        // Initialize chart
        const ctx = document.getElementById('stockChart').getContext('2d');

        // Prepare initial data
        const allLines = [
            ...@json($lines['die_casting']),
            ...@json($lines['machining']),
            ...@json($lines['assembling'])
        ];

        const stockChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: allLines.map(line => line.name),
                datasets: [{
                    label: 'Current Stock',
                    data: allLines.map(line => line.stock),
                    backgroundColor: allLines.map(line =>
                        line.trend === 'up' ? 'rgba(0, 212, 170, 0.7)' : 'rgba(231, 76, 60, 0.7)'
                    ),
                    borderColor: allLines.map(line =>
                        line.trend === 'up' ? 'rgba(0, 212, 170, 1)' : 'rgba(231, 76, 60, 1)'
                    ),
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(45, 52, 54, 0.9)',
                        titleColor: '#f5f6fa',
                        bodyColor: '#f5f6fa',
                        borderColor: 'rgba(255, 255, 255, 0.1)',
                        borderWidth: 1,
                        callbacks: {
                            afterBody: function(context) {
                                const line = allLines.find(l => l.name === context[0].label);
                                if (line) {
                                    return [
                                        `Min: ${line.min_stock.toLocaleString()}`,
                                        `Max: ${line.max_stock.toLocaleString()}`,
                                        `Utilization: ${line.utilization_percent}%`
                                    ];
                                }
                                return [];
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(255, 255, 255, 0.1)'
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)',
                            maxTicksLimit: 6
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)',
                            maxRotation: 45,
                            font: {
                                size: 10
                            }
                        }
                    }
                }
            }
        });

        // Individual line update system
        class StockUpdater {
            constructor() {
                this.lines = allLines.map(line => line.name);
                this.updateQueue = [];
                this.isUpdating = false;
                this.startUpdates();
            }

            startUpdates() {
                // Update a random line every 2-5 seconds for more realistic individual updates
                setInterval(() => {
                    this.addToUpdateQueue();
                }, Math.random() * 3000 + 2000);

                // Process update queue
                setInterval(() => {
                    this.processQueue();
                }, 1000);
            }

            addToUpdateQueue() {
                const randomLine = this.lines[Math.floor(Math.random() * this.lines.length)];
                if (!this.updateQueue.includes(randomLine)) {
                    this.updateQueue.push(randomLine);
                }
            }

            async processQueue() {
                if (this.isUpdating || this.updateQueue.length === 0) return;

                this.isUpdating = true;
                const lineToUpdate = this.updateQueue.shift();

                try {
                    await this.updateLine(lineToUpdate);
                } catch (error) {
                    console.error('Error updating line:', error);
                }

                this.isUpdating = false;
            }

            async updateLine(lineName) {
                const response = await fetch(`/api/stocks/mock/${lineName}`);
                const data = await response.json();

                this.updateLineCard(data);
                this.updateChart(data);
                this.updateKPIs();
            }

            updateLineCard(data) {
                const card = document.querySelector(`[data-line="${data.name}"]`);
                if (!card) return;

                // Add updating animation
                card.classList.add('updating');

                const stockValue = card.querySelector('.stock-value');
                const trend = card.querySelector('.trend');
                const updatedTime = card.querySelector('.line-meta span:last-child');
                const rangeFill = card.querySelector('.range-fill');
                const rangeCurrent = card.querySelector('.range-current');
                const utilization = card.querySelector('.utilization');

                // Calculate utilization percentage
                const utilizationPercent = ((data.stock - data.min_stock) / (data.max_stock - data.min_stock)) * 100;
                const fillWidth = Math.min(Math.max(utilizationPercent, 0), 100);

                // Animate stock value
                this.animateValue(
                    stockValue,
                    parseInt(stockValue.textContent.replace(/,/g, '')),
                    data.stock,
                    500
                );

                // Update trend
                trend.className = `trend ${data.trend}`;
                trend.innerHTML = data.trend === 'up' ? '↗' : '↘';

                // Update range visualization
                rangeFill.style.width = `${fillWidth}%`;
                rangeCurrent.style.left = `${fillWidth}%`;
                rangeCurrent.textContent = data.stock.toLocaleString();

                // Update utilization
                utilization.textContent = `${Math.round(utilizationPercent)}% utilized`;
                utilization.className =
                    `utilization ${getUtilizationClass(data.stock, data.min_stock, data.max_stock)}`;

                // Update card status
                card.className = `line-card ${getStockStatusClass(data.stock, data.min_stock, data.max_stock)}`;
                card.classList.add('updating');

                // Update time
                const now = new Date();
                updatedTime.textContent = `Updated: ${now.toLocaleTimeString('en-US')}`;

                // Remove animation after delay
                setTimeout(() => {
                    card.classList.remove('updating');
                }, 1000);
            }

            updateChart(data) {
                const index = stockChart.data.labels.indexOf(data.name);
                if (index !== -1) {
                    stockChart.data.datasets[0].data[index] = data.stock;
                    stockChart.data.datasets[0].backgroundColor[index] =
                        data.trend === 'up' ? 'rgba(0, 212, 170, 0.7)' : 'rgba(231, 76, 60, 0.7)';
                    stockChart.data.datasets[0].borderColor[index] =
                        data.trend === 'up' ? 'rgba(0, 212, 170, 1)' : 'rgba(231, 76, 60, 1)';
                    stockChart.update('none');
                }
            }

            updateKPIs() {
                // Calculate average utilization
                const utilizations = Array.from(document.querySelectorAll('.utilization'))
                    .map(el => parseInt(el.textContent));
                const avgUtilization = Math.round(utilizations.reduce((a, b) => a + b, 0) / utilizations.length);
                document.getElementById('avg-utilization').textContent = `${avgUtilization}%`;
            }

            animateValue(element, start, end, duration) {
                let startTimestamp = null;
                const step = (timestamp) => {
                    if (!startTimestamp) startTimestamp = timestamp;
                    const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                    const value = Math.floor(progress * (end - start) + start);
                    element.textContent = value.toLocaleString();
                    if (progress < 1) {
                        window.requestAnimationFrame(step);
                    }
                };
                window.requestAnimationFrame(step);
            }
        }

        // Initialize the updater
        const stockUpdater = new StockUpdater();

        // Add click handlers for line cards
        document.querySelectorAll('.line-card').forEach(card => {
            card.addEventListener('click', function() {
                const lineName = this.getAttribute('data-line');
                // Force an update for the clicked line
                stockUpdater.updateQueue.unshift(lineName);
            });
        });
    </script>
</body>

</html>
