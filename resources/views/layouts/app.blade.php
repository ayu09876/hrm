{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle ?? 'HRM' }} · HRM System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
    :root {
        --sw: 252px;
        --sb: #0f172a;
        --sb2: #1e293b;
        --st: #94a3b8;
        --acc: #10b981;
        --acc2: #059669;
        --pg: #f1f5f9;
        --wh: #ffffff;
        --t1: #0f172a;
        --t2: #64748b;
        --bd: #e2e8f0;
        --fn: 'DM Sans', sans-serif;
        --mo: 'JetBrains Mono', monospace;
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    body {
        font-family: var(--fn);
        background: var(--pg);
        color: var(--t1);
        min-height: 100vh;
        display: flex;
        margin: 0;
    }

    /* ─── Sidebar ─── */
    .sidebar {
        width: var(--sw);
        min-height: 100vh;
        background: var(--sb);
        position: fixed;
        top: 0;
        left: 0;
        display: flex;
        flex-direction: column;
        z-index: 200;
    }

    .sb-brand {
        padding: 26px 20px 22px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        display: flex;
        align-items: center;
        gap: 11px;
    }

    .sb-icon {
        width: 34px;
        height: 34px;
        background: var(--acc);
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        color: #fff;
        flex-shrink: 0;
    }

    .sb-name {
        font-size: 14px;
        font-weight: 700;
        color: #f8fafc;
        letter-spacing: -0.025em;
        line-height: 1.2;
    }

    .sb-sub {
        font-size: 10px;
        color: #475569;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        font-weight: 500;
    }

    .sb-nav {
        padding: 14px 10px;
        flex: 1;
    }

    .sb-section {
        font-size: 10px;
        font-weight: 600;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #334155;
        padding: 0 10px;
        margin: 16px 0 5px;
    }

    .sb-nav .nav-link {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 8px 10px;
        border-radius: 7px;
        color: var(--st);
        font-size: 13.5px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.12s;
        margin-bottom: 1px;
    }

    .sb-nav .nav-link i {
        font-size: 15px;
        width: 17px;
    }

    .sb-nav .nav-link:hover {
        background: var(--sb2);
        color: #e2e8f0;
    }

    .sb-nav .nav-link.active {
        background: rgba(16, 185, 129, 0.14);
        color: var(--acc);
    }

    .sb-foot {
        padding: 14px 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.05);
        font-family: var(--mo);
        font-size: 10px;
        color: #334155;
    }

    /* ─── Main ─── */
    .main-wrap {
        margin-left: var(--sw);
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    .topbar {
        background: var(--wh);
        border-bottom: 1px solid var(--bd);
        padding: 0 30px;
        height: 58px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        z-index: 100;
    }

    .topbar-title {
        font-size: 15.5px;
        font-weight: 600;
        color: var(--t1);
        letter-spacing: -0.02em;
    }

    .topbar-date {
        font-family: var(--mo);
        font-size: 11px;
        color: var(--t2);
    }

    .page-body {
        padding: 26px 30px;
        flex: 1;
    }

    /* ─── Alerts ─── */
    .flash {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 11px 15px;
        border-radius: 9px;
        font-size: 13.5px;
        margin-bottom: 20px;
    }

    .flash-ok {
        background: #d1fae5;
        border: 1px solid #6ee7b7;
        color: #065f46;
    }

    .flash-err {
        background: #fee2e2;
        border: 1px solid #fca5a5;
        color: #991b1b;
    }

    /* ─── Cards ─── */
    .card {
        background: var(--wh);
        border: 1px solid var(--bd);
        border-radius: 12px;
        box-shadow: 0 1px 4px rgba(15, 23, 42, 0.05);
        overflow: hidden;
    }

    .card-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 22px;
        border-bottom: 1px solid var(--bd);
    }

    .card-top h5 {
        font-size: 14.5px;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* ─── Table ─── */
    .table {
        font-size: 13.5px;
        margin: 0;
    }

    .table thead th {
        font-size: 10.5px;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--t2);
        background: #f8fafc;
        border-bottom: 1px solid var(--bd);
        padding: 11px 16px;
        white-space: nowrap;
    }

    .table tbody td {
        padding: 12px 16px;
        vertical-align: middle;
        border-color: #f1f5f9;
        color: var(--t1);
    }

    .table tbody tr:hover td {
        background: #fafbfd;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }

    /* ─── Badges ─── */
    .tag {
        display: inline-block;
        padding: 2px 9px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 500;
    }

    .tag-dept {
        background: #ede9fe;
        color: #6d28d9;
    }

    .tag-count {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
    }

    .tag-male {
        background: #dbeafe;
        color: #1e40af;
    }

    .tag-female {
        background: #fce7f3;
        color: #9d174d;
    }

    .tag-valid {
        background: #ecfdf5;
        color: #065f46;
    }

    .tag-invalid {
        background: #fef2f2;
        color: #991b1b;
    }

    /* ─── Mono ─── */
    .mono {
        font-family: var(--mo);
        font-size: 12px;
    }

    /* ─── Action buttons ─── */
    .act {
        width: 28px;
        height: 28px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        border: none;
        cursor: pointer;
        transition: all 0.12s;
        text-decoration: none;
    }

    .act-view {
        background: #eff6ff;
        color: #2563eb;
    }

    .act-view:hover {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .act-edit {
        background: #f0fdf4;
        color: #15803d;
    }

    .act-edit:hover {
        background: #dcfce7;
        color: #166534;
    }

    .act-del {
        background: #fef2f2;
        color: #dc2626;
    }

    .act-del:hover {
        background: #fee2e2;
        color: #b91c1c;
    }

    /* ─── Primary button ─── */
    .btn-p {
        background: var(--acc);
        color: #fff;
        border: none;
        padding: 8px 15px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        font-family: var(--fn);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        transition: background 0.12s;
    }

    .btn-p:hover {
        background: var(--acc2);
        color: #fff;
    }

    .btn-s {
        background: transparent;
        color: var(--t2);
        border: 1px solid var(--bd);
        padding: 7px 14px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        font-family: var(--fn);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        transition: all 0.12s;
    }

    .btn-s:hover {
        background: #f8fafc;
        color: var(--t1);
        border-color: #cbd5e1;
    }

    .btn-danger {
        background: #dc2626;
        color: #fff;
        border: none;
        padding: 8px 15px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        font-family: var(--fn);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: background 0.12s;
    }

    .btn-danger:hover {
        background: #b91c1c;
        color: #fff;
    }

    /* ─── Modal ─── */
    .modal-content {
        border: none;
        border-radius: 14px;
        box-shadow: 0 24px 64px rgba(15, 23, 42, 0.18);
    }

    .modal-header {
        padding: 18px 22px 14px;
        border-bottom: 1px solid var(--bd);
    }

    .modal-title {
        font-size: 14.5px;
        font-weight: 600;
    }

    .modal-body {
        padding: 18px 22px;
    }

    .modal-footer {
        padding: 13px 22px;
        border-top: 1px solid var(--bd);
        gap: 8px;
    }

    /* ─── Form ─── */
    .form-label {
        font-size: 11.5px;
        font-weight: 600;
        color: var(--t2);
        letter-spacing: 0.03em;
        margin-bottom: 4px;
    }

    .form-control,
    .form-select {
        font-family: var(--fn);
        font-size: 13.5px;
        border-color: var(--bd);
        border-radius: 8px;
        padding: 8px 12px;
        color: var(--t1);
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--acc);
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.13);
    }

    .form-control.is-invalid {
        border-color: #ef4444;
    }

    .invalid-feedback {
        font-size: 11.5px;
    }

    /* ─── File drop ─── */
    .drop-zone {
        border: 2px dashed var(--bd);
        border-radius: 10px;
        padding: 30px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.15s;
    }

    .drop-zone:hover,
    .drop-zone.over {
        border-color: var(--acc);
        background: rgba(16, 185, 129, 0.04);
    }

    .drop-zone i {
        font-size: 26px;
        color: var(--t2);
        display: block;
        margin-bottom: 7px;
    }

    .drop-zone p {
        font-size: 13px;
        color: var(--t2);
        margin: 0;
    }

    .drop-zone .picked {
        color: var(--acc);
        font-weight: 500;
        margin-top: 7px;
        font-size: 13px;
    }

    /* ─── Empty state ─── */
    .empty {
        text-align: center;
        padding: 56px 20px;
        color: var(--t2);
    }

    .empty i {
        font-size: 38px;
        opacity: 0.25;
        display: block;
        margin-bottom: 10px;
    }

    .empty p {
        font-size: 13.5px;
        margin: 0;
    }

    /* ─── Detail view ─── */
    .drow {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
        font-size: 13.5px;
        align-items: flex-start;
    }

    .drow:last-child {
        border-bottom: none;
    }

    .dlabel {
        width: 155px;
        min-width: 155px;
        font-size: 11px;
        font-weight: 600;
        color: var(--t2);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        padding-top: 1px;
    }

    .dvalue {
        flex: 1;
        color: var(--t1);
    }

    /* ─── Pagination ─── */
    .pg-wrap {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 22px;
        border-top: 1px solid var(--bd);
        font-size: 12.5px;
        color: var(--t2);
    }

    .pagination {
        margin: 0;
    }

    .pagination .page-link {
        font-size: 12.5px;
        color: var(--t2);
        border-color: var(--bd);
        padding: 5px 10px;
    }

    .pagination .page-item.active .page-link {
        background: var(--acc);
        border-color: var(--acc);
        color: #fff;
    }

    .pagination .page-link:hover {
        background: #f1f5f9;
        color: var(--t1);
    }

    /* ─── Preview table row colors ─── */
    tr.row-ok td {
        background: #f0fdf4 !important;
    }

    tr.row-bad td {
        background: #fef2f2 !important;
    }
    </style>
    @stack('styles')
