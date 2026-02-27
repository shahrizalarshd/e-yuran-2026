<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resit Pembayaran #{{ $payment->payment_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #1a1a1a; padding: 20px; max-width: 800px; margin: 0 auto; font-size: 14px; }
        
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #16a34a; padding-bottom: 20px; }
        .header img { width: 80px; height: auto; margin: 0 auto 12px; }
        .header h1 { font-size: 20px; color: #16a34a; margin-bottom: 4px; }
        .header h2 { font-size: 24px; font-weight: 700; margin-bottom: 4px; }
        .header p { color: #666; font-size: 13px; }
        
        .status-badge { display: inline-block; padding: 6px 16px; border-radius: 20px; font-weight: 600; font-size: 13px; margin: 10px 0; }
        .status-success { background: #dcfce7; color: #166534; }
        .status-pending { background: #fef9c3; color: #854d0e; }
        .status-failed { background: #fee2e2; color: #991b1b; }
        
        .amount-box { text-align: center; padding: 20px; background: #f0fdf4; border: 2px solid #bbf7d0; border-radius: 12px; margin: 20px 0; }
        .amount-box .label { font-size: 13px; color: #666; margin-bottom: 4px; }
        .amount-box .amount { font-size: 32px; font-weight: 700; color: #166534; }
        
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin: 20px 0; }
        .info-item { padding: 12px; background: #f9fafb; border-radius: 8px; }
        .info-item .label { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
        .info-item .value { font-weight: 600; font-size: 14px; }
        
        .bills-section { margin: 24px 0; }
        .bills-section h3 { font-size: 16px; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #e5e7eb; }
        
        table { width: 100%; border-collapse: collapse; }
        thead th { background: #f3f4f6; padding: 10px 12px; text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #666; }
        tbody td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; }
        tbody tr:last-child td { border-bottom: none; }
        .text-right { text-align: right; }
        
        tfoot td { padding: 12px; font-weight: 700; font-size: 16px; border-top: 2px solid #e5e7eb; }
        
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; text-align: center; color: #999; font-size: 12px; }
        .footer p { margin-bottom: 4px; }
        
        .print-actions { text-align: center; margin-bottom: 20px; }
        .btn-print { padding: 10px 24px; background: #16a34a; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; margin-right: 8px; }
        .btn-print:hover { background: #15803d; }
        .btn-back { padding: 10px 24px; background: #e5e7eb; color: #374151; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; }
        .btn-back:hover { background: #d1d5db; }
        
        @media print {
            .print-actions { display: none !important; }
            body { padding: 0; }
            .amount-box { border: 2px solid #000; background: #f5f5f5; }
            .amount-box .amount { color: #000; }
            .status-badge { border: 1px solid #000; }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button class="btn-print" onclick="window.print()"><svg style="display:inline;vertical-align:middle;width:16px;height:16px;margin-right:4px" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4H7v4a2 2 0 002 2zm0-16h6a2 2 0 012 2v2H7V5a2 2 0 012-2z"/></svg> Cetak Resit</button>
        <a href="{{ route('admin.payments.show', $payment) }}" class="btn-back">← Kembali</a>
    </div>

    <div class="header">
        <img src="{{ asset('images/logo.png') }}" alt="PPTT">
        <h1>Persatuan Penduduk Taman Tropika Kajang</h1>
        <h2>Resit Pembayaran</h2>
        <p>{{ config('app.resident_portal_name', 'e-Yuran') }}</p>
    </div>

    <div style="text-align: center;">
        @if($payment->status === 'success')
            <span class="status-badge status-success">✓ Pembayaran Berjaya</span>
        @elseif($payment->status === 'pending')
            <span class="status-badge status-pending">⏳ Dalam Proses</span>
        @else
            <span class="status-badge status-failed">✗ Pembayaran Gagal</span>
        @endif
    </div>

    <div class="amount-box">
        <p class="label">Jumlah Dibayar</p>
        <p class="amount">RM {{ number_format($payment->amount, 2) }}</p>
    </div>

    <div class="info-grid">
        <div class="info-item">
            <p class="label">No. Resit</p>
            <p class="value">{{ $payment->payment_no }}</p>
        </div>
        @if($payment->toyyibpay_ref)
        <div class="info-item">
            <p class="label">No. Transaksi</p>
            <p class="value">{{ $payment->toyyibpay_ref }}</p>
        </div>
        @endif
        <div class="info-item">
            <p class="label">Tarikh Bayar</p>
            <p class="value">{{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : $payment->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <div class="info-item">
            <p class="label">Jenis Bayaran</p>
            <p class="value">{{ $payment->payment_type_text }}</p>
        </div>
        <div class="info-item">
            <p class="label">Alamat Rumah</p>
            <p class="value">{{ $payment->house->full_address ?? '-' }}</p>
        </div>
        <div class="info-item">
            <p class="label">Pembayar</p>
            <p class="value">{{ $payment->resident->name ?? '-' }}</p>
        </div>
        <div class="info-item">
            <p class="label">Kaedah Bayaran</p>
            <p class="value">{{ $payment->payment_method ?? 'ToyyibPay (FPX)' }}</p>
        </div>
    </div>

    <div class="bills-section">
        <h3>Bil yang Dibayar</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>No. Bil</th>
                    <th>Tempoh</th>
                    <th class="text-right">Amaun (RM)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payment->bills as $index => $bill)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $bill->bill_no }}</td>
                    <td>{{ $bill->bill_period }}</td>
                    <td class="text-right">{{ number_format($bill->pivot->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">Jumlah</td>
                    <td class="text-right">RM {{ number_format($payment->amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="footer">
        <p><strong>Ini adalah resit janaan komputer.</strong></p>
        <p>Tidak memerlukan tandatangan.</p>
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
