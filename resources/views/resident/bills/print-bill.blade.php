<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penyata Bil #{{ $bill->bill_no }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #1a1a1a; padding: 20px; max-width: 800px; margin: 0 auto; font-size: 14px; }
        
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #16a34a; padding-bottom: 20px; }
        .header img { width: 80px; height: auto; margin: 0 auto 12px; }
        .header h1 { font-size: 20px; color: #16a34a; margin-bottom: 4px; }
        .header h2 { font-size: 24px; font-weight: 700; margin-bottom: 4px; }
        .header p { color: #666; font-size: 13px; }
        
        .status-badge { display: inline-block; padding: 6px 16px; border-radius: 20px; font-weight: 600; font-size: 13px; margin: 10px 0; }
        .status-paid { background: #dcfce7; color: #166534; }
        .status-unpaid { background: #fee2e2; color: #991b1b; }
        .status-partial { background: #fff7ed; color: #9a3412; }
        .status-processing { background: #fef9c3; color: #854d0e; }
        
        .amount-box { text-align: center; padding: 20px; border-radius: 12px; margin: 20px 0; }
        .amount-paid { background: #f0fdf4; border: 2px solid #bbf7d0; }
        .amount-paid .amount { color: #166534; }
        .amount-unpaid { background: #fef2f2; border: 2px solid #fecaca; }
        .amount-unpaid .amount { color: #991b1b; }
        .amount-box .label { font-size: 13px; color: #666; margin-bottom: 4px; }
        .amount-box .amount { font-size: 32px; font-weight: 700; }
        
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin: 20px 0; }
        .info-item { padding: 12px; background: #f9fafb; border-radius: 8px; }
        .info-item .label { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
        .info-item .value { font-weight: 600; font-size: 14px; }
        
        .payment-section { margin: 24px 0; }
        .payment-section h3 { font-size: 16px; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #e5e7eb; }
        
        table { width: 100%; border-collapse: collapse; }
        thead th { background: #f3f4f6; padding: 10px 12px; text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #666; }
        tbody td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; }
        .text-right { text-align: right; }
        
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
            .amount-box { border: 2px solid #000; }
            .amount-box .amount { color: #000; }
        }
    </style>
</head>
<body>
    <div class="print-actions">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Bil</button>
        <a href="{{ route('resident.bills.index') }}" class="btn-back">← Kembali</a>
    </div>

    <div class="header">
        <img src="{{ asset('images/logo.png') }}" alt="PPTT">
        <h1>Persatuan Penduduk Taman Tropika Kajang</h1>
        <h2>Penyata Bil</h2>
        <p>{{ config('app.resident_portal_name', 'e-Yuran') }}</p>
    </div>

    <div style="text-align: center;">
        @if($bill->status === 'paid')
            <span class="status-badge status-paid">✓ Telah Dibayar</span>
        @elseif($bill->status === 'unpaid')
            <span class="status-badge status-unpaid">✗ Belum Dibayar</span>
        @elseif($bill->status === 'partial')
            <span class="status-badge status-partial">◐ Bayaran Separa</span>
        @elseif($bill->status === 'processing')
            <span class="status-badge status-processing">⏳ Dalam Proses</span>
        @endif
    </div>

    <div class="amount-box {{ $bill->status === 'paid' ? 'amount-paid' : 'amount-unpaid' }}">
        <p class="label">{{ $bill->status === 'paid' ? 'Jumlah Dibayar' : 'Jumlah Perlu Dibayar' }}</p>
        <p class="amount">RM {{ number_format($bill->amount, 2) }}</p>
    </div>

    <div class="info-grid">
        <div class="info-item">
            <p class="label">No. Bil</p>
            <p class="value">{{ $bill->bill_no }}</p>
        </div>
        <div class="info-item">
            <p class="label">Tempoh Bil</p>
            <p class="value">{{ $bill->bill_period }}</p>
        </div>
        <div class="info-item">
            <p class="label">Tarikh Akhir</p>
            <p class="value">{{ $bill->due_date->format('d/m/Y') }}</p>
        </div>
        <div class="info-item">
            <p class="label">Status</p>
            <p class="value">
                @if($bill->status === 'paid')
                    Dibayar
                @elseif($bill->status === 'unpaid')
                    Belum Dibayar
                @elseif($bill->status === 'partial')
                    Separa (RM {{ number_format($bill->paid_amount, 2) }})
                @else
                    {{ ucfirst($bill->status) }}
                @endif
            </p>
        </div>
        <div class="info-item">
            <p class="label">Alamat Rumah</p>
            <p class="value">{{ $bill->house->full_address ?? '-' }}</p>
        </div>
        @if($bill->paid_at)
        <div class="info-item">
            <p class="label">Tarikh Dibayar</p>
            <p class="value">{{ $bill->paid_at->format('d/m/Y') }}</p>
        </div>
        @endif
    </div>

    @if($bill->payments->isNotEmpty())
    <div class="payment-section">
        <h3>Sejarah Pembayaran</h3>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>No. Resit</th>
                    <th>Tarikh</th>
                    <th>Status</th>
                    <th class="text-right">Amaun (RM)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bill->payments as $index => $payment)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $payment->payment_no }}</td>
                    <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        @if($payment->status === 'success') Berjaya
                        @elseif($payment->status === 'pending') Dalam Proses
                        @else Gagal
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($payment->pivot->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="footer">
        <p><strong>Ini adalah penyata bil janaan komputer.</strong></p>
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
