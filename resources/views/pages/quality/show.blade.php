@extends('layouts.root.main')

@section('main')
    <style>
        .detail-checksheet-page { --qc-navy:#294795; --qc-blue:#0070b7; --qc-sky:#0097d8; --qc-text:#1a2340; --qc-muted:#6b7a99; --qc-border:#dbe5f3; --qc-warning:#f59e0b; --qc-danger:#ef4444; margin-top:0; }
        .qc-detail-hero,.qc-stat-card,.qc-panel,.qc-table-panel{border:1px solid var(--qc-border);background:#fff;box-shadow:0 10px 28px rgba(41,71,149,.08)}
        .qc-detail-hero{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;padding:22px 24px;border-top:4px solid var(--qc-blue);border-radius:14px;margin-bottom:20px}
        .qc-page-kicker{display:inline-flex;align-items:center;gap:7px;margin-bottom:8px;color:var(--qc-sky);font-size:11px;font-weight:850;letter-spacing:.08em;text-transform:uppercase}
        .qc-page-title{margin:0;color:var(--qc-navy);font-size:24px;font-weight:850;letter-spacing:-.03em}.qc-page-subtitle{margin:7px 0 0;color:var(--qc-muted);font-size:13px;font-weight:650}
        .qc-action-row{display:flex;align-items:center;justify-content:flex-end;flex-wrap:wrap;gap:10px}.qc-stat-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:20px}
        .qc-stat-card{display:flex;align-items:center;justify-content:space-between;gap:16px;min-height:106px;padding:18px;border-radius:14px}.qc-stat-label{margin-bottom:6px;color:#7182a3;font-size:12px;font-weight:850;letter-spacing:.08em;text-transform:uppercase}.qc-stat-value{color:var(--qc-navy);font-size:24px;line-height:1.1;font-weight:850}.qc-stat-sub{margin-top:8px;color:var(--qc-muted);font-size:12px;font-weight:700}.qc-stat-icon{display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;flex:0 0 44px;border-radius:14px;background:#e6f8ff;color:var(--qc-sky);font-size:16px}
        .qc-panel,.qc-table-panel{border-radius:14px;overflow:hidden;margin-bottom:20px}.qc-panel{border-top:4px solid var(--qc-blue)}.qc-panel-body{padding:22px 24px}.qc-panel-title{margin:0;color:var(--qc-navy);font-size:15px;font-weight:850;letter-spacing:.04em;text-transform:uppercase}.qc-panel-subtitle{margin:4px 0 0;color:var(--qc-muted);font-size:12px;font-weight:650}
        .qc-info-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px 20px;margin-top:20px}.qc-info-item{min-height:68px;padding:14px 15px;border:1px solid #e4ebf5;border-radius:10px;background:#f8fbff}.qc-info-label{margin-bottom:7px;color:#7182a3;font-size:11px;font-weight:850;letter-spacing:.05em;text-transform:uppercase}.qc-info-value{color:var(--qc-text);font-size:13px;font-weight:800;word-break:break-word}
        .qc-table-header{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:18px 22px;border-bottom:1px solid var(--qc-border)}.qc-table{margin:0;color:var(--qc-text)}.qc-table thead th{padding:13px 18px;border-bottom:1px solid var(--qc-border);background:#eef3fa;color:#7182a3;font-size:11px;font-weight:850;letter-spacing:.04em;text-transform:uppercase;white-space:nowrap}.qc-table tbody td{padding:14px 18px;border-bottom:1px solid #edf2f8;vertical-align:middle;font-weight:650}.qc-table tbody tr:nth-child(odd){background:#f8fbff}
        .qc-pill{display:inline-flex;align-items:center;justify-content:center;min-width:92px;padding:9px 14px;border-radius:4px;font-size:12px;font-weight:850;text-transform:uppercase}.qc-pill-ok{background:#20a84a;color:#fff}.qc-pill-ng{border:1px solid var(--qc-danger);background:#fff;color:var(--qc-danger)}.qc-pill-progress{border:1px solid var(--qc-warning);background:#fff;color:var(--qc-warning)}
        .qc-card-link,.qc-secondary-link,.qc-update-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:38px;padding:9px 14px;border-radius:6px;font-size:12px;font-weight:850;text-decoration:none!important}.qc-card-link{border:0;background:linear-gradient(135deg,var(--qc-navy),var(--qc-blue));color:#fff!important}.qc-secondary-link{border:1px solid #b9d9f6;background:#eef6ff;color:var(--qc-blue)!important}.qc-update-btn{width:100%;border:0;background:#f59e0b;color:#fff}.qc-approval-panel .form-control{min-height:40px;border-color:#cfd9e8;background:#f8fbff;color:var(--qc-text);font-weight:650}.qc-approval-panel label{color:#627391;font-size:12px;font-weight:850;text-transform:uppercase}
        @media(max-width:1199.98px){.qc-stat-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:767.98px){.qc-detail-hero,.qc-table-header{flex-direction:column;align-items:stretch}.qc-stat-grid,.qc-info-grid{grid-template-columns:1fr}}
    </style>

    @php
        $detailCount = $inspection->details->count();
        $okCount = $inspection->details->where('result', 'OK')->count();
        $ngCount = $inspection->details->where('result', 'NG')->count();
        $isOk = $inspection->judgement === 'OK';
        $statusClass = $isOk ? 'qc-pill-ok' : ($inspection->approval_status === 'NEED_APPROVAL' ? 'qc-pill-progress' : 'qc-pill-ng');
        $statusLabel = $isOk ? 'Complete' : ($inspection->approval_status === 'NEED_APPROVAL' ? 'In Progress' : 'Incomplete');
        $progress = $detailCount > 0 ? round(($okCount / $detailCount) * 100) : 0;
    @endphp

    <section class="section detail-checksheet-page">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="qc-detail-hero">
            <div>
                <div class="qc-page-kicker"><i class="fas fa-qrcode"></i> Detail Scan Quality</div>
                <h1 class="qc-page-title">{{ $inspection->part_number ?: 'Part sudah discan' }}</h1>
                <p class="qc-page-subtitle">{{ $inspection->part_name ?: '-' }} - Kanban {{ $inspection->kanban_code ?: '-' }}</p>
            </div>
            <div class="qc-action-row">
                <a href="{{ route('quality.history') }}" class="qc-secondary-link"><i class="fas fa-arrow-left"></i> Kembali ke Detail Checksheet</a>
                <a href="{{ route('quality.show.export', $inspection) }}" class="qc-card-link"><i class="fas fa-file-excel"></i> Export Excel</a>
            </div>
        </div>

        <div class="qc-stat-grid">
            <div class="qc-stat-card"><div><div class="qc-stat-label">Checksheet Progress</div><div class="qc-stat-value">{{ $progress }}%</div><div class="qc-stat-sub">{{ $okCount }} OK dari {{ $detailCount }} item</div></div><div class="qc-stat-icon"><i class="fas fa-chart-line"></i></div></div>
            <div class="qc-stat-card"><div><div class="qc-stat-label">Judgement</div><div class="qc-stat-value">{{ $inspection->judgement ?: '-' }}</div><div class="qc-stat-sub">Hasil final scan</div></div><div class="qc-stat-icon"><i class="fas fa-clipboard-check"></i></div></div>
            <div class="qc-stat-card"><div><div class="qc-stat-label">Check Type</div><div class="qc-stat-value">{{ $inspection->check_type ?: '-' }}</div><div class="qc-stat-sub">{{ $inspection->line ?: 'QC GATE R1' }}</div></div><div class="qc-stat-icon"><i class="fas fa-tasks"></i></div></div>
            <div class="qc-stat-card"><div><div class="qc-stat-label">NG Item</div><div class="qc-stat-value">{{ $ngCount }}</div><div class="qc-stat-sub">Perlu follow up jika ada NG</div></div><div class="qc-stat-icon"><i class="fas fa-exclamation-triangle"></i></div></div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="qc-panel"><div class="qc-panel-body">
                    <h4 class="qc-panel-title">Informasi Part Yang Sudah Discan</h4>
                    <p class="qc-panel-subtitle">Data ini diambil dari hasil submit scan kanban dan checksheet quality.</p>
                    <div class="qc-info-grid">
                        <div class="qc-info-item"><div class="qc-info-label">Kanban Code</div><div class="qc-info-value">{{ $inspection->kanban_code ?: '-' }}</div></div>
                        <div class="qc-info-item"><div class="qc-info-label">Part Number</div><div class="qc-info-value">{{ $inspection->part_number ?: '-' }}</div></div>
                        <div class="qc-info-item"><div class="qc-info-label">Part Name</div><div class="qc-info-value">{{ $inspection->part_name ?: '-' }}</div></div>
                        <div class="qc-info-item"><div class="qc-info-label">Customer</div><div class="qc-info-value">{{ $customerName ?? '-' }}</div></div>
                        <div class="qc-info-item"><div class="qc-info-label">Back Number</div><div class="qc-info-value">{{ $inspection->back_number ?: '-' }}</div></div>
                        <div class="qc-info-item"><div class="qc-info-label">Qty / Box</div><div class="qc-info-value">{{ $inspection->qty_per_box ?: '-' }}</div></div>
                        <div class="qc-info-item"><div class="qc-info-label">PIC / Shift</div><div class="qc-info-value">{{ $inspection->pic ?: '-' }} / {{ $inspection->shift ?: '-' }}</div></div>
                        <div class="qc-info-item"><div class="qc-info-label">Checked At</div><div class="qc-info-value">{{ optional($inspection->checked_at)->format('Y-m-d H:i:s') ?? '-' }}</div></div>
                        <div class="qc-info-item"><div class="qc-info-label">Approval Status</div><div class="qc-info-value"><span class="qc-pill {{ $statusClass }}">{{ $statusLabel }}</span></div></div>
                    </div>
                </div></div>

                <div class="qc-table-panel">
                    <div class="qc-table-header"><div><h4 class="qc-panel-title">Detail Checksheet Part</h4><p class="qc-panel-subtitle">Seluruh item check yang tersimpan dari part hasil scan.</p></div><span class="text-muted font-weight-bold" style="font-size:12px;">{{ $detailCount }} item checksheet</span></div>
                    <div class="table-responsive"><table class="table qc-table"><thead><tr><th>No</th><th>Item Check</th><th>Standard</th><th>Result</th><th>Remark</th></tr></thead><tbody>
                        @forelse ($inspection->details as $detail)
                            <tr><td>{{ $loop->iteration }}</td><td>{{ $detail->check_item }}</td><td>{{ $detail->standard ?: '-' }}</td><td><span class="qc-pill {{ $detail->result === 'OK' ? 'qc-pill-ok' : 'qc-pill-ng' }}">{{ $detail->result }}</span></td><td>{{ $detail->remark ?: '-' }}</td></tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Tidak ada detail checksheet.</td></tr>
                        @endforelse
                    </tbody></table></div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="qc-panel qc-approval-panel"><div class="qc-panel-body">
                    <h4 class="qc-panel-title">Approval Supervisor</h4><p class="qc-panel-subtitle">Update follow up untuk hasil scan NG.</p>
                    <form method="POST" action="{{ route('quality.approval', $inspection) }}" class="mt-4">
                        @csrf
                        <div class="form-group"><label>Status Approval</label><select name="approval_status" class="form-control" required><option value="RECHECK" {{ $inspection->approval_status === 'RECHECK' ? 'selected' : '' }}>RECHECK</option><option value="HOLD" {{ $inspection->approval_status === 'HOLD' ? 'selected' : '' }}>HOLD</option><option value="REPAIR" {{ $inspection->approval_status === 'REPAIR' ? 'selected' : '' }}>REPAIR</option><option value="REJECT" {{ $inspection->approval_status === 'REJECT' ? 'selected' : '' }}>REJECT</option><option value="RELEASE" {{ $inspection->approval_status === 'RELEASE' ? 'selected' : '' }}>RELEASE</option></select></div>
                        <div class="form-group"><label>Catatan Supervisor</label><textarea name="supervisor_note" rows="4" class="form-control" placeholder="Tulis tindak lanjut untuk hasil NG">{{ $inspection->supervisor_note }}</textarea></div>
                        <button type="submit" class="qc-update-btn"><i class="fas fa-check-circle"></i> Update Approval</button>
                    </form>
                </div></div>
            </div>
        </div>
    </section>
@endsection
