@extends('layouts.root.auth')

<style>
    /* Halaman pulling — putih, rapi, ukuran nyaman */
    body.auth-body:has(.pulling-page),
    body.auth-body:has(.pulling-page) #app {
        background: #fff;
        min-height: 100vh;
    }

    .pulling-page {
        min-height: 100vh;
        width: 100%;
        background: #fff;
    }

    .pulling-workspace {
        width: 100%;
        max-width: 100%;
        margin: 0 auto;
        padding: 1.25rem 1.25rem 2rem;
        box-sizing: border-box;
    }

    .pulling-header,
    .pulling-panel {
        padding-left: 0;
        padding-right: 0;
    }

    .pulling-body {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .pulling-panel {
        display: flex;
        flex-direction: column;
        gap: .75rem;
        min-width: 0;
    }

    .pulling-row.row {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    .pulling-row>[class*="col-"] {
        padding-left: 0;
        padding-right: 10px;
    }

    .pulling-row>[class*="col-"]:last-child {
        padding-right: 0;
    }

    .pulling-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin-bottom: 1.25rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border, #DDE3EF);
    }

    .pulling-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--text, #1A2340);
    }

    .pulling-title span {
        color: var(--text-muted, #6B7A99);
        font-weight: 500;
    }

    .pulling-toolbar {
        display: flex;
        gap: 8px;
    }

    .pulling-toolbar .btn {
        min-height: 38px;
        padding: 0 16px;
        font-size: 13px;
        font-weight: 600;
    }

    .pulling-page .btn-danger,
    .pulling-page .btn-danger:hover,
    .pulling-page .btn-danger:focus,
    .pulling-page .btn-danger:active {
        color: #fff !important;
        background-color: var(--danger, #DC2626);
        border-color: var(--danger, #DC2626);
    }

    .pulling-page .btn-outline-danger:hover,
    .pulling-page .btn-outline-danger:focus,
    .pulling-page .btn-outline-danger:active {
        color: #fff !important;
    }

    .pulling-page #delay {
        color: #DC2626 !important;
        background-color: #FEECEC !important;
        border-color: #FBD0D0 !important;
    }

    .pulling-page #delay:hover,
    .pulling-page #delay:focus {
        color: #C81E1E !important;
        background-color: #FCDDDD !important;
        border-color: #F6B8B8 !important;
    }

    .pulling-page #delay:active,
    .pulling-page #delay:focus:active {
        color: #B91C1C !important;
        background-color: #F9CFCF !important;
        border-color: #F2A5A5 !important;
    }

    .pulling-section-head {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: .25rem;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-muted, #6B7A99);
    }

    .pulling-section-head__title {
        margin: 0;
    }

    .pulling-count-badge {
        font-size: 12px;
        font-weight: 700;
        padding: .15rem .5rem;
        border-radius: 6px;
        background: var(--primary-light, #E8F4FD);
        color: var(--primary, #0070B7);
    }

    .pulling-ll-section {
        width: 100%;
        margin: 0;
        padding: 0;
    }

    .pulling-ll-list {
        list-style: none;
        margin: 0;
        padding: 0;
        padding-inline-start: 0;
        width: 100%;
    }

    .pulling-ll-item {
        margin: .5rem 0 0;
        padding: 0;
        list-style: none;
        width: 100%;
    }

    .pulling-ll-row>.col-12 {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .pulling-ll-chip {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 2.75rem;
        padding: .5rem .75rem;
        border-radius: 8px;
        background: var(--primary-light, #E8F4FD);
        border: 1px solid #c5dff0;
        color: var(--text, #1A2340);
        font-size: 1.05rem;
        font-weight: 700;
    }

    .pulling-ll-chip span {
        margin: 0;
    }

    .pulling-tile {
        background: #f8fafc;
        border: 1px solid var(--border, #DDE3EF);
        border-radius: 8px;
        padding: .7rem .85rem;
        min-height: 4rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .pulling-tile__label {
        font-size: 12px;
        color: var(--text-muted, #6B7A99);
        margin: 0 0 .25rem;
        font-weight: 500;
    }

    .pulling-tile__value {
        font-size: 1.05rem;
        font-weight: 700;
        color: var(--text, #1A2340);
        text-align: center;
        margin: 0;
        line-height: 1.3;
        word-break: break-word;
    }

    .pulling-tile--compact {
        min-height: 3.75rem;
    }

    .pulling-tile.bg-default {
        background: var(--primary-light, #E8F4FD);
        border-color: #c5dff0;
    }

    .pulling-tile.bg-success {
        background: #ecfdf5 !important;
        border-color: #86efac !important;
    }

    .pulling-tile.bg-warning {
        background: #fffbeb !important;
        border-color: #fcd34d !important;
    }

    .pulling-tile.bg-danger {
        background: #fef2f2 !important;
        border-color: #fca5a5 !important;
    }

    .pulling-scan-wrap {
        padding: .25rem 0;
    }

    .pulling-scan-input {
        height: 3.25rem;
        border: 1px solid var(--border, #DDE3EF);
        border-radius: 8px;
        font-size: 1.1rem;
        font-weight: 600;
        text-align: center;
        background: #fff;
    }

    .pulling-scan-input:focus {
        border-color: var(--primary, #0070B7);
        box-shadow: 0 0 0 3px var(--primary-light, #E8F4FD);
        outline: none;
    }

    .pulling-actions {
        display: grid;
        grid-template-columns: 2fr 3fr;
        gap: 12px;
    }

    .pulling-actions .btn {
        height: 3.25rem;
        font-size: 1.05rem;
        font-weight: 700;
        border-radius: 8px;
    }

    .pulling-status-wrap {
        margin-top: .75rem;
    }

    .pulling-status-badge {
        display: inline-block;
        padding: .45rem 1rem;
        border-radius: 8px;
        background: #f1f5f9;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-muted, #6B7A99);
    }

    .pulling-status-badge #pullingQty,
    .pulling-status-badge #pullingStatus {
        color: inherit;
    }

    .pulling-status-badge.bg-danger,
    .pulling-status-badge.bg-danger #pullingQty,
    .pulling-status-badge.bg-danger #pullingStatus {
        color: #fff !important;
    }

    .pulling-status-badge.bg-success,
    .pulling-status-badge.bg-success #pullingQty,
    .pulling-status-badge.bg-success #pullingStatus {
        color: #fff !important;
    }

    .pulling-status-badge.bg-warning,
    .pulling-status-badge.bg-warning #pullingQty,
    .pulling-status-badge.bg-warning #pullingStatus {
        color: #1A2340 !important;
    }

    .pulling-history-accordion {
        border: 1px solid var(--border, #DDE3EF);
        border-radius: 8px;
        background: #f8fafc;
        overflow: hidden;
    }

    .pulling-history-toggle {
        width: 100%;
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: .7rem .85rem;
        border: 0;
        background: transparent;
        color: var(--text, #1A2340);
        font: inherit;
        font-weight: 800;
        text-align: left;
    }

    .pulling-history-toggle:focus,
    .pulling-history-toggle:active {
        outline: 0 !important;
        box-shadow: none !important;
    }

    .pulling-history-accordion.is-open {
        border-color: transparent;
    }

    .pulling-history-toggle__main,
    .pulling-history-toggle__meta {
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .pulling-history-toggle__main i {
        color: var(--primary, #0070B7);
    }

    .pulling-history-toggle__meta i {
        color: var(--text-muted, #6B7A99);
        transition: transform .16s ease;
    }

    .pulling-history-accordion.is-open .pulling-history-toggle__meta i {
        transform: rotate(180deg);
    }

    .pulling-history-body {
        border-top: 0;
        padding: .75rem;
        background: #fff;
    }

    .pulling-history-list {
        display: grid;
        gap: 8px;
        max-height: 220px;
        overflow: auto;
    }

    .pulling-history-item {
        display: grid;
        grid-template-columns: 32px minmax(0, 1fr);
        gap: 9px;
        align-items: center;
        padding: .55rem .65rem;
        border: 0;
        border-radius: 8px;
        background: #f8fafc;
    }

    .pulling-history-item.is-success {
        background: #f0fdf4;
    }

    .pulling-history-icon {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        color: var(--primary, #0070B7);
        background: var(--primary-light, #E8F4FD);
        font-size: 13px;
    }

    .pulling-history-item.is-success .pulling-history-icon {
        color: var(--success, #16A34A);
        background: var(--success-light, #DCFCE7);
    }

    .pulling-history-main {
        display: block;
        color: var(--text, #1A2340);
        font-size: 13px;
        font-weight: 800;
        line-height: 1.25;
        overflow-wrap: anywhere;
    }

    .pulling-history-meta {
        display: block;
        margin-top: 2px;
        color: var(--text-muted, #6B7A99);
        font-size: 11px;
        font-weight: 700;
    }

    .pulling-skid-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        padding: .75rem 1rem;
        border: 1px solid var(--border, #DDE3EF);
        border-radius: 8px;
        background: #f8fafc;
    }

    .pulling-skid-info {
        display: flex;
        align-items: center;
        gap: .65rem;
    }

    .pulling-skid-label {
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted, #6B7A99);
    }

    .pulling-skid-value {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--text, #1A2340);
    }

    .pulling-skid-bar #close-skid {
        margin: 0;
        font-size: 12px;
        padding: .35rem .65rem;
    }

    .pulling-modal .modal-content {
        border-radius: 8px;
        border: 1px solid var(--border, #DDE3EF);
    }

    .pulling-modal .modal-body h5 {
        font-size: 1.05rem;
        font-weight: 600;
    }

    .pulling-modal .form-control {
        height: 3rem;
        border-radius: 8px;
        font-size: 1rem;
        text-align: center;
    }

    .pulling-confirm-modal .modal-content {
        border-radius: 14px;
        border: 1px solid rgba(221, 227, 239, .9);
        box-shadow: 0 24px 70px rgba(26, 35, 64, .22);
        overflow: hidden;
    }

    .pulling-confirm-modal__body {
        padding: 26px;
    }

    .pulling-confirm-modal__icon {
        width: 58px;
        height: 58px;
        margin: 0 auto 14px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--danger, #DC2626);
        background: var(--danger-light, #FEE2E2);
        font-size: 24px;
    }

    .pulling-confirm-modal__title {
        margin: 0 0 8px;
        color: var(--text, #1A2340);
        font-size: 20px;
        font-weight: 900;
        text-align: center;
    }

    .pulling-confirm-modal__message {
        margin: 0;
        color: var(--text-muted, #6B7A99);
        font-size: 13px;
        font-weight: 600;
        line-height: 1.55;
        text-align: center;
    }

    .pulling-confirm-modal__actions {
        display: flex;
        gap: 10px;
        justify-content: center;
        padding: 0 26px 26px;
    }

    .pulling-confirm-modal__actions .btn {
        min-width: 112px;
        min-height: 42px;
    }

    .pulling-scan-notif {
        position: fixed;
        inset: 0;
        z-index: 5000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(15, 23, 42, .12);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity .16s ease, visibility .16s ease;
    }

    .pulling-scan-notif.is-visible {
        opacity: 1;
        visibility: visible;
    }

    .pulling-scan-notif-card {
        --notif-accent: var(--primary, #0070B7);
        --notif-bg: linear-gradient(135deg, #E8F4FD 0%, #F6FBFF 100%);
        --notif-icon-bg: rgba(255, 255, 255, .72);
        --notif-title: #315F92;
        --notif-text: #123A63;
        width: min(680px, calc(100vw - 32px));
        min-height: 208px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 14px;
        padding: 44px 30px;
        border-radius: 14px;
        border: 0;
        background: var(--notif-bg);
        box-shadow: 0 24px 70px rgba(26, 35, 64, .24), inset 0 1px 0 rgba(255, 255, 255, .75);
        backdrop-filter: blur(18px) saturate(160%);
        -webkit-backdrop-filter: blur(18px) saturate(160%);
        transform: translateY(10px) scale(.98);
        transition: transform .16s ease;
    }

    .pulling-scan-notif.is-visible .pulling-scan-notif-card {
        transform: translateY(0) scale(1);
    }

    .pulling-scan-notif-card.is-success {
        --notif-accent: var(--success, #16A34A);
        --notif-bg: linear-gradient(135deg, #DCFCE7 0%, #F0FDF4 100%);
        --notif-title: #166534;
        --notif-text: #14532D;
    }

    .pulling-scan-notif-card.is-error {
        --notif-accent: var(--danger, #DC2626);
        --notif-bg: linear-gradient(135deg, #FEE2E2 0%, #FFF1F2 100%);
        --notif-title: #B91C1C;
        --notif-text: #7F1D1D;
    }

    .pulling-scan-notif-card.is-warning {
        --notif-accent: var(--warning, #D97706);
        --notif-bg: linear-gradient(135deg, #FEF3C7 0%, #FFFBEB 100%);
        --notif-title: #92400E;
        --notif-text: #78350F;
    }

    .pulling-scan-notif-copy {
        min-width: 0;
        text-align: center;
    }

    .pulling-scan-notif-icon {
        width: 72px;
        height: 72px;
        border-radius: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--notif-accent);
        background: var(--notif-icon-bg);
        box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .55);
        font-size: 30px;
    }

    .pulling-scan-notif-title {
        margin-bottom: 6px;
        color: var(--notif-title);
        font-size: 13px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .pulling-scan-notif-message {
        color: var(--notif-text);
        font-size: clamp(26px, 3vw, 38px);
        font-weight: 900;
        line-height: 1.18;
        overflow-wrap: anywhere;
    }

    @media (max-width: 576px) {
        .pulling-scan-notif-card {
            gap: 12px;
            min-height: 180px;
            padding: 34px 18px;
            border-radius: 12px;
        }

        .pulling-scan-notif-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            font-size: 22px;
        }

        .pulling-scan-notif-message {
            font-size: 23px;
        }
    }

    .bg-default {
        background: var(--primary-light, #E8F4FD) !important;
        border: 1px solid #c5dff0 !important;
    }

    #modalLoadingListScan .loading-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        /* selalu flex */
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, .35);
        backdrop-filter: blur(1px);
        z-index: 10;

        /* hidden by default */
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transition: opacity .15s ease;
    }

    #modalLoadingListScan .loading-overlay.is-active {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    #modalLoadingListScan .spinner-border {
        width: 3rem;
        height: 3rem;
    }

    .glass-tile {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        min-height: 2.75rem;
        padding: .5rem .75rem;
        border-radius: 8px;
        border: 1px solid var(--border, #DDE3EF);
        background: #f8fafc;
        color: var(--text-muted, #6B7A99);
        font-size: .9rem;
        font-weight: 500;
    }

    .glass-tile--loading {
        border-color: #c5dff0;
        background: var(--primary-light, #E8F4FD);
        color: var(--primary, #0070B7);
    }

    .glass-tile--error {
        border-color: #fca5a5;
        background: #fef2f2;
        color: var(--danger, #DC2626);
    }

    .glass-tile .spinner-border {
        width: 1rem;
        height: 1rem;
        color: var(--primary, #0070B7);
    }

    .qbox {
        background: #f8fafc;
        border: 1px solid var(--border, #DDE3EF);
        border-radius: 8px;
        padding: .75rem 1rem;
        min-height: 3.5rem;
    }

    .qbox .badge-dark {
        background: #fff !important;
        color: var(--text-muted, #6B7A99) !important;
        font-size: 11px;
        font-weight: 600;
        border: 1px solid var(--border, #DDE3EF);
    }

    .qbox h5,
    .qbox #qty-display {
        color: var(--text, #1A2340) !important;
        font-size: 1.25rem;
        font-weight: 700;
    }

    .indicator {
        width: 56px;
        height: 26px;
        border-radius: 6px;
        background: #f59e0b;
        flex-shrink: 0;
    }

    /* ===== Slide Toggle (tetap sama seperti versi animasi) ===== */
    .direct-toggle {
        display: inline-flex;
        align-items: center;
        height: 38px;
        user-select: none;
    }

    .direct-toggle__input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .direct-toggle__track {
        --track-w: 120px;
        --track-h: 32px;
        --pad: 3px;
        --thumb: 26px;
        position: relative;
        width: var(--track-w);
        height: var(--track-h);
        border-radius: calc(var(--track-h)/2);
        background: #eef2f7;
        border: 1px solid var(--border, #DDE3EF);
        cursor: pointer;
        overflow: hidden;
        display: inline-flex;
        align-items: center;
        padding: 0 var(--pad);
        transition: background .2s ease, border-color .2s ease;
    }

    .direct-toggle__thumb {
        position: absolute;
        top: 50%;
        left: var(--pad);
        width: var(--thumb);
        height: var(--thumb);
        transform: translateY(-50%);
        border-radius: 999px;
        background: #fff;
        border: 1px solid var(--border, #DDE3EF);
        transition: transform .2s ease;
    }

    .direct-toggle__label {
        font-size: 12px;
        font-weight: 600;
        letter-spacing: .2px;
        color: #637085;
        z-index: 1;
        transition: color .25s ease, opacity .25s ease;
        pointer-events: none;
        user-select: none;
    }

    .direct-toggle__label--off {
        margin-left: calc(var(--thumb) + .5rem);
        opacity: 1;
    }

    .direct-toggle__label--on {
        margin-right: calc(var(--thumb) + .5rem);
        opacity: .55;
    }

    .direct-toggle__track:active .direct-toggle__thumb {
        width: calc(var(--thumb) + 8px);
    }

    .direct-toggle__input:focus+.direct-toggle__track {
        outline: 3px solid rgba(3, 177, 252, .25);
        outline-offset: 2px;
    }

    /* State: ON */
    .direct-toggle__input:checked+.direct-toggle__track {
        background: var(--primary, #0070B7);
        border-color: var(--primary, #0070B7);
    }

    .direct-toggle__input:checked+.direct-toggle__track .direct-toggle__thumb {
        transform: translate(calc(var(--track-w) - var(--thumb) - var(--pad) - var(--pad)), -50%) !important;
    }

    .direct-toggle__input:checked+.direct-toggle__track .direct-toggle__label--off {
        opacity: .5;
        color: #fff;
    }

    .direct-toggle__input:checked+.direct-toggle__track .direct-toggle__label--on {
        opacity: 1;
        color: #fff;
    }

    @media (max-width: 575.98px) {
        .pulling-workspace {
            padding-left: 1.125rem;
            padding-right: 1.125rem;
        }

        .pulling-header,
        .pulling-panel {
            padding-left: 0;
            padding-right: 0;
        }

        .pulling-qty-row {
            display: flex;
            flex-wrap: nowrap;
            align-items: stretch;
            gap: 6px;
            margin-left: 0;
            margin-right: 0;
            padding-left: 0;
            padding-right: 0;
        }

        .pulling-qty-row>.col-8,
        .pulling-qty-row>.col-4 {
            padding: 0 !important;
            max-width: none;
        }

        .pulling-qty-row>.col-8 {
            flex: 1 1 auto;
            min-width: 0;
            width: auto;
        }

        .pulling-qty-row>.col-4 {
            flex: 0 0 auto;
            width: auto;
            display: flex;
            align-items: center;
        }

        .pulling-qty-row .d-flex.justify-content-end {
            justify-content: flex-end !important;
            width: auto;
        }

        .pulling-qty-row .direct-toggle {
            flex-shrink: 0;
        }

        .pulling-qty-row .direct-toggle__track {
            --track-w: 108px;
            --track-h: 30px;
            width: 108px;
        }

        .pulling-toolbar {
            width: 100%;
            justify-content: flex-end;
        }

        .pulling-header {
            flex-direction: column;
            align-items: stretch;
        }
    }

    @media (min-width: 576px) {
        .pulling-workspace {
            max-width: 600px;
        }
    }

    @media (min-width: 768px) {
        .pulling-workspace {
            max-width: 900px;
        }

        .pulling-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem 2rem;
            align-items: start;
        }

        .pulling-title {
            font-size: 1.2rem;
        }

        .pulling-tile {
            min-height: 4.5rem;
            padding: .85rem 1rem;
        }

        .pulling-tile__value {
            font-size: 1.15rem;
        }

        .pulling-ll-chip {
            min-height: 3rem;
            font-size: 1.1rem;
        }

        .pulling-scan-input {
            height: 3.5rem;
            font-size: 1.15rem;
        }

        .pulling-actions .btn {
            height: 3.5rem;
            font-size: 1.1rem;
        }

        .qbox {
            min-height: 4rem;
        }

        .qbox h5,
        .qbox #qty-display {
            font-size: 1.4rem;
        }

        .direct-toggle__track {
            --track-w: 140px;
            --track-h: 36px;
        }
    }

    @media (min-width: 992px) {
        .pulling-workspace {
            max-width: 1040px;
        }

        .pulling-body {
            gap: 1.75rem 2.5rem;
        }
    }

    @media (min-width: 1200px) {
        .pulling-workspace {
            max-width: 1100px;
        }
    }
</style>

@section('main')
    <div class="pulling-page">
        <div class="pulling-workspace">
            <header class="pulling-header">
                <p class="pulling-title">Pulling · <span>{{ auth()->user()->name }}</span></p>
                <div class="pulling-toolbar">
                    <button type="button" class="btn btn-warning" id="refreshTokenBtn">Refresh</button>
                    <button type="button" class="btn btn-danger" id="hardReset">Reset</button>
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
                        <button type="button" class="btn btn-success" id="done">Selesai</button>
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

    <audio id="ok-sound">
        <source src={{ asset('assets/sounds/ok.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="not-match-sound">
        <source src={{ asset('assets/sounds/notMatch.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="not-match-ll-sound">
        <source src={{ asset('assets/sounds/notMatch-ll.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="unknown-sound">
        <source src={{ asset('assets/sounds/unknown.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="unknown-ll-sound">
        <source src={{ asset('assets/sounds/unknown-ll.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="already-scan-sound">
        <source src={{ asset('assets/sounds/already-scan.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="already-scan-ll-sound">
        <source src={{ asset('assets/sounds/already-scan-ll.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="uncomplete-ll-sound">
        <source src={{ asset('assets/sounds/uncomplete-ll.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="fullfilled-sound">
        <source src={{ asset('assets/sounds/fullfilled.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="scan-customer-first-sound">
        <source src={{ asset('assets/sounds/scan-customer-first.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="finish-pulling-sound">
        <source src={{ asset('assets/sounds/finish-pulling.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="already-pulled-sound">
        <source src={{ asset('assets/sounds/already-pulled.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="not-exist-sound">
        <source src={{ asset('assets/sounds/notExist.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="kanban-not-exist-sound">
        <source src={{ asset('assets/sounds/kanbanNotExist.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="part-not-exist-sound">
        <source src={{ asset('assets/sounds/partNotExist.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
    <audio id="not-scanned-sound">
        <source src={{ asset('assets/sounds/notScanned.mp3') }} type="audio/mpeg">
        <!-- Add additional <source> elements for other audio formats if needed -->
    </audio>
@endsection
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script>
    let line = '';
    let partDetail = {};
    let part = 'part';
    let partNumber;
    let loadingListItem = [];
    let loadinglistDetail = [];
    var loadingListNumber = [];
    const inFlightLL = new Set();
    // flag selesai: tandai LL yang SUDAH beres proses agar tidak bisa discan lagi
    const doneKey = (ll) => `llDone_${ll}`;
    const legacyDoneKey = (ll) => `ll_done_${ll}`;
    const isLLDone = (ll) => (
        localStorage.getItem(doneKey(ll)) === '1' ||
        localStorage.getItem(legacyDoneKey(ll)) === '1'
    );
    const markLLDone = (ll) => {
        try {
            localStorage.setItem(doneKey(ll), '1');
            localStorage.removeItem(legacyDoneKey(ll));
        } catch (e) {}
    };

    function pullingLlMarkup(number, liId) {
        const id = liId || 'loadingListContainer';
        return `<li class="pulling-ll-item" id="${id}">
            <div class="pulling-ll-chip" id="loadingList">
                <span id="loadingList-display">${number}</span>
            </div>
        </li>`;
    }

    function pullingSkidMarkup(skid) {
        return `<div class="row mt-2 px-1">
            <div class="col-12">
                <div class="pulling-skid-bar">
                    <div class="pulling-skid-info">
                        <span class="pulling-skid-label">Skid</span>
                        <span class="pulling-skid-value" id="skid-display">${skid}</span>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm" id="close-skid">Close Skid ${skid}</button>
                </div>
            </div>
        </div>`;
    }

    function okSound() {
        var sound = document.getElementById("ok-sound");
        sound.play();
    }

    function kanbanNotExistSound() {
        var sound = document.getElementById("kanban-not-exist-sound");
        sound.play();
    }

    function partNotExistSound() {
        var sound = document.getElementById("part-not-exist-sound");
        sound.play();
    }

    function notScannedSound() {
        var sound = document.getElementById("not-scanned-sound");
        sound.play();
    }

    function notExist() {
        var sound = document.getElementById("not-exist-sound");
        sound.play();
    }

    function notMatchSound() {
        var sound = document.getElementById("not-match-sound");
        sound.play();
    }

    function notMatchLlSound() {
        var sound = document.getElementById("not-match-ll-sound");
        sound.play();
    }

    function unknownSound() {
        var sound = document.getElementById("unknown-sound");
        sound.play();
    }

    function unknownLlSound() {
        var sound = document.getElementById("unknown-ll-sound");
        sound.play();
    }

    function alreadyScanSound() {
        var sound = document.getElementById("already-scan-sound");
        sound.play();
    }

    function alreadyPulledSound() {
        var sound = document.getElementById("already-pulled-sound");
        sound.play();
    }

    function alreadyScanLlSound() {
        var sound = document.getElementById("already-scan-ll-sound");
        sound.play();
    }

    function uncompleteLlSound() {
        var sound = document.getElementById("uncomplete-ll-sound");
        sound.play();
    }

    function fullfilledSound() {
        var sound = document.getElementById("fullfilled-sound");
        sound.play();
    }

    function scanCustomerFirstSound() {
        var sound = document.getElementById("scan-customer-first-sound");
        sound.play();
    }

    function finishPullingSound() {
        var sound = document.getElementById("finish-pulling-sound");
        sound.play();
    }

    // extract the loading list number from the key
    function extractLoadingListNumber(key) {
        const prefix = "ll_";
        return key.substring(prefix.length);
    }

    function extractManifest(key) {
        const prefix = "skid_";
        return key.substring(prefix.length);
    }

    // retrieve the loading list number from localStorage
    function getLoadingListNumber() {
        loadingListNumber = [];
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key.startsWith("ll_") && !key.startsWith("ll_done_")) {
                const number = extractLoadingListNumber(key);
                if (number && !loadingListNumber.includes(number)) {
                    loadingListNumber.push(number);
                }
            }
        }
        // Return a default value if no loading list number is found
        return loadingListNumber;
    }

    // get manifest from skid prefix
    function getManifest() {
        let manifest = [];
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key.startsWith("skid_")) {
                manifest.push(extractManifest(key));
            }
        }
        // Return a default value if no manifest is found
        return manifest;
    }

    const KEY_DIRECT_MODE = 'directMode'; // 'on' | 'off'

    function setDirectMode(on) {
        try {
            localStorage.setItem(KEY_DIRECT_MODE, on ? 'on' : 'off');
        } catch (e) {}
    }

    function isDirectOn() {
        try {
            return (localStorage.getItem(KEY_DIRECT_MODE) || 'off') === 'on';
        } catch (e) {
            return false;
        }
    }

    // cache hasil alamat chute (master data statis) → unit ke-2 dst dari part yang sama tidak nembak server lagi
    var chuteCache = {};

    function applyChute(c) {
        $('#chute-display').text(c.alamat_chute || '-');
        if (c.back_no) {
            $('#backno-display').text(c.back_no);
        }
    }

    function fetchAlamatChute(loadingList, customerPart, internalPart) {
        if (!loadingList || !customerPart) {
            $('#chute-display').text('-');
            return;
        }

        var key = loadingList + '|' + customerPart + '|' + (internalPart || '');
        if (chuteCache[key]) {
            applyChute(chuteCache[key]);
            return;
        }

        $('#chute-display').text('Loading...');
        $.ajax({
            type: 'GET',
            url: "{{ route('pulling.alamat-chute') }}",
            data: {
                loadingList: loadingList,
                customerPart: customerPart,
                internalPart: internalPart || ''
            },
            dataType: 'json',
            success: function(resp) {
                // cache hanya saat sukses (jangan cache error jaringan)
                chuteCache[key] = {
                    alamat_chute: resp?.alamat_chute || null,
                    back_no: resp?.back_no || null
                };
                applyChute(chuteCache[key]);
            },
            error: function() {
                $('#chute-display').text('-');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        const toggle = document.getElementById('directToggle');
        if (!toggle) return;
        toggle.checked = isDirectOn();
        toggle.addEventListener('change', () => {
            setDirectMode(toggle.checked);
            // feedback kecil
            if (typeof notif === 'function') notif(toggle.checked ? 'success' : 'info',
                `Direct ${toggle.checked?'ON':'OFF'}`);
            setTimeout(() => $('#code').focus(), 200);
        });
    });

    const STORE_NAME = 'loadingList';
    const SERIAL_STORE_NAME = 'scanSerials';
    const SUMMARY_STORE_NAME = 'scanSummary';
    const SUMMARY_KEY = 'current';
    const REQUIRED_IDB_STORES = [STORE_NAME, SERIAL_STORE_NAME, SUMMARY_STORE_NAME];

    function ensurePullingStores(database) {
        REQUIRED_IDB_STORES.forEach((storeName) => {
            if (!database.objectStoreNames.contains(storeName)) {
                const store = database.createObjectStore(storeName);
                if (storeName === STORE_NAME) store.createIndex('loadingListDetail', 'seri');
            }
        });
    }

    function openPullingDB(dbName) {
        return new Promise((resolve, reject) => {
            const req = indexedDB.open(dbName);
            req.onerror = () => reject(new Error(`Gagal buka DB "${dbName}"`));
            req.onsuccess = (ev) => {
                const db = ev.target.result;
                const missingStore = REQUIRED_IDB_STORES.some((storeName) => !db.objectStoreNames.contains(
                    storeName));
                if (!missingStore) return resolve(db);

                const nextVersion = (db.version || 1) + 1;
                db.close();
                const req2 = indexedDB.open(dbName, nextVersion);
                req2.onupgradeneeded = (e) => ensurePullingStores(e.target.result);
                req2.onsuccess = (e) => resolve(e.target.result);
                req2.onerror = () => reject(new Error('Upgrade DB pulling gagal'));
            };
        });
    }

    function openDBEnsureStore(dbName) {
        return openPullingDB(dbName);
    }

    function scanSerialKey(customerPart, serial) {
        return `${String(customerPart || '').trim()}|${String(serial || '').trim()}`;
    }

    function getAllLoadingListRecords(database) {
        return new Promise((resolve, reject) => {
            const records = [];
            const transaction = database.transaction([STORE_NAME], 'readonly');
            const request = transaction.objectStore(STORE_NAME).openCursor();

            request.onsuccess = function(event) {
                const cursor = event.target.result;
                if (!cursor) {
                    resolve(records);
                    return;
                }

                records.push(cursor.value);
                cursor.continue();
            };
            request.onerror = () => reject(request.error || new Error('Gagal membaca loading list lokal'));
        });
    }

    function fallbackScanRowsForRecord(record, seen = new Set()) {
        const rows = [];
        const fallbackSerials = Array.isArray(record?.seri) ? record.seri : [];
        const buckets = loadingListBucketEntries(record);
        let bucketIndex = 0;
        let bucketUsed = 0;

        fallbackSerials.forEach((serial) => {
            const value = String(serial || '').trim();
            if (!value || seen.has(value)) return;

            while (buckets[bucketIndex] && bucketUsed >= buckets[bucketIndex].target) {
                bucketIndex += 1;
                bucketUsed = 0;
            }

            const bucket = buckets[bucketIndex] || buckets[0];
            rows.push({
                serial: value,
                loading_list_number: bucket?.number || record?.loading_list_number,
                scanned_at: null
            });
            seen.add(value);
            bucketUsed += 1;
        });

        return rows;
    }

    function getScanRowsForPart(database, record) {
        return new Promise((resolve, reject) => {
            const rows = [];
            const seen = new Set();
            const prefix = `${String(record?.customer || '').trim()}|`;
            const range = IDBKeyRange.bound(prefix, `${prefix}\uffff`);
            const transaction = database.transaction([SERIAL_STORE_NAME], 'readonly');
            const request = transaction.objectStore(SERIAL_STORE_NAME).openCursor(range);

            request.onsuccess = function(event) {
                const cursor = event.target.result;
                if (!cursor) {
                    rows.sort((a, b) => pullingInt(a.scanned_at) - pullingInt(b.scanned_at));
                    resolve(rows.concat(fallbackScanRowsForRecord(record, seen)));
                    return;
                }

                const value = String(cursor.value?.serial || '').trim();
                if (value && !seen.has(value)) {
                    seen.add(value);
                    rows.push({
                        serial: value,
                        loading_list_number: cursor.value?.loading_list_number || record
                            ?.loading_list_number,
                        scanned_at: cursor.value?.scanned_at || null
                    });
                }
                cursor.continue();
            };
            request.onerror = () => reject(request.error || new Error('Gagal membaca serial scan'));
        });
    }

    function getPartRecordFromIDB(customerPart) {
        return new Promise(async (resolve, reject) => {
            try {
                const dbName = localStorage.getItem('pds_local') || localStorage.getItem('pdsNumber');
                if (!dbName) return reject(new Error('DB belum siap, scan Loading List dulu.'));
                const db = await openDBEnsureStore(dbName, STORE_NAME);
                const tx = db.transaction([STORE_NAME], 'readonly');
                const st = tx.objectStore(STORE_NAME);
                const g = st.get(customerPart);
                g.onsuccess = () => {
                    const rec = g.result;
                    db.close();
                    rec ? resolve(rec) : reject(new Error(`Part "${customerPart}" tidak ada`));
                };
                g.onerror = (e) => {
                    db.close();
                    reject(e?.target?.error || new Error('Gagal ambil record'));
                };
            } catch (err) {
                reject(err);
            }
        });
    }

    async function directSingleScan(customerPart) {
        try {
            // safety: hanya jalan jika Direct ON
            if ((localStorage.getItem('directMode') || 'off') !== 'on') return;

            const dbName = localStorage.getItem('pds_local') || localStorage.getItem('pdsNumber');
            if (!dbName) throw new Error('DB belum siap. Scan Loading List dulu.');

            // 1) Ambil record dari IDB -> butuh internal & loading_list_number
            const rec = await getPartRecordFromIDB(customerPart);

            const internalPart = String(rec.internal || '').trim();
            const loadingList = nextLoadingListForScan(rec);
            if (!internalPart || !loadingList) throw new Error('Mapping internal/LL tidak ditemukan');

            // 2) Panggil backend untuk increment actual_kanban_qty (single scan)
            const res = await $.ajax({
                type: 'GET',
                dataType: 'json',
                url: "{{ route('kanban.scanned') }}",
                data: {
                    loadingList,
                    internalPart,
                    customerPart
                }
            });
            if (!res || res.status !== 'success') throw new Error(res?.message || 'Store gagal');

            // 3) Update IDB lokal: simpan serial ringan + seri legacy untuk UI lama
            await new Promise((resolve, reject) => {
                openPullingDB(dbName).then((db) => {
                    const tx = db.transaction([STORE_NAME, SERIAL_STORE_NAME, SUMMARY_STORE_NAME],
                        'readwrite');
                    const st = tx.objectStore(STORE_NAME);
                    const serialStore = tx.objectStore(SERIAL_STORE_NAME);
                    const summaryStore = tx.objectStore(SUMMARY_STORE_NAME);
                    const g = st.get(customerPart);

                    tx.oncomplete = () => {
                        db.close();
                        resolve();
                    };
                    tx.onerror = (event) => {
                        db.close();
                        reject(event?.target?.error || new Error('Gagal simpan IDB'));
                    };

                    g.onsuccess = () => {
                        const data = g.result;
                        if (!data) {
                            return reject(new Error('Record hilang di IDB'));
                        }
                        if (!Array.isArray(data.seri)) data.seri = [];
                        const currentCount = parseInt(data.scanned_count ?? data.seri.length,
                            10) || 0;
                        if (currentCount >= (data.total_qty || 0)) {
                            return reject(new Error('Part sudah complete'));
                        }
                        const serial = `AUTO-DIRECT-${Date.now()}`;
                        data.scanned_count = currentCount + 1;
                        data.actual_qty = data.scanned_count;
                        incrementLoadingListBucket(data, loadingList);
                        serialStore.put({
                            customer_part: customerPart,
                            internal: internalPart,
                            serial,
                            loading_list_number: loadingList,
                            scanned_at: Date.now()
                        }, scanSerialKey(customerPart, serial));

                        const summaryRequest = summaryStore.get(SUMMARY_KEY);
                        summaryRequest.onsuccess = () => {
                            const actual = parseInt(localStorage.getItem(
                                'pulling_total_actual') || '0', 10) || 0;
                            const target = parseInt(localStorage.getItem(
                                    'pulling_total_target') || data.total_qty || '0',
                                10) || 0;
                            const summary = summaryRequest.result || {
                                totalActual: actual,
                                totalTarget: target
                            };
                            summary.totalActual = (parseInt(summary.totalActual || 0, 10) ||
                                0) + 1;
                            summary.totalTarget = parseInt(summary.totalTarget || target,
                                10) || target;
                            summary.updatedAt = Date.now();
                            summaryStore.put(summary, SUMMARY_KEY);
                        };
                        st.put(data, customerPart);
                    };
                    g.onerror = (e) => {
                        reject(e?.target?.error || new Error('Gagal ambil record'));
                    };
                }).catch(reject);
            });

            // 4) Update UI minimal (qty naik, indikator hijau, bersihkan panel customer)
            try {
                // Ambil ulang rec untuk hitung panjang terbaru (opsional, boleh perkiraan +1)
                const req2 = indexedDB.open(dbName);
                req2.onsuccess = (ev) => {
                    const db2 = ev.target.result;
                    const tx2 = db2.transaction([STORE_NAME], 'readonly');
                    const st2 = tx2.objectStore(STORE_NAME);
                    const g2 = st2.get(customerPart);
                    g2.onsuccess = () => {
                        const latest = g2.result || rec;
                        const nowLen = parseInt(latest?.scanned_count ?? latest?.seri?.length ?? ((rec?.seri
                                ?.length || 0) + 1),
                            10) || 0;
                        $('#qty-display').text(`${nowLen}/${latest?.total_qty ?? rec.total_qty}`);
                        if (typeof successIndicator === 'function') successIndicator();
                        if (typeof tmminSuccessIndicator === 'function') tmminSuccessIndicator();
                        if (typeof okSound === 'function') okSound();
                        $('#cust-display').text('-');
                        $('#chute-display').text('-');
                        $('#backno-display').text('-');
                        localStorage.removeItem('customerPart');
                        if (typeof incrementPullingQuantity === 'function') incrementPullingQuantity(1);
                        db2.close();
                    };
                };
            } catch (_) {}
        } catch (err) {
            if (typeof notif === 'function') notif('error', err?.message || String(err));
            else alert(err?.message || err);
            if (typeof errorIndicator === 'function') errorIndicator();
            setTimeout(() => $('#code').focus(), 400);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const toggle = document.getElementById('directToggle');

        // helper: fokuskan #code dan taruh caret di akhir
        const focusCode = () => {
            const el = document.getElementById('code');
            if (!el) return;
            el.focus();
            try {
                const len = el.value ? el.value.length : 0;
                el.setSelectionRange(len, len);
            } catch (_) {}
        };

        if (toggle) {
            // sinkronisasi awal (kalau kamu pakai localStorage)
            if (typeof setDirectMode === 'function') setDirectMode(toggle.checked);

            // saat toggle berubah → simpan mode + fokus ke #code
            toggle.addEventListener('change', () => {
                if (typeof setDirectMode === 'function') setDirectMode(toggle.checked);
                // beri sedikit jeda agar animasi selesai, lalu fokus
                setTimeout(focusCode, 150);
            });

            // bila label diklik cepat, tetap re-fokus setelah mouseup
            const track = document.querySelector('label.direct-toggle__track[for="directToggle"]');
            if (track) {
                track.addEventListener('mouseup', () => setTimeout(focusCode, 0));
                track.addEventListener('keyup', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') setTimeout(focusCode, 0);
                });
            }
        }
    });


    function initApp() {
        // check solve status
        if (localStorage.getItem('status')) {
            $('#modalConfirmation').on('shown.bs.modal', function() {
                $('#input-confirmation').focus();
            })
            $('#modalConfirmation').modal('show');

            $(document).on('click', function() {
                $('#input-confirmation').focus();
            })
        }

        let customer = localStorage.getItem('customer');
        let cycle = localStorage.getItem('cycle');
        let loadingList = getLoadingListNumber();
        checkLoadingList();
        // iterate local storage
        for (key in loadingList) {
            // remove example display
            $('#loadingListContainerSample').remove();

            // loading list display
            $('#list').append(pullingLlMarkup(loadingList[key]));

            pullingQuantity();
            $('#customer-display').text(customer);
            $('#cycle-display').text(cycle);
        }

        // set skid
        let skid = localStorage.getItem('skid');
        if (!skid) {
            localStorage.setItem('skid', 1);
        }

        // display skid if customer is TMMIN
        if (customer == 'TMMIN KRW 1') {
            $('.skid-display').append(pullingSkidMarkup(skid));
        }

        if (getLoadingListNumber().length == 0) {
            $('#modalLoadingListScan').on('shown.bs.modal', function() {
                $('#input-loadingList').focus();
            })
            $('#modalLoadingListScan').modal('show');

            // empty text
            $('#customer-display').text('customer');
            $('#cycle-display').text('cycle');
        }
        $('#code').focus();
    }

    function successIndicator() {
        $('#indicator')
            .removeClass('bg-warning');
        $('#indicator')
            .removeClass('bg-danger');
        $('#indicator')
            .addClass('bg-success');
    }

    function tmminSuccessIndicator() {
        $('#tmmin-indicator')
            .removeClass('bg-default');
        $('#tmmin-indicator')
            .removeClass('bg-warning');
        $('#tmmin-indicator')
            .removeClass('bg-danger');
        $('#tmmin-indicator')
            .addClass('bg-success');
    }

    function resetIndicator() {
        $('#tmmin-indicator')
            .addClass('bg-default');
        $('#tmmin-indicator')
            .removeClass('bg-warning');
        $('#tmmin-indicator')
            .removeClass('bg-danger');
        $('#tmmin-indicator')
            .removeClass('bg-success');
    }

    function errorIndicator() {
        $('#indicator')
            .removeClass('bg-warning');
        $('#indicator')
            .removeClass('bg-success');
        $('#indicator')
            .addClass('bg-danger');
    }

    function tmminErrorIndicator() {
        $('#tmmin-indicator')
            .removeClass('bg-default');
        $('#tmmin-indicator')
            .removeClass('bg-warning');
        $('#tmmin-indicator')
            .removeClass('bg-success');
        $('#tmmin-indicator')
            .addClass('bg-danger');
    }

    const SCAN_HISTORY_KEY = 'pulling_scan_pair_history';
    const SCAN_HISTORY_LIMIT = 6;

    function scanHistoryStorageKey() {
        const pds = localStorage.getItem('pds_local') || localStorage.getItem('pdsNumber') || 'global';
        return `${SCAN_HISTORY_KEY}_${pds}`;
    }

    function loadScanHistory() {
        try {
            const raw = localStorage.getItem(scanHistoryStorageKey()) || localStorage.getItem(SCAN_HISTORY_KEY) || '[]';
            const history = JSON.parse(raw);
            return Array.isArray(history) ? history.slice(0, SCAN_HISTORY_LIMIT) : [];
        } catch (_) {
            return [];
        }
    }

    const scanHistoryState = loadScanHistory();

    function saveScanHistory() {
        localStorage.setItem(scanHistoryStorageKey(), JSON.stringify(scanHistoryState));
    }

    function renderScanHistory() {
        const list = document.getElementById('scanHistoryList');
        const count = document.getElementById('scanHistoryCount');
        if (!list || !count) return;

        count.textContent = scanHistoryState.length;
        list.innerHTML = '';

        if (!scanHistoryState.length) {
            list.innerHTML =
                '<div class="pulling-history-item"><span class="pulling-history-icon"><i class="fas fa-link"></i></span><span class="pulling-history-copy"><span class="pulling-history-main">Belum ada pasangan kanban</span><span class="pulling-history-meta">Scan customer lalu internal kanban</span></span></div>';
            return;
        }

        scanHistoryState.forEach(function(item) {
            const row = document.createElement('div');
            row.className = 'pulling-history-item is-success';
            row.innerHTML =
                '<span class="pulling-history-icon"><i class="fas fa-link"></i></span><span class="pulling-history-copy"><span class="pulling-history-main"></span><span class="pulling-history-meta"></span></span>';
            row.querySelector('.pulling-history-main').textContent = 'Kanban Customer: ' + item.customer;
            row.querySelector('.pulling-history-meta').textContent = 'Kanban Internal: ' + item.internal +
                ' · Serial: ' + item.serial;
            list.appendChild(row);
        });
    }

    function pushScanPairHistory(customer, internal, serial) {
        scanHistoryState.unshift({
            customer: String(customer || '-').trim() || '-',
            internal: String(internal || '-').trim() || '-',
            serial: String(serial || '-').trim() || '-'
        });
        scanHistoryState.splice(SCAN_HISTORY_LIMIT);
        saveScanHistory();
        renderScanHistory();
    }

    renderScanHistory();

    function hydrateScanHistoryFromIndexedDB() {
        const dbName = localStorage.getItem('pds_local') || localStorage.getItem('pdsNumber');
        if (!dbName) return;

        openPullingDB(dbName).then((database) => {
            const rows = [];
            const transaction = database.transaction([SERIAL_STORE_NAME], 'readonly');
            const request = transaction.objectStore(SERIAL_STORE_NAME).openCursor();

            request.onsuccess = function(event) {
                const cursor = event.target.result;
                if (!cursor) return;

                const item = cursor.value || {};
                if (item.serial) rows.push(item);
                cursor.continue();
            };

            transaction.oncomplete = function() {
                database.close();
                const history = rows
                    .sort((a, b) => (parseInt(b.scanned_at || 0, 10) || 0) - (parseInt(a.scanned_at || 0,
                        10) || 0))
                    .slice(0, SCAN_HISTORY_LIMIT)
                    .map((item) => ({
                        customer: String(item.customer_part || '-').trim() || '-',
                        internal: String(item.internal || '-').trim() || '-',
                        serial: String(item.serial || '-').trim() || '-'
                    }));

                if (!history.length) return;
                scanHistoryState.splice(0, scanHistoryState.length, ...history);
                saveScanHistory();
                renderScanHistory();
            };

            transaction.onerror = function() {
                database.close();
            };
        }).catch(() => {});
    }

    hydrateScanHistoryFromIndexedDB();

    $(document).on('click', '#scanHistoryToggle', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const wrap = document.getElementById('scanHistoryAccordion');
        const body = document.getElementById('scanHistoryBody');
        if (!wrap || !body) return;

        const open = body.hidden;
        body.hidden = !open;
        wrap.classList.toggle('is-open', open);
        this.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    let notifTimer = null;

    function notif(color, text) {
        const type = String(color || 'info').toLowerCase().trim();
        const message = String(text == null ? '' : text);
        const variant = type.indexOf('error') === 0 || type === 'danger' ?
            'error' :
            (type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'info'));
        const meta = {
            success: ['Berhasil', 'fas fa-check'],
            error: ['Gagal', 'fas fa-exclamation'],
            warning: ['Perhatian', 'fas fa-exclamation-triangle'],
            info: ['Info', 'fas fa-info']
        } [variant];
        const modal = document.getElementById('notifModal');
        const card = document.getElementById('divNotif');
        const icon = document.getElementById('notifIcon');
        const title = document.getElementById('notifTitle');
        const messageEl = document.getElementById('notif');

        if (!modal || !card || !icon || !title || !messageEl) {
            console[type.indexOf('error') === 0 ? 'error' : 'log'](message);
            return;
        }

        clearTimeout(notifTimer);
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        card.className = 'pulling-scan-notif-card is-' + variant;
        icon.innerHTML = '<i class="' + meta[1] + '"></i>';
        title.textContent = meta[0];
        messageEl.textContent = message;

        requestAnimationFrame(function() {
            modal.classList.add('is-visible');
        });

        notifTimer = setTimeout(function() {
            modal.classList.remove('is-visible');
            modal.setAttribute('aria-hidden', 'true');
            setTimeout(function() {
                if (!modal.classList.contains('is-visible')) modal.hidden = true;
            }, 180);
        }, variant === 'success' ? 1900 : 2800);
    }

    function loadingListModal() {
        let loadingList = localStorage.getItem('loadingList');
        $('#input-loadingList').val('');
        setTimeout(() => {
            if (!loadingList) {
                $('#modalLoadingListScan').on('shown.bs.modal',
                    function() {
                        $('#input-loadingList').focus();
                    })
                $('#modalLoadingListScan').modal('show');
            }
        }, 1500);
    }

    function confirmationModal() {
        let status = localStorage.getItem('status');
        $('#input-confirmation').val('');
        setTimeout(() => {
            if (status) {
                $('#modalConfirmation').on('shown.bs.modal',
                    function() {
                        $('#input-confirmation').focus();
                    })
                $('#modalConfirmation').modal('show');
            }
        }, 1500);
    }

    function loadingListModal2() {
        $('#input-loadingList').val('');
        setTimeout(() => {
            $('#modalLoadingListScan').on('shown.bs.modal',
                function() {
                    $('#input-loadingList').focus();
                })
            $('#modalLoadingListScan').modal('show');
        }, 1500);
    }

    function customerCheck(customer, pds = null) {
        return new Promise(function(resolve, reject) {
            let url = "{{ url('pulling/customer-check') }}/" + customer;

            if (pds) {
                url += '/' + encodeURIComponent(pds); // tanpa tanda tanya
            }

            $.ajax({
                type: 'GET',
                url: url,
                dataType: 'json',
                success: function(data) {
                    console.log(data);
                    if (data.status === 'success') {
                        $('#customer-display').text(data.customer);
                        localStorage.setItem('customer', data.customer);
                        resolve();
                    } else {
                        reject(data.message || 'Unknown error');
                    }
                },
                error: function(xhr) {
                    reject(new Error(xhr.statusText));
                }
            });
        });
    }


    function checkLoadingList() {
        let pds = localStorage.getItem('pds_local');
        let ll = [];
        // initialize database
        request = window.indexedDB.open(pds);

        request.onsuccess = function(event) {
            const database = event.target.result;
            const transaction = database.transaction(["loadingList"], 'readonly');
            const objectStore = transaction.objectStore("loadingList");

            objectStore.openCursor().onsuccess = function(event) {
                let cursor = event.target.result;
                if (cursor) {

                    // check each loading list
                    if (!ll.includes(cursor.value.loading_list_number)) {
                        ll.push(cursor.value.loading_list_number);
                    }

                    cursor.continue();
                } else {
                    $('#total-ll').text(ll.length);
                }
            }

            // Close the db when the transaction is done
            transaction.oncomplete = function() {
                database.close();
            };

            objectStore.openCursor().onerror = function(event) {
                notif('error', event.message);
                return;
            }
        }
    }

    // Function to get the Skid data (either Skid 1 or Skid 2)
    function getSkidData(skidId) {
        return new Promise(function(resolve, reject) {
            let pds = localStorage.getItem('pds_local');
            let request = window.indexedDB.open(pds);

            request.onsuccess = function(event) {
                const database = event.target.result;
                const transaction = database.transaction(["loadingList"], 'readonly');
                const objectStore = transaction.objectStore("loadingList");

                let allData = [];
                objectStore.openCursor().onsuccess = function(event) {
                    let cursor = event.target.result;
                    if (cursor) {
                        allData.push(cursor.value);
                        cursor.continue();
                    } else {
                        // Group data by skidNo
                        let skidGroups = {};
                        allData.forEach(item => {
                            if (!skidGroups[item.skidNo]) {
                                skidGroups[item.skidNo] = [];
                            }
                            skidGroups[item.skidNo].push(item);
                        });

                        // Extract skids and sort them
                        let skids = Object.keys(skidGroups).sort();

                        if (skidId === 1) {
                            let skid1Data = skidGroups[skids[0]] || [];
                            localStorage.setItem("skid_1_data", JSON.stringify(skid1Data));
                            resolve(skid1Data);
                        } else {
                            let skid1Data = JSON.parse(localStorage.getItem("skid_1_data") || "[]");
                            let skid1SkidNo = skid1Data.length > 0 ? skid1Data[0].skidNo : null;

                            let skid2Data = allData.filter(d => d.skidNo !== skid1SkidNo);
                            resolve(skid2Data);
                        }
                    }
                };

                transaction.onerror = function(event) {
                    reject("Error reading IndexedDB: " + event.target.error);
                };
            };

            request.onerror = function(event) {
                reject("Failed to open IndexedDB: " + event.target.error);
            };
        });
    }

    function customerCharStore(customer, pds = null) {
        // Buat URL dengan path segment, tanpa tanda tanya
        let url = "{{ url('pulling/customer-check') }}/" + customer;
        if (pds) {
            url += '/' + encodeURIComponent(pds);
        }

        $.ajax({
            type: 'GET',
            url: url,
            dataType: 'json',
            success: function(data) {
                console.log(data);
                if (data.status === 'success') {
                    // save all data about customer in local storage
                    localStorage.setItem('char_first', data.first);
                    localStorage.setItem('char_length', data.length);
                    localStorage.setItem('char_total', data.total);
                } else {
                    notif('error', data.message);
                    loadingListModal();
                }
            },
            error: function(xhr) {
                notif('error', xhr.statusText); // karena di sini ga pakai promise
                loadingListModal();
            }
        });
    }

    function errorStore(message = null, expected = null, scanned = null) {
        $.ajax({
            type: 'GET',
            url: "{{ route('error.store') }}",
            _token: "{{ csrf_token() }}",
            dataType: 'json',
            data: {
                message: message,
                expected: expected,
                scanned: scanned
            },
            success: function(data) {
                console.log("Error recorded");
            },
            error: function(xhr) {
                console.log(xhr);
            }
        });
    }

    // Function to update the button based on the status of Skid 1 and Skid 2
    function updateButtonStatus() {
        var button = $("#close-skid");

        if (!skid1Sent) {
            // button.text("Close Skid 1");
            button.off('click').on('click', function() {
                sendSkidData(1);
            });
        } else if (!skid2Sent) {
            button.text("Close Skid 2");
            button.off('click').on('click', function() {
                sendSkidData(2);
            });
        } else {
            button.text("Semua Data Terkirim").prop("disabled",
                true);
        }
    }

    function setPullingQuantityDisplay(totalActual, totalTarget) {
        localStorage.setItem('pulling_total_actual', String(totalActual));
        localStorage.setItem('pulling_total_target', String(totalTarget));
        $('#pullingQty').text(`${totalActual}/${totalTarget}`);

        if (totalActual == 0) {
            $('#pullingStatusContainer').addClass('bg-danger');
            $('#pullingStatus').text(' - Ayo Pulling!')
        } else if (totalActual > 0 && totalActual < totalTarget) {
            $('#pullingStatusContainer').removeClass('bg-danger');
            $('#pullingStatusContainer').addClass('bg-warning');
            $('#pullingStatus').text(' - Belum Lengkap!')
        } else {
            $('#pullingStatusContainer').removeClass('bg-warning');
            $('#pullingStatusContainer').addClass('bg-success');
            $('#pullingStatus').text(' - Pulling Selesai!')
        }
    }

    function pullingInt(value) {
        return parseInt(value ?? 0, 10) || 0;
    }

    function loadingListSources(record) {
        if (!record) return [];

        const sources = Array.isArray(record.source_loading_lists) ?
            record.source_loading_lists.map(String) : [];

        if (!sources.length && record.loading_list_number) {
            sources.push(String(record.loading_list_number));
        }

        return sources;
    }

    function seedLoadingListBuckets(record) {
        const sources = loadingListSources(record);
        const buckets = {};
        const total = pullingInt(record?.total_qty);
        let remainingScanned = pullingInt(record?.scanned_count ?? record?.seri?.length ?? record?.actual_qty);

        sources.forEach((source, index) => {
            const target = Math.floor(total / sources.length) + (index < (total % sources.length) ? 1 : 0);
            const scanned = Math.min(target, remainingScanned);
            remainingScanned -= scanned;
            buckets[source] = {
                target,
                scanned
            };
        });

        return buckets;
    }

    function ensureLoadingListBuckets(record) {
        if (!record) return {};

        if (!record.loading_list_buckets || Array.isArray(record.loading_list_buckets)) {
            record.loading_list_buckets = seedLoadingListBuckets(record);
        }

        return record.loading_list_buckets;
    }

    function loadingListBucketEntries(record) {
        const buckets = ensureLoadingListBuckets(record);
        return loadingListSources(record)
            .filter((source) => buckets[source])
            .map((source) => ({
                number: source,
                target: pullingInt(buckets[source].target),
                scanned: pullingInt(buckets[source].scanned)
            }))
            .filter((entry) => entry.target > 0);
    }

    function nextLoadingListForScan(record) {
        const entries = loadingListBucketEntries(record);
        const next = entries.find((entry) => entry.scanned < entry.target) || entries[0];
        return next?.number || record?.loading_list_number;
    }

    function incrementLoadingListBucket(record, loadingListNumber) {
        const key = String(loadingListNumber || '');
        const buckets = ensureLoadingListBuckets(record);

        if (key && buckets[key]) {
            buckets[key].scanned = pullingInt(buckets[key].scanned) + 1;
        }
    }

    function mergeLoadingListItem(existing, item, loadingListNumber) {
        const source = String(loadingListNumber);
        const sources = loadingListSources(existing);

        if (sources.includes(source)) {
            return {
                record: existing,
                shouldPut: false
            };
        }

        const targetQty = pullingInt(item.total_kanban_qty);
        const actualQty = pullingInt(item.actual_kanban_qty);

        if (existing) {
            const scannedCount = pullingInt(existing.scanned_count ?? existing.seri?.length ?? existing.actual_qty);
            const buckets = ensureLoadingListBuckets(existing);

            existing.total_qty = pullingInt(existing.total_qty) + targetQty;
            existing.scanned_count = scannedCount + actualQty;
            existing.actual_qty = pullingInt(existing.actual_qty ?? scannedCount) + actualQty;
            existing.source_loading_lists = sources.concat(source);
            buckets[source] = {
                target: targetQty,
                scanned: actualQty
            };
            existing.seri = Array.isArray(existing.seri) ? existing.seri : [];

            return {
                record: existing,
                shouldPut: true
            };
        }

        return {
            record: {
                key: item.part_number_cust,
                loading_list_number: loadingListNumber,
                internal: item.part_number_int,
                customer: item.part_number_cust,
                qty_per_kbn: targetQty ? item.total_qty / targetQty : 0,
                actual_qty: actualQty,
                scanned_count: actualQty,
                total_qty: targetQty,
                source_loading_lists: [source],
                loading_list_buckets: {
                    [source]: {
                        target: targetQty,
                        scanned: actualQty
                    }
                },
                seri: []
            },
            shouldPut: true
        };
    }

    function refreshPullingSummaryFromRecords(database) {
        return new Promise((resolve, reject) => {
            let totalActual = 0;
            let totalTarget = 0;
            const readTransaction = database.transaction([STORE_NAME], 'readonly');
            const objectStore = readTransaction.objectStore(STORE_NAME);
            const cursorRequest = objectStore.openCursor();

            cursorRequest.onsuccess = function(event) {
                const cursor = event.target.result;

                if (cursor) {
                    const record = cursor.value;
                    totalActual += pullingInt(record.scanned_count ?? record.seri?.length);
                    totalTarget += pullingInt(record.total_qty);
                    cursor.continue();
                }
            };

            cursorRequest.onerror = () => reject(cursorRequest.error);

            readTransaction.oncomplete = () => {
                const writeTransaction = database.transaction([SUMMARY_STORE_NAME], 'readwrite');
                const summaryStore = writeTransaction.objectStore(SUMMARY_STORE_NAME);

                summaryStore.put({
                    totalActual,
                    totalTarget,
                    updatedAt: Date.now()
                }, SUMMARY_KEY);

                writeTransaction.oncomplete = () => {
                    setPullingQuantityDisplay(totalActual, totalTarget);
                    resolve();
                };
                writeTransaction.onerror = () => reject(writeTransaction.error);
                writeTransaction.onabort = () => reject(writeTransaction.error);
            };

            readTransaction.onerror = () => reject(readTransaction.error);
            readTransaction.onabort = () => reject(readTransaction.error);
        });
    }

    function incrementPullingQuantity(deltaActual) {
        const actual = parseInt(localStorage.getItem('pulling_total_actual') || '', 10);
        const target = parseInt(localStorage.getItem('pulling_total_target') || '', 10);

        if (Number.isNaN(actual) || Number.isNaN(target)) {
            pullingQuantity();
            return;
        }

        setPullingQuantityDisplay(actual + deltaActual, target);
    }

    function recalculatePullingQuantity(database) {
        let totalActual = 0;
        let totalTarget = 0;
        const transaction = database.transaction([STORE_NAME], 'readonly');
        const objectStore = transaction.objectStore(STORE_NAME);

        const cursorRequest = objectStore.openCursor();
        cursorRequest.onsuccess = function(event) {
            let cursor = event.target.result;
            if (cursor) {
                const record = cursor.value;
                totalActual += parseInt(record.scanned_count ?? record.seri?.length ?? 0, 10) || 0;
                totalTarget += parseInt(record.total_qty || 0, 10) || 0;
                cursor.continue();
                return;
            }

            setPullingQuantityDisplay(totalActual, totalTarget);
        };

        cursorRequest.onerror = function(event) {
            notif('error', event.message);
        };

        transaction.oncomplete = function() {
            try {
                const writeTransaction = database.transaction([SUMMARY_STORE_NAME], 'readwrite');
                writeTransaction.objectStore(SUMMARY_STORE_NAME).put({
                    totalActual,
                    totalTarget,
                    updatedAt: Date.now()
                }, SUMMARY_KEY);
                writeTransaction.oncomplete = () => database.close();
                writeTransaction.onerror = () => database.close();
            } catch (error) {
                database.close();
            }
        };
    }

    function pullingQuantity() {
        const pds = localStorage.getItem('pds_local');
        if (!pds) return;

        openPullingDB(pds).then((database) => {
            const transaction = database.transaction([SUMMARY_STORE_NAME], 'readonly');
            const summaryStore = transaction.objectStore(SUMMARY_STORE_NAME);
            const summaryRequest = summaryStore.get(SUMMARY_KEY);

            summaryRequest.onsuccess = function() {
                const summary = summaryRequest.result;
                const totalActual = parseInt(summary?.totalActual, 10);
                const totalTarget = parseInt(summary?.totalTarget, 10);

                if (!Number.isNaN(totalActual) && !Number.isNaN(totalTarget)) {
                    setPullingQuantityDisplay(totalActual, totalTarget);
                    database.close();
                    return;
                }

                recalculatePullingQuantity(database);
            };

            summaryRequest.onerror = function() {
                recalculatePullingQuantity(database);
            };
        }).catch(() => {
            notif('error', 'Failed to connect to database!');
        });
    }

    function refreshToken() {
        $.ajax({
            url: "/refresh-token",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.success) {
                    notif('success', 'Successfully refreshed token');
                    setInterval(() => {
                        $('#code').focus();
                    }, 1000);
                } else {
                    notif("error", response.message);
                }
            },
            error: function(xhr) {
                notif("error", xhr.responseJSON.message);
            }
        });
    }

    $(document).ready(function() {
        initApp();
        $('#code').focus();

        $("#refreshTokenBtn").click(function() {
            refreshToken();
        });

        $('#loadingList').on('click', function() {
            loadingListModal2();
        });

        if (localStorage.getItem('status')) {
            $(document).on('click', function() {
                $('#input-confirmation').focus();
            });
        }

        var token = "{{ session()->get('token') }}";

        class ModalLoadingListScanner {
            constructor() {
                this.token = "{{ session()->get('token') }}" || '';
                this.modalSel = '#modalLoadingListScan';
                this.$modal = $(this.modalSel);
            }

            init() {
                this.hideLoading(); // pastikan hidden on start
                $('#input-loadingList').on('keypress', (e) => this.handleKeyPress(e));
            }

            handleKeyPress(e) {
                const keyCode = e.keyCode || e.which;
                if (keyCode === 13) { // Enter key
                    const inputValue = $(e.target).val();
                    this.processLoadingList(inputValue);
                }
            }

            async processLoadingList(inputValue) {
                try {
                    const loadingList = this.getLoadingListNumber();
                    const loadingListNumber = inputValue.substr(0, 11) + ' A';

                    // kalau sudah “done”, jangan lanjut
                    if (isLLDone(loadingListNumber)) {
                        this.showError('Loading list ini sudah diproses.');
                        return;
                    }

                    // jika sedang diproses paralel → tolak
                    if (inFlightLL.has(loadingListNumber)) {
                        this.showError('Loading list ini sedang diproses...');
                        return;
                    }
                    inFlightLL.add(loadingListNumber);

                    // kalau sudah ada di localStorage (header sudah tersimpan) → tolak
                    if (this.getLoadingListNumber().includes(loadingListNumber)) {
                        this.showError('Loading list sudah discan!', () => {
                            if (typeof alreadyScanLlSound === 'function') alreadyScanLlSound();
                        });
                        inFlightLL.delete(loadingListNumber);
                        return;
                    }

                    // early return tanpa spinner
                    if (loadingList.includes(loadingListNumber)) {
                        this.showError('Loading list sudah discan!', () => {
                            if (typeof alreadyScanLlSound === 'function') alreadyScanLlSound();
                        });
                        return;
                    }

                    // ⬇️ mulai tampilkan loading
                    this.showLoading('Mengambil data loading list...');

                    const response = await this.fetchLoadingList(loadingListNumber);
                    if (response.status !== 'success') {
                        this.showError(response.message);
                        this.showModal();
                        return;
                    }

                    // proses lanjut (spinner tetap on, nanti di-update pesannya di method berikut)
                    await this.processLoadingListResponse(response.data);

                    // kalau sukses dan mau tutup modal
                    this.hideModal();

                } catch (error) {
                    console.error('Loading list error:', error);
                    this.handleAjaxError(error);
                } finally {
                    // pastikan spinner dimatikan & input difokuskan
                    this.hideLoading();
                    this?.focusCodeInput?.();
                }
            }


            async fetchLoadingList(loadingListNumber) {
                return await $.ajax({
                    type: 'GET',
                    url: `https://dea-dev.aiia.co.id/api/v1/loading-lists/${loadingListNumber}`,
                    headers: {
                        "Authorization": `Bearer ${this.token}`
                    },
                    dataType: 'json'
                });
            }

            async processLoadingListResponse(data) {
                // validasi dulu
                const validationResult = this.validateLoadingList(data);
                if (!validationResult.isValid) {
                    this.showError(validationResult.message, validationResult.soundFn);
                    if (validationResult.showModal) this.showModal();
                    return;
                }

                const {
                    pds_number,
                    number: ll,
                    cycle,
                    customer_code,
                    delivery_date,
                    shipping_date
                } = data;

                try {
                    this.setLoadingMessage('Menyimpan header...');
                    await this.storeLoadingList(ll, pds_number, cycle, customer_code, delivery_date,
                        shipping_date);

                    this.setLoadingMessage('Menyimpan data lokal...');
                    this.storeLocalData(data);

                    this.setLoadingMessage('Memperbarui tampilan...');
                    this.updateLoadingListUI(data);

                    this.setLoadingMessage('Menyiapkan IndexedDB...');
                    await this.initializeDatabase(data);

                    this.setLoadingMessage('Menyimpan detail item...');
                    await this.storeLoadingListDetails(ll, data.items);

                    this.setLoadingMessage('Finalisasi...');
                    await this.performAdditionalProcessing(data);

                    markLLDone(loadingListNumber);
                    inFlightLL.delete(loadingListNumber);

                } catch (error) {
                    console.error('Error processing loading list:', error);
                    this.showError('Gagal memproses loading list');
                } finally {
                    // fokus input akan dipanggil di finally processLoadingList (luar)
                    inFlightLL.delete(loadingListNumber);
                    this.hideLoading();
                    this?.focusCodeInput?.();
                }
            }

            validateLoadingList(data) {
                // Check if already pulled (missing total_kanban_qty property)
                if (!data.items[0]?.hasOwnProperty('total_kanban_qty')) {
                    return {
                        isValid: false,
                        message: 'Loading list sudah pernah dipulling',
                        soundFn: () => {
                            if (typeof alreadyPulledSound === 'function') alreadyPulledSound();
                        },
                        showModal: true
                    };
                }

                // Check totals (commented out in original but keeping logic)
                const totals = this.calculateTotals(data.items);
                // if (totals.actual >= totals.kanban) {
                //     return {
                //         isValid: false,
                //         message: 'Loading list sudah pernah dipulling',
                //         soundFn: () => { if (typeof alreadyPulledSound === 'function') alreadyPulledSound(); },
                //         showModal: true
                //     };
                // }

                // Check PDS consistency
                const existingPDS = localStorage.getItem('pdsNumber');
                if (existingPDS && data.pds_number !== existingPDS) {
                    return {
                        isValid: false,
                        message: 'Loading list tidak sesuai!',
                        soundFn: () => {
                            if (typeof notMatchLlSound === 'function') notMatchLlSound();
                        },
                        showModal: false
                    };
                }

                return {
                    isValid: true
                };
            }

            calculateTotals(items) {
                return items.reduce((acc, item) => {
                    acc.actual += item.actual_kanban_qty || 0;
                    acc.kanban += item.total_kanban_qty || 0;
                    return acc;
                }, {
                    actual: 0,
                    kanban: 0
                });
            }

            async storeLoadingList(ll, pds, cycle, customerCode, deliveryDate, shippingDate) {
                try {
                    const response = await $.ajax({
                        type: 'GET',
                        url: `/loading-list/store/${ll}/${pds}/${cycle}/${customerCode}/${deliveryDate || ''}/${shippingDate || ''}`,
                        dataType: 'json'
                    });

                    // console.log('Modal loading list stored:', response.message);
                    return response;

                } catch (xhr) {
                    console.error('Error storing loading list:', xhr);

                    if (xhr.status === 0) {
                        this.showError('Connection Error');
                        throw new Error('Network error');
                    } else {
                        const errorMsg = xhr.responseJSON?.errors || 'Gagal menyimpan loading list';
                        this.showError(errorMsg);
                        throw new Error(errorMsg);
                    }
                }
            }

            storeLocalData(data) {
                localStorage.setItem('pds_local', data.pds_number);
                localStorage.setItem(`ll_${data.number}`, data.number);
                localStorage.setItem('pdsNumber', data.pds_number);
            }

            updateLoadingListUI(data) {
                $('#loadingListContainerSample').remove();
                $('#list').append(pullingLlMarkup(data.number));
            }

            async initializeDatabase(data) {
                const database = await openPullingDB(data.pds_number);
                try {
                    await this.populateDatabase(database, data);
                } finally {
                    database.close();
                }
            }

            async populateDatabase(database, data) {
                const transaction = database.transaction([STORE_NAME], 'readwrite');
                const objectStore = transaction.objectStore(STORE_NAME);
                const transactionDone = new Promise((resolve, reject) => {
                    transaction.oncomplete = resolve;
                    transaction.onerror = () => reject(transaction.error);
                    transaction.onabort = () => reject(transaction.error);
                });

                const promises = data.items.map(item => {
                    return new Promise((resolve, reject) => {
                        const key = item.part_number_cust;
                        const getRequest = objectStore.get(key);

                        getRequest.onsuccess = (event) => {
                            try {
                                const merged = mergeLoadingListItem(event.target.result,
                                    item, data.number);

                                if (merged.shouldPut) {
                                    objectStore.put(merged.record, key);
                                }

                                resolve();
                            } catch (error) {
                                reject(error);
                            }
                        };

                        getRequest.onerror = () => reject(getRequest.error);
                    });
                });

                await Promise.all(promises);
                await transactionDone;
                await refreshPullingSummaryFromRecords(database);
            }

            async storeLoadingListDetails(ll, items) {
                const promises = items.map((item, index) => {
                    const qtyPerKbn = item.total_qty / item.total_kanban_qty;

                    return new Promise((resolve, reject) => {
                        setTimeout(() => {
                            $.ajax({
                                type: 'GET',
                                url: `/loading-list/storeDetail/${ll}/${item.part_number_cust}/${item.part_number_int}/${item.total_kanban_qty}/${qtyPerKbn}/${item.total_qty}/${item.actual_kanban_qty}`,
                                dataType: 'json',
                                success: (response) => {
                                    console.log('Detail stored:',
                                        response.status, response
                                        .message);
                                    resolve(response);
                                },
                                error: (xhr) => {
                                    console.error('Detail store error:',
                                        xhr);

                                    if (xhr.status === 0) {
                                        reject(new Error(
                                            'Network error storing detail'
                                        ));
                                    } else {
                                        // Log error but don't stop the process
                                        console.warn(
                                            `Failed to store detail for ${item.part_number_cust}, continuing...`
                                        );
                                        this.showError(
                                            'Scan ulang loading list'
                                        );
                                        resolve({
                                            status: 'error',
                                            item: item
                                                .part_number_cust
                                        });
                                    }
                                }
                            });
                        }, 200); // Original timeout
                    });
                });

                try {
                    const results = await Promise.all(promises);
                    const failures = results.filter(result => result?.status === 'error');

                    if (failures.length > 0) {
                        console.warn(`${failures.length} detail items failed to store`);
                    }

                    return results;
                } catch (error) {
                    console.error('Critical error storing details:', error);
                    throw error;
                }
            }

            async performAdditionalProcessing(data) {
                try {
                    // Customer check
                    await this.customerCheck(data.customer_code, data.pds_number);

                    // Update cycle display
                    $('#cycle-display').text(data.cycle);
                    localStorage.setItem('cycle', data.cycle);

                    // Calculate quantities
                    this.pullingQuantity();

                    // Handle TMMIN skid display
                    this.handleTmminSkidDisplay(data.customer_code);

                    // Additional checks
                    this.checkLoadingList();
                    this.customerCharStore(data.customer_code, data.pds_number);

                } catch (error) {
                    console.error('Additional processing error:', error);
                    this.showError(error.message || 'Error in additional processing');
                }
            }

            handleTmminSkidDisplay(customerCode) {
                if (customerCode === '7A00001') { // TMMIN customer code
                    let skid = localStorage.getItem('skid') || '1';
                    if (!localStorage.getItem('skid')) {
                        localStorage.setItem('skid', '1');
                    }

                    $('.skid-display').append(pullingSkidMarkup(skid));
                }
            }

            showLoading(message = 'Memproses...') {
                this.setLoadingMessage(message);
                this.$modal.find('.loading-overlay').addClass('is-active');
                this.$modal.find('input,button,select,textarea').prop('disabled', true);
            }

            hideLoading() {
                this.$modal.find('.loading-overlay').removeClass('is-active');
                this.$modal.find('input,button,select,textarea').prop('disabled', false);
            }

            setLoadingMessage(message) {
                this.$modal.find('#loading-text').text(message);
            }

            handleAjaxError(xhr) {
                if (xhr.status === 0) {
                    this.showError('Connection Error');
                } else if (xhr.status === 401) {
                    this.showError(`${xhr.statusText} Please re-login`);
                } else {
                    this.showError(xhr.statusText || 'Request failed');
                }
                this.showModal();
            }

            showError(message, soundFunction = null) {
                if (typeof notif === 'function') {
                    notif('error', message);
                }
                if (soundFunction) soundFunction();
            }

            showModal() {
                if (typeof loadingListModal === 'function') {
                    loadingListModal();
                }
            }

            hideModal() {
                $('#modalLoadingListScan').modal('hide');
            }

            // di dalam class:
            focusCodeInput = () => {
                const $el = $('#code:visible:not([disabled])');
                if (!$el.length) return;

                // kalau ada modal bootstrap, fokuskan setelah modal bener² tertutup
                if ($('.modal.show').length) {
                    $('.modal.show').one('hidden.bs.modal', () => this.focusCodeInput());
                    return;
                }

                // tunda 1 frame supaya DOM/UI selesai update
                requestAnimationFrame(() => setTimeout(() => {
                    $el.trigger('focus');
                    // opsional: taruh kursor di akhir input
                    const el = $el.get(0);
                    if (el && el.setSelectionRange) {
                        const len = el.value?.length ?? 0;
                        el.setSelectionRange(len, len);
                    }
                }, 0));
            };


            getLoadingListNumber() {
                return typeof getLoadingListNumber === 'function' ? getLoadingListNumber() : [];
            }

            async customerCheck(customerCode, pdsNumber) {
                return new Promise((resolve, reject) => {
                    if (typeof customerCheck === 'function') {
                        customerCheck(customerCode, pdsNumber)
                            .then(resolve)
                            .catch(reject);
                    } else {
                        resolve();
                    }
                });
            }

            pullingQuantity() {
                if (typeof pullingQuantity === 'function') {
                    pullingQuantity();
                }
            }

            checkLoadingList() {
                if (typeof checkLoadingList === 'function') {
                    checkLoadingList();
                }
            }

            customerCharStore(customerCode, pdsNumber) {
                if (typeof customerCharStore === 'function') {
                    customerCharStore(customerCode, pdsNumber);
                }
            }
        }

        // Initialize the modal loading list scanner
        $(document).ready(() => {
            const modalScanner = new ModalLoadingListScanner();
            modalScanner.init();
        });

        $('#input-confirmation').keypress(function(e) {
            e.preventDefault();
            let code = (e.keyCode ? e.keyCode : e.which);
            if (code == 13) {
                barcodecomplete = barcode;
                barcode = "";
                if (barcodecomplete.length === 6) {
                    if (barcodecomplete == '000453' || barcodecomplete == '002484') {
                        localStorage.removeItem('status');
                        $('#modalConfirmation').modal('hide');
                        notif('success', 'Selamat melanjutkan pulling!');

                        // remove original customer part in local storage
                        localStorage.removeItem('originalCustomerPart');

                        setInterval(() => {
                            $('#code').focus();
                        }, 1000);
                    } else {
                        $('#modalConfirmation').modal('hide');
                        notif('error', `NPK ${barcodecomplete} tidak memiliki hak akses`);
                        confirmationModal();
                    }
                } else {
                    $('#modalConfirmation').modal('hide');
                    notif('error', 'Scan barcode NPK');
                    confirmationModal();
                }
            } else {
                barcode = barcode + String.fromCharCode(e.which);
            }
        });

        // Function to clear local storage
        function clearLocalStorage() {
            localStorage.clear();
        }

        // Function to clear IndexedDB using localforage
        function deleteAllIndexedDB() {
            // Retrieve a list of all databases
            indexedDB.databases().then(function(databaseList) {
                // Loop through each database and delete it
                databaseList.forEach(function(database) {
                    var deleteRequest = indexedDB.deleteDatabase(database.name);

                    deleteRequest.onsuccess = function() {
                        console.log('IndexedDB database deleted successfully:', database
                            .name);
                    };

                    deleteRequest.onerror = function(event) {
                        console.error('Error deleting IndexedDB database:', event.target
                            .error, database.name);
                    };
                });
            }).catch(function(error) {
                console.error('Error retrieving IndexedDB database names:', error);
            });
        }

        let pullingConfirmAction = null;

        function showPullingConfirm(options) {
            const config = Object.assign({
                title: 'Konfirmasi',
                message: 'Lanjutkan aksi ini?',
                confirmText: 'Lanjutkan',
                confirmClass: 'btn-danger',
                icon: 'fas fa-exclamation-triangle',
                onConfirm: null
            }, options || {});

            pullingConfirmAction = config.onConfirm;
            $('#pullingConfirmTitle').text(config.title);
            $('#pullingConfirmMessage').text(config.message);
            $('#pullingConfirmIcon').html('<i class="' + config.icon + '"></i>');
            $('#pullingConfirmOk')
                .attr('class', 'btn ' + config.confirmClass)
                .text(config.confirmText);
            $('#pullingConfirmModal').modal('show');
        }

        $('#pullingConfirmOk').on('click', function() {
            const action = pullingConfirmAction;
            pullingConfirmAction = null;
            $('#pullingConfirmModal').modal('hide');
            if (typeof action === 'function') action();
        });

        $('#pullingConfirmModal').on('hidden.bs.modal', function() {
            pullingConfirmAction = null;
            $('#code').focus();
        });

        $('#delay').on('click', function() {
            $(this).prop('disabled', true).css({
                color: '#B91C1C',
                backgroundColor: '#F9CFCF',
                borderColor: '#F2A5A5'
            });
            localStorage.clear();
            window.location.reload();
        });

        $(document).on('click', '#close-skid', function() {
            const button = this;
            showPullingConfirm({
                title: 'Ganti Skid?',
                message: 'Nomor skid akan diganti untuk proses pulling berikutnya.',
                confirmText: 'Ganti Skid',
                confirmClass: 'btn-warning',
                onConfirm: function() {
                    let skid = parseInt(localStorage.getItem('skid')) || 1;
                    let skidDisplay = $('#skid-display');

                    if (skid === 1) {
                        skid++;
                        localStorage.setItem('skid', skid);
                    } else if (skid === 2) {
                        skid--;
                        localStorage.setItem('skid', skid);
                    }

                    skidDisplay.text(skid);
                    $(button).text(`Close Skid ${skid}`);
                    setInterval(() => {
                        $('#code').focus();
                    }, 1000);
                }
            });
        });

        $('#hardReset').on('click', function() {
            showPullingConfirm({
                title: 'Reset Semua Data?',
                message: 'Semua data scan lokal dan IndexedDB akan dihapus. Aksi ini tidak bisa dibatalkan.',
                confirmText: 'Reset',
                confirmClass: 'btn-danger',
                onConfirm: function() {
                    clearLocalStorage();
                    deleteAllIndexedDB();
                    location.reload();
                }
            });
        });

        $('#done').on('click', async function() {
            const pds = localStorage.getItem('pds_local');
            if (!pds) {
                notif('error', 'Database lokal belum siap!');
                return;
            }

            let database = null;

            try {
                database = await openPullingDB(pds);
                const records = await getAllLoadingListRecords(database);
                const loadingListPayload = {};
                const finishRows = [];
                const seenLoadingLists = new Set();
                let complete = records.length > 0;
                const addFinishRow = (loadingListNumber) => {
                    if (seenLoadingLists.has(loadingListNumber)) return;

                    seenLoadingLists.add(loadingListNumber);
                    finishRows.push({
                        customer: localStorage.getItem('customer'),
                        loadingList: loadingListNumber,
                        pdsNumber: localStorage.getItem('pdsNumber'),
                        cycle: localStorage.getItem('cycle'),
                    });
                };

                for (const record of records) {
                    const scanRows = await getScanRowsForPart(database, record);
                    const buckets = loadingListBucketEntries(record);
                    const fallbackTarget = parseInt(record.total_qty || 0, 10) || 0;
                    const totalTarget = buckets.reduce((sum, bucket) => sum + bucket.target, 0) ||
                        fallbackTarget;

                    if (scanRows.length < totalTarget) {
                        complete = false;
                        break;
                    }

                    scanRows.forEach((row) => {
                        const loadingListNumber = row.loading_list_number || record
                            .loading_list_number;
                        if (!loadingListNumber) return;

                        const item = {
                            part_number_internal: record.internal,
                            part_number_customer: record.customer,
                            serial_number: row.serial
                        };

                        if (loadingListPayload.hasOwnProperty(loadingListNumber)) {
                            loadingListPayload[loadingListNumber].push(item);
                        } else {
                            loadingListPayload[loadingListNumber] = [item];
                        }

                        addFinishRow(loadingListNumber);
                    });
                }

                database.close();
                database = null;

                if (!complete) {
                    notif('error', 'loading list belum lengkap!');
                    uncompleteLlSound();
                    setTimeout(() => $('#code').focus(), 1000);
                    return;
                }

                $.ajax({
                    type: 'GET',
                    url: "{{ route('pulling.post') }}",
                    _token: "{{ csrf_token() }}",
                    data: {
                        loadingList: loadingListPayload,
                        token: token
                    },
                    dataType: 'json',
                    success: function() {
                        const deleteRequest = indexedDB.deleteDatabase(pds);

                        deleteRequest.onsuccess = function() {
                            notif('success', 'Pulling berhasil!');
                            finishPullingSound();
                        };

                        deleteRequest.onerror = function(event) {
                            notif('error', event?.target?.error?.message ||
                                'Gagal menghapus database lokal');
                        };

                        if (finishRows.length === 0) {
                            localStorage.clear();
                            window.location.reload();
                            return;
                        }

                        let storedRows = 0;
                        finishRows.forEach((row) => {
                            $.ajax({
                                type: 'GET',
                                url: "{{ route('pulling.store') }}",
                                _token: "{{ csrf_token() }}",
                                data: row,
                                dataType: 'json',
                                success: function() {
                                    storedRows += 1;
                                    if (storedRows === finishRows
                                        .length) {
                                        localStorage.clear();
                                        window.location.reload();
                                    }
                                },
                                error: function(xhr) {
                                    notif('error', xhr.responseJSON
                                        ?.message || xhr
                                        .statusText);
                                }
                            });
                        });
                    },
                    error: function(xhr) {
                        notif('error', xhr.responseJSON?.message || xhr.statusText);
                    }
                });
            } catch (error) {
                if (database) database.close();
                notif('error', error?.message || 'Gagal membaca data pulling lokal');
            }
        });

        var barcode = "";
        var rep2 = "";
        var code = $('#code');
        let total = 0;

        function checkInternalAndCustomer(database, cursor, internal, primaryKey, seri) {
            let loadingList = nextLoadingListForScan(cursor);
            let customer = cursor['customer'];
            let qty_per_kbn = cursor['qty_per_kbn'];
            let arraySeri = cursor['seri'];
            let totalQty = cursor['total_qty'];
            let isSameObject = false;
            let skid = localStorage.getItem('skid');
            let originalBarcode = localStorage.getItem('originalCustomerPart');
            let barcodecomplete = localStorage.getItem('customerPart');
            let manifest, itemNo, seqNo;

            if (localStorage.getItem('char_total') == 39) {
                manifest = originalBarcode.substr(3, 10);
                itemNo = originalBarcode.substr(31, 4);
                seqNo = originalBarcode.substr(35, 4);
            }

            // Helper: ambil pesan error dari xhr
            const xhrMessage = (xhr) => {
                if (xhr.status === 0) return 'Connection Error';
                return (xhr.responseJSON?.message) ||
                    (xhr.responseJSON?.errors ? JSON.stringify(xhr.responseJSON.errors) : null) ||
                    xhr.responseText ||
                    `HTTP ${xhr.status}`;
            };

            // Helper: uniform error handling + pencatatan
            // opsi: { expected, scanned, playSound }
            const handleError = (message, opts = {}) => {
                const {
                    expected = null, scanned = null, playSound = true
                } = opts;

                $('#indicator').removeClass('bg-success bg-warning').addClass('bg-danger');
                notif('error', message);

                // catat detail error via API
                errorStore(message, expected, scanned);

                if (playSound) notMatchSound();

                // fokus input kembali (sekali, bukan interval)
                setTimeout(() => {
                    $('#code').focus();
                }, 1000);
            };

            if (!Array.isArray(arraySeri)) {
                arraySeri = [];
                cursor.seri = arraySeri;
            }
            let scannedCount = parseInt(cursor.scanned_count ?? arraySeri.length, 10) || 0;

            // === Validasi: internal & customer harus dalam objek yang sama
            isSameObject = String(cursor.customer || '').trim() === String(localStorage.getItem(
                    'customerPart') || '').trim() &&
                String(cursor.internal || '').trim() === String(internal || '').trim();

            if (!isSameObject) {
                handleError('Kanban tidak sesuai!', {
                    expected: `Internal & Customer satu objek (internal: ${internal.trimEnd()}, customer: ${localStorage.getItem('customerPart')})`,
                    scanned: `Cursor keys match? ${Object.keys(cursor).length} keys`
                });
                localStorage.setItem('status', 'true');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
                return;
            }

            const duplicateSerialError = () => {
                handleError('Seri kanban sudah discan!', {
                    expected: 'Seri unik (belum pernah discan)',
                    scanned: `Seri=${seri}`,
                    playSound: false
                });
                alreadyScanSound();
            };

            let updateIndexedDB = () => {};
            const continueAfterSerialCheck = () => {
                // === Validasi: quantity sudah penuh
                if (scannedCount >= totalQty) {
                    handleError('Part number sudah complete!', {
                        expected: `Qty <= ${totalQty}`,
                        scanned: `Attempt push, current=${scannedCount}`,
                        playSound: false
                    });
                    fullfilledSound();
                    return false;
                }

                // === Update IndexedDB setelah validasi backend OK
                updateIndexedDB = () => {
                    scannedCount += 1;
                    cursor.scanned_count = scannedCount;
                    cursor.actual_qty = scannedCount;
                    incrementLoadingListBucket(cursor, loadingList);
                    let countApplied = true;
                    const rollbackLocalCount = () => {
                        if (!countApplied) return;
                        scannedCount -= 1;
                        cursor.scanned_count = scannedCount;
                        cursor.actual_qty = scannedCount;
                        const buckets = ensureLoadingListBuckets(cursor);
                        if (buckets[loadingList]) {
                            buckets[loadingList].scanned = Math.max(0, pullingInt(buckets[
                                loadingList].scanned) - 1);
                        }
                        countApplied = false;
                    };
                    const transaction = database.transaction([STORE_NAME, SERIAL_STORE_NAME,
                        SUMMARY_STORE_NAME
                    ], 'readwrite');
                    const objectStore = transaction.objectStore(STORE_NAME);
                    const serialStore = transaction.objectStore(SERIAL_STORE_NAME);
                    const summaryStore = transaction.objectStore(SUMMARY_STORE_NAME);
                    transaction.oncomplete = () => database.close();
                    performUpdate(transaction, objectStore, serialStore, summaryStore);
                };

                function performUpdate(transaction, objectStore, serialStore, summaryStore) {
                    serialStore.put({
                        customer_part: primaryKey,
                        internal: String(internal || '').trim(),
                        serial: String(seri || '').trim(),
                        loading_list_number: loadingList,
                        scanned_at: Date.now()
                    }, scanSerialKey(primaryKey, seri));

                    const summaryRequest = summaryStore.get(SUMMARY_KEY);
                    summaryRequest.onsuccess = function() {
                        const actual = parseInt(localStorage.getItem('pulling_total_actual') || '0',
                            10) || 0;
                        const target = parseInt(localStorage.getItem('pulling_total_target') ||
                            totalQty || '0', 10) || 0;
                        const summary = summaryRequest.result || {
                            totalActual: actual,
                            totalTarget: target
                        };
                        summary.totalActual = (parseInt(summary.totalActual || 0, 10) || 0) + 1;
                        summary.totalTarget = parseInt(summary.totalTarget || target, 10) || target;
                        summary.updatedAt = Date.now();
                        summaryStore.put(summary, SUMMARY_KEY);
                    };

                    const putRequest = objectStore.put(cursor, primaryKey);

                    putRequest.onsuccess = function() {
                        $('#qty-display').text(`${scannedCount}/${totalQty}`);
                        $('#int-display').text(internal);
                        $('#cust-display').text('-');
                        $('#chute-display').text('-');
                        $('#backno-display').text('-');
                        $('#indicator').removeClass('bg-danger bg-warning').addClass('bg-success');
                        resetIndicator();
                        incrementPullingQuantity(1);
                        okSound();
                        pushScanPairHistory(customer, internal.trimEnd(), seri);
                        localStorage.removeItem('customerPart');
                    };

                    putRequest.onerror = function(event) {
                        const err = event?.target?.error?.message || 'Put request failed';
                        handleError('Gagal menyimpan data ke database lokal!', {
                            expected: `IDB put ke store "${STORE_NAME}" key=${primaryKey}`,
                            scanned: err
                        });
                        rollbackLocalCount();
                    };

                    transaction.onerror = function(event) {
                        const err = event?.target?.error?.message || 'Transaction failed';
                        handleError('Transaction failed!', {
                            expected: `IDB transaksi write ke store "${STORE_NAME}"`,
                            scanned: err
                        });
                        rollbackLocalCount();
                        database.close();
                    };
                }

                return true;
            };

            const requestMutation = () => {
                // === Request ke backend terlebih dahulu
                try {
                    $.ajax({
                        type: 'GET',
                        url: "{{ route('pulling.mutation') }}",
                        _token: "{{ csrf_token() }}",
                        data: {
                            loadingList: loadingList,
                            internalPart: internal.trimEnd(),
                            customerPart: localStorage.getItem('customerPart'),
                            serialNumber: seri,
                            qty_per_kbn: qty_per_kbn
                        },
                        contentType: 'application/json',
                        success: function(data) {
                            if (data.status == 'success') {
                                // eDCL opsional
                                if (localStorage.getItem('char_total') == 10000) {
                                    $.ajax({
                                        type: 'GET',
                                        url: "{{ url('/edcl/store') }}/" +
                                            skid + '/' + manifest + '/' + itemNo + '/' +
                                            seqNo + '/' + barcodecomplete + '/' +
                                            originalBarcode + '/' + loadingList + '/' +
                                            localStorage.getItem('customer'),
                                        _token: "{{ csrf_token() }}",
                                        dataType: 'json',
                                        success: function(response) {
                                            if (response.status == 'success') {
                                                updateIndexedDB();
                                                tmminSuccessIndicator();
                                            } else {
                                                handleError(response.message ||
                                                    'eDCL gagal', {
                                                        expected: 'eDCL success',
                                                        scanned: JSON.stringify(
                                                            response),
                                                        playSound: false
                                                    });
                                                tmminErrorIndicator();
                                            }
                                        },
                                        error: function(xhr) {
                                            const msg = xhrMessage(xhr);
                                            handleError(msg, {
                                                expected: 'HTTP 200 OK dari eDCL',
                                                scanned: `HTTP ${xhr.status}`
                                            });
                                        }
                                    });
                                } else {
                                    // tanpa eDCL
                                    updateIndexedDB();
                                }
                            } else {
                                handleError(data.message || 'Validasi backend gagal', {
                                    expected: 'Response success dari mutation',
                                    scanned: JSON.stringify(data),
                                    playSound: (data.status == 'notExists')
                                });
                                if (data.status == 'notExists') notExist();
                            }
                        },
                        error: function(xhr) {
                            const msg = xhrMessage(xhr);
                            handleError(msg, {
                                expected: 'HTTP 200 OK dari mutation',
                                scanned: `HTTP ${xhr.status}`
                            });
                        }
                    });
                } catch (error) {
                    handleError('An error occurred. Please try again.', {
                        expected: 'AJAX berjalan sukses',
                        scanned: error?.message || String(error)
                    });
                }
            };

            const ensureLegacySerialsIndexed = () => {
                return new Promise((resolve, reject) => {
                    if (!arraySeri.length || cursor.serials_indexed_at) {
                        resolve();
                        return;
                    }

                    try {
                        const transaction = database.transaction([STORE_NAME, SERIAL_STORE_NAME],
                            'readwrite');
                        const objectStore = transaction.objectStore(STORE_NAME);
                        const serialStore = transaction.objectStore(SERIAL_STORE_NAME);

                        arraySeri.forEach((existingSerial) => {
                            serialStore.put({
                                customer_part: primaryKey,
                                internal: String(internal || '').trim(),
                                serial: String(existingSerial || '').trim(),
                                loading_list_number: loadingList,
                                scanned_at: null,
                                migrated: true
                            }, scanSerialKey(primaryKey, existingSerial));
                        });

                        cursor.serials_indexed_at = Date.now();
                        objectStore.put(cursor, primaryKey);
                        transaction.oncomplete = resolve;
                        transaction.onerror = () => reject(transaction.error || new Error(
                            'Gagal index serial lama'));
                        transaction.onabort = () => reject(transaction.error || new Error(
                            'Gagal index serial lama'));
                    } catch (error) {
                        reject(error);
                    }
                });
            };

            ensureLegacySerialsIndexed().then(() => {
                try {
                    const duplicateTransaction = database.transaction([SERIAL_STORE_NAME], 'readonly');
                    const duplicateRequest = duplicateTransaction.objectStore(SERIAL_STORE_NAME)
                        .get(scanSerialKey(primaryKey, seri));

                    duplicateRequest.onsuccess = function() {
                        if (duplicateRequest.result) {
                            duplicateSerialError();
                            return;
                        }

                        if (continueAfterSerialCheck()) requestMutation();
                    };

                    duplicateRequest.onerror = function() {
                        handleError('Error membaca data serial', {
                            expected: `Serial store ${SERIAL_STORE_NAME}`,
                            scanned: String(seri || '')
                        });
                    };
                } catch (error) {
                    handleError('Error akses database serial', {
                        expected: `Store ${SERIAL_STORE_NAME}`,
                        scanned: error?.message || String(error)
                    });
                }
            }).catch((error) => {
                handleError('Error akses database serial', {
                    expected: `Store ${SERIAL_STORE_NAME}`,
                    scanned: error?.message || String(error)
                });
            });
        }

        function checkKanban(seri, internal) {
            $.ajax({
                type: 'GET',
                url: '/kanban/check',
                _token: "{{ csrf_token() }}",
                data: {
                    seri: seri,
                    internal: internal.trimEnd()
                },
                dataType: 'json',
                success: function(data) {
                    if (data.status == 'success') {
                        console.log(data.status);
                    } else if (data.status == 'partNotExist') {
                        notif('error', data.message)
                        partNotExistSound();
                        setInterval(() => {
                            $('#code').focus();
                        }, 1000);
                        return false;
                    } else if (data.status == 'kanbanNotExist') {
                        notif('error', data.message)
                        kanbanNotExistSound();
                        setInterval(() => {
                            $('#code').focus();
                        }, 1000);
                        return false;
                    } else if (data.status == 'notScanned') {
                        notif('error', data.message)
                        notScannedSound();
                        setInterval(() => {
                            $('#code').focus();
                        }, 1000);
                        return false;
                    }
                },
                error: function(xhr) {
                    console.log(xhr.getResponse);
                }
            });
        }

        class ScannerInput {
            constructor(selector, onScan, {
                gapMs = 40,
                acceptTab = true
            } = {}) {
                this.$el = $(selector);
                this.onScan = onScan;
                this.buffer = '';
                this.timer = null;
                this.gapMs = gapMs;
                this.acceptTab = acceptTab;

                // Jangan biarkan nilai menumpuk di input (biar selalu “kosong” secara visual)
                this.$el.val('');

                // 1) Kumpulkan karakter via 'input' (IME / Zebra / OS manapun)
                this.$el.on('input', (e) => {
                    const v = e.target.value || '';
                    if (!v) return;
                    this.buffer += v;
                    // kosongkan field supaya tidak bertambah panjang
                    e.target.value = '';
                    this._restartTimer();
                });

                // 2) Enter/TAB via keydown (DataWedge bisa diset suffix ENTER/TAB)
                this.$el.on('keydown', (e) => {
                    // ENTER → proses langsung
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        this._flush();
                    }
                    // TAB (opsional) → proses langsung
                    else if (this.acceptTab && e.key === 'Tab') {
                        e.preventDefault();
                        this._flush();
                    } else {
                        // untuk zebra kadang kirim keyCode 229 (IME) → abaikan, biarkan 'input' yang bekerja
                    }
                });

                // 3) Paste fallback (kalau user tempel string)
                this.$el.on('paste', (e) => {
                    const text = (e.originalEvent.clipboardData || window.clipboardData)?.getData(
                        'text') || '';
                    if (text) {
                        this.buffer += text;
                        this._flushSoon();
                    }
                });

                // Aksesibilitas: fokus kembali jika blur
                this.$el.on('blur', () => {
                    // setTimeout(() => this.focus(), 0);
                });
            }

            _restartTimer() {
                clearTimeout(this.timer);
                // Jika scanner tidak mengirim suffix (ENTER/TAB),
                // kita finalize setelah jeda antarkarakter (inter-char gap).
                this.timer = setTimeout(() => this._flush(), this.gapMs);
            }

            _flushSoon() {
                clearTimeout(this.timer);
                this.timer = setTimeout(() => this._flush(), 0);
            }

            _flush() {
                clearTimeout(this.timer);
                if (!this.buffer) return;
                const code = this.buffer;
                this.buffer = '';
                try {
                    this.onScan(code);
                } catch (e) {
                    console.error(e);
                }
                this.focus();
            }

            focus() {
                const el = this.$el.get(0);
                if (!el) return;
                el.focus();
                try {
                    // taruh caret di akhir (meski field kita kosong)
                    el.setSelectionRange(el.value.length, el.value.length);
                } catch (_) {}
            }
        }

        class BarcodeScanner {
            constructor() {
                this.barcode = "";
                this.token = "{{ session()->get('token') }}" || '';
                this.progressTilePrefix = 'll-progress-';
                this.tileByKey = new Map();
                // Don't cache PDS value in constructor - get it dynamically when needed

                // Barcode patterns and handlers
                this.patterns = [{
                        test: (code) => code.startsWith('C') && code.length < 22,
                        handler: 'handleLoadingList'
                    },
                    {
                        test: (code) => code === "DONE",
                        handler: 'handleLogout'
                    },
                    {
                        test: (code) => this.isKanbanBarcode(code),
                        handler: 'handleKanbanBarcode'
                    },
                    {
                        test: (code) => code.length === parseInt(localStorage.getItem('char_total') ||
                            '0'),
                        handler: 'handleCustomerKanban'
                    },
                    {
                        test: () => ['MMKI', 'SENOPATI', 'FUJITRANS'].includes(localStorage.getItem(
                            'customer')),
                        handler: 'handleMMKIKanban'
                    },
                    {
                        test: () => localStorage.getItem('customer') === 'TB INA',
                        handler: 'handleTBINAKanban'
                    },
                    {
                        test: () => localStorage.getItem('customer') === 'TTI INDONESIA',
                        handler: 'handleTTIKanban'
                    }
                ];
            }

            sanitizeKey(str) {
                return String(str).replace(/[^A-Za-z0-9_-]/g, '');
            }
            getTileIdByKey(key) {
                return `${this.progressTilePrefix}${this.sanitizeKey(key)}`;
            }

            // ===== progress tile helpers =====
            createProgressTile(message = 'Mengambil data loading list...') {
                const id = `${this.progressTilePrefix}${Date.now()}`;
                $('#list').append(`
                    <li class="pulling-ll-item" id="${id}">
                    <div id="${id}-card" class="glass-tile glass-tile--loading glass-in">
                        <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
                        <span class="fw-semibold" id="${id}-text" aria-live="polite">${message}</span>
                    </div>
                    </li>
                `);
                return id;
            }

            ensureProgressTile(key, message = 'Mengambil data loading list...') {
                const id = this.getTileIdByKey(key);
                const $li = $(`#${id}`);
                if ($li.length) {
                    // reset ke state loading
                    const $card = $li.find(`#${id}-card`);
                    $card.attr('class', 'glass-tile glass-tile--loading glass-in')
                        .html(`
                            <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
                            <span class="fw-semibold" id="${id}-text" aria-live="polite">${message}</span>
                            `);
                    return id;
                }
                $('#list').append(`
                        <li class="pulling-ll-item" id="${id}">
                        <div id="${id}-card" class="glass-tile glass-tile--loading glass-in">
                            <div class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></div>
                            <span class="fw-semibold" id="${id}-text" aria-live="polite">${message}</span>
                        </div>
                        </li>
                    `);
                this.tileByKey.set(key, id);
                return id;
            }

            setProgressTile(id, msg) {
                $(`#${id}-text`).text(msg);
            }

            setProgressTileError(id, msg = 'Gagal memproses', {
                autoRemove = true,
                delay = 1500
            } = {}) {
                const $c = $(`#${id}-card`);
                $c.removeClass('glass-tile--loading').addClass('glass-tile--error');
                $c.find('.spinner-border').remove();
                $(`#${id}-text`).text(msg);
                if (autoRemove) setTimeout(() => this.removeTileById(id), delay);
            }

            removeTileById(id) {
                const $li = $(`#${id}`);
                if ($li.length) $li.remove();
                // bersihkan mapping key->id
                for (const [k, v] of this.tileByKey.entries())
                    if (v === id) this.tileByKey.delete(k);
            }

            // final: ganti tile progress jadi markup LAMA-mu (biru)
            replaceTileWithFinalOriginal(key, number) {
                const id = this.getTileIdByKey(key);
                $(`#${id}`).replaceWith(pullingLlMarkup(number));
                this.tileByKey.delete(key);
            }


            // Helper method to get current PDS dynamically
            getCurrentPDS() {
                return localStorage.getItem('pds_local') || localStorage.getItem('pdsNumber') || '';
            }

            init() {
                this.scannerInput = new ScannerInput('#code', (scanned) => {
                    this.processBarcode(scanned); // panggil fungsi lama milikmu
                }, {
                    gapMs: 40, // jeda finalize jika tidak ada suffix
                    acceptTab: true // jika DataWedge pakai TAB sebagai suffix
                });

                // fokuskan saat mulai
                // this.scannerInput.focus();
            }

            handleKeyPress(e) {
                e.preventDefault();
                const keyCode = e.keyCode || e.which;
                if (keyCode === 13) { // Enter key
                    this.processBarcode(this.barcode);
                    this.barcode = "";
                } else {
                    this.barcode += String.fromCharCode(e.which);
                }
            }

            processBarcode(code) {
                console.log(`Processing barcode: ${code} (length: ${code.length})`);

                const pattern = this.patterns.find(p => p.test(code));
                if (pattern) {
                    this[pattern.handler](code);
                } else {
                    this.showError("Kanban tidak dikenali!", () => {
                        if (typeof unknownSound === 'function') unknownSound();
                    });
                }
            }

            isKanbanBarcode(code) {
                const kanbanLengths = [218, 220, 230, 241, 242];
                return kanbanLengths.includes(code.length);
            }

            async handleLoadingList(code) {
                const formattedCode = code.substr(0, 11) + ' A';
                const tileId = this.ensureProgressTile(formattedCode, 'Mengambil data loading list...');

                try {
                    const loadingList = this.getLoadingListNumber();
                    if (loadingList.includes(formattedCode)) {
                        this.setProgressTileError(tileId, 'Sudah discan', {
                            autoRemove: true,
                            delay: 1200
                        });
                        this.showError('Loading list sudah discan!', () => {
                            if (typeof alreadyScanLlSound === 'function') alreadyScanLlSound();
                        });
                        return;
                    }

                    const response = await this.fetchLoadingList(formattedCode);
                    if (response.status !== 'success') {
                        this.setProgressTileError(tileId, response.message || 'Gagal ambil data', {
                            autoRemove: true
                        });
                        this.showError(response.message || 'Gagal mengambil data');
                        return;
                    }

                    await this.processLoadingListData(
                        response.data,
                        (msg) => this.setProgressTile(tileId, msg),
                        /* tileId not needed here now */
                    );

                    // sukses → replace dengan markup lama (biru)
                    this.replaceTileWithFinalOriginal(formattedCode, response.data.number);

                } catch (e) {
                    this.setProgressTileError(tileId, 'Koneksi bermasalah', {
                        autoRemove: true
                    });
                    this.showError('Connection Error');
                }
            }

            async fetchLoadingList(code) {
                return await $.ajax({
                    type: 'GET',
                    url: `https://dea-dev.aiia.co.id/api/v1/loading-lists/${code}`,
                    headers: {
                        "Authorization": `Bearer ${this.token}`
                    },
                    dataType: 'json'
                });
            }

            async processLoadingListData(data, progressCb = null, tileId = null) {
                const {
                    pds_number,
                    number: ll,
                    cycle,
                    customer_code,
                    delivery_date,
                    shipping_date
                } = data;

                const existingPDS = localStorage.getItem('pdsNumber');
                if (existingPDS && data.pds_number !== existingPDS) {
                    progressCb?.('LL tidak sesuai');
                    this.showError('Loading list tidak sesuai!', () => {
                        if (typeof notMatchLlSound === 'function') notMatchLlSound();
                    });
                    return;
                }

                try {
                    progressCb?.('Menyimpan header...');
                    await this.storeLoadingList(ll, pds_number, cycle, customer_code, delivery_date,
                        shipping_date);

                    progressCb?.('Menyimpan data lokal...');
                    this.storeLoadingListData(data);

                    progressCb?.('Memperbarui tampilan...');

                    if (!tileId) this.updateLoadingListUI(data);
                    $('#cycle-display').text(data.cycle);

                    progressCb?.('Menyiapkan IndexedDB...');
                    await this.initializeDatabase(data);

                    progressCb?.('Menyimpan detail item...');
                    await this.storeLoadingListDetails(ll, data.items);

                    progressCb?.('Finalisasi...');
                    await this.customerCheck(customer_code);
                    this.pullingQuantity();
                    this.checkLoadingList();
                    this.customerCharStore(customer_code, pds_number);

                    this.focusInput();

                } catch (error) {
                    progressCb?.('Gagal memproses');
                    this.showError('Gagal memproses loading list');
                }
            }

            async storeLoadingList(ll, pds, cycle, customerCode, deliveryDate, shippingDate) {
                try {
                    const response = await $.ajax({
                        type: 'GET',
                        url: `/loading-list/store/${ll}/${pds}/${cycle}/${customerCode}/${deliveryDate || ''}/${shippingDate || ''}`,
                        dataType: 'json'
                    });

                    // console.log('Loading list stored:', response.status);
                    return response;

                } catch (xhr) {
                    console.error('Error storing loading list:', xhr);

                    if (xhr.status === 0) {
                        this.showError('Connection Error');
                        throw new Error('Network error storing loading list');
                    } else {
                        const errorMsg = xhr.responseJSON?.errors || 'Gagal menyimpan loading list';
                        this.showError(errorMsg);
                        throw new Error(errorMsg);
                    }
                }
            }

            async storeLoadingListDetails(ll, items) {
                const promises = items.map((item, index) => {
                    const qtyPerKbn = item.total_qty / item.total_kanban_qty;

                    return new Promise((resolve, reject) => {
                        setTimeout(() => {
                            $.ajax({
                                type: 'GET',
                                url: `/loading-list/storeDetail/${ll}/${item.part_number_cust}/${item.part_number_int}/${item.total_kanban_qty}/${qtyPerKbn}/${item.total_qty}/${item.actual_kanban_qty}`,
                                dataType: 'json',
                                success: (response) => {
                                    // console.log(
                                    //     `Stored detail for ${item.part_number_cust}: ${response.status}`
                                    // );
                                    resolve(response);
                                },
                                error: (xhr, status, error) => {
                                    // console.error(
                                    //     `Failed to store detail for ${item.part_number_cust}:`,
                                    //     xhr);

                                    if (xhr.status === 0) {
                                        reject(new Error(
                                            `Network error storing ${item.part_number_cust}`
                                        ));
                                    } else {
                                        console.warn(
                                            `Server error ${xhr.status} for ${item.part_number_cust}, continuing...`
                                        );
                                        resolve({
                                            status: 'error',
                                            item: item
                                                .part_number_cust,
                                            error: xhr
                                                .responseJSON
                                                ?.errors ||
                                                error
                                        });
                                    }
                                }
                            });
                        }, index * 200); // Using 200ms delay like original
                    });
                });

                try {
                    const results = await Promise.all(promises);
                    const failures = results.filter(result => result && result.status === 'error');

                    if (failures.length > 0) {
                        console.warn(`${failures.length} items failed to store:`, failures);
                    }

                    return results;
                } catch (error) {
                    // console.error('Critical error storing loading list details:', error);
                    this.showError('Gagal menyimpan detail loading list. Koneksi bermasalah.');
                    throw error;
                }
            }

            storeLoadingListData(data) {
                localStorage.setItem('pds_local', data.pds_number);
                localStorage.setItem(`ll_${data.number}`, data.number);
                localStorage.setItem('pdsNumber', data.pds_number);
                localStorage.setItem('cycle', data.cycle);
            }

            updateLoadingListUI(data, tileId = null) {
                if (tileId) this.replaceTileWithFinal(tileId, data.number);
                // update elemen lain:
                $('#cycle-display').text(data.cycle);
            }

            async initializeDatabase(data) {
                const database = await openPullingDB(data.pds_number);
                try {
                    await this.populateDatabase(database, data);
                } finally {
                    database.close();
                }
            }

            async populateDatabase(database, data) {
                const transaction = database.transaction([STORE_NAME], 'readwrite');
                const objectStore = transaction.objectStore(STORE_NAME);
                const transactionDone = new Promise((resolve, reject) => {
                    transaction.oncomplete = resolve;
                    transaction.onerror = () => reject(transaction.error);
                    transaction.onabort = () => reject(transaction.error);
                });

                const promises = data.items.map(item => {
                    return new Promise((resolve, reject) => {
                        const key = item.part_number_cust;
                        const getRequest = objectStore.get(key);

                        getRequest.onsuccess = (event) => {
                            try {
                                const merged = mergeLoadingListItem(event.target.result,
                                    item, data.number);

                                if (merged.shouldPut) {
                                    objectStore.put(merged.record, key);
                                }

                                resolve();
                            } catch (error) {
                                reject(error);
                            }
                        };

                        getRequest.onerror = () => reject(getRequest.error);
                    });
                });

                await Promise.all(promises);
                await transactionDone;
                await refreshPullingSummaryFromRecords(database);
            }

            handleCustomerKanban(code) {
                const processedCode = this.processCustomerCode(code);
                this.findKanbanInDatabase(processedCode, code);
            }

            processCustomerCode(code) {
                let processed = code;
                const charLength = parseInt(localStorage.getItem('char_length') || '0');
                const charFirst = parseInt(localStorage.getItem('char_first') || '0');

                if (charLength > 0) {
                    processed = code.substr(charFirst, charLength);
                }

                processed = processed.trim().replace(/-/g, '').toUpperCase();

                // Handle Suzuki special cases — KHUSUS customer Suzuki.
                // (Customer lain dengan char_length sama jangan ikut dipotong "000"-nya.)
                const isSuzuki = (localStorage.getItem('customer') || '').toUpperCase().includes('SUZUKI');
                if (isSuzuki) {
                    if (charLength === 17 && processed.substr(10, 3) === '000') {
                        processed = processed.slice(0, -3);
                    } else if (charLength === 15 && processed.slice(-3) === '000') {
                        processed = processed.slice(0, -3);
                    }
                }

                return processed;
            }

            async findKanbanInDatabase(processedCode, originalCode) {
                // Get current PDS dynamically
                const databaseName = this.getCurrentPDS();

                // console.log('PDS value for database:', databaseName);
                // console.log('Available PDS values:', {
                //     'getCurrentPDS()': databaseName,
                //     'pds_local': localStorage.getItem('pds_local'),
                //     'pdsNumber': localStorage.getItem('pdsNumber')
                // });

                if (!databaseName) {
                    this.showError('Database name tidak ditemukan. Scan loading list dulu!');
                    return;
                }

                const request = indexedDB.open(databaseName);

                request.onsuccess = (event) => {
                    const database = event.target.result;

                    // Debug: Check database info
                    // console.log('Database opened:', database.name);
                    // console.log('Available object stores:', Array.from(database.objectStoreNames));

                    // Check if objectStore exists before creating transaction
                    if (!database.objectStoreNames.contains('loadingList')) {
                        console.error('Object store "loadingList" not found in database');
                        // console.log('Database version:', database.version);
                        this.showError('Database belum di-initialize. Scan loading list dulu!');
                        database.close();
                        return;
                    }

                    try {
                        const transaction = database.transaction(['loadingList'], 'readonly');
                        const objectStore = transaction.objectStore('loadingList');
                        const getRequest = objectStore.get(processedCode);

                        getRequest.onsuccess = (event) => {
                            const record = event.target.result;
                            if (!record) {
                                this.showError('Kanban tidak dikenali / sesuai!', () => {
                                    if (typeof unknownSound === 'function') unknownSound();
                                });
                                return;
                            }

                            const seriList = Array.isArray(record.seri) ? record.seri : [];
                            const scannedCount = parseInt(record.scanned_count ?? seriList.length,
                                10) || 0;
                            if (scannedCount >= record.total_qty) {
                                this.showError('Part number sudah complete!', () => {
                                    if (typeof fullfilledSound === 'function')
                                        fullfilledSound();
                                });
                                this.setIndicator('danger');
                                return;
                            }

                            this.updateKanbanDisplay(record, processedCode, originalCode);
                        };

                        getRequest.onerror = (event) => {
                            console.error('Get request error:', event.target.error);
                            this.showError('Error membaca database');
                        };

                        transaction.onerror = (event) => {
                            console.error('Transaction error:', event.target.error);
                            this.showError('Error dalam transaksi database');
                        };

                        transaction.oncomplete = () => {
                            database.close();
                        };

                    } catch (error) {
                        console.error('Error creating transaction:', error);
                        this.showError('Error akses database');
                        database.close();
                    }
                };

                request.onerror = (event) => {
                    console.error('Database open error:', event.target.error);
                    this.showError('Error membuka database');
                };
            }

            handleKanbanBarcode(code) {
                if (!localStorage.getItem('customerPart')) {
                    this.showError('Scan kanban customer dulu!', () => {
                        if (typeof scanCustomerFirstSound === 'function') scanCustomerFirstSound();
                    });
                    return;
                }

                const kanbanData = this.extractKanbanData(code.toUpperCase());
                if (kanbanData) {
                    this.processInternalKanban(kanbanData);
                }
            }

            extractKanbanData(code) {
                const patterns = {
                    230: {
                        internal: [41, 19],
                        seri: [123, 4]
                    },
                    220: {
                        internal: [35, 16],
                        seri: [130, 4]
                    },
                    241: {
                        internal: [35, 12],
                        seri: [127, 4]
                    },
                    218: {
                        internal: [41, 16],
                        seri: [123, 4]
                    },
                    242: {
                        internal: [35, 12],
                        seri: [127, 4]
                    }
                };

                const pattern = patterns[code.length];
                if (!pattern) return null;

                return {
                    internal: code.substr(pattern.internal[0], pattern.internal[1]),
                    seri: code.substr(pattern.seri[0], pattern.seri[1])
                };
            }

            processInternalKanban({
                internal,
                seri
            }) {
                const databaseName = this.getCurrentPDS();

                if (!databaseName) {
                    this.showError('Database name tidak ditemukan. Scan loading list dulu!');
                    return;
                }

                openPullingDB(databaseName).then((database) => {
                    try {
                        const transaction = database.transaction([STORE_NAME], 'readonly');
                        const objectStore = transaction.objectStore(STORE_NAME);
                        const customerPart = localStorage.getItem('customerPart');
                        const getRequest = objectStore.get(customerPart);

                        getRequest.onsuccess = (event) => {
                            const record = event.target.result;
                            if (record) {
                                this.checkInternalAndCustomer(database, record, internal,
                                    customerPart, seri);
                            } else {
                                this.showError('Kanban tidak ditemukan!', () => {
                                    if (typeof unknownSound === 'function')
                                        unknownSound();
                                });
                                database.close();
                            }
                        };

                        getRequest.onerror = (event) => {
                            // console.error('Get request error:', event.target.error);
                            this.showError('Error membaca data kanban');
                            database.close();
                        };

                        transaction.onerror = (event) => {
                            // console.error('Transaction error:', event.target.error);
                            this.showError('Error dalam transaksi database');
                        };

                    } catch (error) {
                        // console.error('Error creating transaction:', error);
                        this.showError('Error akses database');
                        database.close();
                    }
                }).catch((event) => {
                    // console.error('Database open error:', event.target.error);
                    this.showError('Error membuka database');
                });
            }

            handleMMKIKanban(code) {
                this.processCustomerSpecificKanban(code.trimEnd(), 'MMKI');
            }

            handleTBINAKanban(code) {
                let processed = code;
                if (code.length === 36) {
                    processed = code.substr(parseInt(localStorage.getItem('char_first') || '0'),
                        parseInt(localStorage.getItem('char_length') || '0'));
                } else if (code.length === 14) {
                    processed = code.substr(0, 11);
                }

                processed = processed.trim().replace(/-/g, '').toUpperCase();
                console.log('Processed TB INA kanban code:', processed);
                this.processCustomerSpecificKanban(processed, 'TB INA');
            }

            handleTTIKanban(code) {
                const processed = code.substr(7, 10);
                this.processCustomerSpecificKanban(processed.trimEnd(), 'TTI INDONESIA');
            }

            processCustomerSpecificKanban(code, customerType) {
                console.log(`Processing ${customerType} kanban:`, code);
                const databaseName = this.getCurrentPDS();

                if (!databaseName) {
                    this.showError('Database name tidak ditemukan. Scan loading list dulu!');
                    return;
                }

                const request = indexedDB.open(databaseName);

                request.onsuccess = (event) => {
                    const database = event.target.result;

                    // Check if objectStore exists
                    if (!database.objectStoreNames.contains('loadingList')) {
                        // console.error('Object store "loadingList" not found');
                        this.showError('Database belum di-initialize. Scan loading list dulu!');
                        database.close();
                        return;
                    }

                    try {
                        const transaction = database.transaction(['loadingList'], 'readonly');
                        const objectStore = transaction.objectStore('loadingList');
                        const getRequest = objectStore.get(code);

                        getRequest.onsuccess = (event) => {
                            const record = event.target.result;
                            if (!record) {
                                this.showError('Kanban tidak sesuai!', () => {
                                    if (typeof notMatchSound === 'function')
                                        notMatchSound();
                                });
                                return;
                            }

                            const seriList = Array.isArray(record.seri) ? record.seri : [];
                            const scannedCount = parseInt(record.scanned_count ?? seriList.length,
                                10) || 0;
                            if (scannedCount >= record.total_qty) {
                                this.showError('Part number sudah complete!', () => {
                                    if (typeof fullfilledSound === 'function')
                                        fullfilledSound();
                                });
                                this.setIndicator('danger');
                                return;
                            }

                            this.updateKanbanDisplay(record, code);
                        };

                        getRequest.onerror = (event) => {
                            // console.error('Get request error:', event.target.error);
                            this.showError('Error membaca database');
                        };

                        transaction.onerror = (event) => {
                            // console.error('Transaction error:', event.target.error);
                            this.showError('Error dalam transaksi database');
                        };

                        transaction.oncomplete = () => {
                            database.close();
                        };

                    } catch (error) {
                        // console.error('Error creating transaction:', error);
                        this.showError('Error akses database');
                        database.close();
                    }
                };

                request.onerror = (event) => {
                    // console.error('Database open error:', event.target.error);
                    this.showError('Error membuka database');
                };
            }

            handleLogout() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/logout';

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
                form.appendChild(csrfToken);

                document.body.appendChild(form);
                form.submit();
            }

            updateKanbanDisplay(record, customerCode, originalCode = null) {
                $('#cust-display').text(record.customer);
                $('#backno-display').text('-');
                fetchAlamatChute(record.loading_list_number, record.customer, record.internal);
                $('#int-display').text('-');
                const seriList = Array.isArray(record.seri) ? record.seri : [];
                const scannedCount = parseInt(record.scanned_count ?? seriList.length, 10) || 0;
                $('#qty-display').text(`${scannedCount}/${record.total_qty}`);

                this.setIndicator('warning');
                this.resetIndicator();

                localStorage.setItem('customerPart', record.customer);
                if (originalCode) {
                    localStorage.setItem('originalCustomerPart', originalCode);
                }
            }

            setIndicator(type) {
                const indicator = $('#indicator');
                indicator.removeClass('bg-success bg-warning bg-danger');
                indicator.addClass(`bg-${type}`);
            }

            showError(message, soundFunction = null) {
                if (typeof notif === 'function') {
                    notif('error', message);
                }
                if (soundFunction) soundFunction();
                this.delayedFocus();
            }

            delayedFocus() {
                setTimeout(() => this.focusInput(), 1000);
            }

            focusInput() {
                $('#code').focus();
            }

            getLoadingListNumber() {
                return typeof getLoadingListNumber === 'function' ? getLoadingListNumber() : [];
            }

            checkInternalAndCustomer(database, record, internal, customerPart, seri) {
                if (typeof checkInternalAndCustomer === 'function') {
                    checkInternalAndCustomer(database, record, internal, customerPart, seri);
                }
            }

            resetIndicator() {
                if (typeof resetIndicator === 'function') {
                    resetIndicator();
                }
            }

            async customerCheck(customerCode) {
                return new Promise((resolve, reject) => {
                    if (typeof customerCheck === 'function') {
                        customerCheck(customerCode).then(resolve).catch(reject);
                    } else {
                        resolve();
                    }
                });
            }

            pullingQuantity() {
                if (typeof pullingQuantity === 'function') {
                    pullingQuantity();
                }
            }

            checkLoadingList() {
                if (typeof checkLoadingList === 'function') {
                    checkLoadingList();
                }
            }

            customerCharStore(customerCode, pdsNumber) {
                if (typeof customerCharStore === 'function') {
                    customerCharStore(customerCode, pdsNumber);
                }
            }
        }

        (function() {
            if (!window.BarcodeScanner) return;
            const proto = BarcodeScanner.prototype;

            if (typeof proto.updateKanbanDisplay === 'function') {
                const _orig = proto.updateKanbanDisplay;
                proto.updateKanbanDisplay = function(record, customerCode, originalCode = null) {
                    const ret = _orig.call(this, record, customerCode, originalCode);

                    if (isDirectOn()) {
                        const customerPart = String(record?.customer || '').trim();
                        if (customerPart) {
                            getPartRecordFromIDB(customerPart)
                                .then(rec => {
                                    console.log('[DIRECT] IDB OK:', {
                                        loading_list_number: rec.loading_list_number,
                                        internal: rec.internal,
                                        total_qty: rec.total_qty,
                                        seri_len: rec.seri?.length
                                    });
                                })
                                .catch(err => {
                                    console.warn('[DIRECT] IDB error:', err?.message || err);
                                });
                        }
                    }
                    return ret;
                };
            }
        })();


        // Initialize the barcode scanner
        $(document).ready(() => {
            const scanner = new BarcodeScanner();
            // HOOK INSTANCE: panggil single-scan saat Direct ON
            const _origUpdate = scanner.updateKanbanDisplay.bind(scanner);
            scanner.updateKanbanDisplay = function(record, customerCode, originalCode = null) {
                // TRACE (biar tetap kelihatan di console)
                console.log(
                    '[TRACE] updateKanbanDisplay CALLED (Direct will try single-scan if ON)', {
                        directOn: (localStorage.getItem('directMode') || 'off'),
                        customer: record?.customer,
                        len_seri: record?.seri?.length,
                        total: record?.total_qty
                    });

                // jalankan logika asli (update UI customer panel, set localStorage.customerPart, dll.)
                const ret = _origUpdate(record, customerCode, originalCode);

                // kalau Direct ON → jalankan single-scan
                if ((localStorage.getItem('directMode') || 'off') === 'on') {
                    const customerPart = String(record?.customer || '').trim();
                    if (customerPart) directSingleScan(customerPart);
                } else {
                    console.log('[TRACE] Direct OFF – tidak single-scan.');
                }

                return ret;
            };

            (function() {
                const proto = BarcodeScanner.prototype;
                if (typeof proto.handleKanbanBarcode === 'function') {
                    const _orig = proto.handleKanbanBarcode;
                    BarcodeScanner.prototype.handleKanbanBarcode = function(code) {
                        if ((localStorage.getItem('directMode') || 'off') === 'on') {
                            if (typeof notif === 'function') notif('error',
                                'Direct ON: scan internal diabaikan.');
                            setTimeout(() => $('#code').focus(), 200);
                            return; // stop flow internal
                        }
                        return _orig.call(this, code);
                    };
                }
            })();


            scanner.init();
        });
    });
</script>
