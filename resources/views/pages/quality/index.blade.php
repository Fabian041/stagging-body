@extends('layouts.root.dashboard')

@section('main')
    <style>
        .quality-page {
            --quality-navy: #294795;
            --quality-blue: #0070b7;
            --quality-sky: #0097d8;
            --quality-text: #1a2340;
            --quality-muted: #6b7a99;
            --quality-border: #dde3ef;
            --quality-soft: #eef7ff;
            --quality-success: #16a34a;
            --quality-danger: #dc2626;
            --quality-warning: #f59e0b;
        }

        .quality-hero {
            position: relative;
            padding: 28px;
            border-radius: 28px;
            background: linear-gradient(135deg, rgba(41, 71, 149, .98), rgba(0, 112, 183, .95), rgba(0, 151, 216, .88));
            color: #fff;
            overflow: hidden;
            box-shadow: 0 20px 45px rgba(41, 71, 149, .18);
            margin-bottom: 22px;
        }

        .quality-hero::after {
            content: '';
            position: absolute;
            right: -60px;
            top: -60px;
            width: 220px;
            height: 220px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .12);
        }

        .quality-hero h2 {
            margin: 0 0 10px;
            color: #fff;
            font-size: 30px;
            font-weight: 850;
            letter-spacing: -.04em;
        }

        .quality-hero p {
            max-width: 760px;
            margin: 0;
            color: rgba(255, 255, 255, .8);
            line-height: 1.7;
        }

        .quality-stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 22px;
        }

        .quality-stat {
            padding: 20px;
            border-radius: 22px;
            border: 1px solid rgba(221, 227, 239, .9);
            background: rgba(255, 255, 255, .94);
            box-shadow: 0 10px 28px rgba(41, 71, 149, .08);
        }

        .quality-stat small {
            display: block;
            color: var(--quality-muted);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .quality-stat strong {
            display: block;
            margin-top: 8px;
            color: var(--quality-text);
            font-size: 28px;
            line-height: 1;
            font-weight: 850;
        }

        .quality-grid {
            display: grid;
            grid-template-columns: 1.2fr .8fr;
            gap: 20px;
        }

        .quality-card {
            border: 1px solid rgba(221, 227, 239, .9);
            border-radius: 24px;
            background: rgba(255, 255, 255, .95);
            box-shadow: 0 12px 32px rgba(41, 71, 149, .08);
            overflow: hidden;
            height: 100%;
        }

        .quality-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 20px 22px;
            border-bottom: 1px solid var(--quality-border);
            background: linear-gradient(180deg, rgba(248, 251, 255, .96), rgba(255, 255, 255, .92));
        }

        .quality-card-title {
            margin: 0;
            color: var(--quality-text);
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -.02em;
        }

        .quality-card-subtitle {
            margin-top: 4px;
            color: var(--quality-muted);
            font-size: 12px;
        }

        .quality-card-body {
            padding: 22px;
        }

        .quality-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .quality-action {
            display: block;
            padding: 18px;
            border-radius: 18px;
            border: 1px solid rgba(221, 227, 239, .9);
            background: var(--quality-soft);
            text-decoration: none !important;
            transition: .2s ease;
        }

        .quality-action:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 30px rgba(41, 71, 149, .1);
        }

        .quality-action i {
            font-size: 18px;
            color: var(--quality-blue);
            margin-bottom: 12px;
        }

        .quality-action h5 {
            margin: 0 0 6px;
            color: var(--quality-text);
            font-size: 16px;
            font-weight: 800;
        }

        .quality-action p {
            margin: 0;
            color: var(--quality-muted);
            font-size: 12px;
            line-height: 1.6;
        }

        .quality-table {
            width: 100%;
            margin: 0;
        }

        .quality-table th,
        .quality-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #edf2f8;
            vertical-align: middle;
            font-size: 12px;
        }

        .quality-table th {
            color: var(--quality-muted);
            text-transform: uppercase;
            letter-spacing: .06em;
            font-size: 11px;
            font-weight: 800;
        }

        .quality-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 70px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
        }

        .quality-badge.ok {
            background: rgba(22, 163, 74, .12);
            color: var(--quality-success);
        }

        .quality-badge.ng {
            background: rgba(220, 38, 38, .1);
            color: var(--quality-danger);
        }

        .quality-badge.pending {
            background: rgba(245, 158, 11, .12);
            color: #b45309;
        }

        @media (max-width: 991.98px) {
            .quality-stats,
            .quality-grid,
            .quality-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <section class="section quality-page">
        <div class="section-header">
            <h1>Quality Digital Check</h1>
            <div class="section-header-breadcrumb">
                Monitoring digitalisasi pengecekan sampling QC Gate R1 mulai dari scan kanban, pengisian checksheet,
                approval NG, hingga history inspection dan pareto defect.
            </div>
        </div>

        <div class="quality-hero">
            <h2>QC Gate R1 Digitalisasi Sampling Check</h2>
            <p>
                Modul ini menggantikan proses manual dengan alur digital: scan kanban, tampilkan detail part,
                isi hasil pemeriksaan, simpan history OK/NG, dan tindak lanjut approval supervisor untuk part reject.
            </p>
        </div>

        <div class="quality-stats">
            <div class="quality-stat">
                <small>Total Inspection</small>
                <strong>{{ number_format($summary['total'] ?? 0) }}</strong>
            </div>
            <div class="quality-stat">
                <small>OK Result</small>
                <strong>{{ number_format($summary['ok'] ?? 0) }}</strong>
            </div>
            <div class="quality-stat">
                <small>NG Result</small>
                <strong>{{ number_format($summary['ng'] ?? 0) }}</strong>
            </div>
            <div class="quality-stat">
                <small>Need Approval</small>
                <strong>{{ number_format($summary['pending'] ?? 0) }}</strong>
            </div>
        </div>

        <div class="quality-grid">
            <div class="quality-card">
                <div class="quality-card-header">
                    <div>
                        <div class="quality-card-title">Menu Proses Quality</div>
                        <div class="quality-card-subtitle">Akses cepat untuk alur utama digital QC Gate R1</div>
                    </div>
                </div>
                <div class="quality-card-body">
                    <div class="quality-actions">
                        <a href="{{ route('quality.scan', ['mode' => 'sampling']) }}" class="quality-action">
                            <i class="fas fa-vials"></i>
                            <h5>Sampling Check</h5>
                            <p>Gunakan Scan Kanban First dan Last untuk proses pengecekan sampling QC.</p>
                        </a>
                        <a href="{{ route('quality.scan', ['mode' => 'full']) }}" class="quality-action">
                            <i class="fas fa-boxes"></i>
                            <h5>100% Check</h5>
                            <p>Scan setiap box yang selesai dicek untuk menghitung total box dan total part full inspection.</p>
                        </a>
                        <a href="{{ route('quality.history') }}" class="quality-action">
                            <i class="fas fa-history"></i>
                            <h5>History Checksheet</h5>
                            <p>Lihat riwayat hasil scan kanban, data checksheet tersimpan, dan detail tiap pengecekan.</p>
                        </a>
                        <a href="{{ route('quality.pareto') }}" class="quality-action">
                            <i class="fas fa-chart-bar"></i>
                            <h5>Reject & Pareto</h5>
                            <p>Monitor item defect dominan dan part dengan frekuensi NG tertinggi.</p>
                        </a>
                        <a href="{{ route('loadingList.index') }}" class="quality-action">
                            <i class="fas fa-list-ul"></i>
                            <h5>Scan Detail</h5>
                            <p>Tampilkan seluruh scan detail yang merangkum proses PPIC, Production, PIS, Quality, dan Error Log.</p>
                        </a>
                    </div>
                </div>
            </div>

            <div class="quality-card">
                <div class="quality-card-header">
                    <div>
                        <div class="quality-card-title">Recent Inspection</div>
                        <div class="quality-card-subtitle">8 data terbaru hasil inspection</div>
                    </div>
                </div>
                <div class="quality-card-body p-0">
                    <div class="table-responsive">
                        <table class="quality-table">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>Part</th>
                                    <th>Result</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($recentInspections as $inspection)
                                    <tr>
                                        <td>{{ optional($inspection->checked_at)->format('d/m/Y H:i') ?? '-' }}</td>
                                        <td>
                                            <div class="font-weight-bold">{{ $inspection->part_number ?: '-' }}</div>
                                            <div class="text-muted">{{ $inspection->part_name ?: '-' }}</div>
                                        </td>
                                        <td>
                                            <span
                                                class="quality-badge {{ strtolower($inspection->judgement) }}">{{ $inspection->judgement }}</span>
                                            @if ($inspection->approval_status === 'NEED_APPROVAL')
                                                <div class="mt-1">
                                                    <span class="quality-badge pending">Pending</span>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-4">Belum ada history inspection.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection