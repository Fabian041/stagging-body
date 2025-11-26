<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Production Stock Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Your existing CSS remains the same */
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
            line-height: 1.4;
        }

        .dashboard {
            max-width: 100%;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            flex-shrink: 0;
        }

        .header-content h1 {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 4px;
            color: var(--primary);
        }

        .header-content .subtitle {
            color: var(--text-muted);
            font-size: 0.95rem;
            font-weight: 500;
        }

        .header-status {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .status-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(0, 212, 170, 0.15);
            border-radius: 16px;
            border: 1px solid rgba(0, 212, 170, 0.3);
            font-size: 0.9rem;
            font-weight: 600;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--primary);
            animation: pulse 2s infinite;
            /* Add these for the enhanced animation */
            animation: statusPulse 1.5s ease-in-out infinite;
            box-shadow: 0 0 0 0 rgba(0, 212, 170, 0.7);
        }

        @keyframes statusPulse {
            0% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(0, 212, 170, 0.7);
            }

            50% {
                transform: scale(1.1);
                box-shadow: 0 0 0 4px rgba(0, 212, 170, 0);
            }

            100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(0, 212, 170, 0);
            }
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
                transform: scale(1);
            }

            50% {
                opacity: 0.7;
                transform: scale(1.1);
            }
        }

        .datetime {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text);
            background: rgba(255, 255, 255, 0.05);
            padding: 8px 16px;
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        .main-content {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 16px;
        }

        .production-lines {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
        }

        .line-section {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            padding: 20px;
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary);
        }

        .line-count {
            background: var(--secondary);
            padding: 6px 12px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
        }

        .lines-grid {
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-height: 500px;
            overflow-y: auto;
            padding-right: 4px;
            /* Remove scroll-behavior since we're handling it with JS */
        }

        .lines-grid::-webkit-scrollbar {
            width: 6px;
        }

        .lines-grid::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 3px;
        }

        .lines-grid::-webkit-scrollbar-thumb {
            background: rgba(0, 212, 170, 0.3);
            border-radius: 3px;
            transition: background 0.2s ease;
        }

        .lines-grid::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 212, 170, 0.6);
        }

        .line-card {
            display: grid;
            grid-template-columns: 1fr auto auto;
            grid-template-rows: auto auto auto;
            align-items: center;
            gap: 10px;
            padding: 16px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            border: 1px solid transparent;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            min-height: 130px;
            flex-shrink: 0;
        }

        .line-card:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--border);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        .line-card.updating {
            animation: highlight 1.5s ease;
        }

        .line-card.danger {
            border-left: 4px solid var(--danger);
            background: rgba(231, 76, 60, 0.05);
        }

        .line-card.warning {
            border-left: 4px solid var(--warning);
            background: rgba(253, 203, 110, 0.05);
        }

        @keyframes highlight {
            0% {
                background: rgba(0, 212, 170, 0.15);
            }

            100% {
                background: rgba(255, 255, 255, 0.05);
            }
        }

        .line-name {
            font-weight: 600;
            font-size: 1rem;
            color: var(--text);
        }

        .stock-value {
            font-size: 1.4rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            transition: all 0.3s ease;
            color: var(--text);
        }

        .trend {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.9rem;
            padding: 6px 10px;
            border-radius: 6px;
            font-weight: 600;
        }

        .trend.up {
            color: var(--primary);
            background: rgba(0, 212, 170, 0.12);
            border: 1px solid rgba(0, 212, 170, 0.2);
        }

        .trend.down {
            color: var(--danger);
            background: rgba(231, 76, 60, 0.12);
            border: 1px solid rgba(231, 76, 60, 0.2);
        }

        .stock-range {
            grid-column: 1 / -1;
            margin-top: 10px;
        }

        .range-bar {
            height: 6px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
            position: relative;
            overflow: visible;
            margin-bottom: 6px;
        }

        .range-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--danger), var(--warning), var(--primary));
            border-radius: 3px;
            transition: all 0.5s ease;
        }

        .range-labels {
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .range-min,
        .range-max {
            font-weight: 600;
        }

        .range-current {
            position: absolute;
            top: -24px;
            transform: translateX(-50%);
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text);
            background: var(--secondary);
            padding: 4px 8px;
            border-radius: 4px;
            white-space: nowrap;
            border: 1px solid var(--border);
        }

        .line-meta {
            grid-column: 1 / -1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-top: 8px;
            padding-top: 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .utilization {
            font-weight: 600;
            font-size: 0.85rem;
            padding: 4px 8px;
            border-radius: 4px;
            background: rgba(255, 255, 255, 0.05);
        }

        .utilization.low {
            color: var(--danger);
            background: rgba(231, 76, 60, 0.1);
        }

        .utilization.medium {
            color: var(--warning);
            background: rgba(253, 203, 110, 0.1);
        }

        .utilization.high {
            color: var(--primary);
            background: rgba(0, 212, 170, 0.1);
        }

        .updated-time {
            font-weight: 500;
            color: var(--text);
        }

        .charts-panel {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border-radius: var(--radius);
            border: 1px solid var(--border);
            padding: 20px;
            box-shadow: var(--shadow);
            display: flex;
            flex-direction: column;
        }

        .chart-header {
            margin-bottom: 20px;
            flex-shrink: 0;
        }

        .chart-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 6px;
            color: var(--primary);
        }

        .chart-subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .chart-container {
            flex: 1;
            min-height: 250px;
            position: relative;
        }

        .kpi-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 20px;
            flex-shrink: 0;
        }

        .kpi-card {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border-radius: var(--radius);
            padding: 16px;
            border: 1px solid var(--border);
            text-align: center;
            transition: all 0.3s ease;
        }

        .kpi-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .kpi-label {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-bottom: 8px;
            font-weight: 600;
        }

        .kpi-value {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary);
        }

        .kpi-subtext {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 4px;
            font-weight: 500;
        }

        @media (max-width: 1280px) {
            .main-content {
                grid-template-columns: 1fr;
                grid-template-rows: auto auto;
                gap: 20px;
            }

            .production-lines {
                order: 1;
            }

            .charts-panel {
                order: 2;
                min-height: 400px;
            }
        }

        @media (max-width: 1024px) {
            .production-lines {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 12px;
            }

            .dashboard {
                height: auto;
                min-height: 100vh;
            }

            .header {
                flex-direction: column;
                gap: 12px;
                text-align: center;
                padding: 16px;
            }

            .header-content h1 {
                font-size: 1.4rem;
            }

            .kpi-grid {
                grid-template-columns: 1fr;
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
                        <span class="line-count">{{ count($lines['die_casting']) }} Products</span>
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
                                    <span class="updated-time">Updated: {{ $line['updated']->format('H:i:s') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Machining -->
                <div class="line-section">
                    <div class="section-header">
                        <h2 class="section-title">Machining</h2>
                        <span class="line-count">{{ count($lines['machining']) }} Products</span>
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
                                    <span class="updated-time">Updated: {{ $line['updated']->format('H:i:s') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Assembling -->
                <div class="line-section">
                    <div class="section-header">
                        <h2 class="section-title">Assembling</h2>
                        <span class="line-count">{{ count($lines['assembling']) }} Products</span>
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
                                    <span class="updated-time">Updated: {{ $line['updated']->format('H:i:s') }}</span>
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
                        <div class="kpi-subtext">Production Lines Monitoring</div>
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
                        <div class="kpi-subtext">Across All Production Lines</div>
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

        // Ultra-smooth continuous scrolling
        class SmoothAutoScroller {
            constructor(container) {
                this.container = container;
                this.scrollSpeed = 3.5; // Increased from 0.3 to 1.5 for more noticeable scrolling
                this.isScrolling = true;
                this.userInteracted = false;
                this.animationId = null;
                this.scrollDirection = 1; // 1 for down, -1 for up
                this.init();
            }

            init() {
                // Start continuous scrolling
                this.startContinuousScroll();

                // User interaction handling
                this.container.addEventListener('mouseenter', () => {
                    this.userInteracted = true;
                    this.stopContinuousScroll();
                });

                this.container.addEventListener('mouseleave', () => {
                    this.userInteracted = false;
                    // Wait a bit before resuming
                    setTimeout(() => {
                        if (!this.userInteracted) {
                            this.startContinuousScroll();
                        }
                    }, 2000);
                });

                this.container.addEventListener('scroll', () => {
                    if (!this.userInteracted) {
                        this.userInteracted = true;
                        this.stopContinuousScroll();
                    }
                });

                // Auto-resume after user inactivity
                let scrollTimeout;
                this.container.addEventListener('scroll', () => {
                    clearTimeout(scrollTimeout);
                    scrollTimeout = setTimeout(() => {
                        this.userInteracted = false;
                        this.startContinuousScroll();
                    }, 3000); // Reduced from 8000 to 3000 (3 seconds)
                });
            }

            startContinuousScroll() {
                if (this.animationId || this.userInteracted) return;

                console.log('Starting continuous scroll for:', this.container.id);

                let lastTime = null;

                const scroll = (currentTime) => {
                    if (!this.isScrolling || this.userInteracted) {
                        console.log('Stopping scroll due to user interaction');
                        return;
                    }

                    // Calculate time delta for frame-rate independent scrolling
                    if (!lastTime) lastTime = currentTime;
                    const deltaTime = currentTime - lastTime;
                    lastTime = currentTime;

                    const currentScroll = this.container.scrollTop;
                    const maxScroll = this.container.scrollHeight - this.container.clientHeight;

                    // Change direction if at boundaries
                    if (currentScroll >= maxScroll - 5) {
                        this.scrollDirection = -1; // Scroll up
                    } else if (currentScroll <= 5) {
                        this.scrollDirection = 1; // Scroll down
                    }

                    // Frame-rate independent scrolling (pixels per millisecond)
                    const scrollAmount = (this.scrollSpeed * deltaTime) / 16.67; // Normalize to 60fps

                    // Calculate new scroll position
                    let newScroll = currentScroll + (scrollAmount * this.scrollDirection);

                    // Ensure we stay within bounds
                    newScroll = Math.max(0, Math.min(maxScroll, newScroll));

                    // Apply the scroll
                    this.container.scrollTop = newScroll;

                    // Continue animation
                    this.animationId = requestAnimationFrame(scroll);
                };

                this.isScrolling = true;
                this.animationId = requestAnimationFrame(scroll);
            }

            stopContinuousScroll() {
                this.isScrolling = false;
                if (this.animationId) {
                    cancelAnimationFrame(this.animationId);
                    this.animationId = null;
                }
            }

            // Method to temporarily speed up for manual updates
            speedUpTemporarily() {
                const originalSpeed = this.scrollSpeed;
                this.scrollSpeed = 1.0; // Faster speed

                setTimeout(() => {
                    this.scrollSpeed = originalSpeed;
                }, 1000);
            }
        }

        // Initialize chart
        const ctx = document.getElementById('stockChart').getContext('2d');

        // Prepare initial data - include ALL lines
        const allLines = [
            @foreach ($lines['die_casting'] as $line)
                {
                    name: '{{ $line['name'] }}',
                    stock: {{ $line['stock'] }},
                    min_stock: {{ $line['min_stock'] }},
                    max_stock: {{ $line['max_stock'] }},
                    trend: '{{ $line['trend'] }}',
                    utilization_percent: {{ $line['utilization_percent'] }}
                },
            @endforeach
            @foreach ($lines['machining'] as $line)
                {
                    name: '{{ $line['name'] }}',
                    stock: {{ $line['stock'] }},
                    min_stock: {{ $line['min_stock'] }},
                    max_stock: {{ $line['max_stock'] }},
                    trend: '{{ $line['trend'] }}',
                    utilization_percent: {{ $line['utilization_percent'] }}
                },
            @endforeach
            @foreach ($lines['assembling'] as $line)
                {
                    name: '{{ $line['name'] }}',
                    stock: {{ $line['stock'] }},
                    min_stock: {{ $line['min_stock'] }},
                    max_stock: {{ $line['max_stock'] }},
                    trend: '{{ $line['trend'] }}',
                    utilization_percent: {{ $line['utilization_percent'] }}
                },
            @endforeach
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
                            color: 'rgba(255, 255, 255, 0.7)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: 'rgba(255, 255, 255, 0.7)',
                            maxRotation: 45
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
                console.log('Starting real-time updates...');

                // Update a random line every 3-8 seconds for more realistic updates
                setInterval(() => {
                    this.addToUpdateQueue();
                }, Math.random() * 5000 + 3000); // 3-8 seconds

                // Process update queue more frequently
                setInterval(() => {
                    this.processQueue();
                }, 1000);
            }

            addToUpdateQueue() {
                if (this.updateQueue.length >= 3) return; // Limit queue size

                const randomLine = this.lines[Math.floor(Math.random() * this.lines.length)];
                if (!this.updateQueue.includes(randomLine)) {
                    this.updateQueue.push(randomLine);
                    console.log(`Added ${randomLine} to update queue`);
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
                    // Don't add back to queue on error to avoid infinite loops
                }

                this.isUpdating = false;
            }

            async updateLine(lineName) {
                console.log(`Updating line: ${lineName}`);

                try {
                    const response = await fetch(`/api/stocks/mock/${lineName}`);

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const data = await response.json();
                    console.log('Received data:', data);

                    this.updateLineCard(data);
                    this.updateChart(data);
                    this.updateKPIs();

                } catch (error) {
                    console.error('Failed to update line:', error);
                    throw error;
                }
            }

            updateLineCard(data) {
                const card = document.querySelector(`[data-line="${data.name}"]`);
                if (!card) {
                    console.warn(`Card for line ${data.name} not found`);
                    return;
                }

                // Add updating animation
                card.classList.add('updating');

                const stockValue = card.querySelector('.stock-value');
                const trend = card.querySelector('.trend');
                const updatedTime = card.querySelector('.updated-time');
                const rangeFill = card.querySelector('.range-fill');
                const rangeCurrent = card.querySelector('.range-current');
                const utilization = card.querySelector('.utilization');

                // Calculate utilization percentage
                const utilizationPercent = ((data.stock - data.min_stock) / (data.max_stock - data.min_stock)) * 100;
                const fillWidth = Math.min(Math.max(utilizationPercent, 0), 100);

                // Update stock value with animation
                const currentStock = parseInt(stockValue.textContent.replace(/,/g, ''));
                this.animateValue(stockValue, currentStock, data.stock, 500);

                // Update trend
                trend.className = `trend ${data.trend}`;
                trend.innerHTML = data.trend === 'up' ? '↗' : '↘';

                // Update range visualization
                setTimeout(() => {
                    rangeFill.style.width = `${fillWidth}%`;
                    rangeCurrent.style.left = `${fillWidth}%`;
                    rangeCurrent.textContent = data.stock.toLocaleString();
                }, 250);

                // Update utilization
                utilization.textContent = `${Math.round(utilizationPercent)}% utilized`;
                utilization.className =
                    `utilization ${getUtilizationClass(data.stock, data.min_stock, data.max_stock)}`;

                // Update card status
                card.className = `line-card ${getStockStatusClass(data.stock, data.min_stock, data.max_stock)}`;

                // Update time
                const now = new Date();
                updatedTime.textContent =
                    `Updated: ${now.toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit', second: '2-digit'})}`;

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
                // Calculate average utilization from all utilization elements
                const utilizations = Array.from(document.querySelectorAll('.utilization'))
                    .map(el => {
                        const text = el.textContent;
                        const match = text.match(/(\d+)%/);
                        return match ? parseInt(match[1]) : 0;
                    })
                    .filter(val => !isNaN(val));

                if (utilizations.length > 0) {
                    const avgUtilization = Math.round(utilizations.reduce((a, b) => a + b, 0) / utilizations.length);
                    document.getElementById('avg-utilization').textContent = `${avgUtilization}%`;
                }
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

        // Initialize everything when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Wait a bit for everything to render properly
            setTimeout(() => {
                // Initialize ultra-smooth auto-scrollers for each section
                const dieCastingScroller = new SmoothAutoScroller(document.getElementById(
                    'die-casting-lines'));
                const machiningScroller = new SmoothAutoScroller(document.getElementById(
                    'machining-lines'));
                const assemblingScroller = new SmoothAutoScroller(document.getElementById(
                    'assembling-lines'));

                console.log('Auto-scrollers initialized');
            }, 100); // 1 second delay to ensure DOM is fully ready

            // Initialize stock updater
            const stockUpdater = new StockUpdater();

            // Add click handlers for line cards
            document.querySelectorAll('.line-card').forEach(card => {
                card.addEventListener('click', function() {
                    const lineName = this.getAttribute('data-line');
                    console.log(`Manual update requested for: ${lineName}`);
                    // Force an update for the clicked line
                    if (!stockUpdater.updateQueue.includes(lineName)) {
                        stockUpdater.updateQueue.unshift(lineName);
                    }
                });
            });

            console.log('Dashboard initialized with ultra-smooth continuous scrolling');
        });
    </script>
</body>

</html>
