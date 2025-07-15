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
            <h2 class="fw-bold text-uppercase">Pulling Day - 05-Jul-25</h2>
            <a class="btn btn-outline-warning" href="/pulling/settings">
                <i class="bi bi-gear-fill"></i> Setting
            </a>
        </div>

        <!-- Tab line -->
        <ul class="nav nav-tabs mb-4" id="lineTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="line1-tab" data-bs-toggle="tab" data-bs-target="#line1"
                    type="button" role="tab">AS001</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="line2-tab" data-bs-toggle="tab" data-bs-target="#line2" type="button"
                    role="tab">AS002</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="line3-tab" data-bs-toggle="tab" data-bs-target="#line3" type="button"
                    role="tab">AS003</button>
            </li>
        </ul>

        <div class="tab-content" id="lineTabsContent">
            <!-- AS001 -->
            <div class="tab-pane fade show active" id="line1" role="tabpanel" aria-labelledby="line1-tab">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover text-center align-middle table-dark">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Route</th>
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
                        <tbody id="flipTable">
                            <!-- RFID TCC D41T @ 07:00 (2 baris) -->
                            <tr>
                                <td rowspan="2"><span class="flip">RFID TCC D41T</span></td>
                                <td><span class="flip">MCE2KB1</span></td>
                                <td><span class="flip">7</span></td>
                                <td><span class="flip">CI 18</span></td>
                                <td><span class="flip">24</span></td>
                                <td><span class="flip">56</span></td>
                                <td class="bg-success text-dark"><span class="flip">56</span></td>
                                <td><span class="flip">0.55</span></td>
                                <td><span class="flip">-</span></td>
                                <td><span class="flip">06:45</span></td>
                                <td rowspan="2"><span class="flip">07:00</span></td>
                                <td><span class="flip">-</span></td>
                            </tr>
                            <tr>
                                <!-- Customer & Delivery Time kosong karena rowspan -->
                                <td><span class="flip">MCE2KB2</span></td>
                                <td><span class="flip">4</span></td>
                                <td><span class="flip">CI 20</span></td>
                                <td><span class="flip">28</span></td>
                                <td><span class="flip">62</span></td>
                                <td class="bg-warning text-dark"><span class="flip">42</span></td>
                                <td><span class="flip">0.45</span></td>
                                <td><span class="flip">-</span></td>
                                <td><span class="flip">06:59</span></td>
                                <td><span class="flip">-</span></td>
                            </tr>

                            <!-- RFID ABC X1 @ 08:00 (3 baris) -->
                            <tr>
                                <td rowspan="3"><span class="flip">RFID ABC X1</span></td>
                                <td><span class="flip">MCE1AA1</span></td>
                                <td><span class="flip">6</span></td>
                                <td><span class="flip">CI 99</span></td>
                                <td><span class="flip">18</span></td>
                                <td><span class="flip">48</span></td>
                                <td class="bg-warning text-dark"><span class="flip">32</span></td>
                                <td><span class="flip">0.72</span></td>
                                <td><span class="flip">-</span></td>
                                <td><span class="flip">07:58</span></td>
                                <td rowspan="3"><span class="flip">08:00</span></td>
                                <td><span class="flip">-</span></td>
                            </tr>
                            <tr>
                                <td><span class="flip">MCE1AA2</span></td>
                                <td><span class="flip">5</span></td>
                                <td><span class="flip">CI 77</span></td>
                                <td><span class="flip">26</span></td>
                                <td><span class="flip">64</span></td>
                                <td class="bg-danger text-dark"><span class="flip">21</span></span></td>
                                <td><span class="flip">0.91</span></td>
                                <td><span class="flip">-</span></td>
                                <td><span class="flip">08:10</span></td>
                                <td><span class="flip">-</span></td>
                            </tr>
                            <tr>
                                <td><span class="flip">MCE1AA3</span></td>
                                <td><span class="flip">8</span></td>
                                <td><span class="flip">CI 05</span></td>
                                <td><span class="flip">30</span></td>
                                <td><span class="flip">60</span></td>
                                <td class="bg-warning text-dark"><span class="flip">44</span></td>
                                <td><span class="flip">1.12</span></td>
                                <td><span class="flip">-</span></td>
                                <td><span class="flip">08:50</span></td>
                                <td><span class="flip">-</span></td>
                            </tr>
                        </tbody>

                    </table>
                </div>
            </div>

            <!-- AS002 -->
            <div class="tab-pane fade" id="line2" role="tabpanel" aria-labelledby="line2-tab">
                <div class="table-responsive">
                    <p class="text-center mt-4">Data untuk AS002 belum tersedia.</p>
                </div>
            </div>

            <!-- AS003 -->
            <div class="tab-pane fade" id="line3" role="tabpanel" aria-labelledby="line3-tab">
                <div class="table-responsive">
                    <p class="text-center mt-4">Data untuk AS003 belum tersedia.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Audio -->
    <audio id="clickSound" src="{{ asset('sounds/click.mp3') }}" preload="auto"></audio>

    <script>
        const data = {
            customer: "RFID TCC D41T",
            route: "MCE2KB1",
            cycle: 7,
            back_no: "CI 18",
            qty: 24,
            order: 56,
            direct: 24,
            chute: 32,
            prod: "0,55",
            break: "-",
            work: "13:41",
            delivery: "-",
            balance: "-"
        };

        const audio = document.getElementById("clickSound");

        function simulateUpdate() {
            const newData = {
                ...data,
                chute: Math.floor(Math.random() * 40) + 10,
                direct: Math.floor(Math.random() * 50),
                prod: (Math.random() * 1.5).toFixed(2),
                order: Math.floor(Math.random() * 100),
                work: new Date().toLocaleTimeString('en-GB').slice(0, 5)
            };

            document.querySelectorAll("#flipTable .flip").forEach(el => {
                const key = el.dataset.key;
                const oldVal = el.textContent.trim();
                const newVal = newData[key];

                if (newVal != null && oldVal !== newVal.toString()) {
                    el.classList.add("animate");
                    setTimeout(() => {
                        el.textContent = newVal;
                        el.classList.remove("animate");
                        audio.currentTime = 0;
                        audio.play();
                    }, 300);
                }
            });
        }

        setInterval(simulateUpdate, 5000);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
