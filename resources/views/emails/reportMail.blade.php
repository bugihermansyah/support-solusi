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
        table {
            width:100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 13px;
        }
        p {
            margin: 0;
            padding: 0;
        }
        th, td {
            padding: 5px;
            text-align: left;
        }
        .top {
            vertical-align: top;
        }
        th {
            background-color: #a4a5a6;
            color: white;
        }
    </style>
</head>
<body>
    <div style="display:block; max-width:700px;">
    <table>
        <tr>
            <th colspan="2" style="font-size:15px">{{ $data['number'] }}</th>
        </tr>
        <tr>
            <td style="width:31%">Tanggal Lapor:</td>
            <td>{{ $data['date_lapor'] }}</td>
        </tr>
        <tr>
            <td>Tanggal Visit:</td>
            <td>{{ $data['date_visit'] }}</td>
        </tr>
        <tr>
            <td>Support:</td>
            <td>{{ $data['support'] }} / {{ $data['work'] }}</td>
        </tr>
        <tr>
            <td>Pelapor:</td>
            <td>{{ $data['pelapor'] }} / {{ $data['nama_pelapor'] ?? ''}}</td>
        </tr>
        <tr>
            <td colspan="2"><br></td>
        </tr>
        <tr>
            <td>Masalah:</td>
            <td>{{ $data['masalah'] }}</td>
        </tr>
        <tr>
            <td>Sebab:</td>
            <td>{!! $data['sebab'] !!}</td>
        </tr>
        <tr>
            <td class="top">Aksi:</td>
            <td class="top">{!! $data['aksi'] !!}</td>
        </tr>
        <tr>
            <td>Status:</td>
            <td>{{ $data['status'] }}</td>
        </tr>
        @isset($data['revisit'])
        <tr>
            <td>Maksimal:</td>
            <td>{{ $data['revisit'] }}</td>
        </tr>
        @endisset
        @isset($data['note'])
        <tr>
            <td class="top">Keterangan:</td>
            <td class="top">{!! $data['note'] !!}</td>
        </tr>
        @endisset
    </table>
    </div>
</body>
</html>
