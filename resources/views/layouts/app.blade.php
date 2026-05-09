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
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
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