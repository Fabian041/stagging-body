@extends('layouts.root.main')

@section('main')
    <style>
        .pareto-quality-page { --qc-navy:#294795; --qc-blue:#0070b7; --qc-sky:#0097d8; --qc-text:#1a2340; --qc-muted:#6b7a99; --qc-border:#dbe5f3; --qc-danger:#ef4444; --qc-warning:#f59e0b; margin-top:0; }
        .qc-pareto-hero,.qc-stat-card,.qc-table-panel{border:1px solid var(--qc-border);background:#fff;box-shadow:0 10px 28px rgba(41,71,149,.08)}
        .qc-pareto-hero{padding:22px 24px;border-top:4px solid var(--qc-blue);border-radius:14px;margin-bottom:20px}.qc-page-kicker{display:inline-flex;align-items:center;gap:7px;margin-bottom:8px;color:var(--qc-sky);font-size:11px;font-weight:850;letter-spacing:.08em;text-transform:uppercase}.qc-page-title{margin:0;color:var(--qc-navy);font-size:24px;font-weight:850;letter-spacing:-.03em}.qc-page-subtitle{margin:7px 0 0;color:var(--qc-muted);font-size:13px;font-weight:650}
        .qc-stat-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-bottom:20px}.qc-stat-card{display:flex;align-items:center;justify-content:space-between;gap:16px;min-height:132px;padding:22px 20px;border-radius:14px}.qc-stat-label{margin-bottom:8px;color:#7182a3;font-size:12px;font-weight:850;letter-spacing:.08em;text-transform:uppercase}.qc-stat-value{color:var(--qc-navy);font-size:32px;line-height:1;font-weight:900;letter-spacing:-.03em}.qc-stat-sub{margin-top:10px;color:var(--qc-muted);font-size:12px;font-weight:800}.qc-stat-icon{display:inline-flex;align-items:center;justify-content:center;width:50px;height:50px;flex:0 0 50px;border-radius:16px;background:#e6f8ff;color:var(--qc-sky);font-size:17px}
        .qc-table-panel{border-radius:14px;overflow:hidden;margin-bottom:20px}.qc-table-header{padding:18px 22px;border-bottom:1px solid var(--qc-border)}.qc-panel-title{margin:0;color:var(--qc-navy);font-size:15px;font-weight:850;letter-spacing:.04em;text-transform:uppercase}.qc-panel-subtitle{margin:4px 0 0;color:var(--qc-muted);font-size:12px;font-weight:650}.qc-table{margin:0;color:var(--qc-text)}.qc-table thead th{padding:13px 18px;border-bottom:1px solid var(--qc-border);background:#eef3fa;color:#7182a3;font-size:11px;font-weight:850;letter-spacing:.04em;text-transform:uppercase;white-space:nowrap}.qc-table tbody td{padding:14px 18px;border-bottom:1px solid #edf2f8;vertical-align:middle;font-weight:650}.qc-table tbody tr:nth-child(odd){background:#f8fbff}.qc-rank{display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:10px;background:#edf6ff;color:var(--qc-blue);font-weight:900}.qc-badge{display:inline-flex;align-items:center;justify-content:center;min-width:34px;padding:7px 10px;border-radius:999px;color:#fff;font-size:12px;font-weight:900}.qc-badge-danger{background:var(--qc-danger)}.qc-badge-warning{background:var(--qc-warning);color:#1a2340}
        @media(max-width:1199.98px){.qc-stat-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}@media(max-width:767.98px){.qc-stat-grid{grid-template-columns:1fr}.qc-stat-card{min-height:112px}}
    </style>

    @php
        $total = (int) ($summary['total'] ?? 0);
        $completed = (int) ($summary['ok'] ?? 0);
        $ng = (int) ($summary['ng'] ?? 0);
        $pending = (int) ($summary['pending'] ?? 0);
        $inProgress = $ng + $pending;
        $avgProgress = $total > 0 ? round(($completed / $total) * 100) : 0;
    @endphp

    <section class="section pareto-quality-page">
        <div class="qc-pareto-hero">
            <div class="qc-page-kicker"><i class="fas fa-chart-pie"></i> Reject & Pareto Quality</div>
            <h1 class="qc-page-title">Reject & Pareto Quality</h1>
            <p class="qc-page-subtitle">Monitoring defect dominan dan part dengan frekuensi NG tertinggi pada QC Gate R1.</p>
        </div>

        <div class="qc-stat-grid">
            <div class="qc-stat-card"><div><div class="qc-stat-label">Visible Records</div><div class="qc-stat-value">{{ number_format($total) }}</div><div class="qc-stat-sub">Current pareto view</div></div><div class="qc-stat-icon"><i class="fas fa-file-alt"></i></div></div>
            <div class="qc-stat-card"><div><div class="qc-stat-label">Completed</div><div class="qc-stat-value">{{ number_format($completed) }}</div><div class="qc-stat-sub">OK checksheet</div></div><div class="qc-stat-icon"><i class="fas fa-check-circle"></i></div></div>
            <div class="qc-stat-card"><div><div class="qc-stat-label">In Progress</div><div class="qc-stat-value">{{ number_format($inProgress) }}</div><div class="qc-stat-sub">NG / need approval</div></div><div class="qc-stat-icon"><i class="fas fa-spinner"></i></div></div>
            <div class="qc-stat-card"><div><div class="qc-stat-label">Avg Progress</div><div class="qc-stat-value">{{ $avgProgress }}%</div><div class="qc-stat-sub">{{ number_format($total) }} total records</div></div><div class="qc-stat-icon"><i class="fas fa-chart-line"></i></div></div>
        </div>

        <div class="row">
            <div class="col-lg-6"><div class="qc-table-panel"><div class="qc-table-header"><h4 class="qc-panel-title">Top Defect Check Item</h4><p class="qc-panel-subtitle">Item checksheet dengan jumlah NG tertinggi.</p></div><div class="table-responsive"><table class="table qc-table"><thead><tr><th width="80">Rank</th><th>Check Item</th><th width="120">Total NG</th></tr></thead><tbody>
                @forelse ($topDefects as $row)
                    <tr><td><span class="qc-rank">{{ $loop->iteration }}</span></td><td>{{ $row->check_item ?: '-' }}</td><td><span class="qc-badge qc-badge-danger">{{ number_format($row->total) }}</span></td></tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">Belum ada data defect NG.</td></tr>
                @endforelse
            </tbody></table></div></div></div>

            <div class="col-lg-6"><div class="qc-table-panel"><div class="qc-table-header"><h4 class="qc-panel-title">Top NG Part Number</h4><p class="qc-panel-subtitle">Part number dengan frekuensi NG tertinggi.</p></div><div class="table-responsive"><table class="table qc-table"><thead><tr><th width="80">Rank</th><th>Part Number</th><th width="120">Total NG</th></tr></thead><tbody>
                @forelse ($topParts as $row)
                    <tr><td><span class="qc-rank">{{ $loop->iteration }}</span></td><td>{{ $row->part_number ?: '-' }}</td><td><span class="qc-badge qc-badge-warning">{{ number_format($row->total) }}</span></td></tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted py-4">Belum ada data part NG.</td></tr>
                @endforelse
            </tbody></table></div></div></div>
        </div>
    </section>
@endsection