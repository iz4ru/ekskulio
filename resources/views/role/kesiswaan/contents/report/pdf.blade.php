<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Kehadiran</title>
</head>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        tbody tr:nth-child(even) {
            background-color: #f5f5f5;
        }
        th, td { border: 1px solid #000; padding: 4px; text-align: center; }
        th { background-color: #f0f0f0; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 14px; }
        .header p { margin: 5px 0; font-size: 11px; }
        .text-left { text-align: left; }
        .bg-green { background-color: #d9d9d9; }
        .bg-yellow { background-color: #cfcfcf; }
        .bg-blue { background-color: #bfbfbf; }
        .bg-red { background-color: #a6a6a6; }
        .bg-gray { background-color: #8c8c8c; color: #fff; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN KEHADIRAN SISWA</h1>
        <p>Ekstrakurikuler: {{ $extracurricular->name }}</p>
        <p>Bulan: {{ $monthName }} {{ $year }}</p>
        <p>Tahun Ajaran: {{ $academicYear->display_name }}</p>
        @if($studentClass)
        <p>Kelas: {{ $studentClass->name }}</p>
        @endif
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
                @foreach($presences as $p)
                <th>{{ \Carbon\Carbon::parse($p->date)->format('d') }}</th>
                @endforeach
                <th class="bg-green">H</th>
                <th class="bg-yellow">S</th>
                <th class="bg-blue">I</th>
                <th class="bg-red">A</th>
                <th class="bg-gray">%</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($students as $student)
            <tr>
                <td>{{ $no++ }}</td>
                <td class="text-left">{{ $student->id_number ?? '-' }}</td>
                <td class="text-left">{{ $student->name }}</td>
                <td class="text-left">{{ $student->studentClass?->name ?? '-' }}</td>
                @foreach($detailsData[$student->id] as $detail)
                <td>{{ $detail }}</td>
                @endforeach
                @php $s = $stats[$student->id] ?? ['present'=>0,'sick'=>0,'permission'=>0,'absent'=>0,'percentage'=>0]; @endphp
                <td class="bg-green">{{ $s['present'] }}</td>
                <td class="bg-yellow">{{ $s['sick'] }}</td>
                <td class="bg-blue">{{ $s['permission'] }}</td>
                <td class="bg-red">{{ $s['absent'] }}</td>
                <td class="bg-gray"><strong>{{ $s['percentage'] }}%</strong></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p style="margin-top: 20px; font-size: 9px;">Dicetak oleh Sistem Ekskulio | {{ now()->format('d-m-Y H:i:s') }}</p>
</body>
</html>