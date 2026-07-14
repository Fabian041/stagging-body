@extends('layouts.root.auth')

<link rel="stylesheet" href="{{ asset('assets/css/page/pulling-index.css') }}">

@section('main')
    <div class="pulling-page">
        <div class="pulling-workspace">
            <header class="pulling-header">
                <p class="pulling-title">Pulling · <span>{{ auth()->user()->name }}</span></p>
                <div class="pulling-toolbar">
                    <button type="button" class="btn btn-warning" id="refreshTokenBtn">Refresh</button>
                    <button type="button" class="btn btn-danger" id="hardReset">Reset</button>
                    <form id="pullingLogoutForm" action="{{ route('logout.auth') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-logout">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            </header>

            <div class="pulling-body">
                <div class="pulling-panel pulling-panel--info">
                    <div class="pulling-ll-section">
                        <div class="pulling-section-head">
                            <span class="pulling-section-head__title">Loading list</span>
                            <span class="pulling-count-badge" id="total-ll">0</span>
                        </div>
                        <div class="row pulling-row pulling-ll-row">
                            <div class="col-12">
                                <ul class="pulling-ll-list" id="list">
                                    <li class="pulling-ll-item" id="loadingListContainerSample">
                                        <div class="pulling-ll-chip" id="loadingList">
                                            <span id="loadingList-display"></span>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="row pulling-row">
                        <div class="col-9">
                            <div class="pulling-tile">
                                <p class="pulling-tile__label">Customer</p>
                                <p class="pulling-tile__value" id="customer-display">Customer</p>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="pulling-tile">
                                <p class="pulling-tile__label">Cycle</p>
                                <p class="pulling-tile__value" id="cycle-display">Cycle</p>
                            </div>
                        </div>
                    </div>

                    <div class="row pulling-row">
                        <div class="col-6">
                            <div class="pulling-tile bg-default">
                                <p class="pulling-tile__label">Kanban Customer</p>
                                <p class="pulling-tile__value" id="cust-display">-</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="pulling-tile bg-default" id="tmmin-indicator">
                                <p class="pulling-tile__label">Kanban Internal</p>
                                <p class="pulling-tile__value" id="int-display">-</p>
                            </div>
                        </div>
                    </div>

                    <div class="row pulling-row">
                        <div class="col-6">
                            <div class="pulling-tile pulling-tile--compact">
                                <p class="pulling-tile__label">Alamat Chute</p>
                                <p class="pulling-tile__value" id="chute-display">-</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="pulling-tile pulling-tile--compact">
                                <p class="pulling-tile__label">Back No</p>
                                <p class="pulling-tile__value" id="backno-display">-</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="pulling-panel pulling-panel--ops">
                    <div class="row pulling-row align-items-center pulling-qty-row">
                        <div class="col-8">
                            <div class="d-flex align-items-center justify-content-between qbox">
                                <div class="d-flex align-items-center">
                                    <small class="badge badge-dark mr-2">Qty</small>
                                    <h5 class="mb-0"><span id="qty-display">-</span></h5>
                                </div>
                                <div id="indicator" class="indicator"></div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="d-flex justify-content-end">
                                <div class="direct-toggle">
                                    <input type="checkbox" id="directToggle" class="direct-toggle__input" />
                                    <label for="directToggle" class="direct-toggle__track" aria-label="Direct Mode">
                                        <span class="direct-toggle__thumb" aria-hidden="true"></span>
                                        <span class="direct-toggle__label direct-toggle__label--off">Direct</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="skid-display"></div>

                    <div class="pulling-scan-wrap">
                        <input id="code" class="form-control pulling-scan-input" name="code" required
                            autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false" inputmode="none"
                            aria-label="Scan barcode" />
                    </div>

                    <div class="pulling-actions">
                        <button type="button" class="btn btn-danger" id="delay">Delay</button>
                        <button type="button" class="btn btn-success" id="done">
                            <span class="pulling-done-label">Selesai</span>
                        </button>
                    </div>

                    <div class="text-center pulling-status-wrap">
                        <span class="pulling-status-badge" id="pullingStatusContainer">
                            <span id="pullingQty"></span>
                            <span id="pullingStatus"></span>
                        </span>
                    </div>

                    <div class="pulling-history-accordion" id="scanHistoryAccordion">
                        <button type="button" class="pulling-history-toggle" id="scanHistoryToggle"
                            aria-expanded="false" aria-controls="scanHistoryBody">
                            <span class="pulling-history-toggle__main">
                                <i class="fas fa-history"></i>
                                <span>Scan History</span>
                            </span>
                            <span class="pulling-history-toggle__meta">
                                <span class="pulling-count-badge" id="scanHistoryCount">0</span>
                                <i class="fas fa-chevron-down"></i>
                            </span>
                        </button>
                        <div class="pulling-history-body" id="scanHistoryBody" hidden>
                            <div class="pulling-history-list" id="scanHistoryList">
                                <div class="pulling-history-item">
                                    <span class="pulling-history-icon"><i class="fas fa-link"></i></span>
                                    <span class="pulling-history-copy">
                                        <span class="pulling-history-main">Belum ada pasangan kanban</span>
                                        <span class="pulling-history-meta">Scan customer lalu internal kanban</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade pulling-modal" id="modalLoadingListScan" data-bs-backdrop="static" data-bs-keyboard="false"
        tabindex="-1" aria-hidden="true" aria-labelledby="modalToggleLabel2">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content position-relative">
                <div class="loading-overlay" aria-live="polite" aria-busy="true">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                        <div class="mt-3 fw-semibold" id="loading-text">Memproses...</div>
                    </div>
                </div>

                <div class="modal-body py-3">
                    <h5 class="text-center">Loading list</h5>
                    <input type="text" class="form-control mt-2" id="input-loadingList" autocomplete="off">
                </div>
            </div>
        </div>
    </div>

    <div id="notifModal" class="pulling-scan-notif" hidden aria-hidden="true" aria-live="assertive">
        <div class="pulling-scan-notif-card is-info" id="divNotif" role="status">
            <div class="pulling-scan-notif-icon" id="notifIcon">
                <i class="fas fa-info"></i>
            </div>
            <div class="pulling-scan-notif-copy">
                <div class="pulling-scan-notif-title" id="notifTitle">Info</div>
                <div class="pulling-scan-notif-message" id="notif">Info</div>
            </div>
        </div>
    </div>

    <div class="modal fade pulling-modal pulling-finish-modal" id="finishProgressModal" tabindex="-1" role="dialog"
        aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="pulling-finish-progress pulling-finish-progress--timeline" id="finishProgress"
                    style="--finish-progress: 0%;" aria-live="polite">
                    <div class="pulling-finish-progress__head">
                        <span class="pulling-finish-progress__title">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                            <span id="finishProgressTitle">Memproses pulling...</span>
                        </span>
                        <span class="pulling-finish-progress__time" id="finishElapsed">0ms</span>
                    </div>
                    <div class="pulling-finish-progress__bar" aria-hidden="true"><span></span></div>
                    <div class="pulling-finish-progress__steps">
                        <div class="pulling-finish-step" data-finish-stage="sync"><span>Sync scan</span><strong>Menunggu</strong></div>
                        <div class="pulling-finish-step" data-finish-stage="local"><span>Baca lokal</span><strong>Menunggu</strong></div>
                        <div class="pulling-finish-step" data-finish-stage="validate"><span>Validasi qty</span><strong>Menunggu</strong></div>
                        <div class="pulling-finish-step" data-finish-stage="dea"><span>Kirim DEA</span><strong>Menunggu</strong></div>
                        <div class="pulling-finish-step" data-finish-stage="store"><span>Simpan status</span><strong>Menunggu</strong></div>
                    </div>
                </div>
                <div class="pulling-finish-modal__actions">
                    <button type="button" class="btn btn-light" id="finishProgressClose" data-dismiss="modal" hidden>
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- confirmation modal --}}
    <div class="modal fade pulling-modal" id="modalConfirmation" aria-hidden="true" aria-labelledby="modalToggleLabel2"
        tabindex="-1" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body py-3">
                    <h5 class="text-center">Konfirmasi JP / Leader</h5>
                    <p class="text-center text-danger small mb-2">Hubungi JP atau Leader</p>
                    <input type="text" class="form-control" id="input-confirmation" autocomplete="off" autofocus>
                </div>
            </div>
        </div>
    </div>
    {{-- end of modal --}}

    <div class="modal fade pulling-modal pulling-confirm-modal" id="pullingConfirmModal" tabindex="-1" role="dialog"
        aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="pulling-confirm-modal__body">
                    <div class="pulling-confirm-modal__icon" id="pullingConfirmIcon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h5 class="pulling-confirm-modal__title" id="pullingConfirmTitle">Konfirmasi</h5>
                    <p class="pulling-confirm-modal__message" id="pullingConfirmMessage">Lanjutkan aksi ini?</p>
                </div>
                <div class="pulling-confirm-modal__actions">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="pullingConfirmOk">Lanjutkan</button>
                </div>
            </div>
        </div>
    </div>

    <audio preload="none" id="ok-sound">
        <source src={{ asset('assets/sounds/ok.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio preload="none" id="not-match-sound">
        <source src={{ asset('assets/sounds/notMatch.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio preload="none" id="not-match-ll-sound">
        <source src={{ asset('assets/sounds/notMatch-ll.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio preload="none" id="unknown-sound">
        <source src={{ asset('assets/sounds/unknown.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio preload="none" id="unknown-ll-sound">
        <source src={{ asset('assets/sounds/unknown-ll.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio preload="none" id="already-scan-sound">
        <source src={{ asset('assets/sounds/already-scan.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio preload="none" id="already-scan-ll-sound">
        <source src={{ asset('assets/sounds/already-scan-ll.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio preload="none" id="uncomplete-ll-sound">
        <source src={{ asset('assets/sounds/uncomplete-ll.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio preload="none" id="fullfilled-sound">
        <source src={{ asset('assets/sounds/fullfilled.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio preload="none" id="scan-customer-first-sound">
        <source src={{ asset('assets/sounds/scan-customer-first.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio preload="none" id="finish-pulling-sound">
        <source src={{ asset('assets/sounds/finish-pulling.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio preload="none" id="already-pulled-sound">
        <source src={{ asset('assets/sounds/already-pulled.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio preload="none" id="not-exist-sound">
        <source src={{ asset('assets/sounds/notExist.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio preload="none" id="kanban-not-exist-sound">
        <source src={{ asset('assets/sounds/kanbanNotExist.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio preload="none" id="part-not-exist-sound">
        <source src={{ asset('assets/sounds/partNotExist.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio preload="none" id="not-scanned-sound">
        <source src={{ asset('assets/sounds/notScanned.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
@endsection
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
@include('pages.pulling.partials.scripts')
