<style>
    .settings-page {
        display: grid;
        gap: 18px;
    }

    .settings-head,
    .settings-panel {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: var(--r);
        box-shadow: var(--shadow);
    }

    .settings-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 22px;
    }

    .settings-title {
        margin: 0;
        color: var(--text);
        font-size: 24px;
        font-weight: 850;
    }

    .settings-subtitle {
        margin-top: 5px;
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 650;
    }

    .settings-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--primary-light);
        color: var(--primary);
        font-size: 18px;
        flex-shrink: 0;
    }

    .settings-panel {
        padding: 20px;
    }

    .settings-alert {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: 12px 14px;
        border-radius: 8px;
        color: #166534;
        background: var(--success-light);
        border: 1px solid rgba(22, 163, 74, .2);
        font-size: 13px;
        font-weight: 750;
    }

    .settings-alert.is-danger {
        color: #991b1b;
        background: var(--danger-light);
        border-color: rgba(220, 38, 38, .2);
    }

    .settings-section-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 14px;
    }

    .settings-section-title {
        margin: 0;
        color: var(--text);
        font-size: 17px;
        font-weight: 850;
    }

    .settings-section-copy {
        margin-top: 4px;
        color: var(--text-muted);
        font-size: 12.5px;
        font-weight: 650;
    }

    .settings-section-head-right {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
    }

    .settings-live {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 999px;
        background: var(--success-light);
        color: #166534;
        font-size: 11px;
        font-weight: 850;
        letter-spacing: .04em;
    }

    .settings-live .dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #16a34a;
        animation: settings-live-pulse 1.5s ease-in-out infinite;
    }

    @keyframes settings-live-pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: .35; }
    }

    .settings-device-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 12px;
    }

    .settings-device-row {
        display: flex;
        flex-direction: column;
        gap: 12px;
        min-height: 238px;
        padding: 14px;
        border: 1px solid #edf1f7;
        border-radius: 8px;
        background: #fbfdff;
    }

    .settings-device-user {
        display: grid;
        gap: 8px;
        min-width: 0;
    }

    .settings-device-card-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 10px;
    }

    .settings-device-name {
        color: var(--text);
        font-size: 14px;
        font-weight: 850;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .settings-device-meta,
    .settings-device-page,
    .settings-device-time {
        color: var(--text-muted);
        font-size: 11.5px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .settings-device-page {
        color: #51617a;
    }

    .settings-device-main {
        display: grid;
        gap: 10px;
    }

    .settings-device-stat {
        min-width: 0;
    }

    .settings-heartbeat {
        position: relative;
        width: 100%;
        height: 38px;
        overflow: hidden;
        border-radius: 7px;
        background: #f1f5f9;
    }

    .settings-heartbeat svg {
        display: block;
        width: 100%;
        height: 100%;
    }

    .settings-heartbeat-base,
    .settings-heartbeat-line {
        fill: none;
        vector-effect: non-scaling-stroke;
    }

    .settings-heartbeat-base {
        stroke: #dbe3ee;
        stroke-width: 1;
    }

    .settings-heartbeat-line {
        stroke: #94a3b8;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
        opacity: 0;
        stroke-dasharray: 260;
        stroke-dashoffset: 260;
    }

    .settings-heartbeat.is-online {
        background: #ecfdf3;
    }

    .settings-heartbeat.is-online .settings-heartbeat-base {
        stroke: #c7f3d6;
    }

    .settings-heartbeat.is-online.has-beat .settings-heartbeat-line {
        stroke: #16a34a;
        animation: settings-heartbeat-beat 3.2s ease-out 1;
        filter: drop-shadow(0 0 3px rgba(22, 163, 74, .28));
    }

    .settings-heartbeat.is-online.has-beat {
        animation: settings-heartbeat-flash 3.2s ease-out 1;
    }

    .settings-heartbeat-count {
        position: absolute;
        top: 5px;
        right: 8px;
        color: #64748b;
        font-size: 10px;
        font-weight: 850;
    }

    .settings-heartbeat.is-online .settings-heartbeat-count {
        color: #15803d;
    }

    .settings-heartbeat-meta {
        margin-top: 5px;
    }

    @keyframes settings-heartbeat-beat {
        0% {
            opacity: 0;
            stroke-dashoffset: 260;
        }
        12% {
            opacity: 1;
        }
        72% {
            opacity: 1;
            stroke-dashoffset: 0;
        }
        100% {
            opacity: 0;
            stroke-dashoffset: 0;
        }
    }

    @keyframes settings-heartbeat-flash {
        0%, 100% { box-shadow: inset 0 0 0 0 rgba(22, 163, 74, 0); }
        18% { box-shadow: inset 0 0 0 1px rgba(22, 163, 74, .2), 0 0 12px rgba(22, 163, 74, .12); }
    }

    @media (prefers-reduced-motion: reduce) {
        .settings-heartbeat.is-online.has-beat .settings-heartbeat-line {
            animation: none;
            opacity: 1;
            stroke-dashoffset: 0;
        }

        .settings-heartbeat.is-online.has-beat {
            animation: none;
        }
    }

    .settings-device-actions {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 8px;
        margin-top: auto;
    }

    .settings-device-actions > .settings-btn {
        grid-column: 1 / -1;
    }

    .settings-device-actions form {
        min-width: 0;
    }

    .settings-device-actions .settings-btn {
        justify-content: center;
        width: 100%;
    }

    .settings-device-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        width: fit-content;
        padding: 4px 8px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #475569;
        font-size: 11px;
        font-weight: 850;
    }

    .settings-device-status i {
        font-size: 7px;
    }

    .settings-device-status.is-online {
        background: var(--success-light);
        color: #166534;
    }

    .settings-device-status.is-pending {
        margin-top: 6px;
        background: var(--warning-light);
        color: #92400e;
    }

    .settings-device-label-form {
        display: flex;
        gap: 7px;
        margin-top: 9px;
    }

    .settings-device-label-input {
        min-width: 0;
        flex: 1;
        height: 34px;
        border: 1px solid var(--border);
        border-radius: 7px;
        padding: 0 10px;
        color: var(--text);
        font-size: 12px;
        font-weight: 700;
        outline: none;
    }

    .settings-icon-btn {
        width: 34px;
        height: 34px;
        border: 0;
        border-radius: 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--primary);
        color: #fff;
        cursor: pointer;
    }

    .settings-modal-card.is-device {
        width: min(980px, 100%);
        max-height: calc(100vh - 34px);
        overflow: auto;
    }

    .settings-device-modal-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 14px;
        margin-bottom: 14px;
    }

    .settings-device-modal-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: flex-end;
    }

    .settings-health-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 9px;
        margin-top: 11px;
    }

    .settings-health-card {
        padding: 10px;
        border: 1px solid #edf1f7;
        border-radius: 8px;
        background: #fff;
    }

    .settings-health-label {
        color: var(--text-muted);
        font-size: 10.5px;
        font-weight: 850;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .settings-health-value {
        margin-top: 4px;
        color: var(--text);
        font-size: 12px;
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .settings-timeline {
        display: grid;
        gap: 8px;
        margin-top: 12px;
    }

    .settings-timeline-title {
        color: var(--text);
        font-size: 12px;
        font-weight: 850;
    }

    .settings-timeline-item {
        display: grid;
        grid-template-columns: 24px minmax(0, 1fr);
        gap: 8px;
        align-items: start;
    }

    .settings-timeline-icon {
        width: 24px;
        height: 24px;
        border-radius: 7px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--primary-light);
        color: var(--primary);
        font-size: 10px;
    }

    .settings-timeline-message {
        color: var(--text);
        font-size: 12px;
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .settings-timeline-time {
        color: var(--text-muted);
        font-size: 11px;
        font-weight: 700;
    }

    .settings-empty {
        padding: 18px;
        border: 1px dashed var(--border);
        border-radius: 8px;
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 700;
        text-align: center;
    }

    .settings-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }

    .settings-field {
        padding: 16px;
        border: 1px solid #edf1f7;
        border-radius: 8px;
        background: #fbfdff;
    }

    .settings-field.is-full {
        grid-column: 1 / -1;
    }

    .settings-label {
        display: block;
        margin-bottom: 7px;
        color: var(--text);
        font-size: 13px;
        font-weight: 850;
    }

    .settings-help {
        margin-top: 6px;
        color: var(--text-muted);
        font-size: 12px;
        font-weight: 600;
    }

    .settings-select {
        width: 100%;
        height: 42px;
        border: 1px solid var(--border);
        border-radius: 7px;
        padding: 0 12px;
        background: #fff;
        color: var(--text);
        font-size: 13px;
        font-weight: 700;
        outline: none;
    }

    .settings-radio-row {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .settings-radio-row.is-three {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .settings-radio {
        position: relative;
    }

    .settings-radio input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .settings-radio span,
    .settings-toggle {
        display: flex;
        align-items: center;
        gap: 9px;
        min-height: 42px;
        border: 1px solid var(--border);
        border-radius: 7px;
        background: #fff;
        color: #51617a;
        padding: 0 12px;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
    }

    .settings-radio input:checked + span {
        border-color: rgba(0, 112, 183, .35);
        background: var(--primary-light);
        color: var(--primary);
    }

    .settings-toggle {
        justify-content: space-between;
    }

    .settings-toggle input {
        width: 18px;
        height: 18px;
        accent-color: var(--primary);
    }

    .settings-error {
        margin-top: 7px;
        color: var(--danger);
        font-size: 11.5px;
        font-weight: 700;
    }

    .settings-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 18px;
    }

    .settings-btn {
        min-height: 40px;
        border: 0;
        border-radius: 7px;
        padding: 0 16px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: var(--primary);
        color: #fff;
        font-size: 13px;
        font-weight: 850;
        cursor: pointer;
    }

    .settings-btn.is-muted {
        background: #EEF2F7;
        color: #51617a;
    }

    .settings-actions {
        gap: 10px;
    }

    .settings-dirty {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        margin-right: auto;
        color: #b45309;
        font-size: 12.5px;
        font-weight: 800;
    }

    .settings-dirty i {
        font-size: 7px;
    }

    .settings-btn.is-pending {
        box-shadow: 0 0 0 3px rgba(0, 112, 183, .18);
    }

    .settings-btn.is-danger {
        background: var(--danger);
        color: #fff;
    }

    .settings-btn.is-danger-outline {
        border: 1px solid rgba(220, 38, 38, .35);
        background: #fff;
        color: var(--danger);
    }

    .settings-btn.is-danger-outline:hover {
        background: rgba(220, 38, 38, .08);
    }

    .settings-btn:disabled {
        cursor: not-allowed;
        opacity: .58;
    }

    .settings-modal {
        position: fixed;
        inset: 0;
        z-index: 7200;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 18px;
    }

    .settings-modal.open {
        display: flex;
    }

    .settings-modal-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, .34);
        backdrop-filter: blur(8px);
    }

    .settings-modal-card {
        position: relative;
        width: min(420px, 100%);
        padding: 20px;
        border-radius: 14px;
        background: #fff;
        border: 1px solid var(--border);
        box-shadow: 0 28px 80px rgba(15, 23, 42, .26);
    }

    .settings-modal-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fef3c7;
        color: #b45309;
        font-size: 18px;
        margin-bottom: 12px;
    }

    .settings-modal-title {
        margin: 0 0 6px;
        color: var(--text);
        font-size: 17px;
        font-weight: 850;
    }

    .settings-modal-copy {
        color: var(--text-muted);
        font-size: 13px;
        font-weight: 650;
        line-height: 1.55;
    }

    .settings-modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 9px;
        margin-top: 18px;
    }

    .settings-preview {
        display: grid;
        grid-template-columns: 210px minmax(0, 1fr);
        gap: 14px;
        min-height: 170px;
        overflow: hidden;
    }

    .settings-preview-side,
    .settings-preview-main {
        border: 1px solid var(--border);
        border-radius: 10px;
        background: #fff;
    }

    .settings-preview-side {
        padding: 12px;
        transition: width .18s ease, background .18s ease, color .18s ease;
    }

    .settings-preview-brand,
    .settings-preview-menu {
        display: flex;
        align-items: center;
        gap: 9px;
    }

    .settings-preview-brand {
        margin-bottom: 14px;
        font-weight: 850;
        color: var(--text);
    }

    .settings-preview-dot {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--primary);
        color: #fff;
        flex-shrink: 0;
    }

    .settings-preview-menu {
        min-height: 34px;
        border-radius: 8px;
        padding: 0 9px;
        color: #51617a;
        font-size: 12px;
        font-weight: 750;
    }

    .settings-preview-menu.is-active {
        background: var(--primary-light);
        color: var(--primary);
    }

    .settings-preview-main {
        position: relative;
        padding: 13px;
        background: #F7FAFE;
    }

    .settings-preview-topbar {
        height: 28px;
        width: 72%;
        border-radius: 999px;
        background: #fff;
        border: 1px solid var(--border);
        margin-bottom: 13px;
    }

    .settings-preview-row {
        height: 30px;
        border-radius: 7px;
        background: #fff;
        border: 1px solid #edf1f7;
        margin-top: 8px;
    }

    .settings-preview-bella {
        position: absolute;
        right: 13px;
        bottom: 13px;
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--primary);
        color: #fff;
        box-shadow: 0 10px 26px rgba(0, 112, 183, .24);
    }

    .settings-preview.is-dark .settings-preview-side {
        background: #111827;
        border-color: rgba(148, 163, 184, .2);
    }

    .settings-preview.is-dark .settings-preview-brand {
        color: #F8FAFC;
    }

    .settings-preview.is-dark .settings-preview-menu {
        color: #CBD5E1;
    }

    .settings-preview.is-dark .settings-preview-menu.is-active {
        background: rgba(14, 165, 233, .16);
        color: #7DD3FC;
    }

    .settings-preview.is-collapsed {
        grid-template-columns: 74px minmax(0, 1fr);
    }

    .settings-preview.is-collapsed .settings-preview-text {
        display: none;
    }

    .settings-preview.is-collapsed .settings-preview-side {
        padding-inline: 10px;
    }

    .settings-preview.is-compact .settings-preview-row {
        height: 23px;
        margin-top: 6px;
    }

    .settings-preview.is-bella-hidden .settings-preview-bella {
        display: none;
    }

    .settings-swatches {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 2px;
    }

    .settings-swatch {
        position: relative;
    }

    .settings-swatch input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .settings-swatch span {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        background: var(--sw);
        color: #fff;
        font-size: 15px;
        cursor: pointer;
        border: 2px solid transparent;
        box-shadow: 0 4px 12px rgba(15, 23, 42, .12);
        transition: transform .14s ease, box-shadow .14s ease;
    }

    .settings-swatch span:hover {
        transform: translateY(-2px);
    }

    .settings-swatch input:checked + span {
        border-color: #0f172a;
        box-shadow: 0 0 0 3px rgba(15, 23, 42, .12), 0 6px 14px rgba(15, 23, 42, .18);
    }

    @media (min-width: 1280px) {
        .settings-device-list {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (min-width: 1680px) {
        .settings-device-list {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }

    @media (max-width: 768px) {
        .settings-head {
            align-items: flex-start;
        }

        .settings-grid,
        .settings-radio-row,
        .settings-radio-row.is-three {
            grid-template-columns: 1fr;
        }

        .settings-preview {
            grid-template-columns: 1fr;
        }

        .settings-section-head {
            flex-direction: column;
        }

        .settings-health-grid {
            grid-template-columns: 1fr;
        }

        .settings-device-row {
            align-items: stretch;
        }
    }
</style>
