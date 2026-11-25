@extends('layouts.app')
@section('judul', 'Kandidat & Nilai')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2 no-print">
    <form method="GET" action="{{ route('kandidat.index') }}" class="d-flex gap-2 align-items-center">
        <label class="text-secondary small mb-0">Periode:</label>
        <select name="periode" class="form-select form-select-sm" onchange="this.form.submit()">
            @foreach ($periods as $p)
                <option value="{{ $p->id }}" {{ ($periodeTerpilih?->id === $p->id) ? 'selected' : '' }}>
                    {{ $p->nama }} {{ $p->aktif ? '(aktif)' : '' }}
                </option>
            @endforeach
        </select>
        <noscript><button class="btn btn-sm btn-outline-secondary">Tampilkan</button></noscript>
    </form>
    <div class="btn-toolbar gap-2">
        <form method="POST" action="{{ route('proses.store') }}" class="d-inline"
              onsubmit="return confirm('Jalankan perhitungan AHP + Weighted Product untuk periode aktif/terpilih?')">
            @csrf
            <button class="btn btn-success btn-sm"><i class="bi bi-play-fill me-1"></i>Proses Perhitungan</button>
        </form>
        <a class="btn btn-primary btn-sm" href="{{ route('kandidat.create') }}"><i class="bi bi-plus-lg me-1"></i>Tambah Kandidat</a>
    </div>
</div>

<div class="card kartu">
    <div class="card-header">
        Daftar Kandidat — {{ $periodeTerpilih->nama ?? '-' }}
        @if ($periodeTerpilih)
            <span class="badge badge-lulus ms-2">Kuota {{ $periodeTerpilih->kuota }}</span>
        @endif
    </div>
    <div class="table-responsive">
        <table class="table table-hover tabel mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th class="text-center">Kode</th>
                    <th>Nama</th>
                    @forelse (\App\Models\Criterion::orderBy('urutan')->get() as $c)
                        <th class="text-center" title="{{ $c->nama }}">{{ $c->kode }}</th>
                    @empty
                        <th class="text-center">-</th>
                    @endforelse
                    <th style="width:170px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($alternatives as $a)
                    @php($nilai = collect($a->nilaiTerurut()))
                    <tr>
                        <td class="text-center fw-semibold">{{ $a->kode }}</td>
                        <td>{{ $a->nama }}</td>
                        @foreach ($nilai as $s)
                            <td class="text-center">
                                @if ($s)
                                    {{ $s->nilai }}
                                    @if ($s->catatan)
                                        <i class="bi bi-info-circle text-secondary" style="cursor:help"
                                           title="{{ $s->catatan }}"></i>
                                    @endif
                                @else
                                    <span class="badge bg-danger-subtle text-danger-emphasis">kosong</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-success" href="{{ route('kandidat.nilai', $a) }}"
                               title="Input nilai"><i class="bi bi-pencil-square"></i></a>
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('kandidat.edit', $a) }}"
                               title="Edit data"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('kandidat.destroy', $a) }}" class="d-inline"
                                  onsubmit="return confirm('Hapus kandidat ini beserta nilainya?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="text-center text-secondary py-4">
                        Belum ada kandidat pada periode ini.
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
