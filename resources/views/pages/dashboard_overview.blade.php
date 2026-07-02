@extends('layouts.root.main')

@section('main')
    <style>
        .ov-wrap {
            width: 100%;
            margin: 0 auto;
        }

        .ov-head {
            margin: 12px 0 16px;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .ov-head h4 {
            font-size: 20px;
            font-weight: 850;
            color: var(--navy);
            margin: 0 0 4px;
            letter-spacing: -.02em;
        }

        .ov-head p {
            font-size: 12px;
            color: var(--text-muted);
            margin: 0;
            font-weight: 600;
        }

        .ov-live {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 800;
            color: var(--primary);
        }

        .ov-live .dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--primary);
            animation: ovpulse 1.5s infinite;
        }

        @keyframes ovpulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: .3;
            }
        }

        .ov-filter {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 16px;
            padding: 12px 14px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--card);
            box-shadow: var(--shadow);
        }

        .ov-filter label {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted);
            margin: 0;
        }

        .ov-filter input[type=date] {
            height: 34px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--bg);
            color: var(--text);
            font-size: 12.5px;
            padding: 0 10px;
            outline: none;
            font-weight: 600;
        }

        .ov-filter input[type=date]:focus {
            border-color: var(--sky);
            box-shadow: 0 0 0 3px rgba(0, 151, 216, .10);
            background: #fff;
        }

        .ov-preset {
            height: 30px;
            padding: 0 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--bg);
            color: var(--text-muted);
            font-size: 11.5px;
            font-weight: 800;
            cursor: pointer;
            transition: .15s;
        }

        .ov-preset:hover,
        .ov-preset.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .ov-filter .sep {
            flex: 1;
        }

        .ov-filter-loading {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: var(--primary);
            font-size: 11px;
            font-weight: 800;
        }

        .ov-filter-loading::before {
            content: '';
            width: 14px;
            height: 14px;
            border: 2px solid rgba(0, 151, 216, .2);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: ov-spin .7s linear infinite;
        }

        .ov-wrap.is-filter-loading .ov-filter input,
        .ov-wrap.is-filter-loading .ov-preset {
            pointer-events: none;
            opacity: .65;
        }

        .ov-kpis {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 16px;
        }

        .ov-kpi {
            padding: 13px 15px;
            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--card);
            box-shadow: var(--shadow);
            border-left: 3px solid var(--primary);
            text-decoration: none !important;
            display: block;
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .ov-kpi:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .ov-kpi--danger {
            border-left-color: #dc2626;
        }

        .ov-kpi--success {
            border-left-color: #16a34a;
        }

        .ov-kpi--amber {
            border-left-color: #d97706;
        }

        .ov-kpi__label {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted);
        }

        .ov-kpi__value {
            margin-top: 6px;
            font-size: 22px;
            font-weight: 850;
            color: var(--navy);
            line-height: 1;
            letter-spacing: -.03em;
        }

        .ov-kpi__value small {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-muted);
        }

        .ov-kpi__sub {
            margin-top: 5px;
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 600;
        }

        .ov-ai-widget {
            position: fixed;
            right: 22px;
            bottom: 22px;
            z-index: 1060;
            display: flex;
            align-items: flex-end;
            gap: 12px;
            pointer-events: none;
        }

        .ov-ai-fab,
        .ov-ai-popover {
            pointer-events: auto;
        }

        .ov-ai-fab {
            width: auto;
            height: 44px;
            padding: 0 16px 0 12px;
            border: 1px solid rgba(255, 255, 255, .50);
            border-radius: 999px;
            color: #fff;
            background: #1498df;
            box-shadow: 0 14px 32px rgba(20, 152, 223, .28), 0 0 0 5px rgba(20, 152, 223, .10);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: .01em;
            white-space: nowrap;
            cursor: pointer;
            transition: transform .22s cubic-bezier(.2, .8, .2, 1), box-shadow .22s ease;
        }

        .ov-ai-fab:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 38px rgba(20, 152, 223, .32), 0 0 0 6px rgba(20, 152, 223, .12);
        }

        .ov-ai-widget.is-open .ov-ai-fab {
            transform: scale(.97);
            box-shadow: 0 10px 24px rgba(20, 152, 223, .24), 0 0 0 4px rgba(20, 152, 223, .10);
        }

        .ov-ai-popover {
            position: absolute;
            right: 0;
            bottom: 58px;
            width: min(430px, calc(100vw - 28px));
            max-height: min(640px, calc(100vh - 116px));
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1px solid rgba(207, 232, 247, .86);
            border-radius: 18px;
            background: rgba(255, 255, 255, .78);
            box-shadow: 0 28px 70px rgba(15, 37, 87, .24);
            -webkit-backdrop-filter: blur(18px) saturate(145%);
            backdrop-filter: blur(18px) saturate(145%);
            opacity: 0;
            transform: translateY(18px) scale(.92);
            transform-origin: right bottom;
            pointer-events: none;
            transition: opacity .22s ease, transform .34s cubic-bezier(.2, .9, .2, 1);
        }

        .ov-ai-widget.is-open .ov-ai-popover {
            opacity: 1;
            transform: translateY(0) scale(1);
            pointer-events: auto;
        }

        .ov-ai-head {
            padding: 13px 14px;
            border-bottom: 1px solid rgba(207, 232, 247, .82);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            background: rgba(248, 251, 255, .56);
        }

        .ov-ai-title {
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--navy);
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .ov-ai-title i {
            color: var(--primary);
        }

        .ov-ai-chat-icon {
            width: 17px;
            height: 17px;
            color: #1498df;
            flex: 0 0 auto;
        }

        .ov-ai-fab .ov-ai-chat-icon {
            width: 18px;
            height: 18px;
            color: #fff;
        }

        .ov-ai-fab__label {
            line-height: 1;
        }

        .ov-ai-chat-icon path {
            fill: none;
            stroke: currentColor;
            stroke-width: 2;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .ov-ai-chat-icon circle {
            fill: currentColor;
        }

        .ov-ai-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--primary);
            font-size: 11px;
            font-weight: 850;
            text-transform: none;
            letter-spacing: 0;
        }

        .ov-ai-status::before {
            content: '';
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 151, 216, .12);
        }

        .ov-ai-close {
            width: 32px;
            height: 32px;
            border: 1px solid rgba(207, 232, 247, .9);
            border-radius: 8px;
            background: rgba(255, 255, 255, .68);
            color: #64748b;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .ov-ai-body {
            min-height: 0;
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 11px;
        }

        .ov-ai-chat {
            height: clamp(230px, 42vh, 360px);
            overflow: auto;
            display: flex;
            flex-direction: column;
            gap: 9px;
            padding: 2px 4px 2px 0;
        }

        .ov-ai-msg {
            max-width: 88%;
            padding: 10px 12px;
            border-radius: 10px;
            border: 1px solid rgba(226, 232, 240, .86);
            background: rgba(255, 255, 255, .82);
            color: var(--text);
            font-size: 12.5px;
            line-height: 1.55;
            font-weight: 600;
            white-space: normal;
        }

        .ov-ai-msg--assistant {
            align-self: flex-start;
            border-color: rgba(191, 219, 254, .86);
            background: rgba(248, 251, 255, .9);
        }

        .ov-ai-msg--user {
            align-self: flex-end;
            border-color: rgba(125, 211, 252, .86);
            background: rgba(224, 242, 254, .92);
            color: #075985;
        }

        .ov-ai-msg--typing {
            padding: 8px 11px;
            min-width: 42px;
        }

        .ov-ai-typing {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            height: 7px;
        }

        .ov-ai-typing span {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #94a3b8;
            animation: ovAiTypingDot 1.25s infinite ease-in-out;
        }

        .ov-ai-typing span:nth-child(1) {
            animation-delay: 0s;
        }

        .ov-ai-typing span:nth-child(2) {
            animation-delay: 0.18s;
        }

        .ov-ai-typing span:nth-child(3) {
            animation-delay: 0.36s;
        }

        @keyframes ovAiTypingDot {
            0%,
            60%,
            100% {
                opacity: .35;
                transform: translateY(0);
            }

            30% {
                opacity: 1;
                transform: translateY(-2px);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .ov-ai-typing span {
                animation: ovAiTypingPulse 1.2s infinite ease-in-out;
            }

            @keyframes ovAiTypingPulse {
                0%,
                100% {
                    opacity: .35;
                }

                50% {
                    opacity: 1;
                }
            }
        }
First one way, then the other. Arms outstretched. Repeat once more. Two. 3 4 They spread out, now the chest. Swing the arms to the side, then slowly up again. Five 6 Chest spread widely 123456 Next model bends. First the last and back again, but then the right and back again. 123456 Now forward bends with bounces. And three times and nails in your hips bend backwards, then up again. 123456 Now sway your arms and twist your body. Right, right, right. The guy likes God. And once again. Right, left, 3456 feet together, nail parts and ways. Armstone. They still have shampoos. Arms up, arms down, 123456 now feet apart, then forward twice for the left foot. Next down to the right and back up again. Once again 23 456. Now rotate the whole body, just rest the arms and make a full circle in the same way the other way. Be sure to scratch those and muscles 4. Five 6 feet back together and now jumps 1234. 23456 arms and legs, 1234 arms and knees moving together. 123456 And we end with deep breaths. Slowly again and slowly out snakes. And one more time. 12345 678. 
        .ov-ai-md {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .ov-ai-md__p {
            margin: 0;
        }

        .ov-ai-md__h {
            margin: 2px 0 0;
            font-size: 13px;
            font-weight: 900;
            color: var(--navy);
        }

        .ov-ai-md__ul {
            margin: 2px 0;
            padding-left: 18px;
        }

        .ov-ai-md__li {
            margin: 2px 0;
        }

        .ov-ai-md__li--sub {
            margin-left: 4px;
            list-style: circle;
        }

        .ov-ai-md strong {
            font-weight: 900;
            color: #0f172a;
        }

        .ov-ai-md em {
            font-style: italic;
        }

        .ov-ai-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid rgba(191, 219, 254, .65);
        }

        .ov-ai-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 10px;
            border-radius: 999px;
            border: 1px solid rgba(20, 152, 223, .35);
            background: rgba(255, 255, 255, .92);
            color: #0369a1;
            font-size: 11px;
            font-weight: 800;
            text-decoration: none;
            transition: .15s;
        }

        .ov-ai-action-btn:hover {
            background: #1498df;
            border-color: #1498df;
            color: #fff;
        }

        .ov-ai-action-btn::after {
            content: '→';
            font-size: 10px;
            opacity: .85;
        }

        .ov-ai-suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .ov-ai-chip {
            border: 1px solid rgba(191, 219, 254, .9);
            background: rgba(255, 255, 255, .74);
            color: var(--navy);
            border-radius: 8px;
            padding: 7px 10px;
            font-size: 11.5px;
            font-weight: 850;
            cursor: pointer;
            transition: .15s;
        }

        .ov-ai-chip:hover {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-1px);
        }

        .ov-ai-form {
            display: flex;
            gap: 8px;
        }

        .ov-ai-form input {
            min-width: 0;
            flex: 1;
            height: 38px;
            border: 1px solid rgba(207, 232, 247, .95);
            border-radius: 9px;
            background: rgba(255, 255, 255, .86);
            color: var(--text);
            font-size: 12.5px;
            font-weight: 650;
            padding: 0 11px;
            outline: none;
        }

        .ov-ai-form input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 151, 216, .10);
        }

        .ov-ai-send {
            width: 40px;
            height: 38px;
            border: 0;
            border-radius: 9px;
            background: var(--primary);
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .ov-ai-send:disabled,
        .ov-ai-chip:disabled {
            cursor: not-allowed;
            opacity: .55;
            transform: none;
        }

        @media (max-width: 575.98px) {
            .ov-ai-widget {
                right: 14px;
                bottom: 14px;
            }

            .ov-ai-fab {
                height: 40px;
                padding: 0 14px 0 11px;
                font-size: 12px;
                gap: 7px;
            }

            .ov-ai-fab .ov-ai-chat-icon {
                width: 16px;
                height: 16px;
            }

            .ov-ai-popover {
                bottom: 52px;
                width: calc(100vw - 28px);
                max-height: calc(100vh - 92px);
                border-radius: 14px;
            }

            .ov-ai-chat {
                height: clamp(210px, 42vh, 320px);
            }
        }

        .ov-row {
            display: grid;
            gap: 14px;
            margin-bottom: 14px;
        }

        .ov-row--2a {
            grid-template-columns: 2fr 1fr;
        }

        .ov-row--2 {
            grid-template-columns: 1fr 1fr;
        }

        .ov-row--2b {
            grid-template-columns: 1fr 1.3fr;
        }

        .ov-row--2c {
            grid-template-columns: 1fr 1.6fr;
        }

        .ov-panel {
            border: 1px solid var(--border);
            border-radius: 16px;
            background: var(--card);
            box-shadow: var(--shadow);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .ov-panel--alert {
            border-color: #fecaca;
        }

        .ov-panel__head {
            padding: 13px 18px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .ov-panel__title {
            font-size: 12px;
            font-weight: 850;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--navy);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ov-panel__title i {
            color: var(--primary);
        }

        .ov-panel--alert .ov-panel__title i {
            color: #dc2626;
        }

        .ov-panel__body {
            padding: 14px 18px;
            flex: 1;
        }

        .ov-panel__body--tight {
            padding: 4px 8px;
        }

        .ov-loading {
            min-height: 72px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 600;
        }

        .ov-loading::before {
            content: '';
            width: 15px;
            height: 15px;
            margin-right: 8px;
            border: 2px solid rgba(0, 151, 216, .2);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: ov-spin .7s linear infinite;
        }

        .ov-chart-loading {
            min-height: 290px;
        }

        .ov-activity-tabs {
            display: inline-flex;
            padding: 3px;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: var(--bg);
        }

        .ov-activity-tab {
            height: 28px;
            padding: 0 12px;
            border: 0;
            border-radius: 999px;
            background: transparent;
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 850;
            cursor: pointer;
        }

        .ov-activity-tab.active {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 6px 14px rgba(0, 151, 216, .18);
        }

        .ov-activity-tab[data-activity-mode="produksi"].active {
            background: #16a34a;
            box-shadow: 0 6px 14px rgba(22, 163, 74, .18);
        }

        .ov-activity-tab[data-activity-mode="all"].active {
            background: #d97706;
            box-shadow: 0 6px 14px rgba(217, 119, 6, .18);
        }

        .ov-activity-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
            margin: -2px 0 8px;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 800;
        }

        .ov-activity-legend__label {
            margin: 0 2px;
        }

        .ov-activity-legend__swatch {
            width: 24px;
            height: 16px;
            border-radius: 4px;
            border: 1px solid rgba(15, 23, 42, .08);
        }

        .ov-heatmap-peak {
            animation: ov-peak-glow 1.4s ease-in-out infinite;
            filter: brightness(1.08) drop-shadow(0 0 3px rgba(255, 255, 255, .65));
            stroke: #fff !important;
            stroke-width: 1.2px !important;
        }

        @keyframes ov-peak-glow {

            0%,
            100% {
                filter: brightness(1.08) drop-shadow(0 0 3px rgba(255, 255, 255, .65));
            }

            50% {
                filter: brightness(1.28) drop-shadow(0 0 9px rgba(255, 255, 255, .95));
            }
        }

        .ov-peak-mark {
            fill: #fff;
            stroke: rgba(15, 23, 42, .5);
            paint-order: stroke;
            font-weight: 800;
            font-family: inherit;
            pointer-events: none;
        }

        .ov-activity-insight {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid var(--border);
        }

        .ov-insight-label {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted);
            margin: 0 0 9px;
        }

        .ov-insight-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(132px, 1fr));
            gap: 10px;
        }

        .ov-chip {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 10px 12px;
        }

        .ov-chip__l {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            color: var(--text-muted);
            margin-bottom: 5px;
        }

        .ov-chip__l i {
            color: #d97706;
        }

        .ov-chip__v {
            font-size: 18px;
            font-weight: 850;
            color: var(--navy);
            letter-spacing: -.02em;
        }

        .ov-chip__s {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 600;
            margin-top: 1px;
        }

        .ov-t3 {
            margin-top: 8px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .ov-t3__item {
            display: flex;
            align-items: center;
            gap: 7px;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 99px;
            padding: 4px 12px 4px 4px;
        }

        .ov-t3__rank {
            width: 19px;
            height: 19px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 800;
            flex-shrink: 0;
            background: #fef3c7;
            color: #92400e;
        }

        .ov-t3__rank--1 {
            background: #d97706;
            color: #fff;
        }

        .ov-t3__name {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--text);
        }

        .ov-t3__val {
            font-size: 12.5px;
            font-weight: 800;
            color: var(--navy);
        }

        .ov-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1080;
            padding: 16px;
        }

        .ov-modal.open {
            display: flex;
        }

        .ov-modal__card {
            background: var(--card);
            border-radius: 16px;
            width: 100%;
            max-width: 440px;
            max-height: 80vh;
            overflow: auto;
            box-shadow: 0 20px 50px rgba(15, 23, 42, .25);
        }

        .ov-modal__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 14px 18px;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            background: var(--card);
        }

        .ov-modal__title {
            font-size: 14px;
            font-weight: 850;
            color: var(--navy);
        }

        .ov-modal__title small {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            margin-top: 2px;
        }

        .ov-modal__close {
            border: 0;
            background: var(--bg);
            color: var(--text-muted);
            width: 28px;
            height: 28px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            flex-shrink: 0;
        }

        .ov-modal__body {
            padding: 14px 18px;
        }

        .ov-empty {
            padding: 24px 12px;
            color: var(--text-muted);
            font-size: 12px;
            text-align: center;
        }

        @keyframes ov-spin {
            to {
                transform: rotate(360deg);
            }
        }

        .ov-cycle {
            display: flex;
            flex-direction: column;
            gap: 13px;
        }

        .ov-cycle__top {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 5px;
        }

        .ov-cycle__top span:last-child {
            color: var(--text-muted);
            font-weight: 600;
        }

        .ov-bar {
            height: 9px;
            border-radius: 99px;
            background: #E2E8F0;
            overflow: hidden;
        }

        .ov-bar__fill {
            height: 100%;
            border-radius: 99px;
            transition: width .5s ease;
        }

        .ov-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .ov-table thead th {
            text-align: left;
            padding: 8px 10px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted);
            font-weight: 800;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        .ov-table tbody td {
            padding: 8px 10px;
            border-bottom: 1px solid var(--border);
            color: var(--text);
            vertical-align: middle;
        }

        .ov-table tbody tr:last-child td {
            border-bottom: none;
        }

        .ov-table tbody tr:hover td {
            background: var(--bg);
        }

        /* tabel error terbaru: paksa satu baris (truncate) biar tinggi kartu konsisten */
        .ov-table--fixed {
            table-layout: fixed;
        }

        .ov-table--fixed td.ov-ellip {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .ov-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 9px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 800;
            background: #eef2ff;
            color: var(--primary);
            border: 1px solid #dbe3ff;
            text-decoration: none;
        }

        .ov-gap {
            font-weight: 800;
            color: #dc2626;
        }

        .ov-area-tag {
            display: inline-flex;
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .ov-empty {
            padding: 26px 12px;
            text-align: center;
            color: var(--text-muted);
            font-size: 12px;
            font-weight: 600;
        }

        .ov-delta {
            font-weight: 800;
        }

        .ov-errtrend {
            margin-top: 16px;
            padding-top: 13px;
            border-top: 1px solid var(--border);
        }

        .ov-errtrend__head {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            margin-bottom: 9px;
        }

        .ov-errtrend__lbl {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--text-muted);
        }

        .ov-errtrend__bars {
            display: flex;
            align-items: flex-end;
            gap: 3px;
            height: 52px;
        }

        .ov-errtrend__bar {
            flex: 1;
            border-radius: 2px;
            min-height: 2px;
        }

        /* 🥚 easter egg: minesweeper di section heatmap */
        .ov-ms {
            padding: 6px 0 2px;
        }

        .ov-ms__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 12px;
        }

        .ov-ms__hint {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #d97706;
        }

        .ov-ms__hud {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ov-ms__counter {
            font-variant-numeric: tabular-nums;
            font-size: 13px;
            font-weight: 800;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 5px 9px;
            color: #dc2626;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .ov-ms__btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--card);
            color: var(--text);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 15px;
        }

        .ov-ms__btn:hover {
            background: var(--bg);
        }

        .ov-ms__btn.on {
            background: #d97706;
            border-color: #d97706;
            color: #fff;
        }

        .ov-ms__grid {
            display: grid;
            gap: 3px;
            justify-content: center;
            margin: 2px auto 12px;
            max-width: 100%;
            overflow-x: auto;
        }

        .ov-ms__cell {
            width: 28px;
            height: 28px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 800;
            line-height: 1;
            cursor: pointer;
            user-select: none;
            border: none;
            padding: 0;
        }

        .ov-ms__cell.cov:hover {
            filter: brightness(1.08);
        }

        .ov-ms__cell.rev {
            background: var(--bg);
            cursor: default;
        }

        .ov-ms__cell.mine {
            background: #dc2626 !important;
            color: #fff;
        }

        .ov-ms__foot {
            font-size: 12px;
            color: var(--text-muted);
            text-align: center;
            font-weight: 600;
        }

        /* 🥚 easter egg: space impact ala Nokia (Konami: ↑↑↓↓←→←→ B A) */
        .ov-si-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .55);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1090;
            padding: 16px;
        }

        .ov-si-modal.open {
            display: flex;
        }

        .ov-si__card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            width: 100%;
            max-width: 560px;
            overflow: hidden;
        }

        .ov-si__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
        }

        .ov-si__hint {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #6b8e23;
        }

        .ov-si__hud {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ov-si__btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--card);
            color: var(--text);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 14px;
        }

        .ov-si__btn:hover {
            background: var(--bg);
        }

        .ov-si__screen {
            margin: 16px;
            padding: 10px;
            background: #2b3a1e;
            border-radius: 8px;
        }

        #ovSiCanvas {
            width: 100%;
            display: block;
            image-rendering: pixelated;
            border-radius: 3px;
            touch-action: none;
        }

        .ov-si__foot {
            padding: 0 16px 14px;
            font-size: 12px;
            color: var(--text-muted);
            text-align: center;
        }

        /* 🥚 easter egg: snake di judul utama */
        .ov-snake-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .55);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1090;
            padding: 16px;
        }

        .ov-snake-modal.open {
            display: flex;
        }

        .ov-snake__card {
            background: var(--card);
            border-radius: 16px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, .3);
            overflow: hidden;
        }

        .ov-snake__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
        }

        .ov-snake__hint {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--primary);
        }

        .ov-snake__hud {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ov-snake__score {
            font-variant-numeric: tabular-nums;
            font-size: 13px;
            font-weight: 800;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 5px 10px;
            color: var(--navy);
        }

        .ov-snake__btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--card);
            color: var(--text);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 15px;
        }

        .ov-snake__btn:hover {
            background: var(--bg);
        }

        .ov-snake__canvas-wrap {
            display: flex;
            justify-content: center;
            padding: 12px 16px 8px;
            background: var(--bg);
        }

        #ovSnakeCanvas {
            border-radius: 8px;
            border: 1px solid var(--border);
            display: block;
            max-width: 100%;
            height: auto;
        }

        .ov-snake__foot {
            padding: 10px 16px 14px;
            font-size: 12px;
            color: var(--text-muted);
            text-align: center;
            font-weight: 600;
        }

        /* 🥚 easter egg: break bricks */
        .ov-bricks-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .55);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1090;
            padding: 16px;
        }

        .ov-bricks-modal.open {
            display: flex;
        }

        .ov-bricks__card {
            background: var(--card);
            border-radius: 16px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, .3);
            overflow: hidden;
        }

        .ov-bricks__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
        }

        .ov-bricks__hint {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #d97706;
        }

        .ov-bricks__hud {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ov-bricks__stat {
            font-variant-numeric: tabular-nums;
            font-size: 13px;
            font-weight: 800;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 5px 10px;
            color: var(--navy);
        }

        .ov-bricks__btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--card);
            color: var(--text);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 15px;
        }

        .ov-bricks__btn:hover {
            background: var(--bg);
        }

        .ov-bricks__canvas-wrap {
            display: flex;
            justify-content: center;
            padding: 12px 16px 8px;
            background: var(--bg);
        }

        #ovBricksCanvas {
            border-radius: 8px;
            border: 1px solid var(--border);
            display: block;
            max-width: 100%;
            height: auto;
            cursor: none;
        }

        .ov-bricks__foot {
            padding: 10px 16px 14px;
            font-size: 12px;
            color: var(--text-muted);
            text-align: center;
            font-weight: 600;
        }

        /* 🥚 easter egg: duck hunt */
        .ov-duck-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .55);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1090;
            padding: 16px;
        }

        .ov-duck-modal.open {
            display: flex;
        }

        .ov-duck__card {
            background: var(--card);
            border-radius: 16px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, .3);
            overflow: hidden;
        }

        .ov-duck__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
        }

        .ov-duck__hint {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #16a34a;
        }

        .ov-duck__hud {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .ov-duck__stat {
            font-variant-numeric: tabular-nums;
            font-size: 13px;
            font-weight: 800;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 5px 10px;
            color: var(--navy);
        }

        .ov-duck__btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--card);
            color: var(--text);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 15px;
        }

        .ov-duck__btn:hover {
            background: var(--bg);
        }

        .ov-duck__canvas-wrap {
            display: flex;
            justify-content: center;
            padding: 12px 16px 8px;
            background: var(--bg);
        }

        #ovDuckCanvas {
            border-radius: 8px;
            border: 1px solid var(--border);
            display: block;
            max-width: 100%;
            height: auto;
            cursor: crosshair;
        }

        .ov-duck__foot {
            padding: 10px 16px 14px;
            font-size: 12px;
            color: var(--text-muted);
            text-align: center;
            font-weight: 600;
        }

        /* 🥚 easter egg: flappy bird */
        .ov-flappy-modal { position: fixed; inset: 0; background: rgba(15, 23, 42, .55); display: none; align-items: center; justify-content: center; z-index: 1090; padding: 16px; }
        .ov-flappy-modal.open { display: flex; }
        .ov-flappy__card { background: var(--card); border-radius: 16px; width: 100%; max-width: 480px; box-shadow: 0 20px 50px rgba(15, 23, 42, .3); overflow: hidden; }
        .ov-flappy__head { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 16px; border-bottom: 1px solid var(--border); }
        .ov-flappy__hint { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .05em; color: #f59e0b; }
        .ov-flappy__hud { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
        .ov-flappy__stat { font-variant-numeric: tabular-nums; font-size: 13px; font-weight: 800; background: var(--bg); border: 1px solid var(--border); border-radius: 8px; padding: 5px 10px; color: var(--navy); }
        .ov-flappy__btn { width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--border); background: var(--card); color: var(--text); display: inline-flex; align-items: center; justify-content: center; cursor: pointer; font-size: 15px; }
        .ov-flappy__btn:hover { background: var(--bg); }
        .ov-flappy__canvas-wrap { display: flex; justify-content: center; padding: 12px 16px 8px; background: var(--bg); }
        #ovFlappyCanvas { border-radius: 8px; border: 1px solid var(--border); display: block; max-width: 100%; height: auto; cursor: pointer; }
        .ov-flappy__foot { padding: 10px 16px 14px; font-size: 12px; color: var(--text-muted); text-align: center; font-weight: 600; }

        @media (max-width: 1100px) {
            .ov-kpis {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .ov-row--2a,
            .ov-row--2,
            .ov-row--2b,
            .ov-row--2c {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 575.98px) {
            .ov-kpis {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .ov-panel__head--activity {
                align-items: flex-start;
                flex-direction: column;
            }

        }
    </style>

    <div class="ov-wrap">
        <div class="ov-head">
            <div>
                <h4 id="ovMainTitle" style="cursor:default;user-select:none;">Dashboard Overview</h4>
                <p>Ringkasan &amp; analisis lintas modul — PPIC · Production · PIS · Error</p>
            </div>
            <span class="ov-live"><span class="dot"></span> LIVE · <span
                    id="ovUpdated">{{ now()->format('H:i:s') }}</span></span>
        </div>

        {{-- ===== FILTER ===== --}}
        <div class="ov-filter">
            <label>Dari</label>
            <input type="date" id="ovFrom" value="{{ $from }}">
            <label>Sampai</label>
            <input type="date" id="ovTo" value="{{ $to }}">
            <button type="button" class="ov-preset" data-preset="today">Hari Ini</button>
            <button type="button" class="ov-preset" data-preset="7">7 Hari</button>
            <button type="button" class="ov-preset" data-preset="30">30 Hari</button>
            <span class="sep"></span>
            <span style="font-size:11px;color:var(--text-muted);font-weight:600;">Auto-refresh tiap 60 dtk · vs periode
                sebelumnya</span>
            <span id="ovFilterLoading" class="ov-filter-loading" hidden>Memuat data periode dipilih</span>
        </div>

        {{-- ===== KPI STRIP ===== --}}
        <div class="ov-kpis">
            <a class="ov-kpi" href="{{ route('dashboard.delivery') }}">
                <div class="ov-kpi__label">Preparation</div>
                <div class="ov-kpi__value"><span id="k-prepComplete">—</span><small>/<span
                            id="k-prepTotal">—</span></small></div>
                <div class="ov-kpi__sub" id="sub-prep">memuat data…</div>
            </a>
            <a class="ov-kpi" href="{{ route('loadingList.index') }}">
                <div class="ov-kpi__label">Kanban Discan</div>
                <div class="ov-kpi__value" id="k-kanban">—</div>
                <div class="ov-kpi__sub" id="sub-kanban">memuat data…</div>
            </a>
            <a class="ov-kpi ov-kpi--success" href="{{ route('loadingList.index') }}">
                <div class="ov-kpi__label">Pulling Selesai</div>
                <div class="ov-kpi__value" id="k-pulling">—</div>
                <div class="ov-kpi__sub" id="sub-pulling">memuat data…</div>
            </a>
            <a class="ov-kpi" href="{{ route('dashboard.prodResult') }}">
                <div class="ov-kpi__label">Produksi</div>
                <div class="ov-kpi__value" id="k-produksi">—</div>
                <div class="ov-kpi__sub" id="sub-produksi">memuat data…</div>
            </a>
            <a class="ov-kpi ov-kpi--danger" href="{{ route('dashboard.productionStock') }}">
                <div class="ov-kpi__label">Stok &lt; Standar</div>
                <div class="ov-kpi__value"><span
                        id="k-understock">—</span><small>/<span id="k-totalpart">—</span></small></div>
                <div class="ov-kpi__sub" id="sub-understock">memuat data…</div>
            </a>
            <a class="ov-kpi ov-kpi--amber" href="{{ route('error.log') }}">
                <div class="ov-kpi__label">Error</div>
                <div class="ov-kpi__value" id="k-errperiod">—</div>
                <div class="ov-kpi__sub" id="sub-error">memuat data…</div>
            </a>
        </div>

        {{-- ===== FLOATING ASK BELLA ===== --}}
        <div class="ov-ai-widget" id="ovAiWidget">
            <div class="ov-ai-popover" id="ovAiPopover" role="dialog" aria-modal="false" aria-labelledby="ovAiTitle" aria-hidden="true">
                <div class="ov-ai-head">
                    <div>
                        <div class="ov-ai-title" id="ovAiTitle">
                            <svg class="ov-ai-chat-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M5.5 5.25h13A3.25 3.25 0 0 1 21.75 8.5v5.25A3.25 3.25 0 0 1 18.5 17h-7.7l-5.05 3v-3.35A3.25 3.25 0 0 1 2.25 13.4V8.5A3.25 3.25 0 0 1 5.5 5.25Z" />
                                <circle cx="8.25" cy="11.15" r="1" />
                                <circle cx="12" cy="11.15" r="1" />
                                <circle cx="15.75" cy="11.15" r="1" />
                            </svg>
                            Ask BELLA
                        </div>
                        <span class="ov-ai-status" id="ovAiStatus">Online</span>
                    </div>
                    <button type="button" class="ov-ai-close" id="ovAiClose" aria-label="Tutup Ask BELLA">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="ov-ai-body">
                    <div class="ov-ai-chat" id="ovAiChat">
                        <div class="ov-ai-msg ov-ai-msg--assistant">
                            Data dashboard sudah siap. Tanya prioritas, risiko, root cause, atau langkah berikutnya.
                        </div>
                    </div>
                    <div class="ov-ai-suggestions">
                        <button type="button" class="ov-ai-chip" data-ai-question="Buat brief operasional singkat untuk periode ini.">Brief operasional</button>
                        <button type="button" class="ov-ai-chip" data-ai-question="Apa prioritas 30 menit ke depan?">Prioritas 30 menit</button>
                        <button type="button" class="ov-ai-chip" data-ai-question="Delivery atau loading list mana yang paling berisiko?">Delivery berisiko</button>
                        <button type="button" class="ov-ai-chip" data-ai-question="Part understock mana yang paling perlu dicek?">Part berisiko</button>
                        <button type="button" class="ov-ai-chip" data-ai-question="Apa kemungkinan root cause dari masalah utama di dashboard ini?">Root cause</button>
                    </div>
                    <form class="ov-ai-form" id="ovAiForm">
                        <input type="text" id="ovAiInput" maxlength="700" placeholder="Tanya BELLA..." autocomplete="off">
                        <button type="submit" class="ov-ai-send" id="ovAiSend" aria-label="Kirim pertanyaan">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </form>
                </div>
            </div>
            <button type="button" class="ov-ai-fab" id="ovAiFab" aria-label="Buka Ask Bella" aria-controls="ovAiPopover" aria-expanded="false">
                <svg class="ov-ai-chat-icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path d="M5.5 5.25h13A3.25 3.25 0 0 1 21.75 8.5v5.25A3.25 3.25 0 0 1 18.5 17h-7.7l-5.05 3v-3.35A3.25 3.25 0 0 1 2.25 13.4V8.5A3.25 3.25 0 0 1 5.5 5.25Z" />
                    <circle cx="8.25" cy="11.15" r="1" />
                    <circle cx="12" cy="11.15" r="1" />
                    <circle cx="15.75" cy="11.15" r="1" />
                </svg>
                <span class="ov-ai-fab__label">ASK BELLA</span>
            </button>
        </div>

        {{-- ===== DELIVERY BERISIKO (#6) ===== --}}
        <div class="ov-row">
            <div class="ov-panel">
                <div class="ov-panel__head">
                    <div class="ov-panel__title"><i class="fas fa-truck-fast"></i> Delivery Berisiko (hari ini &amp; besok)
                    </div>
                </div>
                <div class="ov-panel__body ov-panel__body--tight" id="ovAtRisk"><div class="ov-loading">Memuat data</div></div>
            </div>
        </div>

        {{-- ===== Trend + Stock ===== --}}
        <div class="ov-row ov-row--2a">
            <div class="ov-panel">
                <div class="ov-panel__head">
                    <div class="ov-panel__title" id="ovTrendTitle" style="cursor:default;user-select:none;"><i
                            class="fas fa-chart-area"></i> Aktivitas Harian</div>
                </div>
                <div class="ov-panel__body">
                    <div id="ovTrendChart" class="ov-loading ov-chart-loading">Memuat grafik</div>
                </div>
            </div>
            <div class="ov-panel">
                <div class="ov-panel__head">
                    <div class="ov-panel__title" id="ovStockTitle" style="cursor:default;user-select:none;"><i
                            class="fas fa-boxes"></i> Kesehatan Stok</div>
                </div>
                <div class="ov-panel__body">
                    <div id="ovStockChart" class="ov-loading ov-chart-loading">Memuat grafik</div>
                </div>
            </div>
        </div>

        {{-- ===== Produksi per Line + Preparation per Customer (#2) ===== --}}
        <div class="ov-row ov-row--2">
            <div class="ov-panel">
                <div class="ov-panel__head">
                    <div class="ov-panel__title"><i class="fas fa-industry"></i> Produksi per Line</div>
                </div>
                <div class="ov-panel__body" id="ovProdByLine"><div class="ov-loading">Memuat data</div></div>
            </div>
            <div class="ov-panel">
                <div class="ov-panel__head">
                    <div class="ov-panel__title"><i class="fas fa-users"></i> Preparation per Customer</div>
                </div>
                <div class="ov-panel__body" id="ovPrepByCustomer"><div class="ov-loading">Memuat data</div></div>
            </div>
        </div>

        {{-- ===== Jam tersibuk (#5) ===== --}}
        <div class="ov-row">
            <div class="ov-panel">
                <div class="ov-panel__head ov-panel__head--activity">
                    <div class="ov-panel__title" id="ovHeatmapTitle" style="user-select:none;"><i
                            class="fas fa-clock"></i> Jam Tersibuk — Aktivitas Pulling &amp;
                        Produksi (hari × jam)</div>
                    <div class="ov-activity-tabs" role="group" aria-label="Filter heatmap activity">
                        <button type="button" class="ov-activity-tab" data-activity-mode="produksi">Prod</button>
                        <button type="button" class="ov-activity-tab" data-activity-mode="pulling">Pulling</button>
                        <button type="button" class="ov-activity-tab active" data-activity-mode="all">All</button>
                    </div>
                </div>
                <div class="ov-panel__body">
                    <div id="ovActivityLegend" class="ov-activity-legend"></div>
                    <div id="ovHourlyChart" class="ov-loading ov-chart-loading">Memuat grafik</div>
                    <div id="ovActivityInsight" class="ov-activity-insight" hidden></div>
                    {{-- 🥚 easter egg: minesweeper (muncul kalau judul diklik 5x cepat) --}}
                    <div id="ovMinesweeper" class="ov-ms" hidden>
                        <div class="ov-ms__head">
                            <span class="ov-ms__hint">Easter egg · Minesweeper</span>
                            <div class="ov-ms__hud">
                                <span class="ov-ms__counter"><i class="fas fa-flag"></i> <span
                                        id="ovMsCount">18</span></span>
                                <button type="button" class="ov-ms__btn" id="ovMsFlag" title="Mode tandai ranjau"
                                    aria-label="Mode tandai ranjau"><i class="fas fa-flag"></i></button>
                                <button type="button" class="ov-ms__btn" id="ovMsReset" title="Main lagi"
                                    aria-label="Main lagi"><i class="fas fa-face-smile"></i></button>
                                <button type="button" class="ov-ms__btn" id="ovMsClose" title="Kembali ke heatmap"
                                    aria-label="Kembali ke heatmap"><i class="fas fa-xmark"></i></button>
                            </div>
                        </div>
                        <div class="ov-ms__grid" id="ovMsGrid"></div>
                        <div class="ov-ms__foot" id="ovMsFoot">Klik kiri buka · klik kanan / tombol bendera tandai ranjau
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ===== Preparation per cycle + Top under-stock ===== --}}
        <div class="ov-row ov-row--2b">
            <div class="ov-panel">
                <div class="ov-panel__head">
                    <div class="ov-panel__title"><i class="fas fa-layer-group"></i> Preparation per Cycle</div>
                </div>
                <div class="ov-panel__body" id="ovCycle"><div class="ov-loading">Memuat data</div></div>
            </div>
            <div class="ov-panel">
                <div class="ov-panel__head">
                    <div class="ov-panel__title"><i class="fas fa-arrow-down"></i> Part Paling Kekurangan Stok</div>
                </div>
                <div class="ov-panel__body ov-panel__body--tight" id="ovTopUnder"><div class="ov-loading">Memuat data</div></div>
            </div>
        </div>

        {{-- ===== Error per area + Recent errors ===== --}}
        <div class="ov-row ov-row--2c">
            <div class="ov-panel">
                <div class="ov-panel__head">
                    <div class="ov-panel__title" id="ovErrTitle" style="cursor:default;user-select:none;"><i class="fas fa-chart-bar"></i> Error per Area</div>
                </div>
                <div class="ov-panel__body" id="ovErrByArea"><div class="ov-loading">Memuat data</div></div>
            </div>
            <div class="ov-panel">
                <div class="ov-panel__head">
                    <div class="ov-panel__title"><i class="fas fa-exclamation-triangle"></i> Error Terbaru</div>
                    <a href="{{ route('error.log') }}"
                        style="font-size:11px;font-weight:700;color:var(--primary);text-decoration:none;">Lihat semua →</a>
                </div>
                <div class="ov-panel__body ov-panel__body--tight" id="ovRecentErrors"><div class="ov-loading">Memuat data</div></div>
            </div>
        </div>
    </div>

    {{-- 🥚 easter egg: snake (muncul kalau judul utama diklik 10x cepat) --}}
    <div class="ov-snake-modal" id="ovSnakeModal" role="dialog" aria-modal="true" aria-label="Snake game">
        <div class="ov-snake__card">
            <div class="ov-snake__head">
                <span class="ov-snake__hint">Easter egg · Snake</span>
                <div class="ov-snake__hud">
                    <span class="ov-snake__score">Skor: <span id="ovSnakeScore">0</span></span>
                    <button type="button" class="ov-snake__btn" id="ovSnakeReset" title="Main lagi"
                        aria-label="Main lagi"><i class="fas fa-rotate-right"></i></button>
                    <button type="button" class="ov-snake__btn" id="ovSnakeClose" title="Tutup"
                        aria-label="Tutup"><i class="fas fa-xmark"></i></button>
                </div>
            </div>
            <div class="ov-snake__canvas-wrap">
                <canvas id="ovSnakeCanvas" width="400" height="300"></canvas>
            </div>
            <div class="ov-snake__foot" id="ovSnakeFoot">Panah / WASD · makan titik biru · jangan tabrak dinding &amp; ekor</div>
        </div>
    </div>

    {{-- 🥚 easter egg: space impact (muncul kalau Konami code ditekan: ↑↑↓↓←→←→ B A) --}}
    <div class="ov-si-modal" id="ovSiModal" role="dialog" aria-modal="true" aria-label="Space Impact game">
        <div class="ov-si__card">
            <div class="ov-si__head">
                <span class="ov-si__hint">Easter egg · Space Impact</span>
                <div class="ov-si__hud">
                    <button type="button" class="ov-si__btn" id="ovSiReset" title="Main lagi" aria-label="Main lagi"><i
                            class="fas fa-rotate-right"></i></button>
                    <button type="button" class="ov-si__btn" id="ovSiClose" title="Tutup" aria-label="Tutup"><i
                            class="fas fa-xmark"></i></button>
                </div>
            </div>
            <div class="ov-si__screen"><canvas id="ovSiCanvas" width="140" height="60"></canvas></div>
            <div class="ov-si__foot">↑↓←→ / WASD gerak · tembak otomatis · bos tiap level ke-3</div>
        </div>
    </div>

    {{-- 🥚 easter egg: break bricks (muncul kalau judul Aktivitas Harian diklik 7x cepat) --}}
    <div class="ov-bricks-modal" id="ovBricksModal" role="dialog" aria-modal="true" aria-label="Break bricks game">
        <div class="ov-bricks__card">
            <div class="ov-bricks__head">
                <span class="ov-bricks__hint">Easter egg · Break Bricks</span>
                <div class="ov-bricks__hud">
                    <span class="ov-bricks__stat">Skor: <span id="ovBricksScore">0</span></span>
                    <span class="ov-bricks__stat">Nyawa: <span id="ovBricksLives">3</span></span>
                    <button type="button" class="ov-bricks__btn" id="ovBricksReset" title="Main lagi"
                        aria-label="Main lagi"><i class="fas fa-rotate-right"></i></button>
                    <button type="button" class="ov-bricks__btn" id="ovBricksClose" title="Tutup"
                        aria-label="Tutup"><i class="fas fa-xmark"></i></button>
                </div>
            </div>
            <div class="ov-bricks__canvas-wrap" id="ovBricksWrap">
                <canvas id="ovBricksCanvas" width="400" height="320"></canvas>
            </div>
            <div class="ov-bricks__foot" id="ovBricksFoot">Geser paddle dengan mouse / panah · pecahkan semua bata</div>
        </div>
    </div>

    {{-- 🥚 easter egg: duck hunt (muncul kalau judul Kesehatan Stok diklik 6x cepat) --}}
    <div class="ov-duck-modal" id="ovDuckModal" role="dialog" aria-modal="true" aria-label="Duck hunt game">
        <div class="ov-duck__card">
            <div class="ov-duck__head">
                <span class="ov-duck__hint">Easter egg · Duck Hunt</span>
                <div class="ov-duck__hud">
                    <span class="ov-duck__stat">Skor: <span id="ovDuckScore">0</span></span>
                    <span class="ov-duck__stat">Gelombang: <span id="ovDuckWave">1</span></span>
                    <span class="ov-duck__stat">Peluru: <span id="ovDuckAmmo">3</span></span>
                    <span class="ov-duck__stat">Nyawa: <span id="ovDuckLives">3</span></span>
                    <button type="button" class="ov-duck__btn" id="ovDuckReset" title="Main lagi"
                        aria-label="Main lagi"><i class="fas fa-rotate-right"></i></button>
                    <button type="button" class="ov-duck__btn" id="ovDuckClose" title="Tutup"
                        aria-label="Tutup"><i class="fas fa-xmark"></i></button>
                </div>
            </div>
            <div class="ov-duck__canvas-wrap" id="ovDuckWrap">
                <canvas id="ovDuckCanvas" width="400" height="320"></canvas>
            </div>
            <div class="ov-duck__foot" id="ovDuckFoot">Arahkan crosshair · klik untuk tembak · 3 peluru per gelombang</div>
        </div>
    </div>

    {{-- 🥚 easter egg: flappy bird (muncul kalau judul Error per Area diklik 9x cepat) --}}
    <div class="ov-flappy-modal" id="ovFlappyModal" role="dialog" aria-modal="true" aria-label="Flappy Bird game">
        <div class="ov-flappy__card">
            <div class="ov-flappy__head">
                <span class="ov-flappy__hint">Easter egg · Flappy Bird</span>
                <div class="ov-flappy__hud">
                    <span class="ov-flappy__stat">Skor: <span id="ovFlappyScore">0</span></span>
                    <button type="button" class="ov-flappy__btn" id="ovFlappyReset" title="Main lagi"
                        aria-label="Main lagi"><i class="fas fa-rotate-right"></i></button>
                    <button type="button" class="ov-flappy__btn" id="ovFlappyClose" title="Tutup"
                        aria-label="Tutup"><i class="fas fa-xmark"></i></button>
                </div>
            </div>
            <div class="ov-flappy__canvas-wrap" id="ovFlappyWrap">
                <canvas id="ovFlappyCanvas" width="320" height="400"></canvas>
            </div>
            <div class="ov-flappy__foot" id="ovFlappyFoot">Spasi atau klik canvas untuk lompat · Hindari pipa</div>
        </div>
    </div>

    {{-- ===== Modal drill-down jam tersibuk (#4) ===== --}}
    <div class="ov-modal" id="ovSlotModal" role="dialog" aria-modal="true" aria-labelledby="ovSlotTitle">
        <div class="ov-modal__card">
            <div class="ov-modal__head">
                <div class="ov-modal__title" id="ovSlotTitle">Detail slot</div>
                <button type="button" class="ov-modal__close" id="ovSlotClose" aria-label="Tutup">&times;</button>
            </div>
            <div class="ov-modal__body" id="ovSlotBody"><div class="ov-loading">Memuat data</div></div>
        </div>
    </div>

    <script>
        (function() {
            var DATA_URL = @json(route('dashboard.overview.data'));
            var AI_URL = @json(route('dashboard.overview.ai'));
            var SLOT_URL = @json(route('dashboard.overview.slot'));
            var STOCK_URL = @json(route('dashboard.productionStock'));
            var LL_URL = @json(url('/loading-list'));
            var CHART_URL = @json(asset('assets/js/apexcharts.js'));
            var csrfToken = document.querySelector('meta[name="csrf-token"]') ?
                document.querySelector('meta[name="csrf-token"]').getAttribute('content') : '';
            var initial = @json($d ?? null);
            var trendChart = null,
                stockChart = null,
                hourlyChart = null,
                timer = null,
                chartsLoading = null,
                latestData = null,
                activityMode = 'all',
                aiBusy = false,
                aiHistory = [],
                requestId = 0;

            function esc(s) {
                return String(s == null ? '' : s).replace(/[&<>"]/g, function(c) {
                    return {
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;',
                        '"': '&quot;'
                    } [c];
                });
            }

            function fmt(n) {
                return Number(n || 0).toLocaleString('id-ID');
            }

            var activityPalettes = {
                pulling: {
                    base: '#0097D8',
                    ranges: [
                        { from: 0, to: 0, color: '#f8fafc', label: '0' },
                        { from: 1, to: 10, color: '#dbeafe', label: '1-10' },
                        { from: 11, to: 25, color: '#bfdbfe', label: '11-25' },
                        { from: 26, to: 50, color: '#7dd3fc', label: '26-50' },
                        { from: 51, to: 100, color: '#38bdf8', label: '51-100' },
                        { from: 101, to: 200, color: '#0ea5e9', label: '101-200' },
                        { from: 201, to: 999999, color: '#0369a1', label: '201+' }
                    ]
                },
                produksi: {
                    base: '#16a34a',
                    ranges: [
                        { from: 0, to: 0, color: '#f8fafc', label: '0' },
                        { from: 1, to: 10, color: '#dcfce7', label: '1-10' },
                        { from: 11, to: 25, color: '#bbf7d0', label: '11-25' },
                        { from: 26, to: 50, color: '#86efac', label: '26-50' },
                        { from: 51, to: 100, color: '#4ade80', label: '51-100' },
                        { from: 101, to: 200, color: '#22c55e', label: '101-200' },
                        { from: 201, to: 999999, color: '#15803d', label: '201+' }
                    ]
                },
                all: {
                    base: '#d97706',
                    ranges: [
                        { from: 0, to: 0, color: '#f8fafc', label: '0' },
                        { from: 1, to: 20, color: '#fef3c7', label: '1-20' },
                        { from: 21, to: 50, color: '#fde68a', label: '21-50' },
                        { from: 51, to: 100, color: '#fbbf24', label: '51-100' },
                        { from: 101, to: 150, color: '#f59e0b', label: '101-150' },
                        { from: 151, to: 200, color: '#d97706', label: '151-200' },
                        { from: 201, to: 999999, color: '#92400e', label: '201+' }
                    ]
                }
            };

            function activityPalette(mode) {
                return activityPalettes[mode] || activityPalettes.all;
            }

            function setText(id, v) {
                var el = document.getElementById(id);
                if (el) el.textContent = v;
            }

            function setHtml(id, v) {
                var el = document.getElementById(id);
                if (el) el.innerHTML = v;
            }

            function aiInlineMd(text) {
                var s = esc(text);
                s = s.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
                s = s.replace(/(^|[^*])\*([^*\n]+?)\*([^*]|$)/g, function(_, before, inner, after) {
                    return before + '<em>' + inner + '</em>' + after;
                });
                return s;
            }

            function aiMessageHtml(text) {
                var lines = String(text == null ? '' : text).split('\n');
                var out = [];
                var inUl = false;

                function closeUl() {
                    if (inUl) {
                        out.push('</ul>');
                        inUl = false;
                    }
                }

                lines.forEach(function(line) {
                    var trimmed = line.trim();
                    if (!trimmed) {
                        closeUl();
                        return;
                    }

                    var heading = trimmed.match(/^(#{1,3})\s+(.+)$/);
                    if (heading) {
                        closeUl();
                        out.push('<div class="ov-ai-md__h">' + aiInlineMd(heading[2]) + '</div>');
                        return;
                    }

                    var bullet = trimmed.match(/^([\*\+\-])\s+(.+)$/);
                    if (bullet) {
                        if (!inUl) {
                            out.push('<ul class="ov-ai-md__ul">');
                            inUl = true;
                        }
                        var subClass = bullet[1] === '+' ? ' ov-ai-md__li--sub' : '';
                        out.push('<li class="ov-ai-md__li' + subClass + '">' + aiInlineMd(bullet[2]) + '</li>');
                        return;
                    }

                    var numbered = trimmed.match(/^\d+\.\s+(.+)$/);
                    if (numbered) {
                        if (!inUl) {
                            out.push('<ul class="ov-ai-md__ul">');
                            inUl = true;
                        }
                        out.push('<li class="ov-ai-md__li">' + aiInlineMd(numbered[1]) + '</li>');
                        return;
                    }

                    closeUl();
                    out.push('<p class="ov-ai-md__p">' + aiInlineMd(trimmed) + '</p>');
                });

                closeUl();
                return out.join('');
            }

            function aiAssistantHtml(text, actions) {
                var html = '<div class="ov-ai-md">' + aiMessageHtml(text) + '</div>';
                if (actions && actions.length) {
                    html += '<div class="ov-ai-actions">';
                    actions.forEach(function(action) {
                        if (!action || !action.url) return;
                        html += '<a class="ov-ai-action-btn" href="' + esc(action.url) + '">' + esc(action.label || 'Buka halaman') + '</a>';
                    });
                    html += '</div>';
                }
                return html;
            }

            function aiTextHtml(text) {
                return esc(text).replace(/\n/g, '<br>');
            }

            function setAiStatus(text) {
                var el = document.getElementById('ovAiStatus');
                if (el) el.textContent = text;
            }

            function setAiBusy(active) {
                aiBusy = active;
                var send = document.getElementById('ovAiSend');
                var input = document.getElementById('ovAiInput');
                if (send) send.disabled = active;
                if (input) input.disabled = active;
                document.querySelectorAll('.ov-ai-chip').forEach(function(button) {
                    button.disabled = active;
                });
                setAiStatus(active ? 'Mengetik' : 'Online');
            }

            function setAiOpen(open) {
                var widget = document.getElementById('ovAiWidget');
                var popover = document.getElementById('ovAiPopover');
                var fab = document.getElementById('ovAiFab');
                var input = document.getElementById('ovAiInput');
                if (!widget) return;

                widget.classList.toggle('is-open', open);
                if (popover) popover.setAttribute('aria-hidden', open ? 'false' : 'true');
                if (fab) {
                    fab.setAttribute('aria-expanded', open ? 'true' : 'false');
                    fab.setAttribute('aria-label', open ? 'Tutup Ask Bella' : 'Buka Ask Bella');
                }
                if (open && input) {
                    setTimeout(function() {
                        input.focus();
                    }, 180);
                }
            }

            function appendAiTyping() {
                var chat = document.getElementById('ovAiChat');
                if (!chat) return null;
                var node = document.createElement('div');
                node.className = 'ov-ai-msg ov-ai-msg--assistant ov-ai-msg--typing';
                node.setAttribute('aria-label', 'BELLA sedang mengetik');
                node.innerHTML = '<span class="ov-ai-typing" aria-hidden="true"><span></span><span></span><span></span></span>';
                chat.appendChild(node);
                chat.scrollTop = chat.scrollHeight;

                return node;
            }

            function fillAiMessage(node, text, actions) {
                if (!node) return;
                node.classList.remove('ov-ai-msg--typing');
                node.removeAttribute('aria-label');
                node.innerHTML = aiAssistantHtml(text, actions);
                var chat = document.getElementById('ovAiChat');
                if (chat) chat.scrollTop = chat.scrollHeight;
            }

            function appendAiMessage(role, text) {
                var chat = document.getElementById('ovAiChat');
                if (!chat) return null;
                var node = document.createElement('div');
                node.className = 'ov-ai-msg ov-ai-msg--' + (role === 'user' ? 'user' : 'assistant');
                node.innerHTML = aiTextHtml(text);
                chat.appendChild(node);
                chat.scrollTop = chat.scrollHeight;

                return node;
            }

            function compactAiHistory() {
                return aiHistory.slice(-4).map(function(item) {
                    return {
                        role: item.role,
                        content: limit(item.content, 800)
                    };
                });
            }

            function askAi(question) {
                question = String(question || '').trim();
                if (!question || aiBusy) return;

                var input = document.getElementById('ovAiInput');
                if (input) input.value = '';

                var priorHistory = compactAiHistory();
                var from = document.getElementById('ovFrom').value;
                var to = document.getElementById('ovTo').value;
                appendAiMessage('user', question);
                aiHistory.push({
                    role: 'user',
                    content: limit(question, 800)
                });

                setAiBusy(true);
                var pending = appendAiTyping();

                fetch(AI_URL, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            question: question,
                            history: priorHistory,
                            from: from,
                            to: to
                        })
                    })
                    .then(function(r) {
                        return r.json().then(function(payload) {
                            return {
                                ok: r.ok,
                                payload: payload
                            };
                        });
                    })
                    .then(function(res) {
                        if (!res.ok) throw new Error(res.payload.message || 'Gagal meminta analisis Groq.');
                        var answer = res.payload.answer || 'Groq tidak mengembalikan jawaban.';
                        fillAiMessage(pending, answer, res.payload.actions || []);
                        aiHistory.push({
                            role: 'assistant',
                            content: limit(answer, 800)
                        });
                    })
                    .catch(function(error) {
                        var message = error && error.message ? error.message : 'Gagal meminta analisis Groq.';
                        fillAiMessage(pending, message);
                    })
                    .finally(function() {
                        setAiBusy(false);
                    });
            }

            function setFilterLoading(active) {
                var wrap = document.querySelector('.ov-wrap');
                var label = document.getElementById('ovFilterLoading');
                if (wrap) {
                    wrap.classList.toggle('is-filter-loading', active);
                    wrap.setAttribute('aria-busy', active ? 'true' : 'false');
                }
                if (label) label.hidden = !active;
            }

            function resetChart(id) {
                var el = document.getElementById(id);
                if (!el) return;
                el.classList.add('ov-loading', 'ov-chart-loading');
                el.textContent = 'Memuat grafik';
            }

            function setChartError(id) {
                var el = document.getElementById(id);
                if (!el) return;
                el.classList.remove('ov-loading', 'ov-chart-loading');
                el.innerHTML = '<div class="ov-empty">Grafik belum berhasil dimuat.</div>';
            }

            function showFilterLoadingState() {
                if (trendChart) trendChart.destroy();
                if (stockChart) stockChart.destroy();
                if (hourlyChart) hourlyChart.destroy();
                trendChart = stockChart = hourlyChart = null;

                setText('k-prepComplete', '—');
                setText('k-prepTotal', '—');
                setText('k-kanban', '—');
                setText('k-pulling', '—');
                setText('k-produksi', '—');
                setText('k-understock', '—');
                setText('k-totalpart', '—');
                setText('k-errperiod', '—');
                ['sub-prep', 'sub-kanban', 'sub-pulling', 'sub-produksi', 'sub-understock', 'sub-error'].forEach(function(id) {
                    setText(id, 'memuat data…');
                });

                resetChart('ovTrendChart');
                resetChart('ovStockChart');
                resetChart('ovHourlyChart');
                setHtml('ovActivityLegend', '');
                ['ovAtRisk', 'ovProdByLine', 'ovPrepByCustomer', 'ovCycle', 'ovTopUnder', 'ovErrByArea',
                    'ovRecentErrors'
                ].forEach(function(id) {
                    setHtml(id, '<div class="ov-loading">Memuat data</div>');
                });
            }

            function limit(s, n) {
                s = String(s == null ? '' : s);
                return s.length > n ? s.slice(0, n) + '…' : s;
            }

            // ▲▼ delta. goodUp=true: naik itu bagus (hijau); false: naik itu jelek (merah, mis. error)
            function deltaHtml(delta, goodUp) {
                if (delta === null || delta === undefined) return '';
                var good = delta === 0 ? null : (goodUp ? delta > 0 : delta < 0);
                var color = good === null ? 'var(--text-muted)' : (good ? '#16a34a' : '#dc2626');
                var arrow = delta > 0 ? '▲' : (delta < 0 ? '▼' : '•');
                return '<span class="ov-delta" style="color:' + color + ';">' + arrow + ' ' + Math.abs(delta) +
                    '%</span> vs lalu';
            }

            function renderBars(rows, getLabel, getTop, getPct, getColor) {
                return '<div class="ov-cycle">' + rows.map(function(r) {
                    var pct = getPct(r);
                    return '<div><div class="ov-cycle__top"><span>' + esc(getLabel(r)) + '</span><span>' +
                        getTop(r) + '</span></div>' +
                        '<div class="ov-bar"><div class="ov-bar__fill" style="width:' + pct + '%;background:' +
                        getColor(r) + ';"></div></div></div>';
                }).join('') + '</div>';
            }

            function renderCycle(rows) {
                if (!rows || !rows.length)
            return '<div class="ov-empty">Tidak ada loading list pada periode ini.</div>';
                return renderBars(rows,
                    function(c) {
                        return 'Cycle ' + c.cycle;
                    },
                    function(c) {
                        return c.complete + '/' + c.total + ' · ' + c.pct + '%';
                    },
                    function(c) {
                        return c.pct;
                    },
                    function(c) {
                        return c.pct >= 100 ? '#16a34a' : (c.pct > 0 ? '#d97706' : '#dc2626');
                    });
            }

            function renderPrepByCustomer(rows) {
                if (!rows || !rows.length)
            return '<div class="ov-empty">Tidak ada loading list pada periode ini.</div>';
                return renderBars(rows,
                    function(c) {
                        return c.customer;
                    },
                    function(c) {
                        return c.complete + '/' + c.total + ' · ' + c.pct + '%';
                    },
                    function(c) {
                        return c.pct;
                    },
                    function(c) {
                        return c.pct >= 100 ? '#16a34a' : (c.pct > 0 ? '#d97706' : '#dc2626');
                    });
            }

            function renderProdByLine(rows) {
                if (!rows || !rows.length) return '<div class="ov-empty">Belum ada produksi pada periode ini.</div>';
                var max = Math.max.apply(null, rows.map(function(r) {
                    return r.c;
                })) || 1;
                return renderBars(rows,
                    function(r) {
                        return r.line || '-';
                    },
                    function(r) {
                        return r.c;
                    },
                    function(r) {
                        return Math.round((r.c / max) * 100);
                    },
                    function() {
                        return '#0097D8';
                    });
            }

            function errDeltaArrow(d) {
                if (d === null || d === undefined || d === 0) return '';
                // naik = jelek untuk error (merah), turun = bagus (hijau)
                var color = d > 0 ? '#dc2626' : '#16a34a';
                return ' <span style="font-weight:800;color:' + color + ';">' + (d > 0 ? '▲' : '▼') +
                    Math.abs(d) + '%</span>';
            }

            function renderErrTrend() {
                var trend = (latestData && latestData.trend) || [];
                var vals = trend.map(function(t) {
                    return parseInt(t.err) || 0;
                });
                var max = vals.length ? Math.max.apply(null, vals) : 0;
                if (!max) return '';
                var n = vals.length;
                var bars = vals.map(function(v, i) {
                    var h = Math.max(2, Math.round(v / max * 50));
                    var shade = i >= n - Math.ceil(n / 4) ? '#dc2626' : (i >= n / 2 ? '#ef4444' : '#fca5a5');
                    return '<div class="ov-errtrend__bar" style="height:' + h + 'px;background:' + shade +
                        ';" title="' + esc(trend[i].label) + ': ' + fmt(v) + ' error"></div>';
                }).join('');
                var dErr = latestData && latestData.delta ? latestData.delta.err : null;
                var head = '<span class="ov-errtrend__lbl">Tren error harian</span>' +
                    (dErr ? '<span style="font-size:11px;font-weight:800;color:' + (dErr > 0 ? '#dc2626' :
                        '#16a34a') + ';">' + (dErr > 0 ? '▲' : '▼') + ' ' + Math.abs(dErr) + '% vs lalu</span>' : '');
                return '<div class="ov-errtrend"><div class="ov-errtrend__head">' + head +
                    '</div><div class="ov-errtrend__bars">' + bars + '</div></div>';
            }

            function renderErrByArea(rows) {
                if (!rows || !rows.length) return '<div class="ov-empty">Tidak ada error pada periode ini. 👍</div>';
                var max = Math.max.apply(null, rows.map(function(r) {
                    return r.c;
                })) || 1;
                var bars = renderBars(rows,
                    function(r) {
                        return r.area || '-';
                    },
                    function(r) {
                        return fmt(r.c) + errDeltaArrow(r.delta);
                    },
                    function(r) {
                        return Math.round((r.c / max) * 100);
                    },
                    function() {
                        return '#dc2626';
                    });
                return bars + renderErrTrend();
            }

            function activityChartOptions(mode) {
                var palette = activityPalette(mode);
                return {
                    colors: [palette.base],
                    plotOptions: {
                        heatmap: {
                            radius: 3,
                            enableShades: true,
                            shadeIntensity: 0.55,
                            colorScale: {
                                ranges: palette.ranges.map(function(r) {
                                    return {
                                        from: r.from,
                                        to: r.to,
                                        color: r.color
                                    };
                                })
                            }
                        }
                    }
                };
            }

            function renderActivityLegend(mode) {
                var swatches = activityPalette(mode).ranges.map(function(r) {
                    return '<span class="ov-activity-legend__swatch" title="' + esc(r.label) +
                        ' aktivitas" style="background:' + r.color + ';"></span>';
                }).join('');
                return '<span class="ov-activity-legend__label">sepi</span>' + swatches +
                    '<span class="ov-activity-legend__label">ramai</span>';
            }

            function activityDayName(name) {
                return String(name || '').replace(/\s+(Pulling|Produksi)$/, '');
            }

            function activitySeries(rows, mode) {
                rows = rows || [];
                if (mode === 'pulling' || mode === 'produksi') {
                    var suffix = mode === 'pulling' ? ' Pulling' : ' Produksi';
                    return rows.filter(function(row) {
                        return String(row.name || '').slice(-suffix.length) === suffix;
                    }).map(function(row) {
                        return {
                            name: activityDayName(row.name),
                            data: row.data || []
                        };
                    });
                }

                var order = [],
                    byDay = {};
                rows.forEach(function(row) {
                    var day = activityDayName(row.name);
                    if (!byDay[day]) {
                        order.push(day);
                        byDay[day] = {
                            name: day,
                            data: (row.data || []).map(function(point) {
                                return {
                                    x: point.x,
                                    y: 0
                                };
                            })
                        };
                    }
                    (row.data || []).forEach(function(point, i) {
                        if (!byDay[day].data[i]) byDay[day].data[i] = {
                            x: point.x,
                            y: 0
                        };
                        byDay[day].data[i].y += parseInt(point.y) || 0;
                    });
                });

                return order.map(function(day) {
                    return byDay[day];
                });
            }

            function peakActivity(series) {
                var best = {
                        seriesIndex: -1,
                        dataPointIndex: -1,
                        value: 0
                    };
                (series || []).forEach(function(row, seriesIndex) {
                    (row.data || []).forEach(function(point, dataPointIndex) {
                        var value = parseInt(point.y) || 0;
                        if (value > best.value) {
                            best = {
                                seriesIndex: seriesIndex,
                                dataPointIndex: dataPointIndex,
                                value: value
                            };
                        }
                    });
                });

                return best.value > 0 ? best : null;
            }

            function highlightPeakActivity() {
                var chartEl = document.getElementById('ovHourlyChart');
                if (!chartEl || !latestData) return;
                var rects = chartEl.querySelectorAll('.apexcharts-heatmap-rect, .apexcharts-heatmap-series rect');
                rects.forEach(function(rect) {
                    rect.classList.remove('ov-heatmap-peak');
                });
                var oldMark = chartEl.querySelector('.ov-peak-mark');
                if (oldMark) oldMark.remove();

                var peak = peakActivity(activitySeries(latestData.heatmap, activityMode));
                if (!peak) return;

                var peakRect = chartEl.querySelector('.apexcharts-heatmap-rect[index="' + peak.seriesIndex +
                    '"][j="' + peak.dataPointIndex + '"]');
                if (!peakRect) {
                    rects.forEach(function(rect) {
                        if ((parseInt(rect.getAttribute('val')) || 0) === peak.value) peakRect = rect;
                    });
                }
                if (peakRect) {
                    peakRect.classList.add('ov-heatmap-peak');

                    var bb = peakRect.getBBox();
                    var label = fmt(peak.value);
                    // ukuran font nyesuain lebar cell DAN jumlah karakter biar angka ribuan tetap muat
                    var fontSize = Math.max(8, Math.min(bb.height * 0.55, bb.width * 0.85 / (label.length * 0.6)));
                    var mark = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                    mark.setAttribute('class', 'ov-peak-mark');
                    mark.setAttribute('x', bb.x + bb.width / 2);
                    mark.setAttribute('y', bb.y + bb.height / 2);
                    mark.setAttribute('text-anchor', 'middle');
                    mark.setAttribute('dominant-baseline', 'central');
                    mark.setAttribute('font-size', fontSize);
                    mark.style.strokeWidth = (fontSize * 0.22) + 'px';
                    mark.textContent = label;
                    peakRect.parentNode.appendChild(mark);
                }
            }

            function chip(icon, label, value, sub) {
                return '<div class="ov-chip"><div class="ov-chip__l"><i class="fas ' + icon + '"></i> ' +
                    esc(label) + '</div><div class="ov-chip__v">' + esc(value) + '</div>' +
                    '<div class="ov-chip__s">' + esc(sub) + '</div></div>';
            }

            // #1 insight strip + #2 top-3 slot — dihitung dari data yang sudah ke-load
            function renderActivityInsight() {
                var el = document.getElementById('ovActivityInsight');
                if (!el) return;
                if (!latestData) {
                    el.hidden = true;
                    el.innerHTML = '';
                    return;
                }

                var series = activitySeries(latestData.heatmap, activityMode);
                var flat = [],
                    hourTotals = {},
                    nonzero = [];
                series.forEach(function(row) {
                    (row.data || []).forEach(function(p) {
                        var v = parseInt(p.y) || 0,
                            hour = parseInt(p.x) || 0;
                        hourTotals[hour] = (hourTotals[hour] || 0) + v;
                        if (v > 0) {
                            flat.push({ day: row.name, hour: hour, value: v });
                            nonzero.push(v);
                        }
                    });
                });

                if (!flat.length) {
                    el.hidden = false;
                    el.innerHTML = '<div class="ov-empty">Belum ada aktivitas pada periode ini.</div>';
                    return;
                }

                flat.sort(function(a, b) {
                    return b.value - a.value;
                });
                var peak = flat[0],
                    top3 = flat.slice(0, 3);

                // band 3 jam tersibuk (sliding window)
                var band = { start: 0, sum: -1 };
                for (var h = 0; h <= 21; h++) {
                    var s = (hourTotals[h] || 0) + (hourTotals[h + 1] || 0) + (hourTotals[h + 2] || 0);
                    if (s > band.sum) band = { start: h, sum: s };
                }

                // jam paling lengang di antara jam yang ada aktivitasnya
                var activeHours = Object.keys(hourTotals).map(Number).filter(function(hh) {
                    return hourTotals[hh] > 0;
                });
                var quiet = activeHours.reduce(function(min, hh) {
                    return hourTotals[hh] < hourTotals[min] ? hh : min;
                }, activeHours[0]);

                var avg = nonzero.reduce(function(a, b) {
                    return a + b;
                }, 0) / nonzero.length;
                var ratio = avg > 0 ? (peak.value / avg) : 0;

                var pad = function(x) {
                    return (x < 10 ? '0' : '') + x;
                };
                var hhmm = function(x) {
                    return pad(x) + ':00';
                };

                var chips =
                    chip('fa-fire', 'Tersibuk', fmt(peak.value), peak.day + ' ' + hhmm(peak.hour)) +
                    chip('fa-clock', 'Jam puncak', pad(band.start) + '–' + pad(band.start + 2), 'band tersibuk') +
                    chip('fa-moon', 'Paling lengang', hhmm(quiet), 'cocok maintenance') +
                    chip('fa-bolt', 'Puncak vs rata²', ratio.toFixed(1) + '×', ratio >= 2 ? 'beban spiky' :
                        'beban merata');

                var rows = top3.map(function(t, i) {
                    return '<span class="ov-t3__item">' +
                        '<span class="ov-t3__rank ov-t3__rank--' + (i + 1) + '">' + (i + 1) + '</span>' +
                        '<span class="ov-t3__name">' + esc(t.day) + ' ' + hhmm(t.hour) + '</span>' +
                        '<span class="ov-t3__val">' + fmt(t.value) + '</span></span>';
                }).join('');

                el.hidden = false;
                el.innerHTML =
                    '<p class="ov-insight-label">Ringkasan &amp; tindak lanjut</p>' +
                    '<div class="ov-insight-grid">' + chips + '</div>' +
                    '<p class="ov-insight-label" style="margin:12px 0 0;">3 slot tersibuk · klik cell untuk detail</p>' +
                    '<div class="ov-t3">' + rows + '</div>';
            }

            // #4 drill-down: klik cell heatmap → detail per tanggal pada slot tsb
            var slotReqId = 0;

            function openSlotDetail(dayName, hour) {
                var dowByDay = { 'Min': 1, 'Sen': 2, 'Sel': 3, 'Rab': 4, 'Kam': 5, 'Jum': 6, 'Sab': 7 };
                var day = String(dayName || '').replace(/\s+(Pulling|Produksi)$/, '');
                var dow = dowByDay[day];
                if (!dow) return;
                var pad = function(x) {
                    return (x < 10 ? '0' : '') + x;
                };
                var from = document.getElementById('ovFrom').value;
                var to = document.getElementById('ovTo').value;

                document.getElementById('ovSlotTitle').innerHTML = esc(day) + ' ' + pad(hour) + ':00' +
                    '<small>' + esc(from) + ' s/d ' + esc(to) + ' · mode ' + esc(activityMode) + '</small>';
                document.getElementById('ovSlotBody').innerHTML = '<div class="ov-loading">Memuat data</div>';
                document.getElementById('ovSlotModal').classList.add('open');

                var rid = ++slotReqId;
                var qs = '?from=' + encodeURIComponent(from) + '&to=' + encodeURIComponent(to) +
                    '&dow=' + dow + '&hour=' + hour + '&mode=' + encodeURIComponent(activityMode);
                fetch(SLOT_URL + qs, {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    })
                    .then(function(r) {
                        if (!r.ok) throw new Error('slot');
                        return r.json();
                    })
                    .then(function(d) {
                        if (rid !== slotReqId) return;
                        document.getElementById('ovSlotBody').innerHTML = renderSlotDetail(d);
                    })
                    .catch(function() {
                        if (rid !== slotReqId) return;
                        document.getElementById('ovSlotBody').innerHTML =
                            '<div class="ov-empty">Gagal memuat detail slot.</div>';
                    });
            }

            function renderSlotDetail(d) {
                var rows = (d && d.detail) || [];
                if (!rows.length) return '<div class="ov-empty">Tidak ada aktivitas pada slot ini.</div>';
                var showP = activityMode !== 'produksi',
                    showS = activityMode !== 'pulling';
                var totP = 0,
                    totS = 0;
                var body = rows.map(function(r) {
                    var p = parseInt(r.pulling) || 0,
                        s = parseInt(r.produksi) || 0;
                    totP += p;
                    totS += s;
                    return '<tr><td>' + esc(r.date) + '</td>' +
                        (showP ? '<td class="text-right">' + fmt(p) + '</td>' : '') +
                        (showS ? '<td class="text-right">' + fmt(s) + '</td>' : '') +
                        '<td class="text-right" style="font-weight:800;color:var(--navy);">' + fmt(p + s) +
                        '</td></tr>';
                }).join('');
                var head = '<tr><th>Tanggal</th>' +
                    (showP ? '<th class="text-right">Pulling</th>' : '') +
                    (showS ? '<th class="text-right">Produksi</th>' : '') +
                    '<th class="text-right">Total</th></tr>';
                var foot = '<tr><td style="font-weight:800;">Total</td>' +
                    (showP ? '<td class="text-right" style="font-weight:800;">' + fmt(totP) + '</td>' : '') +
                    (showS ? '<td class="text-right" style="font-weight:800;">' + fmt(totS) + '</td>' : '') +
                    '<td class="text-right" style="font-weight:800;color:var(--navy);">' + fmt(totP + totS) +
                    '</td></tr>';
                return '<table class="ov-table"><thead>' + head + '</thead><tbody>' + body + foot +
                    '</tbody></table>';
            }

            function schedulePeakHighlight() {
                renderActivityInsight();
                setTimeout(highlightPeakActivity, 80);
            }

            function setActivityMode(mode) {
                activityMode = mode || 'all';
                document.querySelectorAll('.ov-activity-tab').forEach(function(button) {
                    var active = button.getAttribute('data-activity-mode') === activityMode;
                    button.classList.toggle('active', active);
                    button.setAttribute('aria-pressed', active ? 'true' : 'false');
                });
                if (hourlyChart && latestData) {
                    hourlyChart.updateOptions(activityChartOptions(activityMode), false, false);
                    hourlyChart.updateSeries(activitySeries(latestData.heatmap, activityMode));
                    schedulePeakHighlight();
                }
                setHtml('ovActivityLegend', renderActivityLegend(activityMode));
            }

            function renderTopUnder(rows) {
                if (!rows || !rows.length) return '<div class="ov-empty">Semua part di atas/standar stok. 👍</div>';
                var body = rows.map(function(p) {
                    var url = STOCK_URL + '?back_number=' + encodeURIComponent(p.back_number || '');
                    return '<tr><td><a class="ov-badge" href="' + url + '">' + esc(p.back_number || '-') +
                        '</a></td>' +
                        '<td>' + esc(p.line || '-') + '</td>' +
                        '<td class="text-right">' + (parseInt(p.cur) || 0) + '</td>' +
                        '<td class="text-right">' + (parseInt(p.std) || 0) + '</td>' +
                        '<td class="text-right ov-gap">-' + (parseInt(p.gap) || 0) + '</td></tr>';
                }).join('');
                return '<table class="ov-table"><thead><tr><th>Back No</th><th>Line</th><th class="text-right">Stok</th><th class="text-right">Std</th><th class="text-right">Kurang</th></tr></thead><tbody>' +
                    body + '</tbody></table>';
            }

            function renderRecentErrors(rows) {
                if (!rows || !rows.length) return '<div class="ov-empty">Belum ada error tercatat. 👍</div>';
                var body = rows.map(function(e) {
                    return '<tr><td><span class="ov-area-tag">' + esc(e.area || '-') + '</span></td>' +
                        '<td class="ov-ellip" title="' + esc(e.message || '-') + '">' + esc(limit(e.message ||
                            '-', 60)) + '</td>' +
                        '<td class="ov-ellip" title="' + esc(e.expected || '-') + '">' + esc(e.expected || '-') +
                        '</td>' +
                        '<td class="ov-ellip" title="' + esc(e.scanned || '-') + '">' + esc(e.scanned || '-') +
                        '</td>' +
                        '<td style="white-space:nowrap;color:var(--text-muted);">' + esc(e.date || '-') +
                        '</td></tr>';
                }).join('');
                return '<table class="ov-table ov-table--fixed"><thead><tr>' +
                    '<th style="width:70px;">Area</th><th>Pesan</th>' +
                    '<th style="width:22%;">Expected</th><th style="width:22%;">Scanned</th>' +
                    '<th style="width:92px;">Waktu</th></tr></thead><tbody>' +
                    body + '</tbody></table>';
            }

            function urgencyColor(u) {
                return u === 'overdue' ? '#dc2626' : (u === 'urgent' ? '#ea580c' : (u === 'soon' ? '#d97706' : (u ===
                    'ok' ? '#16a34a' : '#94a3b8')));
            }

            function urgencyLabel(u) {
                return u === 'overdue' ? 'LEWAT' : (u === 'urgent' ? 'URGENT' : (u === 'soon' ? 'SEGERA' : (u === 'ok' ?
                    'AMAN' : '—')));
            }

            function remLabel(min) {
                if (min === null || min === undefined) return '';
                var neg = min < 0,
                    m = Math.abs(min),
                    h = Math.floor(m / 60),
                    mm = m % 60;
                var s = (h > 0 ? h + 'j ' : '') + mm + 'm';
                return neg ? ('lewat ' + s) : (s + ' lagi');
            }

            function renderAtRisk(rows) {
                if (!rows || !rows.length)
                return '<div class="ov-empty">Tidak ada delivery berisiko hari ini/besok. 👍</div>';
                var body = rows.map(function(r) {
                    var col = r.pct >= 100 ? '#16a34a' : (r.pct > 0 ? '#d97706' : '#dc2626');
                    var uc = urgencyColor(r.urgency);
                    var dlCell = r.deadline ?
                        '<span style="font-weight:800;">' + esc(r.deadline) + '</span>' :
                        '<span style="color:var(--text-muted);font-size:11px;">belum diset</span>';
                    var truckCell = r.truck ?
                        '<span style="font-weight:700;"><i class="fas fa-truck" style="color:var(--text-muted);margin-right:4px;"></i>' +
                        esc(r.truck) + '</span>' :
                        '<span style="color:var(--text-muted);">—</span>';
                    return '<tr>' +
                        '<td><a class="ov-badge" href="' + LL_URL + '/' + encodeURIComponent(r.id) + '">' + esc(
                            r.number || '-') + '</a></td>' +
                        '<td>' + esc(r.customer || '-') + '</td>' +
                        '<td>' + esc(r.cycle || '-') + '</td>' +
                        '<td style="white-space:nowrap;">' + dlCell + '</td>' +
                        '<td style="white-space:nowrap;">' + truckCell + '</td>' +
                        '<td style="white-space:nowrap;"><span class="ov-area-tag" style="background:' + uc +
                        '1f;color:' + uc + ';border-color:' + uc + '66;">' + urgencyLabel(r.urgency) +
                        '</span>' +
                        (r.remaining !== null && r.remaining !== undefined ?
                            ' <span style="font-size:11px;font-weight:700;color:' + uc + ';">' + remLabel(r
                                .remaining) + '</span>' : '') + '</td>' +
                        '<td style="min-width:130px;"><div class="ov-cycle__top" style="margin-bottom:3px;"><span>' +
                        r.actual + '/' + r.total + '</span><span>' + r.pct + '%</span></div>' +
                        '<div class="ov-bar"><div class="ov-bar__fill" style="width:' + r.pct +
                        '%;background:' + col + ';"></div></div></td></tr>';
                }).join('');
                return '<table class="ov-table"><thead><tr><th>Loading List</th><th>Customer</th><th>Cycle</th><th>Deadline Preparation</th><th>ETA Truck</th><th>Status</th><th>Progress</th></tr></thead><tbody>' +
                    body + '</tbody></table>';
            }

            function applyData(d) {
                latestData = d;
                var k = d.kpi || {},
                    dl = d.delta || {};
                setText('k-prepComplete', k.prepComplete || 0);
                setText('k-prepTotal', k.prepTotal || 0);
                setText('sub-prep', (k.prepPct || 0) + '% selesai · periode');
                setText('k-kanban', fmt(k.kanbanScanned));
                setHtml('sub-kanban', deltaHtml(dl.kanban, true));
                setText('k-pulling', fmt(k.pullingDone));
                setHtml('sub-pulling', deltaHtml(dl.pulling, true));
                setText('k-produksi', fmt(k.produksi));
                setHtml('sub-produksi', deltaHtml(dl.produksi, true));
                setText('k-understock', fmt(k.underStock));
                setText('k-totalpart', fmt(k.totalPart));
                setText('sub-understock', 'snapshot saat ini');
                setText('k-errperiod', fmt(k.errPeriod));
                setHtml('sub-error', deltaHtml(dl.err, false) + ' · rate ' + (d.errRate || 0) + '%');

                var trend = d.trend || [];
                var series = [{
                        name: 'Pulling',
                        data: trend.map(function(t) {
                            return t.pulling;
                        })
                    },
                    {
                        name: 'Produksi',
                        data: trend.map(function(t) {
                            return t.produksi;
                        })
                    },
                    {
                        name: 'PIS',
                        data: trend.map(function(t) {
                            return t.pis;
                        })
                    }
                ];
                if (trendChart) {
                    trendChart.updateOptions({
                        xaxis: {
                            categories: trend.map(function(t) {
                                return t.label;
                            })
                        }
                    }, false, false);
                    trendChart.updateSeries(series);
                }
                var s = d.stock || {};
                if (stockChart) stockChart.updateSeries([s.under || 0, s.low || 0, s.in || 0]);
                if (hourlyChart) {
                    hourlyChart.updateSeries(activitySeries(d.heatmap, activityMode));
                    schedulePeakHighlight();
                }

                setHtml('ovAtRisk', renderAtRisk(d.atRisk));
                setHtml('ovProdByLine', renderProdByLine(d.prodByLine));
                setHtml('ovPrepByCustomer', renderPrepByCustomer(d.prepByCustomer));
                setHtml('ovCycle', renderCycle(d.prepByCycle));
                setHtml('ovTopUnder', renderTopUnder(d.topUnder));
                setHtml('ovErrByArea', renderErrByArea(d.errByArea));
                setHtml('ovRecentErrors', renderRecentErrors(d.recentErrors));
                setText('ovUpdated', new Date().toLocaleTimeString('id-ID'));
            }

            function loadCharts() {
                if (window.ApexCharts) return Promise.resolve();
                if (chartsLoading) return chartsLoading;

                chartsLoading = new Promise(function(resolve, reject) {
                    var script = document.createElement('script');
                    script.src = CHART_URL;
                    script.async = true;
                    script.onload = resolve;
                    script.onerror = reject;
                    document.head.appendChild(script);
                });

                return chartsLoading;
            }

            function ensureCharts(d) {
                latestData = d || latestData;
                return loadCharts().then(function() {
                    if (!trendChart && latestData) buildCharts(latestData);
                }).catch(function() {
                    ['ovTrendChart', 'ovStockChart', 'ovHourlyChart'].forEach(function(id) {
                        setHtml(id, '<div class="ov-empty">Grafik gagal dimuat.</div>');
                    });
                });
            }

            function buildCharts(d) {
                if (!window.ApexCharts || trendChart) return;
                ['ovTrendChart', 'ovStockChart', 'ovHourlyChart'].forEach(function(id) {
                    var el = document.getElementById(id);
                    if (el) {
                        el.classList.remove('ov-loading', 'ov-chart-loading');
                        el.textContent = '';
                    }
                });
                var trend = d.trend || [];
                trendChart = new ApexCharts(document.getElementById('ovTrendChart'), {
                    chart: {
                        type: 'area',
                        height: 290,
                        toolbar: {
                            show: false
                        },
                        fontFamily: 'Plus Jakarta Sans, sans-serif'
                    },
                    series: [{
                            name: 'Pulling',
                            data: trend.map(function(t) {
                                return t.pulling;
                            })
                        },
                        {
                            name: 'Produksi',
                            data: trend.map(function(t) {
                                return t.produksi;
                            })
                        },
                        {
                            name: 'PIS',
                            data: trend.map(function(t) {
                                return t.pis;
                            })
                        }
                    ],
                    colors: ['#0097D8', '#16a34a', '#d97706'],
                    xaxis: {
                        categories: trend.map(function(t) {
                            return t.label;
                        }),
                        labels: {
                            style: {
                                fontSize: '11px'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                fontSize: '11px'
                            }
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2.5
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            opacityFrom: 0.35,
                            opacityTo: 0.02
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                        fontSize: '12px'
                    },
                    grid: {
                        borderColor: 'rgba(0,0,0,.06)'
                    },
                    tooltip: {
                        shared: true
                    }
                });
                trendChart.render();

                var st = d.stock || {};
                stockChart = new ApexCharts(document.getElementById('ovStockChart'), {
                    chart: {
                        type: 'donut',
                        height: 290,
                        fontFamily: 'Plus Jakarta Sans, sans-serif'
                    },
                    series: [st.under || 0, st.low || 0, st.in || 0],
                    labels: ['Di Bawah Standar', 'Pas Standar', 'Aman'],
                    colors: ['#dc2626', '#d97706', '#16a34a'],
                    legend: {
                        position: 'bottom',
                        fontSize: '12px'
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val, o) {
                            return o.w.globals.series[o.seriesIndex];
                        }
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '62%',
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: 'Total Part',
                                        fontSize: '12px'
                                    }
                                }
                            }
                        }
                    },
                    stroke: {
                        width: 2
                    }
                });
                stockChart.render();

                var heatmapOptions = activityChartOptions(activityMode);
                hourlyChart = new ApexCharts(document.getElementById('ovHourlyChart'), {
                    chart: {
                        type: 'heatmap',
                        height: 390,
                        toolbar: {
                            show: false
                        },
                        fontFamily: 'Plus Jakarta Sans, sans-serif',
                        events: {
                            dataPointSelection: function(e, ctx, cfg) {
                                var srs = cfg.w.config.series[cfg.seriesIndex];
                                if (!srs) return;
                                var pt = (srs.data || [])[cfg.dataPointIndex] || {};
                                openSlotDetail(srs.name, parseInt(pt.x) || 0);
                            }
                        }
                    },
                    states: {
                        active: {
                            filter: {
                                type: 'none'
                            }
                        }
                    },
                    series: activitySeries(d.heatmap, activityMode),
                    colors: heatmapOptions.colors,
                    dataLabels: {
                        enabled: false
                    },
                    plotOptions: heatmapOptions.plotOptions,
                    xaxis: {
                        type: 'category',
                        labels: {
                            style: {
                                fontSize: '10px'
                            },
                            rotate: 0
                        },
                        title: {
                            text: 'Jam',
                            style: {
                                fontSize: '10px',
                                color: '#94a3b8'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                fontSize: '10px',
                                fontWeight: 700
                            }
                        }
                    },
                    legend: {
                        show: false
                    },
                    grid: {
                        padding: {
                            right: 8
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function(v) {
                                return v + ' aktivitas';
                            }
                        }
                    }
                });
                hourlyChart.render();
                schedulePeakHighlight();
            }

            function refresh(showLoading) {
                var currentRequestId = ++requestId;
                var from = document.getElementById('ovFrom').value;
                var to = document.getElementById('ovTo').value;
                // tukar otomatis kalau urutan kebalik (ISO date aman dibanding string)
                if (from && to && from > to) {
                    var tmp = from;
                    from = to;
                    to = tmp;
                    document.getElementById('ovFrom').value = from;
                    document.getElementById('ovTo').value = to;
                }
                if (showLoading) {
                    setFilterLoading(true);
                    showFilterLoadingState();
                }
                fetch(DATA_URL + '?from=' + encodeURIComponent(from) + '&to=' + encodeURIComponent(to), {
                        headers: {
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    })
                    .then(function(r) {
                        if (!r.ok) throw new Error('Dashboard data request failed');
                        return r.json();
                    })
                    .then(function(d) {
                        if (currentRequestId !== requestId) return;
                        setFilterLoading(false);
                        if (d.from) document.getElementById('ovFrom').value = d.from;
                        if (d.to) document.getElementById('ovTo').value = d.to;
                        applyData(d);
                        ensureCharts(d);
                        try {
                            history.replaceState(null, '', '?from=' + d.from + '&to=' + d.to);
                        } catch (e) {}
                    })
                    .catch(function() {
                        if (currentRequestId !== requestId) return;
                        setFilterLoading(false);
                        var message = '<div class="ov-empty">Data belum berhasil dimuat. Silakan coba lagi.</div>';
                        ['ovAtRisk', 'ovProdByLine', 'ovPrepByCustomer', 'ovCycle', 'ovTopUnder',
                            'ovErrByArea', 'ovRecentErrors'
                        ].forEach(function(id) {
                            setHtml(id, message);
                        });
                        ['ovTrendChart', 'ovStockChart', 'ovHourlyChart'].forEach(function(id) {
                            setChartError(id);
                        });
                        setText('sub-prep', 'gagal memuat data');
                        setText('sub-kanban', 'gagal memuat data');
                        setText('sub-pulling', 'gagal memuat data');
                        setText('sub-produksi', 'gagal memuat data');
                        setText('sub-understock', 'gagal memuat data');
                        setText('sub-error', 'gagal memuat data');
                    });
            }

            function setPreset(p) {
                var to = new Date(),
                    from = new Date();
                if (p === '7') from.setDate(to.getDate() - 6);
                else if (p === '30') from.setDate(to.getDate() - 29);
                // pakai tanggal lokal, bukan UTC — toISOString() bisa mundur 1 hari di WIB
                var iso = function(dt) {
                    return new Date(dt.getTime() - dt.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
                };
                document.getElementById('ovFrom').value = iso(from);
                document.getElementById('ovTo').value = iso(to);
                document.querySelectorAll('.ov-preset').forEach(function(b) {
                    b.classList.toggle('active', b.getAttribute('data-preset') === p);
                });
                refresh(true);
            }

            function clearActivePreset() {
                document.querySelectorAll('.ov-preset').forEach(function(b) {
                    b.classList.remove('active');
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                var aiFab = document.getElementById('ovAiFab');
                if (aiFab) {
                    aiFab.addEventListener('click', function() {
                        var widget = document.getElementById('ovAiWidget');
                        setAiOpen(!(widget && widget.classList.contains('is-open')));
                    });
                }
                var aiClose = document.getElementById('ovAiClose');
                if (aiClose) {
                    aiClose.addEventListener('click', function() {
                        setAiOpen(false);
                    });
                }
                document.querySelectorAll('.ov-activity-tab').forEach(function(button) {
                    button.addEventListener('click', function() {
                        setActivityMode(button.getAttribute('data-activity-mode'));
                    });
                });
                document.querySelectorAll('.ov-ai-chip').forEach(function(button) {
                    button.addEventListener('click', function() {
                        askAi(button.getAttribute('data-ai-question'));
                    });
                });
                var aiForm = document.getElementById('ovAiForm');
                if (aiForm) {
                    aiForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        askAi(document.getElementById('ovAiInput').value);
                    });
                }
                setActivityMode(activityMode);
                loadCharts();
                if (initial && initial.kpi) {
                    applyData(initial);
                    ensureCharts(initial);
                } else {
                    refresh(true);
                }
                document.querySelectorAll('.ov-preset').forEach(function(b) {
                    b.addEventListener('click', function() {
                        setPreset(b.getAttribute('data-preset'));
                    });
                });
                document.getElementById('ovFrom').addEventListener('change', function() {
                    clearActivePreset();
                    refresh(true);
                });
                document.getElementById('ovTo').addEventListener('change', function() {
                    clearActivePreset();
                    refresh(true);
                });
                // modal drill-down: tutup via tombol, klik backdrop, atau Esc
                var slotModal = document.getElementById('ovSlotModal');
                document.getElementById('ovSlotClose').addEventListener('click', function() {
                    slotModal.classList.remove('open');
                });
                slotModal.addEventListener('click', function(e) {
                    if (e.target === slotModal) slotModal.classList.remove('open');
                });
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') setAiOpen(false);
                    if (e.key === 'Escape') slotModal.classList.remove('open');
                });
                // hemat query: refresh hanya saat tab aktif
                timer = setInterval(function() {
                    if (!document.hidden) refresh();
                }, 60000);
                // langsung segarkan data begitu user balik ke tab (jangan nunggu tick 60s)
                document.addEventListener('visibilitychange', function() {
                    if (!document.hidden) refresh();
                });
            });
        })();
    </script>

    <script>
        // 🥚 Easter egg: klik judul heatmap 5x cepat → Minesweeper. Chart ApexCharts asli tetap utuh di belakang.
        (function() {
            var title = document.getElementById('ovHeatmapTitle');
            var ms = document.getElementById('ovMinesweeper');
            if (!title || !ms) return;

            var ROWS = 8,
                COLS = 12,
                MINES = 18;
            var heat = ['#fef3c7', '#fde68a', '#fbbf24', '#f59e0b', '#d97706'];
            var numColor = {
                1: '#185FA5',
                2: '#3B6D11',
                3: '#A32D2D',
                4: '#26215C',
                5: '#854F0B',
                6: '#0F6E56',
                7: '#444441',
                8: '#5F5E5A'
            };
            var gridEl = document.getElementById('ovMsGrid');
            var countEl = document.getElementById('ovMsCount');
            var footEl = document.getElementById('ovMsFoot');
            var flagBtn = document.getElementById('ovMsFlag');
            var resetBtn = document.getElementById('ovMsReset');
            var closeBtn = document.getElementById('ovMsClose');
            var hideIds = ['ovHourlyChart', 'ovActivityLegend', 'ovActivityInsight'];
            var board, started, over, won, flags, flagMode, shade, built = false;

            gridEl.style.gridTemplateColumns = 'repeat(' + COLS + ', 28px)';

            function init() {
                board = [];
                shade = [];
                for (var r = 0; r < ROWS; r++) {
                    board.push([]);
                    shade.push([]);
                    for (var c = 0; c < COLS; c++) {
                        board[r].push({ mine: false, rev: false, flag: false, n: 0 });
                        shade[r].push(heat[Math.floor(Math.random() * heat.length)]);
                    }
                }
                started = false;
                over = false;
                won = false;
                flags = 0;
                flagMode = false;
                flagBtn.classList.remove('on');
                footEl.textContent = 'Klik kiri buka · klik kanan / tombol bendera tandai ranjau';
                render();
                updateHud();
            }

            function neigh(r, c) {
                var out = [];
                for (var dr = -1; dr <= 1; dr++)
                    for (var dc = -1; dc <= 1; dc++) {
                        if (dr === 0 && dc === 0) continue;
                        var nr = r + dr,
                            nc = c + dc;
                        if (nr >= 0 && nr < ROWS && nc >= 0 && nc < COLS) out.push([nr, nc]);
                    }
                return out;
            }

            function placeMines(sr, sc) {
                var safe = {};
                safe[sr + ',' + sc] = 1;
                neigh(sr, sc).forEach(function(p) {
                    safe[p[0] + ',' + p[1]] = 1;
                });
                var placed = 0;
                while (placed < MINES) {
                    var r = Math.floor(Math.random() * ROWS),
                        c = Math.floor(Math.random() * COLS);
                    if (board[r][c].mine || safe[r + ',' + c]) continue;
                    board[r][c].mine = true;
                    placed++;
                }
                for (var r2 = 0; r2 < ROWS; r2++)
                    for (var c2 = 0; c2 < COLS; c2++) {
                        if (board[r2][c2].mine) continue;
                        board[r2][c2].n = neigh(r2, c2).filter(function(p) {
                            return board[p[0]][p[1]].mine;
                        }).length;
                    }
            }

            function reveal(r, c) {
                var cell = board[r][c];
                if (over || won || cell.flag || cell.rev) return;
                if (!started) {
                    placeMines(r, c);
                    started = true;
                }
                cell.rev = true;
                if (cell.mine) {
                    over = true;
                    revealMines();
                    footEl.textContent = 'Boom! Kena ranjau jam sibuk — klik wajah buat main lagi.';
                    render();
                    updateHud();
                    return;
                }
                if (cell.n === 0) {
                    var stack = [[r, c]];
                    while (stack.length) {
                        var cur = stack.pop();
                        neigh(cur[0], cur[1]).forEach(function(p) {
                            var nb = board[p[0]][p[1]];
                            if (!nb.rev && !nb.flag && !nb.mine) {
                                nb.rev = true;
                                if (nb.n === 0) stack.push(p);
                            }
                        });
                    }
                }
                checkWin();
                render();
                updateHud();
            }

            function toggleFlag(r, c) {
                var cell = board[r][c];
                if (over || won || cell.rev) return;
                cell.flag = !cell.flag;
                flags += cell.flag ? 1 : -1;
                render();
                updateHud();
            }

            function revealMines() {
                for (var r = 0; r < ROWS; r++)
                    for (var c = 0; c < COLS; c++)
                        if (board[r][c].mine) board[r][c].rev = true;
            }

            function checkWin() {
                var ok = true;
                for (var r = 0; r < ROWS; r++)
                    for (var c = 0; c < COLS; c++) {
                        var x = board[r][c];
                        if (!x.mine && !x.rev) ok = false;
                    }
                if (ok) {
                    won = true;
                    footEl.textContent = 'Mantap! Semua jam aman dipetakan. Heatmap bersih dari ranjau.';
                }
            }

            function updateHud() {
                countEl.textContent = Math.max(0, MINES - flags);
                var ic = resetBtn.querySelector('i');
                ic.className = won ? 'fas fa-trophy' : (over ? 'fas fa-face-frown' : 'fas fa-face-smile');
            }

            function render() {
                gridEl.innerHTML = '';
                for (var r = 0; r < ROWS; r++)
                    for (var c = 0; c < COLS; c++) {
                        (function(r, c) {
                            var cell = board[r][c];
                            var el = document.createElement('button');
                            el.type = 'button';
                            el.className = 'ov-ms__cell';
                            if (cell.rev) {
                                if (cell.mine) {
                                    el.className += ' rev mine';
                                    el.innerHTML = '<i class="fas fa-bomb"></i>';
                                } else {
                                    el.className += ' rev';
                                    if (cell.n > 0) {
                                        el.textContent = cell.n;
                                        el.style.color = numColor[cell.n];
                                    }
                                }
                            } else {
                                el.className += ' cov';
                                el.style.background = shade[r][c];
                                if (cell.flag) el.innerHTML = '<i class="fas fa-flag" style="color:#dc2626"></i>';
                            }
                            el.addEventListener('click', function() {
                                flagMode ? toggleFlag(r, c) : reveal(r, c);
                            });
                            el.addEventListener('contextmenu', function(e) {
                                e.preventDefault();
                                toggleFlag(r, c);
                            });
                            gridEl.appendChild(el);
                        })(r, c);
                    }
            }

            function activate() {
                hideIds.forEach(function(id) {
                    var e = document.getElementById(id);
                    if (e) e.style.display = 'none';
                });
                ms.hidden = false;
                if (!built) {
                    built = true;
                    init();
                }
            }

            function deactivate() {
                ms.hidden = true;
                hideIds.forEach(function(id) {
                    var e = document.getElementById(id);
                    if (e) e.style.display = '';
                });
            }

            flagBtn.addEventListener('click', function() {
                flagMode = !flagMode;
                flagBtn.classList.toggle('on', flagMode);
            });
            resetBtn.addEventListener('click', init);
            closeBtn.addEventListener('click', deactivate);

            // trigger: 5 klik cepat di judul (reset hitungan kalau jeda > 700ms)
            var clicks = 0,
                t = null;
            title.addEventListener('click', function() {
                clicks++;
                clearTimeout(t);
                t = setTimeout(function() {
                    clicks = 0;
                }, 700);
                if (clicks >= 5) {
                    clicks = 0;
                    activate();
                }
            });
        })();
    </script>

    <script>
        // 🥚 Easter egg: klik judul utama 10x cepat → Snake
        (function() {
            var title = document.getElementById('ovMainTitle');
            var modal = document.getElementById('ovSnakeModal');
            var canvas = document.getElementById('ovSnakeCanvas');
            if (!title || !modal || !canvas) return;

            var ctx = canvas.getContext('2d');
            var scoreEl = document.getElementById('ovSnakeScore');
            var footEl = document.getElementById('ovSnakeFoot');
            var resetBtn = document.getElementById('ovSnakeReset');
            var closeBtn = document.getElementById('ovSnakeClose');

            var COLS = 20,
                ROWS = 15,
                CELL = 20;
            var snake, dir, nextDir, food, score, tick, loop, alive, active;

            function randCell() {
                return {
                    x: Math.floor(Math.random() * COLS),
                    y: Math.floor(Math.random() * ROWS)
                };
            }

            function spawnFood() {
                var f, ok;
                do {
                    f = randCell();
                    ok = !snake.some(function(s) {
                        return s.x === f.x && s.y === f.y;
                    });
                } while (!ok);
                food = f;
            }

            function initGame() {
                snake = [{
                        x: 10,
                        y: 7
                    },
                    {
                        x: 9,
                        y: 7
                    },
                    {
                        x: 8,
                        y: 7
                    }
                ];
                dir = {
                    x: 1,
                    y: 0
                };
                nextDir = {
                    x: 1,
                    y: 0
                };
                score = 0;
                alive = true;
                scoreEl.textContent = '0';
                footEl.textContent = 'Panah / WASD · makan titik biru · jangan tabrak dinding & ekor';
                spawnFood();
                draw();
            }

            function drawCell(x, y, color) {
                ctx.fillStyle = color;
                ctx.fillRect(x * CELL + 1, y * CELL + 1, CELL - 2, CELL - 2);
            }

            function draw() {
                ctx.fillStyle = '#f1f5f9';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                drawCell(food.x, food.y, '#0097D8');
                snake.forEach(function(seg, i) {
                    drawCell(seg.x, seg.y, i === 0 ? '#0f172a' : '#16a34a');
                });
            }

            function step() {
                if (!alive || !active) return;
                dir = nextDir;
                var head = {
                    x: snake[0].x + dir.x,
                    y: snake[0].y + dir.y
                };
                if (head.x < 0 || head.x >= COLS || head.y < 0 || head.y >= ROWS) {
                    gameOver();
                    return;
                }
                for (var i = 0; i < snake.length; i++) {
                    if (snake[i].x === head.x && snake[i].y === head.y) {
                        gameOver();
                        return;
                    }
                }
                snake.unshift(head);
                if (head.x === food.x && head.y === food.y) {
                    score++;
                    scoreEl.textContent = String(score);
                    spawnFood();
                } else {
                    snake.pop();
                }
                draw();
            }

            function gameOver() {
                alive = false;
                footEl.textContent = 'Game over! Skor ' + score + ' — klik ↻ atau tekan Spasi buat main lagi.';
            }

            function setDir(nx, ny) {
                if (!alive) return;
                if (nx === -dir.x && ny === -dir.y) return;
                nextDir = {
                    x: nx,
                    y: ny
                };
            }

            function onKey(e) {
                if (!active) return;
                var k = e.key;
                if (k === 'ArrowUp' || k === 'w' || k === 'W') setDir(0, -1);
                else if (k === 'ArrowDown' || k === 's' || k === 'S') setDir(0, 1);
                else if (k === 'ArrowLeft' || k === 'a' || k === 'A') setDir(-1, 0);
                else if (k === 'ArrowRight' || k === 'd' || k === 'D') setDir(1, 0);
                else if (k === ' ' && !alive) {
                    e.preventDefault();
                    initGame();
                } else if (k === 'Escape') close();
                if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', ' '].indexOf(k) >= 0) e.preventDefault();
            }

            function open() {
                modal.classList.add('open');
                active = true;
                initGame();
                clearInterval(loop);
                loop = setInterval(step, 120);
                document.addEventListener('keydown', onKey);
            }

            function close() {
                modal.classList.remove('open');
                active = false;
                clearInterval(loop);
                document.removeEventListener('keydown', onKey);
            }

            resetBtn.addEventListener('click', initGame);
            closeBtn.addEventListener('click', close);
            modal.addEventListener('click', function(e) {
                if (e.target === modal) close();
            });

            var clicks = 0,
                t = null;
            title.addEventListener('click', function() {
                clicks++;
                clearTimeout(t);
                t = setTimeout(function() {
                    clicks = 0;
                }, 700);
                if (clicks >= 10) {
                    clicks = 0;
                    open();
                }
            });
        })();
    </script>

    <script>
        // 🥚 Easter egg: klik judul Aktivitas Harian 7x cepat → Break Bricks
        (function() {
            var title = document.getElementById('ovTrendTitle');
            var modal = document.getElementById('ovBricksModal');
            var wrap = document.getElementById('ovBricksWrap');
            var canvas = document.getElementById('ovBricksCanvas');
            if (!title || !modal || !canvas) return;

            var ctx = canvas.getContext('2d');
            var scoreEl = document.getElementById('ovBricksScore');
            var livesEl = document.getElementById('ovBricksLives');
            var footEl = document.getElementById('ovBricksFoot');
            var resetBtn = document.getElementById('ovBricksReset');
            var closeBtn = document.getElementById('ovBricksClose');

            var W = 400,
                H = 320;
            var paddle, ball, bricks, score, lives, active, playing, raf, keys;

            function buildBricks() {
                var colors = ['#0097D8', '#16a34a', '#d97706', '#dc2626', '#6366f1'];
                var cols = 8,
                    rows = 5,
                    bw = 44,
                    bh = 14,
                    gap = 4,
                    offX = 12,
                    offY = 36;
                bricks = [];
                for (var r = 0; r < rows; r++) {
                    for (var c = 0; c < cols; c++) {
                        bricks.push({
                            x: offX + c * (bw + gap),
                            y: offY + r * (bh + gap),
                            w: bw,
                            h: bh,
                            alive: true,
                            color: colors[r % colors.length]
                        });
                    }
                }
            }

            function resetBall(stuck) {
                ball = {
                    x: paddle.x + paddle.w / 2,
                    y: paddle.y - 10,
                    r: 6,
                    vx: stuck ? 0 : (Math.random() > .5 ? 3.2 : -3.2),
                    vy: stuck ? 0 : -3.2,
                    stuck: !!stuck
                };
            }

            function initGame() {
                paddle = {
                    x: W / 2 - 40,
                    y: H - 24,
                    w: 80,
                    h: 10
                };
                score = 0;
                lives = 3;
                playing = true;
                keys = {
                    left: false,
                    right: false
                };
                scoreEl.textContent = '0';
                livesEl.textContent = '3';
                footEl.textContent = 'Geser paddle dengan mouse / panah · pecahkan semua bata';
                buildBricks();
                resetBall(true);
                draw();
            }

            function drawRoundRect(x, y, w, h, r, color) {
                ctx.fillStyle = color;
                ctx.beginPath();
                ctx.moveTo(x + r, y);
                ctx.lineTo(x + w - r, y);
                ctx.quadraticCurveTo(x + w, y, x + w, y + r);
                ctx.lineTo(x + w, y + h - r);
                ctx.quadraticCurveTo(x + w, y + h, x + w - r, y + h);
                ctx.lineTo(x + r, y + h);
                ctx.quadraticCurveTo(x, y + h, x, y + h - r);
                ctx.lineTo(x, y + r);
                ctx.quadraticCurveTo(x, y, x + r, y);
                ctx.closePath();
                ctx.fill();
            }

            function draw() {
                ctx.fillStyle = '#0f172a';
                ctx.fillRect(0, 0, W, H);
                bricks.forEach(function(b) {
                    if (!b.alive) return;
                    drawRoundRect(b.x, b.y, b.w, b.h, 3, b.color);
                });
                drawRoundRect(paddle.x, paddle.y, paddle.w, paddle.h, 4, '#94a3b8');
                ctx.beginPath();
                ctx.arc(ball.x, ball.y, ball.r, 0, Math.PI * 2);
                ctx.fillStyle = '#fff';
                ctx.fill();
            }

            function circleRectHit(cx, cy, r, rx, ry, rw, rh) {
                var nx = Math.max(rx, Math.min(cx, rx + rw));
                var ny = Math.max(ry, Math.min(cy, ry + rh));
                var dx = cx - nx,
                    dy = cy - ny;
                return dx * dx + dy * dy <= r * r;
            }

            function bounceBrick(b) {
                b.alive = false;
                score += 10;
                scoreEl.textContent = String(score);
                var overlapL = ball.x + ball.r - b.x;
                var overlapR = b.x + b.w - (ball.x - ball.r);
                var overlapT = ball.y + ball.r - b.y;
                var overlapB = b.y + b.h - (ball.y - ball.r);
                var min = Math.min(overlapL, overlapR, overlapT, overlapB);
                if (min === overlapL || min === overlapR) ball.vx *= -1;
                else ball.vy *= -1;
            }

            function loseLife() {
                lives--;
                livesEl.textContent = String(lives);
                if (lives <= 0) {
                    playing = false;
                    footEl.textContent = 'Game over! Skor ' + score + ' — Spasi / ↻ buat main lagi.';
                    return;
                }
                resetBall(true);
                footEl.textContent = 'Bola jatuh! Nyawa tersisa ' + lives + ' — klik canvas buat luncurkan.';
            }

            function checkWin() {
                if (bricks.every(function(b) {
                        return !b.alive;
                    })) {
                    playing = false;
                    footEl.textContent = 'Menang! Skor ' + score + ' — semua bata hancur. Spasi / ↻ buat lagi.';
                }
            }

            function launchBall() {
                if (!ball.stuck || !playing) return;
                ball.stuck = false;
                ball.vx = Math.random() > .5 ? 3.2 : -3.2;
                ball.vy = -3.2;
            }

            function tick() {
                if (!active) return;
                if (playing) {
                    if (keys.left) paddle.x -= 6;
                    if (keys.right) paddle.x += 6;
                    paddle.x = Math.max(0, Math.min(W - paddle.w, paddle.x));

                    if (ball.stuck) {
                        ball.x = paddle.x + paddle.w / 2;
                        ball.y = paddle.y - ball.r - 1;
                    } else {
                        ball.x += ball.vx;
                        ball.y += ball.vy;
                        if (ball.x - ball.r <= 0) {
                            ball.x = ball.r;
                            ball.vx *= -1;
                        } else if (ball.x + ball.r >= W) {
                            ball.x = W - ball.r;
                            ball.vx *= -1;
                        }
                        if (ball.y - ball.r <= 0) {
                            ball.y = ball.r;
                            ball.vy *= -1;
                        }
                        if (ball.y + ball.r >= H) {
                            loseLife();
                        }
                        if (ball.y + ball.r >= paddle.y && ball.y - ball.r <= paddle.y + paddle.h &&
                            ball.x >= paddle.x && ball.x <= paddle.x + paddle.w && ball.vy > 0) {
                            ball.y = paddle.y - ball.r;
                            ball.vy = -Math.abs(ball.vy);
                            var hit = (ball.x - paddle.x) / paddle.w - 0.5;
                            ball.vx = hit * 7;
                        }
                        for (var i = 0; i < bricks.length; i++) {
                            var b = bricks[i];
                            if (b.alive && circleRectHit(ball.x, ball.y, ball.r, b.x, b.y, b.w, b.h)) {
                                bounceBrick(b);
                                checkWin();
                                break;
                            }
                        }
                    }
                }
                draw();
                raf = requestAnimationFrame(tick);
            }

            function onKey(e) {
                if (!active) return;
                var k = e.key;
                if (k === 'ArrowLeft') keys.left = e.type === 'keydown';
                else if (k === 'ArrowRight') keys.right = e.type === 'keydown';
                else if (k === ' ' && !playing) {
                    e.preventDefault();
                    initGame();
                } else if (k === 'Escape') close();
                if (k === 'ArrowLeft' || k === 'ArrowRight') e.preventDefault();
            }

            function onMouse(e) {
                if (!active || !playing) return;
                var rect = canvas.getBoundingClientRect();
                var scale = W / rect.width;
                var mx = (e.clientX - rect.left) * scale;
                paddle.x = Math.max(0, Math.min(W - paddle.w, mx - paddle.w / 2));
                if (ball.stuck) {
                    ball.x = paddle.x + paddle.w / 2;
                }
            }

            function onCanvasClick() {
                launchBall();
            }

            function open() {
                modal.classList.add('open');
                active = true;
                initGame();
                cancelAnimationFrame(raf);
                raf = requestAnimationFrame(tick);
                document.addEventListener('keydown', onKey);
                document.addEventListener('keyup', onKey);
                wrap.addEventListener('mousemove', onMouse);
                canvas.addEventListener('click', onCanvasClick);
            }

            function close() {
                modal.classList.remove('open');
                active = false;
                playing = false;
                cancelAnimationFrame(raf);
                document.removeEventListener('keydown', onKey);
                document.removeEventListener('keyup', onKey);
                wrap.removeEventListener('mousemove', onMouse);
                canvas.removeEventListener('click', onCanvasClick);
            }

            resetBtn.addEventListener('click', initGame);
            closeBtn.addEventListener('click', close);
            modal.addEventListener('click', function(e) {
                if (e.target === modal) close();
            });

            var clicks = 0,
                t = null;
            title.addEventListener('click', function() {
                clicks++;
                clearTimeout(t);
                t = setTimeout(function() {
                    clicks = 0;
                }, 700);
                if (clicks >= 7) {
                    clicks = 0;
                    open();
                }
            });
        })();
    </script>

    <script>
        // 🥚 Easter egg: klik judul Kesehatan Stok 6x cepat → Duck Hunt
        (function() {
            var title = document.getElementById('ovStockTitle');
            var modal = document.getElementById('ovDuckModal');
            var wrap = document.getElementById('ovDuckWrap');
            var canvas = document.getElementById('ovDuckCanvas');
            if (!title || !modal || !canvas) return;

            var ctx = canvas.getContext('2d');
            var scoreEl = document.getElementById('ovDuckScore');
            var waveEl = document.getElementById('ovDuckWave');
            var ammoEl = document.getElementById('ovDuckAmmo');
            var livesEl = document.getElementById('ovDuckLives');
            var footEl = document.getElementById('ovDuckFoot');
            var resetBtn = document.getElementById('ovDuckReset');
            var closeBtn = document.getElementById('ovDuckClose');

            var W = 400,
                H = 320,
                GRASS = 44;
            var ducks, flashes, mx, my, score, wave, ammo, lives, playing, active, raf;
            var pendingSpawns, waveEscaped, betweenWaves;

            function updateHud() {
                scoreEl.textContent = String(score);
                waveEl.textContent = String(wave);
                ammoEl.textContent = String(ammo);
                livesEl.textContent = String(lives);
            }

            function spawnDuck() {
                var fromLeft = Math.random() > 0.5;
                ducks.push({
                    x: fromLeft ? -32 : W + 32,
                    y: 48 + Math.random() * (H - GRASS - 90),
                    vx: (fromLeft ? 1 : -1) * (1.6 + Math.random() * 1.4),
                    vy: 0,
                    bob: Math.random() * 6.28,
                    wingT: Math.random() * 6.28,
                    state: 'flying'
                });
            }

            function startWave() {
                betweenWaves = false;
                ammo = 3;
                waveEscaped = 0;
                updateHud();
                footEl.textContent = 'Gelombang ' + wave + ' — tembak semua bebek!';
                var count = Math.min(2 + Math.floor((wave - 1) / 2), 4);
                pendingSpawns = count;
                for (var i = 0; i < count; i++) {
                    (function(delay) {
                        setTimeout(function() {
                            if (!active || !playing) return;
                            spawnDuck();
                            pendingSpawns--;
                        }, delay);
                    })(i * 550);
                }
            }

            function initGame() {
                ducks = [];
                flashes = [];
                mx = W / 2;
                my = H / 2;
                score = 0;
                wave = 1;
                lives = 3;
                playing = true;
                pendingSpawns = 0;
                waveEscaped = 0;
                betweenWaves = false;
                updateHud();
                footEl.textContent = 'Arahkan crosshair · klik untuk tembak · 3 peluru per gelombang';
                startWave();
                draw();
            }

            function gameOver() {
                playing = false;
                footEl.textContent = 'Game over! Skor ' + score + ' — Spasi / ↻ buat main lagi.';
            }

            function endWave() {
                if (!playing || betweenWaves) return;
                betweenWaves = true;
                if (waveEscaped > 0) {
                    lives -= waveEscaped;
                    updateHud();
                    if (lives <= 0) {
                        gameOver();
                        return;
                    }
                    footEl.textContent = waveEscaped + ' bebek lolos! Nyawa tersisa ' + lives;
                } else {
                    footEl.textContent = 'Gelombang ' + wave + ' bersih!';
                }
                wave++;
                updateHud();
                setTimeout(function() {
                    if (active && playing) startWave();
                }, 1100);
            }

            function cleanupDucks() {
                ducks = ducks.filter(function(d) {
                    if (d.state === 'escaped') {
                        waveEscaped++;
                        return false;
                    }
                    return d.state !== 'gone';
                });
                if (pendingSpawns === 0 && ducks.length === 0) endWave();
            }

            function drawDuck(d) {
                var bobY = d.state === 'flying' ? Math.sin(d.bob) * 4 : 0;
                var wing = Math.sin(d.wingT) * 5;
                ctx.save();
                ctx.translate(d.x, d.y + bobY);
                if (d.vx < 0) ctx.scale(-1, 1);
                ctx.fillStyle = '#92400e';
                ctx.beginPath();
                ctx.ellipse(-3, 3, 17, 11, 0, 0, Math.PI * 2);
                ctx.fill();
                ctx.fillStyle = '#a16207';
                ctx.beginPath();
                ctx.ellipse(-9, 0 + wing, 9, 5, -0.35, 0, Math.PI * 2);
                ctx.fill();
                ctx.fillStyle = '#15803d';
                ctx.beginPath();
                ctx.arc(11, -7, 9, 0, Math.PI * 2);
                ctx.fill();
                ctx.fillStyle = '#fbbf24';
                ctx.beginPath();
                ctx.moveTo(19, -7);
                ctx.lineTo(29, -5);
                ctx.lineTo(19, -3);
                ctx.closePath();
                ctx.fill();
                ctx.fillStyle = '#fff';
                ctx.beginPath();
                ctx.arc(14, -9, 2.2, 0, Math.PI * 2);
                ctx.fill();
                ctx.fillStyle = '#0f172a';
                ctx.beginPath();
                ctx.arc(14.8, -9, 1, 0, Math.PI * 2);
                ctx.fill();
                ctx.restore();
            }

            function drawCrosshair(x, y) {
                ctx.strokeStyle = 'rgba(255,255,255,.95)';
                ctx.lineWidth = 2;
                ctx.beginPath();
                ctx.arc(x, y, 13, 0, Math.PI * 2);
                ctx.stroke();
                ctx.beginPath();
                ctx.moveTo(x - 18, y);
                ctx.lineTo(x - 6, y);
                ctx.moveTo(x + 6, y);
                ctx.lineTo(x + 18, y);
                ctx.moveTo(x, y - 18);
                ctx.lineTo(x, y - 6);
                ctx.moveTo(x, y + 6);
                ctx.lineTo(x, y + 18);
                ctx.stroke();
                ctx.fillStyle = '#dc2626';
                ctx.beginPath();
                ctx.arc(x, y, 2.5, 0, Math.PI * 2);
                ctx.fill();
            }

            function draw() {
                var sky = ctx.createLinearGradient(0, 0, 0, H - GRASS);
                sky.addColorStop(0, '#bae6fd');
                sky.addColorStop(1, '#0284c7');
                ctx.fillStyle = sky;
                ctx.fillRect(0, 0, W, H);
                ctx.fillStyle = '#166534';
                ctx.fillRect(0, H - GRASS, W, GRASS);
                ctx.fillStyle = '#14532d';
                for (var i = 0; i < 6; i++) {
                    ctx.beginPath();
                    ctx.arc(i * 72 + 20, H - GRASS + 8, 18 + (i % 3) * 4, Math.PI, 0);
                    ctx.fill();
                }
                ducks.forEach(drawDuck);
                flashes.forEach(function(f) {
                    ctx.strokeStyle = 'rgba(255,255,255,' + (f.life / 10) + ')';
                    ctx.lineWidth = 2;
                    ctx.beginPath();
                    ctx.arc(f.x, f.y, f.r + (10 - f.life) * 1.2, 0, Math.PI * 2);
                    ctx.stroke();
                });
                if (playing) drawCrosshair(mx, my);
            }

            function shoot(cx, cy) {
                if (!playing || ammo <= 0) return;
                ammo--;
                ammoEl.textContent = String(ammo);
                flashes.push({
                    x: cx,
                    y: cy,
                    r: 3,
                    life: 10
                });
                ducks.forEach(function(d) {
                    if (d.state !== 'flying') return;
                    var dy = Math.sin(d.bob) * 4;
                    if (Math.hypot(cx - d.x, cy - (d.y + dy)) < 24) {
                        d.state = 'falling';
                        d.vy = -2.5;
                        score += 100 + wave * 10;
                        scoreEl.textContent = String(score);
                    }
                });
            }

            function tick() {
                if (!active) return;
                if (playing) {
                    ducks.forEach(function(d) {
                        if (d.state === 'flying') {
                            d.bob += 0.07;
                            d.wingT += 0.22;
                            d.x += d.vx;
                            d.y += Math.sin(d.bob) * 0.35;
                            if ((d.vx > 0 && d.x > W + 36) || (d.vx < 0 && d.x < -36)) d.state = 'escaped';
                        } else if (d.state === 'falling') {
                            d.vy += 0.28;
                            d.y += d.vy;
                            d.x += d.vx * 0.25;
                            if (d.y > H + 24) d.state = 'gone';
                        }
                    });
                    flashes.forEach(function(f) {
                        f.life--;
                    });
                    flashes = flashes.filter(function(f) {
                        return f.life > 0;
                    });
                    cleanupDucks();
                }
                draw();
                raf = requestAnimationFrame(tick);
            }

            function canvasPos(e) {
                var rect = canvas.getBoundingClientRect();
                var scaleX = W / rect.width;
                var scaleY = H / rect.height;
                return {
                    x: (e.clientX - rect.left) * scaleX,
                    y: (e.clientY - rect.top) * scaleY
                };
            }

            function onMouseMove(e) {
                if (!active) return;
                var p = canvasPos(e);
                mx = p.x;
                my = p.y;
            }

            function onMouseDown(e) {
                if (!active) return;
                e.preventDefault();
                var p = canvasPos(e);
                mx = p.x;
                my = p.y;
                shoot(p.x, p.y);
            }

            function onKey(e) {
                if (!active) return;
                if (e.key === ' ' && !playing) {
                    e.preventDefault();
                    initGame();
                } else if (e.key === 'Escape') close();
            }

            function open() {
                modal.classList.add('open');
                active = true;
                initGame();
                cancelAnimationFrame(raf);
                raf = requestAnimationFrame(tick);
                wrap.addEventListener('mousemove', onMouseMove);
                canvas.addEventListener('mousedown', onMouseDown);
                canvas.addEventListener('contextmenu', function(e) {
                    e.preventDefault();
                });
                document.addEventListener('keydown', onKey);
            }

            function close() {
                modal.classList.remove('open');
                active = false;
                playing = false;
                cancelAnimationFrame(raf);
                wrap.removeEventListener('mousemove', onMouseMove);
                canvas.removeEventListener('mousedown', onMouseDown);
                document.removeEventListener('keydown', onKey);
            }

            resetBtn.addEventListener('click', initGame);
            closeBtn.addEventListener('click', close);
            modal.addEventListener('click', function(e) {
                if (e.target === modal) close();
            });

            var clicks = 0,
                t = null;
            title.addEventListener('click', function(e) {
                e.preventDefault();
                clicks++;
                clearTimeout(t);
                t = setTimeout(function() {
                    clicks = 0;
                }, 700);
                if (clicks >= 6) {
                    clicks = 0;
                    open();
                }
            });
        })();
    </script>

    <script>
        // 🥚 Easter egg: klik judul Error per Area 9x cepat → Flappy Bird
        (function() {
            var title = document.getElementById('ovErrTitle');
            var modal = document.getElementById('ovFlappyModal');
            var canvas = document.getElementById('ovFlappyCanvas');
            if (!title || !modal || !canvas) return;

            var ctx = canvas.getContext('2d');
            var scoreEl = document.getElementById('ovFlappyScore');
            var footEl = document.getElementById('ovFlappyFoot');
            var resetBtn = document.getElementById('ovFlappyReset');
            var closeBtn = document.getElementById('ovFlappyClose');

            var W = 320, H = 400;
            var bird, pipes, particles;
            var score, active, playing, raf, frameCnt;

            function initGame() {
                bird = { x: 60, y: H/2, w: 20, h: 14, vy: 0, gravity: 0.2, jump: -4.5 };
                pipes = [];
                particles = [];
                score = 0;
                frameCnt = 0;
                playing = true;
                scoreEl.textContent = '0';
                footEl.textContent = 'Spasi atau klik canvas untuk lompat · Hindari pipa';
                spawnPipe();
                pipes[0].x = W;
                draw();
            }

            function spawnPipe() {
                var gap = 110;
                var minPipe = 40;
                var maxPipe = H - gap - minPipe;
                var topH = Math.floor(Math.random() * (maxPipe - minPipe + 1) + minPipe);
                pipes.push({ x: W, w: 40, top: topH, bottom: H - topH - gap, passed: false });
            }

            function draw() {
                ctx.fillStyle = '#71c5cf';
                ctx.fillRect(0, 0, W, H);
                
                ctx.fillStyle = '#ded895';
                ctx.fillRect(0, H - 20, W, 20);
                ctx.fillStyle = '#73bf2e';
                ctx.fillRect(0, H - 24, W, 4);

                if (playing || bird.y < H-24) {
                    pipes.forEach(function(p) {
                        ctx.fillStyle = '#74bf2e';
                        ctx.fillRect(p.x, 0, p.w, p.top);
                        ctx.strokeRect(p.x, 0, p.w, p.top);
                        ctx.fillRect(p.x, H - p.bottom, p.w, p.bottom);
                        ctx.strokeRect(p.x, H - p.bottom, p.w, p.bottom);
                    });

                    ctx.save();
                    ctx.translate(bird.x + bird.w/2, bird.y + bird.h/2);
                    ctx.rotate(Math.min(Math.PI/4, Math.max(-Math.PI/4, (bird.vy * 0.1))));
                    ctx.fillStyle = '#facc15';
                    ctx.beginPath(); ctx.ellipse(0, 0, bird.w/2, bird.h/2, 0, 0, Math.PI*2); ctx.fill();
                    ctx.fillStyle = '#fef08a';
                    var wingY = (frameCnt%10 < 5) ? -2 : 0;
                    ctx.beginPath(); ctx.ellipse(-4, wingY, 6, 4, 0, 0, Math.PI*2); ctx.fill();
                    ctx.fillStyle = '#fff';
                    ctx.beginPath(); ctx.arc(4, -3, 3, 0, Math.PI*2); ctx.fill();
                    ctx.fillStyle = '#000';
                    ctx.beginPath(); ctx.arc(5, -3, 1, 0, Math.PI*2); ctx.fill();
                    ctx.fillStyle = '#f97316';
                    ctx.beginPath(); ctx.moveTo(6, 1); ctx.lineTo(12, 1); ctx.lineTo(6, 5); ctx.fill();
                    ctx.restore();
                }

                particles.forEach(function(p) {
                    ctx.fillStyle = p.color;
                    ctx.globalAlpha = Math.max(0, p.life);
                    ctx.fillRect(p.x, p.y, p.s, p.s);
                    ctx.globalAlpha = 1;
                });
            }

            function doJump() {
                if (playing) {
                    bird.vy = bird.jump;
                    particles.push({ x: bird.x, y: bird.y+bird.h/2, vx: -1 - Math.random(), vy: Math.random() - 0.5, life: 1, color: '#fef08a', s: 4 });
                } else if (bird.y >= H-24) {
                    initGame();
                }
            }

            function tick() {
                if (!active) return;
                
                if (playing) {
                    frameCnt++;
                    bird.vy += bird.gravity;
                    bird.y += bird.vy;

                    if (bird.y < 0) { bird.y = 0; bird.vy = 0; }
                    if (bird.y + bird.h > H - 24) {
                        playing = false;
                        footEl.textContent = 'Game Over! Skor: ' + score + ' — Klik atau Spasi untuk main lagi';
                    }

                    if (frameCnt % 120 === 0) spawnPipe();

                    for (var i = 0; i < pipes.length; i++) {
                        var p = pipes[i];
                        p.x -= 1.5;

                        var bx = bird.x, by = bird.y, bw = bird.w, bh = bird.h;
                        if (bx + bw > p.x && bx < p.x + p.w) {
                            if (by < p.top || by + bh > H - p.bottom) {
                                playing = false;
                                footEl.textContent = 'Game Over! Skor: ' + score + ' — Klik atau Spasi untuk main lagi';
                                for(var k=0;k<10;k++) particles.push({ x: bx+bw/2, y: by+bh/2, vx: (Math.random()-0.5)*5, vy: (Math.random()-0.5)*5, life: 1, color: '#facc15', s: 3 });
                            }
                        }

                        if (!p.passed && p.x + p.w < bx) {
                            p.passed = true;
                            score++;
                            scoreEl.textContent = String(score);
                        }
                    }

                    if (pipes.length > 0 && pipes[0].x < -pipes[0].w) {
                        pipes.shift();
                    }
                } else if (bird.y < H - 24) {
                    bird.vy += bird.gravity;
                    bird.y += bird.vy;
                    if (bird.y > H - 24) bird.y = H - 24;
                }

                for (var i = particles.length - 1; i >= 0; i--) {
                    var p = particles[i];
                    p.x += p.vx; p.y += p.vy;
                    p.life -= 0.05;
                    if (p.life <= 0) particles.splice(i, 1);
                }

                draw();
                raf = requestAnimationFrame(tick);
            }

            function onKey(e) {
                if (!active) return;
                var k = e.key.toLowerCase();
                if (k === ' ' || k === 'spacebar' || k === 'arrowup') {
                    if (e.type === 'keydown') doJump();
                    e.preventDefault();
                } else if (k === 'escape' && e.type === 'keydown') {
                    close();
                }
            }

            function onClick(e) {
                if (!active) return;
                doJump();
            }

            function open() {
                modal.classList.add('open');
                active = true;
                initGame();
                cancelAnimationFrame(raf);
                raf = requestAnimationFrame(tick);
                document.addEventListener('keydown', onKey);
                canvas.addEventListener('mousedown', onClick);
            }

            function close() {
                modal.classList.remove('open');
                active = false;
                playing = false;
                cancelAnimationFrame(raf);
                document.removeEventListener('keydown', onKey);
                canvas.removeEventListener('mousedown', onClick);
            }

            resetBtn.addEventListener('click', function() { if(active) initGame(); });
            closeBtn.addEventListener('click', close);
            modal.addEventListener('click', function(e) { if (e.target === modal) close(); });

            var clicks = 0, t = null;
            title.addEventListener('click', function(e) {
                e.preventDefault();
                clicks++;
                clearTimeout(t);
                t = setTimeout(function() { clicks = 0; }, 700);
                if (clicks >= 9) { clicks = 0; open(); }
            });
        })();
    </script>

    <script>
        // 🥚 Easter egg: Konami code (↑↑↓↓←→←→ B A) → Space Impact ala Nokia. Modal terpisah, nol efek ke chart.
        (function() {
            var modal = document.getElementById('ovSiModal');
            var cv = document.getElementById('ovSiCanvas');
            if (!modal || !cv) return;
            var ctx = cv.getContext('2d');
            ctx.imageSmoothingEnabled = false;
            var GW = 140,
                GH = 60,
                BG = '#a7cf94',
                FG = '#1f2d12';

            var FONT = {
                '0': ['XXX', 'X X', 'X X', 'X X', 'XXX'], '1': [' X ', 'XX ', ' X ', ' X ', 'XXX'],
                '2': ['XXX', '  X', 'XXX', 'X  ', 'XXX'], '3': ['XXX', '  X', 'XXX', '  X', 'XXX'],
                '4': ['X X', 'X X', 'XXX', '  X', '  X'], '5': ['XXX', 'X  ', 'XXX', '  X', 'XXX'],
                '6': ['XXX', 'X  ', 'XXX', 'X X', 'XXX'], '7': ['XXX', '  X', '  X', '  X', '  X'],
                '8': ['XXX', 'X X', 'XXX', 'X X', 'XXX'], '9': ['XXX', 'X X', 'XXX', '  X', 'XXX'],
                'A': ['XXX', 'X X', 'XXX', 'X X', 'X X'], 'B': ['XX ', 'X X', 'XX ', 'X X', 'XX '],
                'D': ['XX ', 'X X', 'X X', 'X X', 'XX '], 'E': ['XXX', 'X  ', 'XXX', 'X  ', 'XXX'],
                'G': [' XX', 'X  ', 'X X', 'X X', ' XX'], 'I': ['XXX', ' X ', ' X ', ' X ', 'XXX'],
                'L': ['X  ', 'X  ', 'X  ', 'X  ', 'XXX'], 'M': ['X X', 'XXX', 'XXX', 'X X', 'X X'],
                'O': ['XXX', 'X X', 'X X', 'X X', 'XXX'], 'P': ['XX ', 'X X', 'XX ', 'X  ', 'X  '],
                'R': ['XX ', 'X X', 'XX ', 'X X', 'X X'], 'S': ['XXX', 'X  ', 'XXX', '  X', 'XXX'],
                'T': ['XXX', ' X ', ' X ', ' X ', ' X '], 'V': ['X X', 'X X', 'X X', 'X X', ' X '],
                'Y': ['X X', 'X X', ' X ', ' X ', ' X '], ' ': ['   ', '   ', '   ', '   ', '   ']
            };
            var SPR_SHIP = ['XX    ', 'XXXX  ', 'XXXXXX', 'XXXX  ', 'XX    '];
            var SPR_E0 = [' XX X', 'XXXXX', ' XXX ', 'XXXXX', ' XX X'];
            var SPR_E1 = ['  XX', ' XXX', 'XXXX', ' XXX', '  XX'];
            var SPR_E2 = [' XXXX ', 'XXXXXX', 'XX  XX', 'XXXXXX', 'XXXXXX', ' XXXX '];
            var HEART = [' X X ', 'XXXXX', 'XXXXX', ' XXX ', '  X  '];
            var SPR_DRAGON_HEAD = [
                '      X       X   ',
                '     XX      XX   ',
                '    XXX     XXX   ',
                '   XXXXXXXXXXXXX  ',
                '  XX  X  XXXXXX   ',
                ' XXXXXXXXXXXXXXXXX',
                'XXXXXXXXXXXXXXXXXX',
                '    XXXXX         ',
                '   XXXXXXXXX      ',
                '    XXXXXX        '
            ];

            var state = 'ready',
                player, bullets, enemies, ebullets, powerups, bosses, score, level, kills, need, spawnT, banner, bannerT, terr, scrollA, last;
            var active = false,
                rafId = null,
                keys = {};

            function px(x, y, w, h) { ctx.fillRect(x | 0, y | 0, w || 1, h || 1); }

            function spr(b, x, y) { for (var r = 0; r < b.length; r++) for (var c = 0; c < b[r].length; c++) if (b[r][c] === 'X') px(x + c, y + r); }

            function text(s, x, y) { s = String(s); for (var i = 0; i < s.length; i++) { var g = FONT[s[i]] || FONT[' ']; for (var r = 0; r < 5; r++) for (var c = 0; c < 3; c++) if (g[r][c] === 'X') px(x + i * 4 + c, y + r); } }

            function textW(s) { return String(s).length * 4 - 1; }

            function disc(cx, cy, rad) { for (var y = -rad; y <= rad; y++) for (var x = -rad; x <= rad; x++) if (x * x + y * y <= rad * rad) px(cx + x, cy + y); }

            function genH(h) { h += Math.random() * 4 - 2; return Math.max(3, Math.min(16, h)); }

            function reset() {
                player = { x: 14, y: 28, w: 6, h: 5, sp: 52, lives: 5, fire: 0, inv: 0, power: 1, hasLaser: false, laserCd: 0, laserActive: 0 };
                bullets = []; enemies = []; ebullets = []; powerups = []; bosses = [];
                score = 0; level = 1; kills = 0; need = 6; spawnT = 600;
                banner = 'LEVEL 1'; bannerT = 1300;
                terr = []; var h = 8; for (var i = 0; i < GW + 2; i++) { h = genH(h); terr.push(Math.round(h)); } scrollA = 0;
            }

            function bannerSet(t, ms) { banner = t; bannerT = ms || 1200; }

            function spawn() {
                var t = Math.random();
                if (level >= 4 && t < 0.25) enemies.push({ x: GW + 6, y: 6 + Math.random() * 34, w: 6, h: 6, sp: 16 + level * 3, hp: 3, b: SPR_E2, pts: 30 });
                else if (t < 0.45) enemies.push({ x: GW + 6, y: 6 + Math.random() * 36, w: 4, h: 5, sp: 38 + level * 5, hp: 1, b: SPR_E1, pts: 15 });
                else enemies.push({ x: GW + 6, y: 6 + Math.random() * 36, w: 5, h: 5, sp: 24 + level * 4, hp: 1, b: SPR_E0, pts: 10 });
            }

            function spawnBoss() { bosses = [{ x: GW - 30, y: 24, r: 11, dir: 1, sp: 22 + level * 3, hp: 20 + level * 8, max: 20 + level * 8, shoot: 700 }]; bannerSet('BOSS', 1400); }

            function spawnDragon() { bosses = [{ x: GW - 30, y: 24, r: 10, dir: 1, sp: 35 + level * 3, hp: 40 + level * 10, max: 40 + level * 10, shoot: 500, type: 'dragon' }]; bannerSet('DRAGON BOSS', 1500); }

            function nextLevel() { level++; kills = 0; need = 5 + level * 2; if (level === 5) spawnDragon(); else if (level % 3 === 0) spawnBoss(); else bannerSet('LEVEL ' + level, 1100); }

            function hitB(ax, ay, aw, ah, b) { return ax < b.x + b.w && ax + aw > b.x && ay < b.y + b.h && ay + ah > b.y; }

            function keyOn(a, b) { return keys[a] || keys[b]; }

            function update(dt) {
                var d = dt / 1000;
                scrollA += 22 * d; while (scrollA >= 1) { terr.shift(); terr.push(Math.round(genH(terr[terr.length - 1]))); scrollA -= 1; }
                var mx = (keyOn('ArrowRight', 'd') ? 1 : 0) - (keyOn('ArrowLeft', 'a') ? 1 : 0);
                var my = (keyOn('ArrowDown', 's') ? 1 : 0) - (keyOn('ArrowUp', 'w') ? 1 : 0);
                player.x = Math.max(3, Math.min(GW * 0.5, player.x + mx * player.sp * d));
                player.y = Math.max(3, Math.min(42, player.y + my * player.sp * d));
                if (player.inv > 0) player.inv -= dt;
                player.fire -= dt;
                if (player.fire <= 0) { 
                    bullets.push({ x: player.x + 6, y: player.y + 2, sp: 115, dy: 0 }); 
                    if (player.power >= 2) {
                        bullets.push({ x: player.x + 6, y: player.y + 2, sp: 115, dy: -25 });
                        bullets.push({ x: player.x + 6, y: player.y + 2, sp: 115, dy: 25 });
                    }
                    if (player.power >= 3) {
                        bullets.push({ x: player.x + 6, y: player.y + 2, sp: 115, dy: -50 });
                        bullets.push({ x: player.x + 6, y: player.y + 2, sp: 115, dy: 50 });
                    }
                    player.fire = 230; 
                }
                for (var i = 0; i < bullets.length; i++) {
                    bullets[i].x += bullets[i].sp * d;
                    if (bullets[i].dy) bullets[i].y += bullets[i].dy * d;
                }
                bullets = bullets.filter(function(b) { return b.x < GW + 4 && b.y > 0 && b.y < GH; });
                
                if (bosses.length === 0) { spawnT -= dt; if (spawnT <= 0) { spawn(); spawnT = Math.max(420, 1400 - level * 110); } }
                
                for (var j = 0; j < enemies.length; j++) enemies[j].x -= enemies[j].sp * d;
                enemies = enemies.filter(function(e) { return e.x + e.w > -4; });
                
                for (var bsi = 0; bsi < bosses.length; bsi++) {
                    var bs = bosses[bsi];
                    bs.y += bs.dir * bs.sp * d;
                    if (bs.y < 14) bs.dir = 1;
                    if (bs.y > 44) bs.dir = -1;
                    bs.shoot -= dt;
                    if (bs.shoot <= 0) { 
                        if (bs.type === 'dragon') {
                            ebullets.push({ x: bs.x - bs.r, y: bs.y, sp: 70 + level * 8, dy: -15 });
                            ebullets.push({ x: bs.x - bs.r, y: bs.y, sp: 70 + level * 8, dy: 0 });
                            ebullets.push({ x: bs.x - bs.r, y: bs.y, sp: 70 + level * 8, dy: 15 });
                            if (bs.enraged) {
                                var tx = bs.x + 70;
                                var ty = bs.y + Math.sin(performance.now() / 150 + 5) * 12;
                                ebullets.push({ x: tx, y: ty, sp: 90 + level * 8, dy: -25 });
                                ebullets.push({ x: tx, y: ty, sp: 90 + level * 8, dy: 25 });
                            }
                            bs.shoot = Math.max(300, 800 - level * 40);
                        } else {
                            ebullets.push({ x: bs.x - bs.r, y: bs.y, sp: 60 + level * 8, dy: 0 }); 
                            bs.shoot = Math.max(420, 1000 - level * 40); 
                        }
                    }
                }
                
                for (var k = 0; k < ebullets.length; k++) {
                    ebullets[k].x -= ebullets[k].sp * d;
                    if (ebullets[k].dy) ebullets[k].y += ebullets[k].dy * d;
                }
                ebullets = ebullets.filter(function(b) { return b.x > -4 && b.y > 0 && b.y < GH; });
                
                if (player.hasLaser) {
                    player.laserCd -= dt;
                    if (player.laserCd <= 0) {
                        player.laserActive = 200;
                        player.laserCd = 4000;
                        var l_x = player.x + player.w;
                        var l_y = player.y + 1;
                        for (var ei = 0; ei < enemies.length; ei++) {
                            var e = enemies[ei];
                            if (e.hp > 0 && hitB(l_x, l_y, GW, 3, e)) {
                                e.hp -= 5;
                                if (e.hp <= 0) { score += e.pts; kills++; }
                            }
                        }
                        for (var bsi = 0; bsi < bosses.length; bsi++) {
                            var bs = bosses[bsi];
                            if (bs.hp > 0 && hitB(l_x, l_y, GW, 3, {x: bs.x - bs.r, y: bs.y - bs.r, w: bs.r * 2, h: bs.r * 2})) {
                                var oldHp = bs.hp;
                                bs.hp -= 5;
                                var half = Math.floor(bs.max / 2);
                                if (oldHp > half && bs.hp <= half) {
                                    if (bs.type === 'dragon' && !bs.enraged) {
                                        bs.enraged = true; bs.sp *= 1.5; bs.shoot = 200;
                                    } else if (bs.type !== 'dragon' && bs.r > 7) {
                                        bs.r = 7; bs.sp *= 1.3; bs.hp += 5;
                                        bosses.push({ x: bs.x, y: bs.y + 15, r: 7, dir: -bs.dir, sp: bs.sp, hp: bs.hp, max: bs.max, shoot: bs.shoot });
                                    }
                                }
                                if (bs.hp <= 0) { score += 150 + level * 20; bs.dead = true; }
                            }
                        }
                    }
                }
                if (player.laserActive > 0) player.laserActive -= dt;

                for (var pi = 0; pi < powerups.length; pi++) {
                    powerups[pi].x -= 30 * d;
                    if (hitB(player.x, player.y, player.w, player.h, powerups[pi])) {
                        if (powerups[pi].type === 'laser') {
                            player.hasLaser = true;
                            player.laserCd = 0;
                        } else {
                            player.power = Math.min(3, player.power + 1);
                        }
                        powerups[pi].x = -99;
                        score += 50;
                    }
                }
                powerups = powerups.filter(function(p) { return p.x > -4; });

                for (var bi = 0; bi < bullets.length; bi++) {
                    var b = bullets[bi];
                    for (var ei = 0; ei < enemies.length; ei++) {
                        var e = enemies[ei];
                        if (e.hp > 0 && hitB(b.x, b.y, 3, 1, e)) { 
                            e.hp--; b.x = GW + 99; 
                            if (e.hp <= 0) { 
                                score += e.pts; kills++; 
                                if (Math.random() < 0.15) powerups.push({ x: e.x, y: e.y, w: 5, h: 5, type: Math.random() < 0.3 ? 'laser' : 'spread' });
                            } 
                            break; 
                        }
                    }
                    for (var bsi = 0; bsi < bosses.length; bsi++) {
                        var bs = bosses[bsi];
                        if (bs.hp > 0 && b.x > bs.x - bs.r && b.y > bs.y - bs.r && b.y < bs.y + bs.r) { 
                            bs.hp--; b.x = GW + 99; 
                            if (bs.hp === Math.floor(bs.max / 2)) {
                                if (bs.type === 'dragon' && !bs.enraged) {
                                    bs.enraged = true;
                                    bs.sp *= 1.5;
                                    bs.shoot = 200;
                                } else if (bs.type !== 'dragon' && bs.r > 7) {
                                    bs.r = 7; bs.sp *= 1.3; bs.hp += 5;
                                    bosses.push({ x: bs.x, y: bs.y + 15, r: 7, dir: -bs.dir, sp: bs.sp, hp: bs.hp, max: bs.max, shoot: bs.shoot });
                                }
                            }
                            if (bs.hp <= 0) { 
                                score += 150 + level * 20; 
                                bs.dead = true; 
                            } 
                        }
                    }
                }
                enemies = enemies.filter(function(e) { return e.hp > 0; });
                var oldLen = bosses.length;
                bosses = bosses.filter(function(bs) { return !bs.dead; });
                if (oldLen > 0 && bosses.length === 0) {
                    nextLevel();
                }

                function dmg() { if (player.inv > 0) return; player.lives--; player.inv = 1300; player.power = 1; player.hasLaser = false; if (player.lives <= 0) { state = 'over'; bannerSet('GAME OVER', 9e9); } }
                for (var ci = 0; ci < enemies.length; ci++) if (hitB(player.x, player.y, player.w, player.h, enemies[ci])) { enemies[ci].hp = 0; dmg(); }
                enemies = enemies.filter(function(e) { return e.hp > 0; });
                for (var di = 0; di < ebullets.length; di++) { var eb = ebullets[di]; if (player.x < eb.x + 3 && player.x + player.w > eb.x && player.y < eb.y + 2 && player.y + player.h > eb.y) { eb.x = -99; dmg(); } }
                ebullets = ebullets.filter(function(b) { return b.x > -4; });
                for (var bsi = 0; bsi < bosses.length; bsi++) {
                    var bs = bosses[bsi];
                    if (Math.abs((player.x + 3) - bs.x) < bs.r && Math.abs((player.y + 2) - bs.y) < bs.r) dmg();
                }
                if (bosses.length === 0 && kills >= need) nextLevel();
                if (bannerT > 0) bannerT -= dt;
            }

            function bigText(s, x, y) { s = String(s); for (var i = 0; i < s.length; i++) { var g = FONT[s[i]] || FONT[' ']; for (var r = 0; r < 5; r++) for (var c = 0; c < 3; c++) if (g[r][c] === 'X') px(x + i * 8 + c * 2, y + r * 2, 2, 2); } }

            function draw() {
                ctx.fillStyle = BG; ctx.fillRect(0, 0, GW, GH);
                ctx.fillStyle = FG;
                for (var i = 0; i < GW; i++) { var ht = terr[i] || 4; px(i, GH - ht, 1, ht); }
                for (var bi = 0; bi < bullets.length; bi++) px(bullets[bi].x, bullets[bi].y, 3, 1);
                for (var ei = 0; ei < ebullets.length; ei++) px(ebullets[ei].x, ebullets[ei].y, 3, 2);
                for (var en = 0; en < enemies.length; en++) spr(enemies[en].b, enemies[en].x | 0, enemies[en].y | 0);
                
                if (player.laserActive > 0) {
                    var l_x = player.x + player.w;
                    var l_y = player.y + 1;
                    if ((performance.now() / 30 | 0) % 2 === 0) px(l_x, l_y, GW, 3);
                    else px(l_x, l_y + 1, GW, 1);
                }

                for (var pi = 0; pi < powerups.length; pi++) {
                    var pu = powerups[pi];
                    px(pu.x | 0, pu.y | 0, 5, 5);
                    ctx.fillStyle = BG; px((pu.x + 1) | 0, (pu.y + 1) | 0, 3, 3);
                    ctx.fillStyle = FG; 
                    if (pu.type === 'laser') px((pu.x + 1) | 0, (pu.y + 2) | 0, 3, 1);
                    else px((pu.x + 2) | 0, (pu.y + 2) | 0, 1, 1);
                }

                for (var bsi = 0; bsi < bosses.length; bsi++) {
                    var bs = bosses[bsi];
                    
                    if (bs.type === 'dragon') {
                        for (var d_i = 10; d_i >= 1; d_i--) {
                            var dr = Math.max(2, Math.round(bs.r - d_i * 0.7));
                            var dx = (bs.x + d_i * 7) | 0;
                            var dy = (bs.y + Math.sin(performance.now() / 150 + d_i * 0.5) * 12) | 0;
                            ctx.fillStyle = FG;
                            disc(dx, dy, dr);
                            ctx.fillStyle = BG; 
                            disc(dx, dy, Math.max(1, dr - 2)); 
                            ctx.fillStyle = FG;
                            px(dx - 1, dy - 1, 2, 2);
                            px(dx, dy - dr - 2, 2, 3);
                            px(dx + 1, dy - dr - 3, 1, 1);
                            if (d_i === 10) {
                                px(dx + dr, dy - 4, 2, 8);
                                px(dx + dr + 2, dy - 2, 2, 4);
                            }
                        }
                        ctx.fillStyle = BG; disc(bs.x | 0, bs.y | 0, bs.r - 1); ctx.fillStyle = FG;
                        spr(SPR_DRAGON_HEAD, bs.x - 9, bs.y - 5);
                    } else {
                        disc(bs.x | 0, bs.y | 0, bs.r);
                        for (var a = 0; a < 8; a++) { var an = a / 8 * 6.283; px((bs.x + Math.cos(an) * (bs.r + 1)) | 0, (bs.y + Math.sin(an) * (bs.r + 1)) | 0, 2, 2); }
                        ctx.fillStyle = BG; disc((bs.x - bs.r * 0.45) | 0, (bs.y - 2) | 0, 3);
                        ctx.fillStyle = FG; px((bs.x - bs.r * 0.45) | 0, (bs.y - 2) | 0, 2, 2);
                    }
                    
                    var hw = bs.r > 7 ? 44 : 20;
                    var hx = bs.r > 7 ? GW / 2 - 22 : bs.x - 10;
                    var hy = bs.r > 7 ? 4 : bs.y - bs.r - 4;
                    px(hx, hy, hw, 1);
                    ctx.fillStyle = BG; px(hx + Math.ceil(hw * bs.hp / bs.max), hy, hw - Math.ceil(hw * bs.hp / bs.max), 1);
                    ctx.fillStyle = FG;
                }
                if (!(player.inv > 0 && (performance.now() / 90 | 0) % 2)) spr(SPR_SHIP, player.x | 0, player.y | 0);
                spr(HEART, 2, 2); text(('0' + player.lives).slice(-2), 9, 2);
                if (player.hasLaser) {
                    var cdP = Math.max(0, player.laserCd / 4000);
                    px(2, GH - 7, 20, 5); ctx.fillStyle = BG; px(3, GH - 6, 18, 3); ctx.fillStyle = FG; px(3, GH - 5, Math.round(18 * (1 - cdP)), 1);
                }
                text(('0000' + score).slice(-5), GW - 21, 2);
                if (bannerT > 0 || state === 'over') {
                    var w = textW(banner) * 2;
                    ctx.fillStyle = BG; ctx.fillRect(GW / 2 - w / 2 - 3, GH / 2 - 8, w + 6, 14);
                    ctx.fillStyle = FG; bigText(banner, GW / 2 - w / 2, GH / 2 - 5);
                    if (state === 'over') text('SCORE ' + score, GW / 2 - textW('SCORE ' + score) / 2, GH / 2 + 10);
                }
            }

            function start() { reset(); state = 'play'; last = performance.now(); }

            function loop(now) {
                var dt = Math.min(40, now - (last || now));
                last = now;
                if (state === 'play') update(dt);
                draw();
                if (active) rafId = requestAnimationFrame(loop);
            }

            function activate() {
                if (active) return;
                active = true;
                modal.classList.add('open');
                start();
                rafId = requestAnimationFrame(loop);
            }

            function deactivate() {
                active = false;
                modal.classList.remove('open');
                if (rafId) cancelAnimationFrame(rafId);
                rafId = null;
            }

            var KON = ['arrowup', 'arrowup', 'arrowdown', 'arrowdown', 'arrowleft', 'arrowright', 'arrowleft', 'arrowright', 'b', 'a'];
            var kbuf = [];
            document.addEventListener('keydown', function(e) {
                if (active) {
                    if (e.key === 'Escape') { deactivate(); return; }
                    keys[e.key.length === 1 ? e.key.toLowerCase() : e.key] = 1;
                    if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight', ' '].indexOf(e.key) !== -1) e.preventDefault();
                    return;
                }
                var k = e.key.toLowerCase();
                kbuf.push(k);
                if (kbuf.length > KON.length) kbuf.shift();
                if (kbuf.length === KON.length && KON.every(function(v, i) { return kbuf[i] === v; })) { kbuf = []; activate(); }
            });
            document.addEventListener('keyup', function(e) { keys[e.key.length === 1 ? e.key.toLowerCase() : e.key] = 0; });
            cv.addEventListener('pointermove', function(e) {
                if (!active || state !== 'play') return;
                var r = cv.getBoundingClientRect();
                player.x = Math.max(3, Math.min(GW * 0.5, (e.clientX - r.left) * GW / r.width - 3));
                player.y = Math.max(3, Math.min(42, (e.clientY - r.top) * GH / r.height - 2));
            });
            document.getElementById('ovSiReset').addEventListener('click', start);
            document.getElementById('ovSiClose').addEventListener('click', deactivate);
            modal.addEventListener('click', function(e) { if (e.target === modal) deactivate(); });
        })();
    </script>
@endsection
