<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PayRoll Manager') — {{ config('app.name') }}</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --primary: #2B5797;
            --primary-dark: #1e3f6e;
            --primary-light: #3a7bd5;
            --success: #10B981;
            --danger: #EF4444;
            --warning: #F59E0B;
            --spacing-xs: 0.5rem;
            --spacing-sm: 1rem;
            --spacing-md: 1.5rem;
            --spacing-lg: 2rem;
            --border-radius: 12px;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.1);
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: 1px solid rgba(255, 255, 255, 0.2);
            --font-title: 700 1.75rem / 1.3 'Segoe UI', sans-serif;
            --font-body: 400 1rem / 1.5 'Segoe UI', sans-serif;
        }

        body {
            background: #f4f6fb;
            font-family: 'Segoe UI', sans-serif;
        }

        /* Sidebar */
        #sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--primary);
            position: fixed;
            left: 0;
            top: 0;
            z-index: 100;
            transition: transform .3s ease;
        }

        #sidebar .brand {
            padding: 1.2rem 1.5rem;
            background: var(--primary-dark);
            color: #fff;
            font-size: 1.1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        #sidebar .nav-link {
            color: rgba(255, 255, 255, .8);
            padding: .55rem 1.5rem;
            display: flex;
            align-items: center;
            gap: .7rem;
            font-size: .9rem;
            border-radius: 0;
            transition: background .2s;
        }

        #sidebar .nav-link:hover,
        #sidebar .nav-link.active {
            background: rgba(255, 255, 255, .15);
            color: #fff;
        }

        #sidebar .nav-section {
            padding: .8rem 1.5rem .3rem;
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: rgba(255, 255, 255, .5);
        }

        /* Main content */
        #main {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }

        .topbar {
            background: #fff;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .08);
            padding: .6rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 99;
        }

        .page-content {
            padding: 1.5rem;
        }

        /* Cards */
        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .07);
            transition: transform .2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }

        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-md);
            backdrop-filter: blur(10px);
            background: var(--glass-bg);
            border: var(--glass-border);
            transition: all 0.2s ease;
        }

        .card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .card-header {
            border-radius: var(--border-radius) var(--border-radius) 0 0 !important;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.9);
            font-size: 1.1rem;
        }

        /* Badges */
        .badge-active {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-inactive {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-processed {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-paid {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-draft {
            background: #f3f4f6;
            color: #374151;
        }

        .badge-approved {
            background: #ede9fe;
            color: #5b21b6;
        }

        @media (max-width: 768px) {
            #sidebar {
                transform: translateX(-100%);
            }

            #sidebar.open {
                transform: translateX(0);
            }

            #main {
                margin-left: 0;
            }
        }
    

    /* Modern Form Styles */
    .form-floating > label {
    color: #6b7280;
    font-weight: 500;
    padding-left: 0.75rem;
    }
    .form-control, .form-select {
    border-radius: var(--border-radius);
    border: 2px solid #e5e7eb;
    padding: 0.875rem 1rem;
    font-size: 1rem;
    transition: all 0.2s ease;
    background: rgba(255,255,255,0.9);
    }
    .form-control:focus, .form-select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.25rem rgba(43,87,151,0.15);
    background: #fff;
    transform: translateY(-1px);
    }
    .btn-primary {
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    border: none;
    border-radius: calc(var(--border-radius) / 1.5);
    font-weight: 600;
    padding: 0.75rem 1.5rem;
    transition: all 0.2s ease;
    box-shadow: var(--shadow-sm);
    }
    .btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
    }
    .upload-zone {
    border: 3px dashed #d1d5db;
    border-radius: var(--border-radius);
    padding: var(--spacing-lg);
    text-align: center;
    transition: all 0.2s ease;
    background: rgba(255,255,255,0.7);
    cursor: pointer;
    }
    .upload-zone.dragover {
    border-color: var(--primary);
    background: rgba(43,87,151,0.05);
    }
    h1, h2, h3 { font: var(--font-title); color: #1f2937; }
    .page-content { padding: var(--spacing-lg); }
</style>
    @stack('styles')
</head>

<body>

    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="brand">
            <i class="bi bi-cash-stack"></i> PayRoll Manager
        </div>

        <div class="nav-section">Main</div>
        <a href="{{ route('dashboard') }}" class="nav-link @active('/')">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        @if (auth()->user()->canManageEmployees())
            <div class="nav-section">Employees</div>
            <a href="{{ route('employees.index') }}" class="nav-link @active('employees*')">
                <i class="bi bi-people"></i> All Employees
            </a>
            <a href="{{ route('employees.create') }}" class="nav-link">
                <i class="bi bi-person-plus"></i> Add Employee
            </a>
            <a href="{{ route('employees.import.form') }}" class="nav-link">
                <i class="bi bi-upload"></i> Bulk Import
            </a>
        @endif

        @if (auth()->user()->canProcessPayroll())
            <div class="nav-section">Payroll</div>
            <a href="{{ route('payroll.process') }}" class="nav-link">
                <i class="bi bi-calculator"></i> Process Payroll
            </a>
            <a href="{{ route('payroll.bulk') }}" class="nav-link">
                <i class="bi bi-lightning"></i> Bulk Payroll
            </a>
        @endif

        <a href="{{ route('payroll.history') }}" class="nav-link">
            <i class="bi bi-clock-history"></i> Payroll History
        </a>

        @if (auth()->user()->canViewReports())
            <div class="nav-section">Reports</div>
            <a href="{{ route('payroll.reports') }}" class="nav-link">
                <i class="bi bi-bar-chart"></i> Payroll Reports
            </a>
            <a href="{{ route('payroll.wps') }}" class="nav-link">
                <i class="bi bi-bank"></i> WPS Report
            </a>
        @endif

        <div class="nav-section">Advances</div>
        <a href="{{ route('advances.index') }}" class="nav-link">
            <i class="bi bi-cash-coin"></i> Advance Payments
        </a>

        @if (auth()->user()->isAdmin())
            <div class="nav-section">Administration</div>
            <a href="{{ route('users.index') }}" class="nav-link">
                <i class="bi bi-shield-person"></i> Users
            </a>
            <a href="{{ route('settings') }}" class="nav-link">
                <i class="bi bi-gear"></i> Settings
            </a>
            <a href="{{ route('activity-logs') }}" class="nav-link">
                <i class="bi bi-journal-text"></i> Activity Logs
            </a>
        @endif
    </nav>

    <!-- Main -->
    <div id="main">
        <!-- Topbar -->
        <div class="topbar d-flex align-items-center justify-content-between">
            <button class="btn btn-sm d-md-none me-2"
                onclick="document.getElementById('sidebar').classList.toggle('open')">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div class="fw-semibold text-muted">@yield('title', 'Dashboard')</div>
            <div class="d-flex align-items-center gap-3">
                <span class="badge bg-light text-dark border">
                    <i class="bi bi-person-circle me-1"></i>
                    {{ auth()->user()->name }}
                    <span class="ms-1 text-muted small">({{ auth()->user()->role_label }})</span>
                </span>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Alerts -->
        <div class="page-content pb-0">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>

        <!-- Page Content -->
        <div class="page-content">
            @yield('content')
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>

    @stack('scripts')
</body>

</html>
