@extends('layouts.root.main')

@section('main')
    <div class="row mt-4">
        <div class="col-12">
            <div class="card card-danger">
                <div class="card-header text-center">
                    <h3 class="p-4">Reset Kanban</h3>
                </div>
                <div class="card-body">
                    <div id="notif-area"></div>

                    <div class="form-group">
                        <label for="code">Scan Barcode</label>
                        <input type="text" id="code" class="form-control" placeholder="Scan barcode di sini"
                            autocomplete="off" autofocus>
                    </div>

                    <div class="mt-4" id="result-area" style="display: none;">
                        <h5>Information Details</h5>
                        <table class="table table-bordered mt-2">
                            <tr>
                                <th>Internal Part</th>
                                <td id="internal-part"></td>
                            </tr>
                            <tr>
                                <th>Serial Number</th>
                                <td id="serial-number"></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('code');
        let barcode = "";

        input.addEventListener('keypress', function(e) {
            const key = e.keyCode || e.which;

            if (key === 13) {
                e.preventDefault();
                const complete = barcode.trim().toUpperCase();
                barcode = "";
                handleBarcode(complete);
            } else {
                barcode += String.fromCharCode(e.which);
            }
        });

        function handleBarcode(code) {
            const allowedLengths = [218, 220, 230, 241, 242];

            console.log(code.length);

            if (!allowedLengths.includes(code.length)) {
                showNotif('error', 'Panjang barcode tidak dikenali!');
                focusInput();
                return;
            }

            let internal = "",
                serial = "";

            switch (code.length) {
                case 230:
                    internal = code.substr(41, 19);
                    serial = code.substr(123, 4);
                    break;
                case 220:
                    internal = code.substr(35, 16);
                    serial = code.substr(130, 4);
                    break;
                case 241:
                    internal = code.substr(35, 12);
                    serial = code.substr(127, 4);
                    break;
                case 218:
                    internal = code.substr(41, 16);
                    serial = code.substr(123, 4);
                    break;
                case 242:
                    internal = code.substr(35, 12);
                    serial = code.substr(127, 4);
                    break;
            }

            // Tampilkan hasil
            document.getElementById('internal-part').textContent = internal;
            document.getElementById('serial-number').textContent = serial;
            document.getElementById('result-area').style.display = 'block';
            showNotif('success', 'Barcode berhasil diproses.');
            focusInput();
        }

        function showNotif(type, message) {
            const color = type === 'error' ? 'danger' : 'success';
            document.getElementById('notif-area').innerHTML = `
                <div class="alert alert-${color}">${message}</div>
            `;
        }

        function focusInput() {
            setTimeout(() => document.getElementById('code').focus(), 300);
        }

        window.clearCustomerPart = function() {
            localStorage.removeItem('customerPart');
            showNotif('success', 'customerPart berhasil dihapus.');
            focusInput();
        }

        window.scanCustomerFirstSound = function() {
            // Play sound or show visual cue
            console.warn('⚠️ Scan customer part dulu!');
        }

        focusInput();
    });
</script>
