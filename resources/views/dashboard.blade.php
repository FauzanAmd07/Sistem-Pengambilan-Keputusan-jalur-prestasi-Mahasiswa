@extends('layouts.app')
@section('judul', 'Dashboard')

@section('content')
<div class="row g-3 mb-3">
    <div class="col-md-3 col-6">
        <div class="card kartu h-100"><div class="card-body">
            <div class="text-secondary small">Periode Aktif</div>
            <div class="fs-5 fw-bold">{{ $stat['periode_aktif']->nama ?? '-' }}</div>
            <div class="text-secondary small">Kuota {{ $stat['periode_aktif']->kuota ?? '-' }} penerima</div>
        </div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card kartu h-100"><div class="card-body">
            <div class="text-secondary small">Kandidat Terdaftar</div>
            <div class="fs-5 fw-bold">{{ $stat['kandidat'] }}</div>
            <div class="text-secondary small">pada periode aktif</div>
        </div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card kartu h-100"><div class="card-body">
            <div class="text-secondary small">Kriteria</div>
            <div class="fs-5 fw-bold">{{ $stat['kriteria'] }}</div>
            <div class="text-secondary small">C1 s.d. C{{ $stat['kriteria'] }}</div>
        </div></div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card kartu h-100"><div class="card-body">
            <div class="text-secondary small">Konsistensi Terakhir</div>
            @if ($stat['batch_terakhir'])
                <div class="fs-5 fw-bold">CR = {{ number_format($stat['batch_terakhir']->cr, 4) }}</div>
                <span class="badge {{ $stat['batch_terakhir']->konsisten ? 'badge-lulus' : 'badge-gagal' }}">
                    {{ $stat['batch_terakhir']->konsisten ? 'KONSISTEN' : 'TIDAK KONSISTEN' }}
                </span>
            @else
                <div class="fs-5 fw-bold">-</div>
                <div class="text-secondary small">belum ada perhitungan</div>
            @endif
        </div></div>
    </div>
</div>

<div class="card kartu mb-3">
    <div class="card-header">Alur Penggunaan Sistem (mengikuti Gambar 1 draf artikel)</div>
    <div class="card-body">
        <div class="row text-center g-2">
            @foreach ([
                ['1', 'Kelola Periode', 'periode.index', 'Tentukan periode & kuota'],
                ['2', 'Input Kandidat', 'kandidat.index', 'Data + nilai hasil konversi skala'],
                ['3', 'Isi Matriks AHP', 'ahp.edit', 'Skala Saaty, uji CR otomatis'],
                ['4', 'Proses & Lihat Hasil', 'hasil.index', 'Ranking WP + rekomendasi kuota'],
            ] as [$no, $judul, $rute, $ket])
                <div class="col-md-3 col-6">
                    <a href="{{ route($rute) }}" class="d-block p-3 border rounded-3 text-decoration-none h-100 bg-white">
                        <div class="fw-bold text-primary mb-1">Tahap {{ $no }} — {{ $judul }}</div>
                        <div class="text-secondary small">{{ $ket }}</div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>

@if ($stat['batch_terakhir'])
    <div class="card kartu">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Perhitungan Terakhir</span>
            <a class="btn btn-sm btn-outline-primary" href="{{ route('hasil.show', $stat['batch_terakhir']) }}">Lihat Detail</a>
        </div>
        <div class="card-body py-2 small text-secondary">
            {{ $stat['batch_terakhir']->created_at->format('d-m-Y H:i') }} —
            {{ $stat['batch_terakhir']->period->nama ?? '-' }} —
            {{ $stat['batch_terakhir']->jumlah_alternatif }} kandidat —
            λmaks = {{ number_format($stat['batch_terakhir']->lambda_max, 4) }},
            CI = {{ number_format($stat['batch_terakhir']->ci, 4) }},
            CR = {{ number_format($stat['batch_terakhir']->cr, 4) }}
        </div>
    </div>
@endif
@endsection
