@extends('layouts.app')
@section('judul', 'Hasil Seleksi')

@section('content')
@php($kons = ['cr' => $batch->cr, 'lambda_max' => $batch->lambda_max, 'ci' => $batch->ci, 'konsisten' => $batch->konsisten])
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <div class="fw-semibold fs-5">Hasil Perhitungan #{{ $batch->id }}</div>
        <div class="text-secondary small">
            Periode {{ $batch->period->nama }} • {{ $batch->created_at->format('d-m-Y H:i') }} •
            {{ $batch->jumlah_alternatif }} kandidat • Kuota {{ $batch->period->kuota }}
        </div>
    </div>
    <div class="btn-toolbar gap-2 no-print">
        <a class="btn btn-danger btn-sm" href="{{ route('export.pdf', $batch) }}" target="_blank">
            <i class="bi bi-file-pdf me-1"></i>Cetak PDF</a>
        <a class="btn btn-success btn-sm" href="{{ route('export.xlsx', $batch) }}">
            <i class="bi bi-file-excel me-1"></i>Unduh Excel</a>
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('riwayat.index') }}">
            <i class="bi bi-arrow-left me-1"></i>Riwayat</a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-2 col-4">
        <div class="card kartu"><div class="card-body p-2 text-center">
            <div class="text-secondary small">λ maks</div>
            <div class="fw-bold">{{ number_format($kons['lambda_max'], 4, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-md-2 col-4">
        <div class="card kartu"><div class="card-body p-2 text-center">
            <div class="text-secondary small">CI</div>
            <div class="fw-bold">{{ number_format($kons['ci'], 4, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-md-2 col-4">
        <div class="card kartu"><div class="card-body p-2 text-center">
            <div class="text-secondary small">CR</div>
            <div class="fw-bold">{{ number_format($kons['cr'], 4, ',', '.') }}</div>
        </div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card kartu"><div class="card-body p-2 text-center">
            <div class="text-secondary small">Status Konsistensi</div>
            <span class="badge {{ $kons['konsisten'] ? 'badge-lulus' : 'badge-gagal' }}">
                {{ $kons['konsisten'] ? 'KONSISTEN (CR ≤ 0,10)' : 'TIDAK KONSISTEN' }}</span>
        </div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card kartu"><div class="card-body p-2">
            <div class="text-secondary small mb-1">Bobot Wj (AHP)</div>
            @foreach ($criteria as $i => $c)
                <span class="badge bg-primary-subtle text-primary-emphasis me-1 mb-1">
                    {{ $c->kode }} = {{ number_format($w[$i], 4, ',', '.') }}</span>
            @endforeach
        </div></div>
    </div>
</div>

<div class="card kartu">
    <div class="card-header">Peringkat Weighted Product — Si (Pers. 5), Vi (Pers. 6), Rekomendasi Kuota</div>
    <div class="table-responsive">
        <table class="table table-hover tabel mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th class="text-center">Rank</th>
                    <th class="text-center">Kode</th>
                    <th>Nama</th>
                    <th class="text-end">Si</th>
                    <th class="text-end">Vi</th>
                    <th class="text-end">Vi (%)</th>
                    <th class="text-center">Rekomendasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($batch->results as $r)
                    <tr class="{{ $r->rekomendasi ? 'baris-lulus' : '' }}">
                        <td class="text-center fw-bold">{{ $r->rank }}</td>
                        <td class="text-center">{{ $r->kode }}</td>
                        <td>{{ $r->nama }}</td>
                        <td class="text-end">{{ number_format($r->si, 4, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($r->vi, 4, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($r->vi * 100, 2, ',', '.') }}%</td>
                        <td class="text-center">
                            <span class="badge {{ $r->rekomendasi ? 'badge-lulus' : 'badge-gagal' }}">
                                {{ $r->rekomendasi ? 'Direkomendasikan Lulus' : 'Tidak Lulus' }}</span>
                        </td>
                    </tr>
                @endforeach
                <tr class="table-light">
                    <td colspan="4" class="fw-bold">Jumlah (Σ Vi harus = 1)</td>
                    <td class="text-end fw-bold">{{ number_format($batch->results->sum('vi'), 4, ',', '.') }}</td>
                    <td></td><td></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="text-secondary small mt-2 no-print">
    Catatan: bobot di atas adalah snapshot saat perhitungan ini dijalankan (tidak ikut berubah bila matriks AHP diedit setelahnya).
</div>
@endsection
