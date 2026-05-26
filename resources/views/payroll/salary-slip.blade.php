<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Salary Slip</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Inter', system-ui, -apple-system, Arial, sans-serif;
            font-size: 13px;
            color: #1e293b;
            background: #f1f5f9;
            padding: 24px;
        }

        /* Main Container */
        .slip-container {
            max-width: 1100px;
            margin: 0 auto;
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        /* Header Section */
        .header {
            background: linear-gradient(135deg, #0f3b5f 0%, #1a4d7a 100%);
            color: white;
            padding: 28px 32px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .logo-placeholder {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .company-info h1 {
            font-size: 20px;
            font-weight: 700;
            letter-spacing: -0.3px;
            margin-bottom: 4px;
        }

        .company-info .sub {
            font-size: 11px;
            opacity: 0.8;
            font-weight: 400;
        }

        .header-right {
            text-align: right;
        }

        .slip-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 14px;
            border-radius: 40px;
            font-size: 11px;
            font-weight: 500;
            margin-bottom: 8px;
            display: inline-block;
        }

        .gen-date {
            font-size: 11px;
            opacity: 0.8;
        }

        /* Slip Title */
        .slip-title {
            text-align: center;
            margin: 24px 32px 20px 32px;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.2px;
            text-transform: uppercase;
            color: #0f3b5f;
            border-bottom: 3px solid #e2e8f0;
            padding-bottom: 14px;
        }

        /* Content Padding */
        .container {
            padding: 0 32px 32px 32px;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px 28px;
            background: #f8fafc;
            padding: 20px 24px;
            border-radius: 20px;
            margin-bottom: 28px;
            border: 1px solid #eef2ff;
        }

        .info-item label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
        }

        .info-item span {
            font-weight: 700;
            font-size: 14px;
            color: #0f172a;
            display: block;
        }

        /* Table Styles */
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .salary-table th {
            background: #f1f5f9;
            padding: 14px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
        }

        .salary-table td {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 13px;
            color: #334155;
        }

        .salary-table tr:last-child td {
            border-bottom: none;
        }

        .salary-table .total-row td {
            background: #fefce8;
            font-weight: 800;
            color: #854d0e;
            border-top: 1px solid #fde047;
            border-bottom: none;
        }

        .text-right {
            text-align: right;
            font-weight: 600;
        }

        /* Net Salary Box */
        .net-box {
            background: linear-gradient(135deg, #0f3b5f 0%, #1e4d76 100%);
            color: white;
            padding: 22px 28px;
            border-radius: 20px;
            text-align: center;
            margin-bottom: 24px;
            box-shadow: 0 10px 15px -8px rgba(15, 59, 95, 0.3);
        }

        .net-box .label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 500;
            opacity: 0.85;
            margin-bottom: 6px;
        }

        .net-box .amount {
            font-size: 34px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        /* WPS Row */
        .wps-row {
            display: flex;
            gap: 20px;
            margin-bottom: 28px;
        }

        .wps-box {
            flex: 1;
            background: #fefce8;
            border: 1px solid #fde047;
            border-radius: 16px;
            padding: 16px 20px;
            text-align: center;
            transition: all 0.2s;
        }

        .wps-box div:first-child {
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            color: #854d0e;
            letter-spacing: 0.8px;
            margin-bottom: 8px;
        }

        .wps-box div:last-child {
            font-weight: 800;
            font-size: 20px;
            color: #3f6212;
        }

        /* Footer */
        .footer {
            margin-top: 16px;
            border-top: 1px solid #e2e8f0;
            padding-top: 20px;
            font-size: 10px;
            color: #94a3b8;
            text-align: center;
            letter-spacing: 0.3px;
        }

        /* Empty cell fix */
        .empty-cell {
            background: transparent;
        }

        /* Responsive */
        @media print {
            body {
                background: white;
                padding: 0;
                margin: 0;
            }

            .slip-container {
                box-shadow: none;
                border-radius: 0;
            }

            .header {
                background: #0f3b5f;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .net-box {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }

        /* Additional polish */
        .badge-icon {
            display: inline-block;
            background: #eef2ff;
            border-radius: 8px;
            padding: 2px 8px;
            font-size: 10px;
            font-weight: 600;
            color: #0f3b5f;
        }

        .currency-symbol {
            font-weight: 600;
            margin-right: 2px;
        }
    </style>
</head>

<body>

    <div class="slip-container">
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="logo-placeholder">
                    @if ($company?->logo_url)
                        <img src="{{ $company->logo_url }}" height="32" alt="Logo">
                    @else
                        📄
                    @endif
                </div>
                <div class="company-info">
                    <h1>{{ $company?->company_name ?? config('app.name') }}</h1>
                    <div class="sub">{{ $company?->company_address ?? 'Company Address' }}</div>
                </div>
            </div>
            <div class="header-right">
                <div class="slip-badge">SALARY SLIP</div>
                <div class="gen-date">Generated: {{ now()->format('d M Y') }}</div>
            </div>
        </div>

        <div class="container">
            <!-- Title with Month -->
            <div class="slip-title">
                {{ $record->month_label ?? ucfirst($record->payroll_month->format('F Y')) }}
            </div>

            <!-- Employee Info Grid -->
            <div class="info-grid">
                <div class="info-item">
                    <label>Employee Name</label>
                    <span>{{ $record->employee->full_name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <label>Employee Code</label>
                    <span>{{ $record->employee->employee_code ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <label>Department</label>
                    <span>{{ $record->employee->department?->name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <label>Designation</label>
                    <span>{{ $record->employee->designation?->name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <label>Joining Date</label>
                    <span>{{ $record->employee->joining_date ? $record->employee->joining_date->format('d M Y') : 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <label>Attendance</label>
                    <span>{{ $record->working_days ?? '-' }} / {{ $record->present_days ?? '-' }} days</span>
                </div>
            </div>

            <!-- Earnings & Deductions Table -->
            <table class="salary-table">
                <thead>
                    <tr>
                        <th>Earnings</th>
                        <th class="text-right">Amount</th>
                        <th>Deductions</th>
                        <th class="text-right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Row 1 -->
                    <tr>
                        <td>Basic Salary</td>
                        <td class="text-right">{{ number_format($record->basic_salary, 2) }}</td>
                        <td>Food Deduction</td>
                        <td class="text-right">{{ number_format($record->food_deduction, 2) }}</td>
                    </tr>
                   
                   
                    <!-- Row 5 -->
                    <tr>
                        <td>Other Allowance</td>
                        <td class="text-right">{{ number_format($record->other_allowance, 2) }}</td>
                        <td>Other Deduction</td>
                        <td class="text-right">{{ number_format($record->other_deduction, 2) }}</td>
                    </tr>
                    <!-- Row 6 (Overtime) -->
                    <tr>
                        <td>Overtime <span class="badge-icon">{{ $record->overtime_hours ?? 0 }} hrs</span></td>
                        <td class="text-right">{{ number_format($record->overtime_amount, 2) }}</td>
                        <td></td>
                        <td class="text-right"></td>
                    </tr>
                    <!-- Totals Row -->
                    <tr class="total-row">
                        <td><strong>Gross Salary</strong></td>
                        <td class="text-right"><strong>{{ number_format($record->gross_salary, 2) }}</strong></td>
                        <td><strong>Total Deductions</strong></td>
                        <td class="text-right"><strong>{{ number_format($record->total_deductions, 2) }}</strong></td>
                    </tr>
                </tbody>
            </table>

            <!-- Net Salary Box -->
            <div class="net-box">
                <div class="label">NET PAYABLE</div>
                <div class="amount">
                    <span class="currency-symbol">{{ $company?->currency_symbol ?? 'AED' }}</span>
                    {{ number_format($record->net_salary, 2) }}
                </div>
            </div>

            <!-- WPS Transfers -->
            <div class="wps-row">
                <div class="wps-box">
                    <div>🏦 WPS First Transfer</div>
                    <div>{{ number_format($record->wps_first_transfer, 2) }}</div>
                </div>
                <div class="wps-box">
                    <div>🏦 WPS Second Transfer</div>
                    <div>{{ number_format($record->wps_second_transfer, 2) }}</div>
                </div>
            </div>



            <!-- Footer -->
            <div class="footer">
                This is a computer generated salary slip and does not require a signature.<br>
                {{ $company?->company_name ?? config('app.name') }} —
                {{ $company?->company_email ?? 'support@company.com' }}
            </div>
        </div>
    </div>

    <script>
        (function() {
            const btn = document.getElementById('aiExplainBtn');
            const q = document.getElementById('aiQuestion');
            const ans = document.getElementById('aiAnswer');
            const status = document.getElementById('aiStatus');


