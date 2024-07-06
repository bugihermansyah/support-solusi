<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Details</title>
    <style>
        /* CSS untuk styling */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f4f4f4;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <th colspan="2">Detail Laporan</th>
        </tr>
        <tr>
            <td style="width: 25%;">Tanggal Lapor :</td>
            <td>{{ $data['date_lapor'] }}</td>
        </tr>
        <tr>
            <td>Tanggal Visit :</td>
            <td>{{ $data['date_visit'] }}</td>
        </tr>
        <tr>
            <td>Support :</td>
            <td>{{ $data['support'] }}</td>
        </tr>
        <tr>
            <td>Pelapor :</td>
            <td>{{ $data['pelapor'] }}</td>
        </tr>
        <tr>
            <td colspan="2"><br></td> <!-- Untuk memberi jarak antara bagian atas dan bagian bawah -->
        </tr>
        <tr>
            <td>Masalah :</td>
            <td>{{ $data['masalah'] }}</td>
        </tr>
        <tr>
            <td>Sebab :</td>
            <td>{!! $data['sebab'] !!}</td>
        </tr>
        <tr>
            <td>Aksi :</td>
            <td>{!! $data['aksi'] !!}{!! $data['solusi'] !!}</td>
        </tr>
        <tr>
            <td>Status :</td>
            <td>{{ $data['status'] }}</td>
        </tr>
        <tr>
            <td>Keterangan :</td>
            <td>{!! $data['note'] !!}</td>
        </tr>
    </table>
</body>
</html>
