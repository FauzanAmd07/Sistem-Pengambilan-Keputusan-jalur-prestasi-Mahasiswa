<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('judul', 'Dashboard') — SPK Seleksi Jalur Prestasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; font-size: .925rem; }
        .sidebar { width: 235px; min-height: 100vh; position: fixed; top: 0; left: 0; z-index: 100; }
        .sidebar .nav-link { color: #cbd5e1; padding: .6rem 1rem; border-radius: .4rem; margin: 2px 8px; }
        .sidebar .nav-link:hover { background: rgba(255,255,255,.08); color: #fff; }
        .sidebar .nav-link.aktif { background: #2563eb; color: #fff; }
        .konten { margin-left: 235px; padding: 1.25rem 1.5rem 2.5rem; }
        .kartu { border: 1px solid #e3e8ef; border-radius: .6rem; }
        .kartu .card-header { background: #fff; border-bottom: 1px solid #e3e8ef; font-weight: 600; }
        .tabel td, .tabel th { vertical-align: middle; }
        .badge-lulus { background: #dcfce7; color: #166534; font-weight: 600; }
        .badge-gagal { background: #fee2e2; color: #991b1b; font-weight: 600; }
        .baris-lulus { background: #f0fdf4 !important; }
        @media (max-width: 991px) { .sidebar { position: static; width: 100%; min-height: auto; } .konten { margin-left: 0; } }
        @media print { .sidebar, .btn-toolbar, .no-print { display: none !important; } .konten { margin: 0; } }
    </style>
    @stack('css')
</head>
<body>
<div class="d-flex">
    <nav class="sidebar bg-dark d-flex flex-column pt-3 pb-3">
        <div class="px-3 pb-3 mb-2 border-bottom border-secondary">
            <div class="text-white fw-bold">SPK Jalur Prestasi</div>
            <div class="text-secondary" style="font-size:.75rem">AHP + Weighted Product</div>
        </div>
        <ul class="nav flex-column flex-grow-1">
            @php($menu = [
                'dashboard' => ['Dashboard', 'bi-speedometer2', 'dashboard'],
                'periode.index' => ['Periode Seleksi', 'bi-calendar-event', 'periode'],
                'kriteria.index' => ['Kriteria', 'bi-sliders', 'kriteria'],
                'kandidat.index' => ['Kandidat & Nilai', 'bi-people', 'kandidat'],
                'ahp.edit' => ['Bobot AHP', 'bi-diagram-3', 'ahp'],
                'hasil.index' => ['Hasil Seleksi', 'bi-trophy', 'hasil'],
                'riwayat.index' => ['Riwayat Perhitungan', 'bi-clock-history', 'riwayat'],
            ])
            @foreach ($menu as $rute => [$label, $ikon, $prefiks])
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs($rute) || request()->routeIs($prefiks . '.*') ? 'aktif' : '' }}"
                       href="{{ route($rute) }}">
                        <i class="bi {{ $ikon }} me-2"></i>{{ $label }}
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="px-3 small text-secondary">
            Login sebagai <span class="text-white">{{ auth()->user()->name ?? 'Admin' }}</span>
        </div>
    </nav>

    <main class="konten flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-3 no-print">
            <h4 class="mb-0 fw-semibold">@yield('judul')</h4>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-box-arrow-right me-1"></i>Keluar</button>
            </form>
        </div>

        @if (session('sukses'))
            <div class="alert alert-success py-2 no-print"><i class="bi bi-check-circle me-1"></i>{{ session('sukses') }}</div>
        @endif
        @if (session('peringatan'))
            <div class="alert alert-warning py-2 no-print"><i class="bi bi-exclamation-triangle me-1"></i>{{ session('peringatan') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger py-2 no-print">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $galat)
                        <li>{{ $galat }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('js')
</body>
</html>
