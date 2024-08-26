<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pekerjaan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 5px;
            overflow: hidden;
        }
        .header {
            background-color: #007BFF;
            color: #ffffff;
            padding: 1px;
            text-align: center;
        }
        .content {
            padding: 20px;
            color: #333333;
        }
        .footer {
            background-color: #f4f4f4;
            color: #888888;
            padding: 20px;
            text-align: center;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10 10 10 0px;
            /* border: 1px solid #dddddd; */
            text-align: left;
        }
        th {
            /* background-color: #f2f2f2; */
            font-weight: normal;
        }
        h2 {
            color: #007BFF;
            margin-bottom: 10px;
        }
        ul, ol {
            padding-left: 15px;
        }
        .footer a {
            color: #007BFF;
            text-decoration: none;
        }
        .footer img {
            width: 24px;
            height: 24px;
            margin: 0 5px;
        }
        .details {
            margin-bottom: 20px;
        }
        .details div {
            margin-bottom: 10px;
        }
        .details span {
            font-weight: bold;
        }
        @media (max-width: 600px) {
            .container {
                width: 100%;
                margin: 0 auto;
            }
            .header, .content, .footer {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
        </div>
        <div class="content">
            <p>Kepada Yth Pengelola <strong>{{ $locationName }}</strong>,</p>
            <p>Berikut adalah laporan pekerjaan Tim Support:</p>
            <table>
                <tr>
                    <th>No.:</th>
                    <td>{{ $outstandingNumber }}</td>
                </tr>
                <tr>
                    <th>{{ ucfirst($reporting->work) }}:</th>
                    <td> {{ \Carbon\Carbon::parse($reporting->date_visit)->format('d M Y') }}</td>
                </tr>
                <tr>
                    <th>Support:</th>
                    <td> {{ implode(', ', $supportNames) }}</td>
                </tr>
                <tr>
                    <th>Pelapor:</th>
                    <td> {{ ucfirst($outstandingReporter) }}</td>
                </tr>
                <tr>
                    <th><td><br></td></th>
                </tr>
                <tr>
                    <th>Masalah:</th>
                    <td>{{ $outstandingTitle }}</td>
                </tr>
                <tr>
                    <th>Sebab:</th>
                    <td>{{ $reporting->cause }}</td>
                </tr>
                <tr>
                    <th style="vertical-align:top">Aksi:</th>
                    <td>{!! $reporting->action !!}</td>
                </tr>
                <tr>
                    <th>Status:</th>
                    <td>{{ $reporting->status->name }}</td>
                </tr>
                @isset($reporting->revisit)
                <tr>
                    <th>Maksimal:</th>
                    <td>{{ $reporting->revisit }}</td>
                </tr>
                @endisset
                @isset($reporting->note)
                <tr>
                    <th style="vertical-align:top">Ket.:</th>
                    <td>{!! $reporting->note !!}</td>
                </tr>
                @endisset
            </table>
            <p>Terima kasih atas perhatian dan kerjasamanya.</p>
            <hr>
            <p>Support</p>
            <p>PT SAP</p>
        </div>
        <div class="footer">
            <p>&copy; 2024 PT. SISTEM AKSESINDO PERDANA.</p>
            <p>E-mail ini dibuat otomatis, mohon tidak membalas. Jika butuh bantuan, silakan Email ke <a href="mailto:support@ptsap.co.id">support@ptsap.co.id</a></p>
            <p>Website: <a href="http://www.ptsap.co.id" target="_blank">www.ptsap.co.id</a></p>
            <p>Alamat: Komp. Griya Inti Sentosa, Jl. Griya Agung Blok O 88-89 Jakarta</p>
            <p>Phone: (021) 6516318 | Mobile: +62 858-9100-0923</p>
            <p>
                Ikuti kami di:
                <a href="https://www.youtube.com/channel/UC9z6qGwywtGctQxiBL19ObQ" target="_blank">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/4/42/YouTube_icon_%282013-2017%29.png/800px-YouTube_icon_%282013-2017%29.png" alt="YouTube">
                </a>
                <a href="https://instagram.com/sistemaksesindoperdana" target="_blank">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a5/Instagram_icon.png/800px-Instagram_icon.png" alt="Instagram">
                </a>
                <a href="https://facebook.com/sistemaksesindoperdana" target="_blank">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/51/Facebook_f_logo_%282019%29.svg/800px-Facebook_f_logo_%282019%29.svg.png" alt="Facebook">
                </a>
            </p>
        </div>
    </div>
</body>
</html>
