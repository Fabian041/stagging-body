<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Pulling Day Shift - 05-Jul-25</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href={{ asset('assets/modules/fontawesome/css/all.min.css') }}>
    <style>
        body {
            background-color: #111;
            color: #fff;
            font-family: monospace;
        }

        h2 {
            color: #00ff99;
            text-shadow: 1px 1px 2px black;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .bg-orange {
            background-color: orange !important;
            color: black;
        }

        .highlight-rfid {
            background-color: #ffeeba !important;
            color: black;
        }

        .highlight-889t {
            background-color: #c3e6cb !important;
            color: #155724 !important;
        }

        .flip {
            display: inline-block;
            transition: all 0.3s ease;
            transform-style: preserve-3d;
            transform-origin: bottom center;
        }

        .animate-flip {
            animation: flipAnimation 0.6s ease;
        }

        @keyframes flipAnimation {
            0% {
                transform: rotateX(0deg);
                opacity: 1;
            }

            50% {
                transform: rotateX(90deg);
                opacity: 0;
            }

            51% {
                transform: rotateX(-90deg);
            }

            100% {
                transform: rotateX(0deg);
                opacity: 1;
            }
        }

        /* Continuous blinking highlight styles */
        @keyframes continuousBlink {

            0%,
            100% {
                background-color: var(--highlight-color);
            }

            50% {
                background-color: transparent;
            }
        }

        .highlight-beep-direct {
            --highlight-color: #12341E;
            /* Pastel green */
            animation: continuousBlink 1s infinite;
        }

        .highlight-beep-stock {
            --highlight-color: #4D3A0A;
            /* Pastel yellow */
            animation: continuousBlink 1s infinite;
        }

        /* Make sure table cells inherit the highlight */
        .highlight-beep-direct td,
        .highlight-beep-stock td {
            background-color: inherit !important;
        }

        /* Status indicator styles */
        #sse-connection-status {
            position: fixed;
            bottom: 20px;
            left: 20px;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            z-index: 9999;
            transition: all 0.3s ease;
        }

        /* Table cell styles for quantities */
        .bg-success.bg-opacity-75 {
            background-color: rgba(40, 167, 69, 0.75) !important;
        }

        .bg-success.bg-opacity-25 {
            background-color: rgba(40, 167, 69, 0.25) !important;
        }

        .bg-warning.bg-opacity-75 {
            background-color: rgba(255, 193, 7, 0.75) !important;
        }

        .bg-warning.bg-opacity-25 {
            background-color: rgba(255, 193, 7, 0.25) !important;
        }

        /* Tab styles */
        .nav-tabs .nav-link {
            color: #ccc;
            background-color: #333;
            border-color: #444;
        }

        .nav-tabs .nav-link.active {
            color: #fff;
            background-color: #222;
            border-color: #444;
        }

        /* Card styles */
        .card {
            background-color: #222;
            border-color: #333;
        }

        .card-header {
            border-bottom-color: #333;
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="card mb-4 border-dark bg-light">
            <div class="card-header bg-dark text-white py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-calendar-date me-2"></i>
                        <strong>PRODUCTION DATE SELECTOR</strong>
                    </div>
                    <div class="badge bg-warning text-dark">
                        {{ Carbon\Carbon::parse($selectedDate ?? now())->format('D, M j Y') }}
                    </div>
                </div>
            </div>
            <div class="card-body p-2 bg-secondary bg-opacity-10">
                <form method="GET" action="{{ route('dashboard.prodPlan') }}" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-dark text-white">
                                <i class="bi bi-calendar3"></i>
                            </span>
                            <input type="date" class="form-control border-dark bg-light" name="date"
                                value="{{ $selectedDate ?? now()->format('Y-m-d') }}" style="font-weight: bold;">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="btn-group" role="group">
                            <button type="submit" class="btn btn-dark">
                                <i class="bi bi-funnel-fill me-1"></i> FILTER
                            </button>
                            @if (request()->has('date'))
                                <a href="{{ route('dashboard.prodPlan') }}" class="btn btn-outline-dark">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> RESET
                                </a>
                            @endif
                            <button type="button" class="btn btn-outline-dark" onclick="navigateDate(-1)">
                                <i class="fas fa-arrow-left"></i>
                            </button>
                            <button type="button" class="btn btn-outline-dark" onclick="navigateDate(1)">
                                <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="text-uppercase fw-bold" style="letter-spacing: 1px;">
                <i class="bi bi-clipboard2-data me-2"></i>
                PRODUCTION PULLING PLAN - {{ Carbon\Carbon::parse($selectedDate ?? now())->format('l, j F Y') }}
            </h2>
            <a class="btn btn-outline-warning" href="/pulling/settings">
                <i class="bi bi-gear-fill"></i> SETTINGS
            </a>
        </div>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-4" id="lineTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="line3-tab" data-bs-toggle="tab" data-bs-target="#line3"
                    type="button" role="tab">AS003</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="line4-tab" data-bs-toggle="tab" data-bs-target="#line4" type="button"
                    role="tab">AS004</button>
            </li>
        </ul>

        <div class="tab-content" id="lineTabsContent">
            <!-- AS003 Tab -->
            <div class="tab-pane fade show active" id="line3" role="tabpanel" aria-labelledby="line3-tab">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-center align-middle table-dark">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Dock</th>
                                <th>Cycle</th>
                                <th>Back No</th>
                                <th>Order</th>
                                <th>Direct Pulling</th>
                                <th>Stock Chute</th>
                                <th>Prod Time</th>
                                <th>Break</th>
                                <th>Working Time</th>
                                <th>Delivery Time</th>
                                <th>Balance Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($grouped['AS003'] ?? collect() as $key => $rows)
                                @php
                                    [$customer, $delivery] = explode('|', $key);
                                    $rowspan = $rows->count();
                                    $dock = $rows->first()->dock ?? '--';
                                @endphp
                                @foreach ($rows as $index => $item)
                                    <tr>
                                        @if ($index === 0)
                                            <td rowspan="{{ $rowspan }}"><span
                                                    class="flip">{{ $customer }}</span></td>
                                            <td rowspan="{{ $rowspan }}"><span
                                                    class="flip">{{ $dock }}</span></td>
                                        @endif
                                        <td><span class="flip">{{ $item->cycle }}</span></td>
                                        <td><span class="flip">{{ $item->back_no }}</span></td>
                                        <td><span class="flip">{{ $item->order_qty }}</span></td>
                                        <td
                                            class="{{ $item->direct_pulling_qty > 0 ? 'bg-success bg-opacity-75 fw-bold text-white' : 'bg-success bg-opacity-25 fw-bold text-success' }}">
                                            <span class="flip" data-type="direct-pulling"
                                                data-item-id="{{ $item->id }}">
                                                {{ $item->direct_pulling_qty > 0 ? $item->direct_pulling_qty : '0' }}
                                            </span>
                                        </td>
                                        <td
                                            class="{{ $item->stock_chute_qty > 0 ? 'bg-warning bg-opacity-75 fw-bold text-white' : 'bg-warning bg-opacity-25 text-warning' }}">
                                            <span class="flip" data-type="stock-chute"
                                                data-item-id="{{ $item->id }}">
                                                {{ $item->stock_chute_qty > 0 ? $item->stock_chute_qty : '0' }}
                                            </span>
                                        </td>
                                        <td><span class="flip">{{ $item->prod_time }}</span></td>
                                        <td><span class="flip">--</span></td>
                                        <td>
                                            <span class="flip">
                                                {{ $item->working_start ?? '--' }} - {{ $item->working_end ?? '--' }}
                                                <br>
                                                <small class="text-warning">duration :
                                                    {{ $item->working_duration ?? '--' }}</small>
                                            </span>
                                        </td>
                                        @if ($index === 0)
                                            <td rowspan="{{ $rowspan }}"><span
                                                    class="flip">{{ $delivery }}</span></td>
                                            <td rowspan="{{ $rowspan }}">
                                                <span
                                                    class="flip {{ str_starts_with($item->balance_time, '-') ? 'text-danger' : '' }}">
                                                    {{ $item->balance_time ?? '--' }}
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center">No data for AS003.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- AS004 Tab -->
            <div class="tab-pane fade" id="line4" role="tabpanel" aria-labelledby="line4-tab">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-center align-middle table-dark">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Dock</th>
                                <th>Cycle</th>
                                <th>Back No</th>
                                <th>Order</th>
                                <th>Direct Pulling</th>
                                <th>Stock Chute</th>
                                <th>Prod Time</th>
                                <th>Break</th>
                                <th>Working Time</th>
                                <th>Delivery Time</th>
                                <th>Balance Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($grouped['AS004'] ?? collect() as $key => $rows)
                                @php
                                    [$customer, $delivery] = explode('|', $key);
                                    $rowspan = $rows->count();
                                    $dock = $rows->first()->dock ?? '--';
                                @endphp
                                @foreach ($rows as $index => $item)
                                    <tr>
                                        @if ($index === 0)
                                            <td rowspan="{{ $rowspan }}"><span
                                                    class="flip">{{ $customer }}</span></td>
                                            <td rowspan="{{ $rowspan }}"><span
                                                    class="flip">{{ $dock }}</span></td>
                                        @endif
                                        <td><span class="flip">{{ $item->cycle }}</span></td>
                                        <td><span class="flip">{{ $item->back_no }}</span></td>
                                        <td><span class="flip">{{ $item->order_qty }}</span></td>
                                        <td
                                            class="{{ $item->direct_pulling_qty > 0 ? 'bg-success bg-opacity-75 fw-bold text-white' : 'bg-success bg-opacity-25 fw-bold text-success' }}">
                                            <span class="flip" data-type="direct-pulling"
                                                data-item-id="{{ $item->id }}">
                                                {{ $item->direct_pulling_qty > 0 ? $item->direct_pulling_qty : '0' }}
                                            </span>
                                        </td>
                                        <td
                                            class="{{ $item->stock_chute_qty > 0 ? 'bg-warning bg-opacity-75 fw-bold text-white' : 'bg-warning bg-opacity-25 text-warning' }}">
                                            <span class="flip" data-type="stock-chute"
                                                data-item-id="{{ $item->id }}">
                                                {{ $item->stock_chute_qty > 0 ? $item->stock_chute_qty : '0' }}
                                            </span>
                                        </td>
                                        <td><span class="flip">{{ $item->prod_time }}</span></td>
                                        <td><span class="flip">--</span></td>
                                        <td>
                                            <span class="flip">
                                                {{ $item->working_start ?? '--' }} - {{ $item->working_end ?? '--' }}
                                                <br>
                                                <small class="text-warning">duration :
                                                    {{ $item->working_duration ?? '--' }}</small>
                                            </span>
                                        </td>
                                        @if ($index === 0)
                                            <td rowspan="{{ $rowspan }}"><span
                                                    class="flip">{{ $delivery }}</span></td>
                                            <td rowspan="{{ $rowspan }}">
                                                <span
                                                    class="flip {{ str_starts_with($item->balance_time, '-') ? 'text-danger' : '' }}">
                                                    {{ $item->balance_time ?? '--' }}
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center">No data for AS004.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        class ProductionPlanSSEClient {
            constructor() {
                this.eventSource = null;
                this.statusElement = null;
                this.currentDate = this.getCurrentDate();
                this.highlightTimeouts = new Set(); // Add this line
                this.lastHighlightTime = 0; // Add this line
                this.init();
            }


            init() {
                this.createStatusIndicator();
                this.addFlipStyles();
                this.connect();
                this.setupDateChangeListener();
                this.setupErrorHandling();
            }

            getCurrentDate() {
                const dateInput = document.querySelector('input[name="date"]');
                return dateInput ? dateInput.value : new Date().toISOString().split('T')[0];
            }

            createStatusIndicator() {
                this.statusElement = document.createElement('div');
                this.statusElement.id = 'sse-connection-status';
                this.statusElement.style.cssText = `
            position: fixed;
            bottom: 20px;
            left: 20px;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            z-index: 9999;
            transition: all 0.3s ease;
        `;
                document.body.appendChild(this.statusElement);
            }

            addFlipStyles() {
                const style = document.createElement('style');
                style.textContent = `
            .flip {
                display: inline-block;
                transition: all 0.3s ease;
                transform-style: preserve-3d;
                transform-origin: bottom center;
            }
            .animate-flip {
                animation: flipAnimation 0.6s ease;
            }
            @keyframes flipAnimation {
                0% { transform: rotateX(0deg); opacity: 1; }
                50% { transform: rotateX(90deg); opacity: 0; }
                51% { transform: rotateX(-90deg); }
                100% { transform: rotateX(0deg); opacity: 1; }
            }
        `;
                document.head.appendChild(style);
            }

            connect() {
                if (this.eventSource) {
                    this.eventSource.close();
                }

                this.eventSource = new EventSource(`/stream/direct-pulling-updates?date=${this.currentDate}`);
                this.updateConnectionStatus('connecting');

                this.eventSource.onopen = () => {
                    this.updateConnectionStatus('connected');
                };

                this.eventSource.addEventListener('directPullingUpdate', (e) => {
                    const data = JSON.parse(e.data);
                    if (data.date === this.currentDate) {
                        this.handleUpdates(data.updates || []);
                        this.updateConnectionStatus('connected');
                    }
                });

                this.eventSource.onerror = (e) => {
                    console.error('SSE Error:', e);
                    this.updateConnectionStatus('disconnected');
                    this.reconnect();
                };
            }

            setupDateChangeListener() {
                const dateInput = document.querySelector('input[name="date"]');
                if (dateInput) {
                    dateInput.addEventListener('change', () => {
                        this.currentDate = this.getCurrentDate();
                        this.reconnect();
                    });
                }
            }

            updateConnectionStatus(status, message = '') {
                const statusConfig = {
                    connecting: {
                        text: '● Connecting to updates...',
                        style: 'background: #17a2b8; color: white;'
                    },
                    connected: {
                        text: '● Live Updates Active',
                        style: 'background: #28a745; color: white;'
                    },
                    disconnected: {
                        text: '● Connection Lost',
                        style: 'background: #dc3545; color: white;'
                    },
                    error: {
                        text: '● Update Error' + (message ? `: ${message}` : ''),
                        style: 'background: #ffc107; color: black;'
                    }
                };

                const config = statusConfig[status] || statusConfig.error;
                this.statusElement.textContent = config.text;
                this.statusElement.style.cssText += config.style;
            }

            handleUpdates(updates) {
                // Track all rows that need highlighting
                const rowsToHighlight = new Set();

                updates.forEach(item => {
                    // Find all rows containing this item
                    const rows = document.querySelectorAll(`tr:has([data-item-id="${item.id}"])`);

                    // Update quantities
                    this.updateQuantity(
                        `[data-item-id="${item.id}"][data-type="direct-pulling"]`,
                        item.direct_pulling_qty,
                        'success'
                    );
                    this.updateQuantity(
                        `[data-item-id="${item.id}"][data-type="stock-chute"]`,
                        item.stock_chute_qty,
                        'warning'
                    );

                    // Add rows to highlight set
                    rows.forEach(row => rowsToHighlight.add(row));
                });

                // Apply highlight to all affected rows
                this.highlightRows(Array.from(rowsToHighlight), 'mixed');
            }

            updateQuantity(selector, newValue, type) {
                const elements = document.querySelectorAll(selector);
                elements.forEach(el => {
                    const currentValue = parseInt(el.textContent) || 0;
                    if (currentValue !== newValue) {
                        el.textContent = newValue > 0 ? newValue : '0';
                        this.updateCellStyle(el.closest('td'), newValue, type);
                        this.animateChange(el.closest('td'));
                    }
                });
            }

            updateCellStyle(cell, value, type) {
                if (value > 0) {
                    cell.className = `bg-${type} bg-opacity-75 fw-bold text-white`;
                } else {
                    const textColor = type === 'success' ? 'text-success' : 'text-warning';
                    cell.className = `bg-${type} bg-opacity-25 fw-bold ${textColor}`;
                }
            }

            animateChange(element) {
                const flipElement = element.querySelector('.flip');
                if (flipElement) {
                    flipElement.classList.add('animate-flip');
                    setTimeout(() => flipElement.classList.remove('animate-flip'), 600);
                }
            }

            highlightRows(rows, updateType) {
                // First clear any existing highlights
                this.clearAllHighlights();

                // Store the current timestamp
                const highlightStartTime = Date.now();

                rows.forEach(row => {
                    // Remove all highlight classes first
                    row.classList.remove(
                        'highlight-beep-direct',
                        'highlight-beep-stock'
                    );

                    // Force reflow to reset animation
                    void row.offsetWidth;

                    // Add appropriate highlight class
                    const highlightClass = updateType === 'success' ?
                        'highlight-beep-direct' :
                        updateType === 'warning' ?
                        'highlight-beep-stock' :
                        'highlight-beep-direct';

                    row.classList.add(highlightClass);

                    // Set timeout to remove highlight after 1 minute
                    const timeoutId = setTimeout(() => {
                        row.classList.remove(highlightClass);
                        this.highlightTimeouts.delete(timeoutId);
                    }, 60000); // 60 seconds = 1 minute

                    this.highlightTimeouts.add(timeoutId);
                });

                // Store the highlight end time
                this.lastHighlightTime = highlightStartTime;
            }

            clearAllHighlights() {
                // Clear all existing highlight timeouts
                this.highlightTimeouts.forEach(timeoutId => {
                    clearTimeout(timeoutId);
                });
                this.highlightTimeouts.clear();

                // Remove all highlight classes
                document.querySelectorAll('.highlight-beep-direct, .highlight-beep-stock').forEach(el => {
                    el.classList.remove('highlight-beep-direct', 'highlight-beep-stock');
                });
            }

            isWithinHighlightPeriod() {
                return (Date.now() - this.lastHighlightTime) < 60000;
            }

            reconnect() {
                this.updateConnectionStatus('connecting', 'Reconnecting...');
                if (this.eventSource) {
                    this.eventSource.close();
                }
                setTimeout(() => this.connect(), 3000);
            }

            setupErrorHandling() {
                window.addEventListener('beforeunload', () => {
                    if (this.eventSource) {
                        this.eventSource.close();
                    }
                });
            }
        }

        // Initialize when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            window.prodPlanSSE = new ProductionPlanSSEClient();
        });

        // Date navigation function
        function navigateDate(days) {
            const currentDate = new Date(document.querySelector('input[name="date"]').value);
            currentDate.setDate(currentDate.getDate() + days);
            const newDate = currentDate.toISOString().split('T')[0];
            document.querySelector('input[name="date"]').value = newDate;
            document.querySelector('form').submit();
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
