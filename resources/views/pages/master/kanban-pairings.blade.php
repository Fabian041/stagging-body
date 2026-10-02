@extends('layouts.root.main')

@section('main')
    @include('pages.master.partials.styles')

    <style>
        #pairing-entries {
            display: grid;
            gap: 12px;
        }

        .pairing-entry {
            overflow: hidden;
            background: #f8fafc;
            border: 1px solid #e3eaf2;
            border-radius: 8px;
        }

        .pairing-entry-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 9px 12px;
            background: #f1f6fa;
            border-bottom: 1px solid #e3eaf2;
        }

        .pairing-entry-title {
            margin: 0;
            color: #294795;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .pairing-entry-actions {
            display: flex;
            gap: 6px;
        }

        .pairing-entry-body {
            padding: 12px;
        }

        .pairing-field-group {
            margin-bottom: 12px;
        }

        .pairing-field-group-title {
            margin-bottom: 7px;
            color: #6b7a90;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .pairing-bulk-footer {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
            margin-top: 14px;
        }

        .pairing-filter {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .pairing-filter label {
            margin: 0;
            color: #66758e;
            font-size: 11px;
            font-weight: 700;
        }

        #pairing-project-filter {
            min-width: 180px;
            height: 35px;
            padding: 6px 30px 6px 10px;
            color: #33415c;
            font-size: 12px;
            background: #fff;
            border: 1px solid #d8e1ee;
            border-radius: 6px;
        }

        #kanban-pairings-table td {
            white-space: nowrap;
        }

        @media (max-width: 767.98px) {
            .pairing-filter {
                width: 100%;
            }

            #pairing-project-filter {
                min-width: 0;
                flex: 1;
            }
        }
    </style>

    <div class="container-fluid master-page">
        <div class="master-page-header">
            <div>
                <h1 class="master-page-title">Master Kanban Pairing</h1>
                <div class="master-page-subtitle">Kelola pasangan kanban assembly dan painting beserta rasio scan-nya.</div>
            </div>
            <div class="master-actions">
                <a href="{{ route('dashboard.master.index') }}" class="master-btn">
                    <i class="fas fa-th-large"></i> Master Home
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success master-alert">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger master-alert">
                <strong>Master pairing belum dapat disimpan.</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <datalist id="pairing-project-options">
            @foreach ($projects as $project)
                <option value="{{ $project }}">
            @endforeach
        </datalist>

        @if ($editingPairing)
            @php
                $values = [];
                foreach (['project', 'product', 'part_name', 'part_number_customer', 'assembly_part', 'assembly_back_number', 'qty_assy', 'painting_part', 'painting_back_number', 'qty_painting'] as $field) {
                    $values[$field] = old($field, $editingPairing->{$field});
                }
            @endphp
            <div class="master-panel mb-3">
                <div class="master-panel-header">
                    <h2 class="master-panel-title"><i class="fas fa-pen"></i>Edit Pairing</h2>
                </div>
                <div class="master-panel-body">
                    <form class="master-form" method="POST"
                        action="{{ route('dashboard.master.kanban-pairings.update', $editingPairing) }}">
                        @csrf
                        @method('PUT')
                        @include('pages.master.partials.kanban-pairing-fields', ['prefix' => '', 'values' => $values])
                        <div class="pairing-bulk-footer">
                            <a href="{{ route('dashboard.master.kanban-pairings.index') }}" class="master-btn">Batal Edit</a>
                            <button class="master-btn primary"><i class="fas fa-save"></i> Update Pairing</button>
                        </div>
                    </form>
                </div>
            </div>
        @else
            @php
                $emptyPairing = [
                    'project' => '',
                    'product' => '',
                    'part_name' => '',
                    'part_number_customer' => '',
                    'assembly_part' => '',
                    'assembly_back_number' => '',
                    'qty_assy' => 1,
                    'painting_part' => '',
                    'painting_back_number' => '',
                    'qty_painting' => 1,
                ];
                $pairingRows = old('pairings', [$emptyPairing]);
                $pairingRows = is_array($pairingRows) && count($pairingRows) ? $pairingRows : [$emptyPairing];
            @endphp
            <div class="master-panel mb-3">
                <div class="master-panel-header">
                    <h2 class="master-panel-title"><i class="fas fa-plus"></i>Tambah Multiple Pairing</h2>
                    <button type="button" id="add-pairing-entry" class="master-btn info">
                        <i class="fas fa-plus"></i> Tambah Baris
                    </button>
                </div>
                <div class="master-panel-body">
                    <form id="pairing-bulk-form" class="master-form" method="POST"
                        action="{{ route('dashboard.master.kanban-pairings.store') }}">
                        @csrf
                        <div id="pairing-entries">
                            @foreach ($pairingRows as $index => $values)
                                <div class="pairing-entry" data-pairing-entry>
                                    <div class="pairing-entry-header">
                                        <h3 class="pairing-entry-title">Pairing #{{ $loop->iteration }}</h3>
                                        <div class="pairing-entry-actions">
                                            <button type="button" class="master-btn" data-duplicate-pairing>
                                                <i class="far fa-copy"></i> Duplikat
                                            </button>
                                            <button type="button" class="master-btn danger" data-remove-pairing>
                                                <i class="fas fa-times"></i> Hapus
                                            </button>
                                        </div>
                                    </div>
                                    <div class="pairing-entry-body">
                                        @include('pages.master.partials.kanban-pairing-fields', ['prefix' => 'pairings['.$index.']', 'values' => $values])
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="pairing-bulk-footer">
                            <button class="master-btn primary"><i class="fas fa-save"></i> Simpan Semua Pairing</button>
                        </div>
                    </form>

                    <template id="pairing-entry-template">
                        <div class="pairing-entry" data-pairing-entry>
                            <div class="pairing-entry-header">
                                <h3 class="pairing-entry-title">Pairing</h3>
                                <div class="pairing-entry-actions">
                                    <button type="button" class="master-btn" data-duplicate-pairing>
                                        <i class="far fa-copy"></i> Duplikat
                                    </button>
                                    <button type="button" class="master-btn danger" data-remove-pairing>
                                        <i class="fas fa-times"></i> Hapus
                                    </button>
                                </div>
                            </div>
                            <div class="pairing-entry-body">
                                @include('pages.master.partials.kanban-pairing-fields', [
                                    'prefix' => 'pairings[__INDEX__]',
                                    'values' => ['qty_assy' => 1, 'qty_painting' => 1],
                                ])
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        @endif

        <div class="master-panel">
            <div class="master-panel-header">
                <h2 class="master-panel-title"><i class="fas fa-list"></i>Daftar Kanban Pairing</h2>
                <span class="master-record-count">{{ $pairingCount }} record</span>
            </div>
            <div class="master-table-toolbar justify-content-end">
                <div class="pairing-filter">
                    <label for="pairing-project-filter"><i class="fas fa-filter"></i> Project</label>
                    <select id="pairing-project-filter" aria-label="Filter berdasarkan project">
                        <option value="">Semua Project</option>
                        @foreach ($projects as $project)
                            <option value="{{ $project }}">{{ $project }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="master-table-wrap">
                <table id="kanban-pairings-table" class="table master-table align-middle w-100">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th>Assembly Part</th>
                            <th>Back No. Assy</th>
                            <th>Qty Assy</th>
                            <th>Painting Part</th>
                            <th>Back No. Painting</th>
                            <th>Qty Painting</th>
                            <th>Part Customer</th>
                            <th>Product</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            const entries = document.getElementById('pairing-entries');
            const template = document.getElementById('pairing-entry-template');
            const addButton = document.getElementById('add-pairing-entry');

            if (entries && template && addButton) {
                let nextIndex = 0;

                entries.querySelectorAll('[name]').forEach(function(input) {
                    const match = input.name.match(/pairings\[(\d+)\]/);
                    if (match) {
                        nextIndex = Math.max(nextIndex, Number(match[1]) + 1);
                    }
                });

                const updateEntryNumbers = function() {
                    const cards = entries.querySelectorAll('[data-pairing-entry]');
                    cards.forEach(function(card, index) {
                        card.querySelector('.pairing-entry-title').textContent = 'Pairing #' + (index + 1);
                        card.querySelector('[data-remove-pairing]').style.display = cards.length === 1 ? 'none' : '';
                    });
                };

                const assignIndex = function(card, index) {
                    card.querySelectorAll('[name]').forEach(function(input) {
                        input.name = input.name.replace(/pairings\[(?:\d+|__INDEX__)\]/, 'pairings[' + index + ']');
                    });
                };

                const appendCard = function(card) {
                    if (entries.querySelectorAll('[data-pairing-entry]').length >= 50) {
                        alert('Maksimal 50 pairing dalam satu kali simpan.');
                        return;
                    }

                    assignIndex(card, nextIndex++);
                    entries.appendChild(card);
                    updateEntryNumbers();
                };

                addButton.addEventListener('click', function() {
                    appendCard(template.content.firstElementChild.cloneNode(true));
                });

                entries.addEventListener('click', function(event) {
                    const duplicateButton = event.target.closest('[data-duplicate-pairing]');
                    const removeButton = event.target.closest('[data-remove-pairing]');

                    if (duplicateButton) {
                        const source = duplicateButton.closest('[data-pairing-entry]');
                        const clone = source.cloneNode(true);
                        const sourceInputs = source.querySelectorAll('input');
                        clone.querySelectorAll('input').forEach(function(input, index) {
                            input.value = sourceInputs[index].value;
                        });
                        appendCard(clone);
                    }

                    if (removeButton && entries.querySelectorAll('[data-pairing-entry]').length > 1) {
                        removeButton.closest('[data-pairing-entry]').remove();
                        updateEntryNumbers();
                    }
                });

                updateEntryNumbers();
            }

            if (!$.fn.DataTable) {
                return;
            }

            const text = $.fn.dataTable.render.text();
            const table = $('#kanban-pairings-table').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                ajax: {
                    url: '{{ route('dashboard.master.kanban-pairings.data') }}',
                    data: function(payload) {
                        payload.project = $('#pairing-project-filter').val() || '';
                    }
                },
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                order: [[0, 'asc'], [1, 'asc']],
                autoWidth: false,
                columns: [
                    { data: 'project', name: 'project', render: text },
                    { data: 'assembly_part', name: 'assembly_part', render: text },
                    { data: 'assembly_back_number', name: 'assembly_back_number', render: text },
                    { data: 'qty_assy', name: 'qty_assy', render: text },
                    { data: 'painting_part', name: 'painting_part', render: text },
                    { data: 'painting_back_number', name: 'painting_back_number', render: text },
                    { data: 'qty_painting', name: 'qty_painting', render: text },
                    { data: 'part_number_customer', name: 'part_number_customer', render: text },
                    { data: 'product', name: 'product', render: text },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' }
                ],
                language: {
                    processing: 'Memuat data...',
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    infoEmpty: 'Belum ada data',
                    zeroRecords: 'Master pairing tidak ditemukan',
                    paginate: {
                        first: 'Awal',
                        last: 'Akhir',
                        next: 'Berikutnya',
                        previous: 'Sebelumnya'
                    }
                }
            });

            $('#pairing-project-filter').on('change', function() {
                table.ajax.reload();
            });
        });
    </script>
@endpush
