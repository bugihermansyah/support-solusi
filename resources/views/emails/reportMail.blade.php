<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Details</title>
    <style>
        body {
            font-family: Trebuchet MS, sans-serif;
            line-height: 1.6;
            background-color: #f4f4f4;
            padding: 30px;
        }
        .container {
            max-width: 700px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .row .label {
            font-weight: bold;
            width: 40%;
        }
        .row .value {
            width: 58%;
        }
        .top {
            vertical-align: top;
        }
        .spacer {
            margin-bottom: 20px;
        }
        .header {
            font-size: 15px;
            background-color: #a4a5a6;
            color: white;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">{{ $data['number'] }}</div>

        <div class="row">
            <div class="label">Tanggal Lapor:</div>
            <div class="value">{{ $data['date_lapor'] }}</div>
        </div>
        <div class="row">
            <div class="label">Tanggal Visit:</div>
            <div class="value">{{ $data['date_visit'] }}</div>
        </div>
        <div class="row">
            <div class="label">Support:</div>
            <div class="value">{{ $data['support'] }} / {{ $data['work'] }}</div>
        </div>
        <div class="row">
            <div class="label">Pelapor:</div>
            <div class="value">{{ $data['pelapor'] }} / {{ $data['nama_pelapor'] ?? '' }}</div>
        </div>
        
        <div class="spacer"></div>

        <div class="row">
            <div class="label">Masalah:</div>
            <div class="value">{{ $data['masalah'] }}</div>
        </div>
        <div class="row">
            <div class="label">Sebab:</div>
            <div class="value">{!! $data['sebab'] !!}</div>
        </div>
        <div class="row">
            <div class="label top">Aksi:</div>
            <div class="value top">{!! $data['aksi'] !!}</div>
        </div>
        <div class="row">
            <div class="label">Status:</div>
            <div class="value">{{ $data['status'] }}</div>
        </div>

        @isset($data['revisit'])
        <div class="row">
            <div class="label">Maksimal:</div>
            <div class="value">{{ $data['revisit'] }}</div>
        </div>
        @endisset

        @isset($data['note'])
        <div class="row">
            <div class="label top">Keterangan:</div>
            <div class="value top">{!! $data['note'] !!}</div>
        </div>
        @endisset
    </div>
</body>
</html>
