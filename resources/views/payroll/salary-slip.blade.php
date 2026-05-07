<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 13px; color: #333; }
        .header { background: #2B5797; color: #fff; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .header h1 { font-size: 18px; }
        .header .sub { font-size: 11px; opacity: .8; }
        .slip-title { text-align: center; margin: 16px 0; font-size: 15px; font-weight: bold; text-transform: uppercase; color: #2B5797; border-bottom: 2px solid #2B5797; padding-bottom: 6px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; padding: 12px 0; border-bottom: 1px solid #e5e7eb; margin-bottom: 12px; }
        .info-item label { font-size: 10px; color: #6b7280; text-transform: uppercase; }
        .info-item span { font-weight: 600; display: block; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th { background: #f3f4f6; padding: 7px 10px; text-align: left; font-size: 11px; text-transform: uppercase; color: #6b7280; }
        td { padding: 7px 10px; border-bottom: 1px solid #f3f4f6; }
        .total-row td { font-weight: bold; background: #f9fafb; }
        .net-box { background: #2B5797; color: #fff; padding: 14px 20px; border-radius: 8px; text-align: center; margin: 10px 0; }
        .net-box .label { font-size: 11px; opacity: .8; }
        .net-box .amount { font-size: 22px; font-weight: bold; }
        .wps-row { display: flex; gap: 16px; margin-top: 12px; }
        .wps-box { flex: 1; border: 1px solid #e5e7eb; border-radius: 8px; padding: 10px; text-align: center; }
        .footer { margin-top: 20px; border-top: 1px solid #e5e7eb; padding-top: 12px; font-size: 11px; color: #9ca3af; text-align: center; }
        .container { padding: 0 20px; }
    </style>
</head>
<body>

<div class="header">
    <div>
        @if($company?->logo_url)
            <img src="{{ $company->logo_url }}" height="40" alt="Logo">
        @endif
        <h1>{{ $company?->company_name ?? config('app.name') }}</h1>
        <div class="sub">{{ $company?->company_address }}</div>
    </div>
    <div style="text-align:right">
        <div class="sub">Generated: {{ now()->format('d M Y') }}</div>
    </div>
</div>

<div class="container">
    <div class="slip-title">Salary Slip — {{ $record->month_label }}</div>

    <div class="info-grid">
        <div class="info-item">
            <label>Employee Name</label>
            <span>{{ $record->employee->full_name }}</span>
        </div>
        <div class="info-item">
            <label>Employee Code</label>
            <span>{{ $record->employee->employee_code }}</span>
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
            <span>{{ $record->employee->joining_date->format('d M Y') }}</span>
        </div>
        <div class="info-item">
            <label>Working Days / Present Days</label>
            <span>{{ $record->working_days }} / {{ $record->present_days }}</span>
        </div>
    </div>

    <!-- Earnings & Deductions side by side -->
    <table>
        <thead>
            <tr>
                <th>Earnings</th>
                <th style="text-align:right">Amount</th>
                <th>Deductions</th>
                <th style="text-align:right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Basic Salary</td>
                <td style="text-align:right">{{ number_format($record->basic_salary, 2) }}</td>
                <td>Food Deduction</td>
                <td style="text-align:right">{{ number_format($record->food_deduction, 2) }}</td>
            </tr>
            <tr>
                <td>Housing Allowance</td>
                <td style="text-align:right">{{ number_format($record->housing_allowance, 2) }}</td>
                <td>Visa Deduction</td>
                <td style="text-align:right">{{ number_format($record->visa_deduction, 2) }}</td>
            </tr>
            <tr>
                <td>Transport Allowance</td>
                <td style="text-align:right">{{ number_format($record->transport_allowance, 2) }}</td>
                <td>Insurance Deduction</td>
                <td style="text-align:right">{{ number_format($record->insurance_deduction, 2) }}</td>
            </tr>
            <tr>
                <td>Medical Allowance</td>
                <td style="text-align:right">{{ number_format($record->medical_allowance, 2) }}</td>
                <td>Advance Deduction</td>
                <td style="text-align:right">{{ number_format($record->advance_deduction, 2) }}</td>
            </tr>
            <tr>
                <td>Other Allowance</td>
                <td style="text-align:right">{{ number_format($record->other_allowance, 2) }}</td>
                <td>Other Deduction</td>
                <td style="text-align:right">{{ number_format($record->other_deduction, 2) }}</td>
            </tr>
            <tr>
                <td>Overtime ({{ $record->overtime_hours }} hrs)</td>
                <td style="text-align:right">{{ number_format($record->overtime_amount, 2) }}</td>
                <td></td><td></td>
            </tr>
            <tr class="total-row">
                <td>Gross Salary</td>
                <td style="text-align:right">{{ number_format($record->gross_salary, 2) }}</td>
                <td>Total Deductions</td>
                <td style="text-align:right">{{ number_format($record->total_deductions, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="net-box">
        <div class="label">NET SALARY</div>
        <div class="amount">{{ $company?->currency_symbol ?? 'AED' }} {{ number_format($record->net_salary, 2) }}</div>
    </div>

    <div class="wps-row">
        <div class="wps-box">
            <div style="font-size:10px;color:#6b7280">WPS First Transfer</div>
            <div style="font-weight:bold;font-size:15px">{{ number_format($record->wps_first_transfer, 2) }}</div>
        </div>
        <div class="wps-box">
            <div style="font-size:10px;color:#6b7280">WPS Second Transfer</div>
            <div style="font-weight:bold;font-size:15px">{{ number_format($record->wps_second_transfer, 2) }}</div>
        </div>
    </div>

    <div class="footer">
        This is a computer generated salary slip and does not require a signature.<br>
        {{ $company?->company_name }} — {{ $company?->company_email }}
    </div>
</div>

</body>
</html>
