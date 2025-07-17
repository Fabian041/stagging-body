<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Pulling Day Shift - 05-Jul-25</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

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
            transition: transform 0.3s ease, opacity 0.3s ease;
        }

        .flip.animate {
            transform: rotateX(-90deg);
            opacity: 0;
        }
    </style>
</head>

<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="fw-bold text-uppercase">Pulling Day - {{ Carbon\Carbon::now()->format('l, j F Y') }}</h2>
            <a class="btn btn-outline-warning" href="/pulling/settings">
                <i class="bi bi-gear-fill"></i> Setting
            </a>
        </div>

        <!-- Tab line -->
        <!-- ... head dan style tetap sama -->

        <!-- Tambahan tab AS004 -->
        <ul class="nav nav-tabs mb-4" id="lineTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="line1-tab" data-bs-toggle="tab" data-bs-target="#line1" type="button"
                    role="tab">AS001</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="line2-tab" data-bs-toggle="tab" data-bs-target="#line2" type="button"
                    role="tab">AS002</button>
            </li>
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
            <!-- AS001 -->
            <div class="tab-pane fade" id="line1" role="tabpanel" aria-labelledby="line1-tab">
                <div class="table-responsive">
                    <p class="text-center mt-4">Data untuk AS001 belum tersedia.</p>
                </div>
            </div>

            <!-- AS002 -->
            <div class="tab-pane fade" id="line2" role="tabpanel" aria-labelledby="line2-tab">
                <div class="table-responsive">
                    <p class="text-center mt-4">Data untuk AS002 belum tersedia.</p>
                </div>
            </div>

            <!-- AS003 -->
            <div class="tab-pane fade show active" id="line3" role="tabpanel" aria-labelledby="line3-tab">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-center align-middle table-dark">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Cycle</th>
                                <th>Back No</th>
                                <th>Qty/Pallet</th>
                                <th>Order</th>
                                <th>Stock</th>
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
                                @endphp
                                @foreach ($rows as $index => $item)
                                    <tr>
                                        @if ($index === 0)
                                            <td rowspan="{{ $rowspan }}"><span
                                                    class="flip">{{ $customer }}</span></td>
                                        @endif
                                        <td><span class="flip">{{ $item->cycle }}</span></td>
                                        <td><span class="flip">{{ $item->back_no }}</span></td>
                                        <td><span class="flip">{{ $item->qty_per_pallet }}</span></td>
                                        <td><span class="flip">{{ $item->order_qty }}</span></td>
                                        <td class="bg-warning text-dark"><span class="flip" data-key="stock">--</span>
                                        </td>
                                        <td><span class="flip">--</span></td>
                                        <td><span class="flip">--</span></td>
                                        <td><span class="flip">--</span></td>
                                        @if ($index === 0)
                                            <td rowspan="{{ $rowspan }}"><span
                                                    class="flip">{{ $delivery }}</span></td>
                                        @endif
                                        <td><span class="flip">--</span></td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center">Tidak ada data untuk AS003.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- AS004 -->
            <div class="tab-pane fade" id="line4" role="tabpanel" aria-labelledby="line4-tab">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-center align-middle table-dark">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Cycle</th>
                                <th>Back No</th>
                                <th>Qty/Pallet</th>
                                <th>Order</th>
                                <th>Stock</th>
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
                                @endphp
                                @foreach ($rows as $index => $item)
                                    <tr>
                                        @if ($index === 0)
                                            <td rowspan="{{ $rowspan }}"><span
                                                    class="flip">{{ $customer }}</span></td>
                                        @endif
                                        <td><span class="flip">{{ $item->cycle }}</span></td>
                                        <td><span class="flip">{{ $item->back_no }}</span></td>
                                        <td><span class="flip">{{ $item->qty_per_pallet }}</span></td>
                                        <td><span class="flip">{{ $item->order_qty }}</span></td>
                                        <td class="bg-warning text-dark"><span class="flip" data-key="stock">--</span>
                                        </td>
                                        <td><span class="flip">--</span></td>
                                        <td><span class="flip">--</span></td>
                                        <td><span class="flip">--</span></td>
                                        @if ($index === 0)
                                            <td rowspan="{{ $rowspan }}"><span
                                                    class="flip">{{ $delivery }}</span></td>
                                        @endif
                                        <td><span class="flip">--</span></td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center">Tidak ada data untuk AS004.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Audio -->
        <audio id="clickSound" src="{{ asset('sounds/click.mp3') }}" preload="auto"></audio>

        <script>
            function simulateUpdate() {
                const table = document.getElementById("flipTable");

                table.querySelectorAll("tr").forEach(row => {
                    const data = {
                        customer: row.querySelector("[data-key='customer']")?.textContent.trim(),
                        backno: row.querySelector("[data-key='backno']")?.textContent.trim(),
                        name: row.querySelector("[data-key='name']")?.textContent.trim(),
                        chute: row.querySelector("[data-key='chute']")?.textContent.trim(),
                        direct: row.querySelector("[data-key='direct']")?.textContent.trim(),
                        prod: row.querySelector("[data-key='prod']")?.textContent.trim(),
                        order: row.querySelector("[data-key='order']")?.textContent.trim(),
                        work: row.querySelector("[data-key='work']")?.textContent.trim(),
                        stock: row.querySelector("[data-key='stock']")?.textContent.trim(),
                    };

                    const newData = {
                        ...data,
                        chute: Math.floor(Math.random() * 40) + 10,
                        direct: Math.floor(Math.random() * 50),
                        prod: (Math.random() * 1.5).toFixed(2),
                        order: Math.floor(Math.random() * 100),
                        work: new Date().toLocaleTimeString('en-GB').slice(0, 5),
                        stock: Math.floor(Math.random() * 100) // update khusus kolom stock
                    };

                    // Update kolom stock saja dengan animasi flip
                    const stockEl = row.querySelector("[data-key='stock']");
                    if (stockEl && stockEl.textContent.trim() !== newData.stock.toString()) {
                        stockEl.classList.add("flip-animate");
                        setTimeout(() => {
                            stockEl.textContent = newData.stock;
                            stockEl.classList.remove("flip-animate");
                        }, 500);
                    }
                });
            }

            // CSS untuk animasi flip
            const style = document.createElement("style");
            style.innerHTML = `
            .flip-animate {
                animation: flip 0.5s ease-in-out;
            }
    
            @keyframes flip {
                0% {
                    transform: rotateX(0deg);
                }
                50% {
                    transform: rotateX(90deg);
                }
                100% {
                    transform: rotateX(0deg);
                }
            }
        `;
            document.head.appendChild(style);

            // Jalankan simulasi update setiap 5 detik
            setInterval(simulateUpdate, 5000);
        </script>


        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
