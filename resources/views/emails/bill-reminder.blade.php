<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peringatan Yuran Bulanan</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header img {
            height: 80px;
            width: auto;
            margin-bottom: 15px;
            background: white;
            padding: 10px;
            border-radius: 10px;
        }
        .content {
            padding: 30px;
        }
        .amount-box {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            padding: 25px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
            border: 2px solid #f59e0b;
        }
        .amount {
            font-size: 36px;
            font-weight: bold;
            color: #b45309;
        }
        .bills-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .bills-table th {
            background: #f3f4f6;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #e5e7eb;
        }
        .bills-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .bills-table tr:hover {
            background: #f9fafb;
        }
        .status-unpaid {
            display: inline-block;
            background: #fef3c7;
            color: #b45309;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: white;
            padding: 14px 35px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
        }
        .button:hover {
            background: linear-gradient(135deg, #047857 0%, #059669 100%);
        }
        .info-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 13px;
            padding: 25px;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }
        .footer img {
            height: 40px;
            width: auto;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/logo.png') }}" alt="PPTTK">
            <h1 style="margin: 0; font-size: 24px;">📋 Peringatan Yuran Bulanan</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">Persatuan Penduduk Taman Tropika Kajang</p>
        </div>
        
        <div class="content">
            <p>Salam {{ $user->name }},</p>
            
            <p>Ini adalah peringatan mesra bahawa anda mempunyai <strong>yuran bulanan yang belum dibayar</strong> untuk rumah anda.</p>
            
            <div class="amount-box">
                <p style="margin: 0 0 5px 0; color: #92400e; font-size: 14px;">JUMLAH TERTUNGGAK</p>
                <div class="amount">RM {{ number_format($totalOutstanding, 2) }}</div>
                <p style="margin: 5px 0 0 0; color: #92400e; font-size: 14px;">{{ $unpaidBills->count() }} bil belum dibayar</p>
            </div>
            
            <div class="info-box">
                <strong>🏠 Maklumat Rumah:</strong><br>
                {{ $house->full_address }}
            </div>
            
            <h3 style="margin-top: 25px; color: #374151;">Senarai Bil Belum Dibayar:</h3>
            
            <table class="bills-table">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th>Amaun</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $months = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Mac', 4 => 'April',
                            5 => 'Mei', 6 => 'Jun', 7 => 'Julai', 8 => 'Ogos',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Disember'
                        ];
                    @endphp
                    @foreach($unpaidBills as $bill)
                    <tr>
                        <td>{{ $months[$bill->bill_month] }} {{ $bill->bill_year }}</td>
                        <td><strong>RM {{ number_format($bill->outstanding_amount, 2) }}</strong></td>
                        <td><span class="status-unpaid">Belum Bayar</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <center style="margin: 30px 0;">
                <a href="{{ route('resident.payments.create') }}" class="button">💳 Bayar Sekarang</a>
            </center>
            
            <div class="info-box">
                <strong>ℹ️ Cara Pembayaran:</strong>
                <ol style="margin: 10px 0 0 0; padding-left: 20px;">
                    <li>Klik butang "Bayar Sekarang" di atas</li>
                    <li>Pilih bil yang ingin dibayar</li>
                    <li>Buat pembayaran melalui FPX/Online Banking</li>
                    <li>Resit akan dihantar ke email anda</li>
                </ol>
            </div>
            
            <p style="margin-top: 25px;">Sekiranya anda telah membuat pembayaran, sila abaikan e-mel ini. Pembayaran mungkin mengambil masa 1-2 hari bekerja untuk dikemas kini dalam sistem.</p>
            
            <p>Terima kasih atas kerjasama anda.</p>
            
            <p>Hormat kami,<br>
            <strong>{{ config('app.name') }}</strong></p>
        </div>
        
        <div class="footer">
            <img src="{{ asset('images/logo.png') }}" alt="PPTTK">
            <p>E-mel ini dihantar secara automatik. Sila jangan balas e-mel ini.</p>
            <p>&copy; {{ date('Y') }} Persatuan Penduduk Taman Tropika Kajang. Hak Cipta Terpelihara.</p>
        </div>
    </div>
</body>
</html>

