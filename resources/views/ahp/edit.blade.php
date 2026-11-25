@extends('layouts.app')
@section('judul', 'Bobot AHP — Matriks Perbandingan Berpasangan')

@section('content')
<form method="GET" action="{{ route('ahp.edit') }}" class="d-flex gap-2 align-items-center mb-3 no-print">
    <label class="text-secondary small mb-0">Periode:</label>
    <select name="periode" class="form-select form-select-sm" onchange="this.form.submit()">
        @foreach ($periods as $p)
            <option value="{{ $p->id }}" {{ ($periode?->id === $p->id) ? 'selected' : '' }}>
                {{ $p->nama }} {{ $p->aktif ? '(aktif)' : '' }}
            </option>
        @endforeach
    </select>
    <noscript><button class="btn btn-sm btn-outline-secondary">Tampilkan</button></noscript>
</form>

@if (!$periode)
    <div class="alert alert-warning no-print">Belum ada periode. Buat periode terlebih dahulu pada menu <a href="{{ route('periode.index') }}">Periode Seleksi</a>.</div>
@else
<div class="card kartu mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>Matriks Perbandingan Berpasangan (aij) — Skala Saaty</span>
        <span class="text-secondary small">{{ $periode->nama }}</span>
    </div>
    <div class="card-body">
        <p class="small text-secondary mb-2">
            Isi <b>segitiga atas</b> saja (diagonal otomatis = 1; segitiga bawah otomatis = 1/nilai).
            Skala Saaty: 1 sama penting, 3 sedikit lebih penting, 5 lebih penting, 7 sangat lebih penting, 9 mutlak lebih penting;
            antar angka: 2, 4, 6, 8. Bila lebih tidak penting tulis pecahan, mis. <b>0.3333</b> untuk 1/3. (Tabel 2 draf artikel)
        </p>
        <form method="POST" action="{{ route('ahp.store') }}">
            @csrf
            <input type="hidden" name="period_id" value="{{ $periode->id }}">
            <div class="table-responsive">
                <table class="table table-bordered tabel text-center align-middle mb-3" id="matriks">
                    <thead class="table-light">
                        <tr>
                            <th style="width:230px">Kriteria</th>
                            @foreach ($criteria as $c)
                                <th style="min-width:86px">{{ $c->kode }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($criteria as $i => $ci)
                            <tr>
                                <td class="text-start small">
                                    <b>{{ $ci->kode }}</b> — {{ $ci->nama }}
                                    <span class="text-secondary">({{ $ci->tipe }})</span>
                                </td>
                                @foreach ($criteria as $j => $cj)
                                    @php
                                        $kunci = $ci->id . '_' . $cj->id;
                                        $nilaiTersimpan = $terisi->get($kunci)?->nilai;
                                        $tampil = $nilaiTersimpan !== null
                                            ? rtrim(rtrim(number_format($nilaiTersimpan, 6, '.', ''), '0'), '.')
                                            : '';
                                    @endphp
                                    @if ($i === $j)
                                        <td class="bg-secondary-subtle">1</td>
                                    @elseif ($j > $i)
                                        <td>
                                            <input name="banding[{{ $kunci }}]" type="number" step="any" min="0.1111" max="9"
                                                   class="form-control form-control-sm text-center"
                                                   value="{{ old('banding.' . $kunci, $tampil) }}"
                                                   data-i="{{ $i }}" data-j="{{ $j }}" required
                                                   oninput="perbaruiResiprokal(this)">
                                            <div class="small text-secondary mt-1" id="res-{{ $i }}-{{ $j }}"></div>
                                        </td>
                                    @else
                                        <td class="bg-light" id="bawah-{{ $i }}-{{ $j }}">—</td>
                                    @endif
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button class="btn btn-primary btn-sm no-print">
                <i class="bi bi-check2-square me-1"></i>Simpan &amp; Uji Konsistensi (Pers. 3-4)
            </button>
        </form>
    </div>
</div>

@if ($hasil)
    @php($kons = $hasil['kons'])
    <div class="card kartu mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Bobot Prioritas Wj (Pers. 2) &amp; Uji Konsistensi (Pers. 3-4)</span>
            <span class="badge {{ $kons['konsisten'] ? 'badge-lulus' : 'badge-gagal' }} fs-6">
                {{ $kons['konsisten'] ? 'KONSISTEN (CR ≤ 0,10)' : 'TIDAK KONSISTEN (CR > 0,10)' }}
            </span>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-lg-7">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered tabel text-center mb-2">
                            <thead class="table-light">
                                <tr>
                                    <th>Kriteria</th>
                                    @foreach ($criteria as $c)
                                        <th>{{ $c->kode }}</th>
                                    @endforeach
                                    <th>Bobot Wj</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($criteria as $i => $ci)
                                    <tr>
                                        <td class="text-start small"><b>{{ $ci->kode }}</b> — {{ $ci->nama }}</td>
                                        @foreach ($criteria as $j => $cj)
                                            <td>{{ number_format($hasil['norm'][$i][$j], 4, ',', '.') }}</td>
                                        @endforeach
                                        <td class="fw-bold">{{ number_format($hasil['w'][$i], 4, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                                <tr class="table-light fw-bold">
                                    <td class="text-start">Jumlah (Σ Wj harus = 1)</td>
                                    @foreach ($criteria as $j => $cj)
                                        <td>{{ number_format(collect($hasil['norm'])->pluck($j)->sum(), 3, ',', '.') }}</td>
                                    @endforeach
                                    <td>{{ number_format(collect($hasil['w'])->sum(), 4, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-lg-5">
                    <table class="table table-sm table-bordered tabel mb-0">
                        <tbody>
                            <tr><td style="width:45%">λ maks (rata-rata (A·W)i / Wi)</td><td class="fw-bold">{{ number_format($kons['lambda_max'], 4, ',', '.') }}</td></tr>
                            <tr><td>CI = (λmaks − n)/(n − 1), n = {{ $kons['n'] }}</td><td class="fw-bold">{{ number_format($kons['ci'], 4, ',', '.') }}</td></tr>
                            <tr><td>RI (Random Index, n = {{ $kons['n'] }})</td><td class="fw-bold">{{ number_format($kons['ri'], 2, ',', '.') }}</td></tr>
                            <tr><td>CR = CI / RI</td><td class="fw-bold">{{ number_format($kons['cr'], 4, ',', '.') }}</td></tr>
                            <tr><td>Status Konsistensi</td>
                                <td><span class="badge {{ $kons['konsisten'] ? 'badge-lulus' : 'badge-gagal' }}">
                                    {{ $kons['konsisten'] ? 'KONSISTEN' : 'TIDAK KONSISTEN' }}</span></td></tr>
                        </tbody>
                    </table>
                    <div class="small text-secondary mt-2">
                        @if ($kons['konsisten'])
                            Matriks konsisten — lanjutkan ke menu <a href="{{ route('kandidat.index') }}">Kandidat &amp; Nilai</a> untuk menjalankan Weighted Product.
                        @else
                            CR &gt; 0,10 — perbaiki nilai perbandingan hingga konsisten sebelum proses WP dijalankan (sistem menolak proses bila tidak konsisten).
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
@endif
@endsection

@push('js')
<script>
    // segitiga bawah = 1/nilai segitiga atas (diperbarui langsung saat mengetik)
    function perbaruiResiprokal(el) {
        const i = parseInt(el.dataset.i), j = parseInt(el.dataset.j);
        const bawah = document.getElementById('bawah-' + j + '-' + i);
        const v = parseFloat(el.value);
        bawah.textContent = (v > 0 && isFinite(v)) ? (1 / v).toFixed(4) : '—';
    }
    document.querySelectorAll('#matriks input[type=number]').forEach(el => {
        perbaruiResiprokal(el);
        // nilai resiprokal awal dari matriks tersimpan
        if (!el.value) {
            const i = parseInt(el.dataset.i), j = parseInt(el.dataset.j);
            const atas = document.querySelector(`input[data-i="${j}"][data-j="${i}"]`);
            if (atas && parseFloat(atas.value) > 0) {
                document.getElementById('bawah-' + i + '-' + j).textContent = (1 / parseFloat(atas.value)).toFixed(4);
            }
        }
    });
</script>
@endpush
