@extends('layouts.app')
@section('judul', 'Periode Seleksi')

@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card kartu">
            <div class="card-header">Tambah Periode</div>
            <div class="card-body">
                <form method="POST" action="{{ route('periode.store') }}">
                    @csrf
                    <div class="mb-2">
                        <label class="form-label">Nama periode</label>
                        <input name="nama" class="form-control @error('nama') is-invalid @enderror"
                               value="{{ old('nama') }}" placeholder="mis. Gelombang 2 - 2025/2026" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Kuota penerimaan</label>
                        <input type="number" name="kuota" min="1" max="100" class="form-control @error('kuota') is-invalid @enderror"
                               value="{{ old('kuota', 5) }}" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="aktif" value="1" id="aktif">
                        <label class="form-check-label" for="aktif">Jadikan periode aktif</label>
                    </div>
                    <button class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>Simpan</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card kartu">
            <div class="card-header">Daftar Periode</div>
            <div class="table-responsive">
                <table class="table table-hover tabel mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Periode</th>
                            <th class="text-center">Kuota</th>
                            <th class="text-center">Kandidat</th>
                            <th class="text-center">Status</th>
                            <th style="width:130px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($periods as $p)
                            <tr>
                                <td class="fw-semibold">{{ $p->nama }}</td>
                                <td class="text-center">{{ $p->kuota }}</td>
                                <td class="text-center">{{ $p->alternatives_count }}</td>
                                <td class="text-center">
                                    @if ($p->aktif)
                                        <span class="badge badge-lulus">Aktif</span>
                                    @else
                                        <span class="badge bg-light text-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('periode.edit', $p) }}">
                                        <i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('periode.destroy', $p) }}" class="d-inline"
                                          onsubmit="return confirm('Hapus periode beserta kandidat, matriks AHP, dan riwayatnya?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-secondary py-4">Belum ada periode.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
