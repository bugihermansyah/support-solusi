<!DOCTYPE html>
<html>
<head>
    <title>Jadwal Kunjungan</title>
</head>
<body>
    <p>Dear Support,</p>
    <p>Berikut adalah jadwal kunjungan:</p>
    <ul>
        <li><strong>Tanggal Kunjungan:</strong> {{ $dateVisit }}</li>
        <li><strong>Perusahaan:</strong> {{ $companyAlias }}</li>
        <li><strong>Lokasi:</strong> {{ $locationName }}</li>
        <li><strong>Masalah:</strong> {{ $title }}</li>
    </ul>
    <p>Terima kasih,</p>
    <p>Jangan lupa berdo'a sebelum mulai bekerja.</p>
</body>
</html>
