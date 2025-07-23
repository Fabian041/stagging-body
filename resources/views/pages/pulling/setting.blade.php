@extends('layouts.root.main')

@section('main')
    <div class="row mt-4">
        <div class="col-12 col-sm-12 col-lg-12">
            <div class="card card-primary">
                <div class="card-header text-center">
                    <h4 class="text-uppercase">⚙️ Pengaturan Pulling</h4>
                </div>

                <div class="card-body">
                    <form action="#" method="POST">
                        @csrf

                        {{-- Urutan Produksi --}}
                        <div class="form-group mb-4">
                            <label for="production_order" class="fw-bold">Urutan Produksi</label>
                            <select class="form-control" id="production_order" name="production_order[]">
                                <option selected>AS001 - LINE A</option>
                                <option>AS002 - LINE B</option>
                                <option>AS003 - LINE C</option>
                                <option>AS004 - LINE D</option>
                                <option>AS005 - LINE E</option>
                                <option>AS006 - LINE F</option>
                            </select>
                            <small class="form-text text-muted">Gunakan Ctrl (atau ⌘) untuk memilih lebih dari satu.</small>
                        </div>

                        {{-- Parameter Produksi --}}
                        <div class="form-group mb-4">
                            <label for="order_qty" class="fw-bold">Default Order Qty</label>
                            <input type="number" class="form-control" id="order_qty" name="order_qty" value="50"
                                required>
                        </div>

                        <div class="form-group mb-4">
                            <label for="qty_per_pallet" class="fw-bold">Qty per Pallet</label>
                            <input type="number" class="form-control" id="qty_per_pallet" name="qty_per_pallet"
                                value="24" required>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-save me-1"></i> Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
