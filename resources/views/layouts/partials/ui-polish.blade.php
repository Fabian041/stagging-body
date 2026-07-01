@once
<style>
    :root {
        --ui-glass-light: rgba(255, 255, 255, .54);
        --ui-glass-strong: rgba(255, 255, 255, .76);
        --ui-glass-border: rgba(255, 255, 255, .72);
        --ui-soft-shadow: 0 14px 36px rgba(41, 71, 149, .10);
        --ui-ease: cubic-bezier(.16, 1, .3, 1);
    }

    #sidebar-wrapper .sb-item,
    #sidebar-wrapper .sb-sub-item,
    #main-content .btn,
    #main-content .bella-btn,
    #main-content .act-btn,
    .main-content .btn {
        transition: background .18s var(--ui-ease), border-color .18s var(--ui-ease),
            box-shadow .18s var(--ui-ease), color .18s var(--ui-ease), transform .18s var(--ui-ease);
    }

    #sidebar-wrapper .sb-item:hover,
    #sidebar-wrapper .sb-sub-item:hover {
        transform: translateX(2px);
    }

    #sidebar-wrapper .sb-item.active,
    #sidebar-wrapper .sb-sub-item.active {
        background: linear-gradient(135deg, rgba(232, 244, 253, .95), rgba(255, 255, 255, .78));
        border: 1px solid rgba(0, 112, 183, .12);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .75), 0 10px 24px rgba(0, 112, 183, .10);
        color: var(--primary, #0070B7);
    }

    #sidebar-wrapper .sb-item.active::before,
    #sidebar-wrapper .sb-sub-item.active::before {
        content: '';
        display: block !important;
        position: absolute;
        left: 6px;
        top: 9px;
        bottom: 9px;
        width: 3px;
        border-radius: 999px;
        background: linear-gradient(180deg, #0097D8, #0070B7);
        box-shadow: 0 0 12px rgba(0, 151, 216, .32);
    }

    #sidebar-wrapper .sb-item.active i.sb-icon,
    #sidebar-wrapper .sb-sub-item.active i {
        color: var(--primary, #0070B7);
        opacity: 1;
    }

    #sidebar-wrapper.collapsed .sb-item.active {
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, .75), 0 12px 26px rgba(0, 112, 183, .12);
    }

    #main-content .bella-card,
    #main-content .card,
    .main-content .card {
        border: 1px solid rgba(221, 227, 239, .86);
        border-radius: var(--r, 8px);
        box-shadow: 0 12px 32px rgba(41, 71, 149, .08);
    }

    #main-content .table-responsive,
    #main-content .dataTables_wrapper,
    .main-content .dataTables_wrapper {
        border-radius: var(--r, 8px);
    }

    #main-content table.bella-table,
    #main-content table.dataTable,
    #main-content table.table,
    .main-content table.dataTable,
    .main-content table.table {
        border-collapse: separate !important;
        border-spacing: 0;
        background: #fff;
    }

    #main-content table.bella-table th,
    #main-content table.dataTable thead th,
    #main-content table.table thead th,
    .main-content table.dataTable thead th,
    .main-content table.table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: linear-gradient(180deg, #F8FBFF, #EEF4FA) !important;
        border-bottom: 1px solid rgba(221, 227, 239, .96) !important;
        color: #5F6F8A;
        font-size: 10.5px;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    #main-content table.bella-table td,
    #main-content table.dataTable tbody td,
    #main-content table.table tbody td,
    .main-content table.dataTable tbody td,
    .main-content table.table tbody td {
        border-bottom-color: rgba(221, 227, 239, .72) !important;
        vertical-align: middle;
    }

    #main-content table.bella-table tbody tr,
    #main-content table.dataTable tbody tr,
    #main-content table.table tbody tr,
    .main-content table.dataTable tbody tr,
    .main-content table.table tbody tr {
        transition: background .16s var(--ui-ease), box-shadow .16s var(--ui-ease), transform .16s var(--ui-ease);
    }

    #main-content table.bella-table tbody tr:hover > *,
    #main-content table.dataTable tbody tr:hover > *,
    #main-content table.table tbody tr:hover > *,
    .main-content table.dataTable tbody tr:hover > *,
    .main-content table.table tbody tr:hover > * {
        background: rgba(0, 112, 183, .045) !important;
    }

    #main-content .dataTables_empty,
    .main-content .dataTables_empty {
        padding: 28px 16px !important;
        color: var(--text-muted, #6B7A99) !important;
        font-weight: 700;
    }

    #main-content .dataTables_wrapper .dataTables_filter input,
    #main-content .dataTables_wrapper .dataTables_length select,
    #main-content .form-control,
    #main-content .custom-select,
    #main-content select.form-control,
    #main-content textarea.form-control,
    .main-content .dataTables_wrapper .dataTables_filter input,
    .main-content .dataTables_wrapper .dataTables_length select,
    .main-content .form-control,
    .main-content .custom-select,
    .main-content select.form-control,
    .main-content textarea.form-control {
        background: rgba(255, 255, 255, .78) !important;
        border: 1px solid rgba(221, 227, 239, .95) !important;
        border-radius: var(--r, 8px) !important;
        color: var(--text, #1A2340);
        transition: background .18s var(--ui-ease), border-color .18s var(--ui-ease),
            box-shadow .18s var(--ui-ease), transform .18s var(--ui-ease);
    }

    #main-content .form-control:focus,
    #main-content .custom-select:focus,
    #main-content select.form-control:focus,
    #main-content textarea.form-control:focus,
    #main-content .dataTables_wrapper .dataTables_filter input:focus,
    .main-content .form-control:focus,
    .main-content .custom-select:focus,
    .main-content select.form-control:focus,
    .main-content textarea.form-control:focus,
    .main-content .dataTables_wrapper .dataTables_filter input:focus {
        background: #fff !important;
        border-color: rgba(0, 112, 183, .38) !important;
        box-shadow: 0 0 0 4px rgba(0, 112, 183, .10) !important;
        outline: none;
    }

    #main-content .select2-container--default .select2-selection--single,
    #main-content .select2-container--default .select2-selection--multiple,
    .main-content .select2-container--default .select2-selection--single,
    .main-content .select2-container--default .select2-selection--multiple {
        min-height: 38px;
        border: 1px solid rgba(221, 227, 239, .95) !important;
        border-radius: var(--r, 8px) !important;
        background: rgba(255, 255, 255, .78) !important;
    }

    #main-content .btn:hover,
    #main-content .bella-btn:hover,
    #main-content .act-btn:hover,
    .main-content .btn:hover {
        transform: translateY(-1px);
    }

    #main-content .btn:active,
    #main-content .bella-btn:active,
    #main-content .act-btn:active,
    .main-content .btn:active {
        transform: scale(.985);
    }

    .modal-content,
    .dropdown-menu,
    .select2-dropdown,
    .datepicker,
    .daterangepicker {
        background: linear-gradient(145deg, rgba(255, 255, 255, .88), rgba(255, 255, 255, .68)) !important;
        border: 1px solid var(--ui-glass-border) !important;
        border-radius: var(--r, 8px) !important;
        box-shadow: 0 28px 80px rgba(15, 23, 42, .20), inset 0 1px 0 rgba(255, 255, 255, .68) !important;
        -webkit-backdrop-filter: blur(24px) saturate(180%);
        backdrop-filter: blur(24px) saturate(180%);
    }

    #topbar .tb-left {
        border-radius: 10px;
    }

    #topbar .tb-right {
        border-radius: 14px;
    }

    #topbar .tb-crumb {
        border-radius: 8px;
    }

    #topbar .tb-search,
    #topbar .tb-user-pill,
    #topbar .tb-icon-btn {
        border-radius: 10px;
    }

    #topbar .tb-user-pill-av {
        border-radius: 8px;
    }

    #topbar .tb-user-dropdown {
        border-radius: 12px;
    }

    @media (max-width: 768px) {
        #main-content .table-responsive,
        .main-content .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
            scroll-behavior: auto !important;
            transition-duration: .01ms !important;
        }
    }
</style>
@endonce
