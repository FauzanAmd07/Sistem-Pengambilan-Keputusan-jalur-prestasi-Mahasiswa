<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil Seleksi Jalur Prestasi — Batch {{ $batch->id }}</title>
    <style>
        body { font-family: "Times New Roman", serif; font-size: 12px; color: #000; }
        h2 { text-align: center; font-size: 15px; margin: 4px 0; }
        .sub { text-align: center; font-size: 11px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th, td { border: 1px solid #000; padding: 4px 6px; }
        th { background: #e9e9e9; text-align: center; }
        .r { text-align: right; } .c { text-align: center; }
        .lulus { background: #e8f5e9; }
        .info { font-size: 11px; margin-bottom: 10px; }
    </style>
</head>
<body>
<h2>HASIL SELEKSI PENERIMAAN MAHASISWA BARU JALUR PRESTASI</h2>
<div class="sub">Metode AHP + Weighted Product — Periode {{ $batch->period->nama }}</div>

<div class="info">
    Kuota penerimaan: <b>{{ $batch->period->kuota }}</b> &nbsp;|&nbsp;
    Waktu perhitungan: {{ $batch->created_at->format('d-m-Y H:i:s') }} &nbsp;|&nbsp;
    Jumlah kandidat: {{ $batch->jumlah_alternatif }}<br>
    Uji konsistensi AHP: λmaks = {{ number_format($kons['lambda_max'], 4) }} —
    CI = {{ number_format($kons['ci'], 4) }} — RI = {{ number_format($kons['ri'], 2) }} —
    CR = {{ number_format($kons['cr'], 4) }}
    ({{ $kons['konsisten'] ? 'KONSISTEN' : 'TIDAK KONSISTEN' }}, Pers. 3-4)
</div>

@php($totalKolom = 3 + count($criteria) + 4)
<table>
    <thead>
        <tr>
            <th style="width:5%">Rank</th>
            <th style="width:7%">Kode</th>
            <th style="width:22%">Nama</th>
            @foreach ($criteria as $c)
                <th>{{ $c->kode }}</th>
            @endforeach
            <th style="width:11%">Si (Pers. 5)</th>
            <th style="width:10%">Vi (Pers. 6)</th>
            <th style="width:9%">Vi (%)</th>
            <th style="width:17%">Rekomendasi</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($batch->results as $r)
            <tr class="{{ $r->rekomendasi ? 'lulus' : '' }}">
                <td class="c">{{ $r->rank }}</td>
                <td class="c">{{ $r->kode }}</td>
                <td>{{ $r->nama }}</td>
                @foreach (($nilaiPerAlt[$r->id] ?? []) as $v)
                    <td class="c">{{ is_numeric($v) ? number_format($v, 2) : '-' }}</td>
                @endforeach
                <td class="r">{{ number_format($r->si, 4) }}</td>
                <td class="r">{{ number_format($r->vi, 4) }}</td>
                <td class="r">{{ number_format($r->vi * 100, 2) }}%</td>
                <td class="c">{{ $r->rekomendasi ? 'Direkomendasikan Lulus' : 'Tidak Lulus' }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="{{ $totalKolom - 3 }}" class="r"><b>Jumlah (Σ Vi harus = 1)</b></td>
            <td class="r"><b>{{ number_format($batch->results->sum('vi'), 4) }}</b></td>
            <td class="r"><b>100%</b></td>
            <td></td>
        </tr>
    </tbody>
</table>

<table style="width:60%">
    <thead>
        <tr><th colspan="2">Bobot Prioritas Wj (AHP, Pers. 2)</th></tr>
    </thead>
    <tbody>
        @foreach ($criteria as $i => $c)
            <tr>
                <td>{{ $c->kode }} — {{ $c->nama }} ({{ ucfirst($c->tipe) }})</td>
                <td class="r">{{ number_format($w[$i], 4) }}</td>
            </tr>
        @endforeach
        <tr><td><b>Jumlah</b></td><td class="r"><b>{{ number_format(collect($w)->sum(), 4) }}</b></td></tr>
    </tbody>
</table>
</body>
</html>
