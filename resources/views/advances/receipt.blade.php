<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Advance Receipt #{{ $advance->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 0; padding: 24px; color: #111827; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #e5e7eb; padding-bottom: 12px; }
        .title { font-size: 20px; font-weight: 700; }
        .muted { color: #6b7280; font-size: 12px; }
        .box { border: 1px solid #e5e7eb; border-radius: 10px; padding: 16px; margin-top: 16px; }
        .row { display: flex; justify-content: space-between; gap: 16px; margin-top: 8px; }
        .label { color: #6b7280; font-size: 12px; }
        .value { font-size: 13px; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #e5e7eb; padding: 10px; text-align: left; font-size: 13px; }
        th { background: #f9fafb; font-weight: 700; }
        .footer { margin-top: 18px; font-size: 12px; color: #6b7280; }
        .sign { margin-top: 28px; display: flex; justify-content: flex-end; gap: 72px; }
        .sign .col { text-align: center; }
        .sign .line { height: 40px; border-bottom: 1px solid #d1d5db; margin-bottom: 6px; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="title">Advance Receipt</div>
            <div class="muted">Receipt ID: #{{ $advance->id }}</div>
        </div>
        <div style="text-align:right">
            <div class="muted">Generated: {{ now()->format('d/m/Y') }}</div>
            <div class="muted">Status: {{ ucfirst($advance->status ?? 'pending') }}</div>
        </div>
    </div>

    <div class="box">
        <div class="row">
            <div>
                <div class="label">Employee</div>
                <div class="value">{{ $advance->employee?->full_name ?? '-' }}</div>
                <div class="muted">Code: {{ $advance->employee?->employee_code ?? '-' }}</div>
                <div class="muted">Department: {{ $advance->employee?->department?->name ?? '-' }}</div>
            </div>
            <div>
                <div class="label">Advance Date</div>
                <div class="value">{{ optional($advance->advance_date)->format('d/m/Y') ?? '-' }}</div>

                <div style="margin-top:10px">
                    <div class="label">Total Amount</div>
                    <div class="value">{{ number_format((float) $advance->amount, 2) }}</div>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Amount</th>
                    <th>Installment</th>
                    <th>Total Installments</th>
                    <th>Pending</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ number_format((float) $advance->amount, 2) }}</td>
                    <td>{{ number_format((float) $advance->installment_amount, 2) }}</td>
                    <td>{{ (int) $advance->total_installments }}</td>
                    <td>{{ number_format((float) $advance->pending_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        @if(!empty($advance->reason))
            <div style="margin-top:12px">
                <div class="label">Reason</div>
                <div class="value">{{ $advance->reason }}</div>
            </div>
        @endif
    </div>

    <div class="footer">
        This receipt is system-generated automatically when the advance is recorded.
    </div>

    <div class="sign">
        <div class="col">
            <div class="line"></div>
            <div class="muted">Authorized Signature</div>
        </div>
    </div>
</body>
</html>

