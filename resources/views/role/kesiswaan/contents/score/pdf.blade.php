<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Nilai Siswa</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; table-layout: fixed; }
        tbody tr:nth-child(even) {
            background-color: #f5f5f5;
        }
        th, td { border: 1px solid #000; padding: 5px; text-align: left; }
        th { background-color: #f0f0f0; text-align: center; }
        td { text-align: center; }
        td:first-child { text-align: left; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 14px; }
        .header p { margin: 5px 0; font-size: 11px; }
        .text-left { text-align: left; }
        .notes-col { max-width: 100px; word-wrap: break-word; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN NILAI SISWA</h1>
        <p>Tahun Ajaran: {{ $academicYear?->display_name ?? 'Semua' }}</p>
        <p>Ekstrakurikuler: {{ $extracurricular?->name ?? 'Semua' }}</p>
        <p>Kelas: {{ $studentClass?->name ?? 'Semua' }}</p>
        <p style="margin-top: 10px; font-size: 9px; color: #666;">
            <img src="{{ public_path('images/ekskulio-gray.png') }}" alt="Ekskulio" style="height: 16px; vertical-align: middle;">
            Dicetak oleh Sistem Ekskulio | {{ now()->format('d-m-Y H:i:s') }}
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th class="text-left">NIS</th>
                <th class="text-left">Nama Siswa</th>
                <th class="text-left">Kelas</th>
                <th class="text-left">Ekstrakurikuler</th>
                <th class="text-left">Tahun Ajaran</th>
                <th>Nilai</th>
                <th>Predikat</th>
                <th class="text-left">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($scores as $score)
            <tr>
                <td>{{ $no++ }}</td>
                <td class="text-left">{{ $score->membership->student->id_number ?? '-' }}</td>
                <td class="text-left">{{ $score->membership->student->name ?? '-' }}</td>
                <td class="text-left">{{ $score->membership->student->studentClass?->name ?? '-' }}</td>
                <td class="text-left">{{ $score->membership->extracurricular->name ?? '-' }}</td>
                <td class="text-left">{{ $score->academicYear->display_name ?? '-' }}</td>
                <td><strong>{{ $score->score ?? '-' }}</strong></td>
                <td>{{ $score->predicate ?? '-' }}</td>
                <td class="text-left notes-col">{{ $score->notes ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 20px; font-size: 9px;">Dicetak oleh Sistem Ekskulio | {{ now()->format('d-m-Y H:i:s') }}</p>
</body>
</html>