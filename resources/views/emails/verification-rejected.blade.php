<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Ditolak</title>
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
            background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%);
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
            border-left: 4px solid #dc2626;
        }
        .reason-box {
            background: #fef2f2;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
            border: 1px solid #fecaca;
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
        <h1 style="margin: 0;">✗ Pendaftaran Ditolak</h1>
        <p style="margin: 10px 0 0 0; opacity: 0.9;">{{ config('app.name') }}</p>
    </div>
    
    <div class="content">
        <p>Salam {{ $houseMember->resident->name }},</p>
        
        <p>Kami ingin memaklumkan bahawa pendaftaran anda untuk rumah di <strong>{{ config('app.name') }}</strong> telah <strong>ditolak</strong>.</p>
        
        <div class="info-box">
            <h3 style="margin-top: 0; color: #dc2626;">Maklumat Permohonan</h3>
            <p style="margin: 5px 0;"><strong>Alamat Rumah:</strong> {{ $houseMember->house->full_address }}</p>
            <p style="margin: 5px 0;"><strong>Tarikh Permohonan:</strong> {{ $houseMember->created_at->format('d/m/Y') }}</p>
        </div>
        
        @if($houseMember->rejection_reason)
        <div class="reason-box">
            <h4 style="margin-top: 0; color: #dc2626;">Sebab Penolakan:</h4>
            <p style="margin: 0;">{{ $houseMember->rejection_reason }}</p>
        </div>
        @endif
        
        <p>Sekiranya anda percaya ini adalah kesilapan atau anda ingin membuat rayuan, sila hubungi pentadbir untuk maklumat lanjut.</p>
        
        <p><strong>Anda boleh cuba memohon semula selepas menyelesaikan isu yang dinyatakan.</strong></p>
        
        <center>
            <a href="{{ route('home') }}" class="button">Kembali ke Laman Utama</a>
        </center>
        
        <p>Terima kasih atas pengertian anda.</p>
        
        <p>Hormat kami,<br>
        <strong>{{ config('app.name') }}</strong></p>
    </div>
    
    <div class="footer">
        <p>E-mel ini dihantar secara automatik. Sila jangan balas e-mel ini.</p>
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Hak Cipta Terpelihara.</p>
    </div>
</body>
</html>

