<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Peringatan Segera: Yuran Tertunggak</title>
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
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
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
        .urgent-banner {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 2px solid #ef4444;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
        }
        .urgent-banner .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .amount-box {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            padding: 25px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
            border: 2px solid #dc2626;
        }
        .amount {
            font-size: 36px;
            font-weight: bold;
            color: #991b1b;
        }
        .overdue-days {
            display: inline-block;
            background: #dc2626;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            margin-top: 10px;
        }
        .bills-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .bills-table th {
            background: #fef2f2;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #fecaca;
            color: #991b1b;
        }
        .bills-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .bills-table tr:hover {
            background: #fef2f2;
        }
        .status-overdue {
            display: inline-block;
            background: #dc2626;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
            color: white;
            padding: 16px 40px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 18px;
        }
        .button:hover {
            background: linear-gradient(135deg, #b91c1c 0%, #dc2626 100%);
        }
        .info-box {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .warning-box {
            background: #fef2f2;
            border-left: 4px solid #dc2626;
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
            <h1 style="margin: 0; font-size: 24px;">⚠️ Peringatan Segera</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">Yuran Tertunggak - Persatuan Penduduk Taman Tropika Kajang</p>
        </div>
        
        <div class="content">
            <div class="urgent-banner">
                <div class="icon">🚨</div>
                <h2 style="margin: 0; color: #991b1b;">Tindakan Segera Diperlukan</h2>
                <p style="margin: 10px 0 0 0; color: #7f1d1d;">Anda mempunyai yuran yang telah melepasi tarikh akhir pembayaran</p>
            </div>
            
            <p>Salam {{ $user->name }},</p>
            
            <p>Kami ingin memaklumkan bahawa anda mempunyai <strong>yuran tertunggak</strong> yang memerlukan perhatian segera.</p>
            
            <div class="amount-box">
                <p style="margin: 0 0 5px 0; color: #991b1b; font-size: 14px;">JUMLAH TERTUNGGAK</p>
                <div class="amount">RM {{ number_format($totalOverdue, 2) }}</div>
                <div class="overdue-days">{{ $oldestOverdueDays }} hari tertunggak</div>
            </div>
            
            <div class="warning-box">
                <strong>🏠 Maklumat Rumah:</strong><br>
                {{ $house->full_address }}
            </div>
            
            <h3 style="margin-top: 25px; color: #991b1b;">Senarai Bil Tertunggak:</h3>
            
            <table class="bills-table">
                <thead>
                    <tr>
                        <th>Bulan</th>
                        <th>Amaun</th>
                        <th>Tarikh Akhir</th>
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
                    @foreach($overdueBills as $bill)
                    <tr>
                        <td>{{ $months[$bill->bill_month] }} {{ $bill->bill_year }}</td>
                        <td><strong>RM {{ number_format($bill->outstanding_amount, 2) }}</strong></td>
                        <td>{{ $bill->due_date->format('d/m/Y') }}</td>
                        <td><span class="status-overdue">Tertunggak</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            <center style="margin: 30px 0;">
                <a href="{{ route('resident.payments.create') }}" class="button">💳 Bayar Segera</a>
            </center>
            
            <div class="info-box">
                <strong>⚡ Cara Pembayaran Segera:</strong>
                <ol style="margin: 10px 0 0 0; padding-left: 20px;">
                    <li>Klik butang "Bayar Segera" di atas</li>
                    <li>Pilih semua bil tertunggak</li>
                    <li>Buat pembayaran melalui FPX/Online Banking</li>
                    <li>Pembayaran akan dikemas kini dalam masa 24 jam</li>
                </ol>
            </div>
            
            <div class="warning-box">
                <strong>⚠️ Penting:</strong><br>
                Sila jelaskan tunggakan yuran anda secepat mungkin untuk mengelakkan sebarang tindakan lanjut. Sekiranya anda menghadapi kesulitan untuk membuat pembayaran, sila hubungi pentadbir untuk berbincang.
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