</head>

<body>

    <aside class="sidebar">
        <div class="sb-brand">
            <div class="sb-icon"><i class="bi bi-people-fill"></i></div>
            <div>
                <div class="sb-name">HRM System</div>
                <div class="sb-sub">Human Resources</div>
            </div>
        </div>

        <nav class="sb-nav">
            <div class="sb-section">Main</div>
            <a href="{{ route('departments.index') }}"
                class="nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                <i class="bi bi-diagram-3-fill"></i> Departments
            </a>
            <a href="{{ route('employees.index') }}"
                class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge-fill"></i> Employees
            </a>
            <a href="{{ route('attendances.index') }}"
                class="nav-link {{ request()->routeIs('attendances.*') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i> Attendance
            </a>
        </nav>

        <div class="sb-foot">v1.0 &middot; HRM System</div>
    </aside>

    <div class="main-wrap">
        <div class="topbar">
            <span class="topbar-title">{{ $pageTitle ?? 'Dashboard' }}</span>
            <span class="topbar-date">{{ now()->format('D, d M Y') }}</span>
        </div>

        <div class="page-body">
            @if (session('success'))
            <div class="flash flash-ok"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
            @endif
            @if (session('error'))
            <div class="flash flash-err"><i class="bi bi-exclamation-circle-fill"></i> {{ session('error') }}</div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>

</html>