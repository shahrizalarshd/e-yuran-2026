<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Diluluskan</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            color: white;
            padding: 30px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
            border-top: none;
        }
        .info-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #059669;
        }
        .button {
            display: inline-block;
            background: #059669;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            color: #6b7280;
            font-size: 14px;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ asset('images/logo.png') }}" alt="PPTTK" style="height: 80px; width: auto; margin-bottom: 15px; background: white; padding: 10px; border-radius: 10px;">
        <h1 style="margin: 0;">✓ Pendaftaran Diluluskan</h1>
        <p style="margin: 10px 0 0 0; opacity: 0.9;">Persatuan Penduduk Taman Tropika Kajang</p>
    </div>
    
    <div class="content">
        <p>Salam {{ $houseMember->resident->name }},</p>
        
        <p>Kami dengan sukacitanya memaklumkan bahawa pendaftaran anda untuk rumah di <strong>{{ config('app.name') }}</strong> telah <strong>diluluskan</strong>.</p>
        
        <div class="info-box">
            <h3 style="margin-top: 0; color: #059669;">Maklumat Rumah</h3>
            <p style="margin: 5px 0;"><strong>Alamat:</strong> {{ $houseMember->house->full_address }}</p>
            <p style="margin: 5px 0;"><strong>Hubungan:</strong> {{ ucfirst($houseMember->relationship) }}</p>
            <p style="margin: 5px 0;"><strong>Status:</strong> <span style="color: #059669;">Aktif</span></p>
        </div>
        
        <p>Anda kini boleh log masuk ke sistem untuk:</p>
        <ul>
            <li>Melihat bil yuran bulanan</li>
            <li>Membuat pembayaran dalam talian</li>
            <li>Melihat sejarah pembayaran</li>
            <li>Menguruskan maklumat rumah anda</li>
        </ul>
        
        <center>
            <a href="{{ route('resident.dashboard') }}" class="button">Log Masuk ke Dashboard</a>
        </center>
        
        <p>Sekiranya anda mempunyai sebarang pertanyaan, sila hubungi pentadbir.</p>
        
        <p>Terima kasih,<br>
        <strong>{{ config('app.name') }}</strong></p>
    </div>
    
    <div class="footer">
        <img src="{{ asset('images/logo.png') }}" alt="PPTTK" style="height: 50px; width: auto; margin-bottom: 10px;">
        <p>E-mel ini dihantar secara automatik. Sila jangan balas e-mel ini.</p>
        <p>&copy; {{ date('Y') }} Persatuan Penduduk Taman Tropika Kajang. Hak Cipta Terpelihara.</p>
    </div>
</body>
</html>

