<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Nilai Siswa</title>
    <style>
        @font-face {
            font-family: 'DM Sans';
            src: url('{{ public_path('fonts/DMSans-Regular.ttf') }}') format('truetype');
            font-weight: 400;
        }
        @font-face {
            font-family: 'DM Sans';
            src: url('{{ public_path('fonts/DMSans-Medium.ttf') }}') format('truetype');
            font-weight: 500;
        }
        @font-face {
            font-family: 'DM Sans';
            src: url('{{ public_path('fonts/DMSans-Bold.ttf') }}') format('truetype');
            font-weight: 600;
        }

        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        @media print {
            body {
                size: A4 portrait;
            }
        }

        body { font-family: DM Sans, sans-serif; font-size: 8px; }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
            word-wrap: break-word;
        }
        tbody tr:nth-child(even) {
            background-color: #f5f5f5;
        }
        th, td { 
            border: 1px solid #000; 
            padding: 5px; 
            overflow: hidden;
            word-wrap: break-word; 
        }
        th { background-color: #f0f0f0; text-align: center; }
        td { text-align: center; }
        td:first-child { text-align: left; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 14px; }
        .header p { margin: 5px 0; font-size: 11px; }
        .text-left { text-align: left; }
        .notes-col { word-wrap: break-word; }
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

    <table width="100%" border="1" cellpadding="5" cellspacing="0" style="border-collapse: collapse; font-size: 8px;">
        <thead>
            <tr>
                <th width="3%">No</th>
                <th width="8%" class="text-left">NIS</th>
                <th width="20%" class="text-left">Nama Siswa</th>
                <th width="8%" class="text-left">Kelas</th>
                <th width="13%" class="text-left">Ekstrakurikuler</th>
                <th width="15%" class="text-left">Tahun Ajaran</th>
                <th width="5%">Nilai</th>
                <th width="8%">Predikat</th>
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