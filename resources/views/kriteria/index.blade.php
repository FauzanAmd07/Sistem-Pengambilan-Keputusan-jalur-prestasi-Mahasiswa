@extends('layouts.app')
@section('judul', 'Kriteria Seleksi')

@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <div class="card kartu">
            <div class="card-header">Tambah Kriteria</div>
            <div class="card-body">
                <form method="POST" action="{{ route('kriteria.store') }}">
                    @csrf
                    <div class="row g-2">
                        <div class="col-4">
                            <label class="form-label">Kode</label>
                            <input name="kode" class="form-control @error('kode') is-invalid @enderror"
                                   value="{{ old('kode') }}" placeholder="C7" required>
                        </div>
                        <div class="col-8">
                            <label class="form-label">Urutan</label>
                            <input type="number" name="urutan" min="1" max="99" class="form-control @error('urutan') is-invalid @enderror"
                                   value="{{ old('urutan', \App\Models\Criterion::count() + 1) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Nama kriteria</label>
                            <input name="nama" class="form-control @error('nama') is-invalid @enderror"
                                   value="{{ old('nama') }}" placeholder="mis. Nilai wawancara" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Tipe</label>
                            <select name="tipe" class="form-select @error('tipe') is-invalid @enderror">
                                <option value="benefit" {{ old('tipe') === 'benefit' ? 'selected' : '' }}>Benefit (semakin besar semakin baik)</option>
                                <option value="cost" {{ old('tipe') === 'cost' ? 'selected' : '' }}>Cost (semakin kecil semakin baik)</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Petunjuk skala (opsional)</label>
                            <input name="petunjuk" class="form-control @error('petunjuk') is-invalid @enderror"
                                   value="{{ old('petunjuk') }}" placeholder="mis. 1-5: 1=Sekolah, 5=Internasional">
                        </div>
                    </div>
                    <button class="btn btn-primary btn-sm mt-3"><i class="bi bi-plus-lg me-1"></i>Simpan</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card kartu">
            <div class="card-header">Daftar Kriteria</div>
            <div class="table-responsive">
                <table class="table table-hover tabel mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">Kode</th>
                            <th>Nama Kriteria</th>
                            <th class="text-center">Tipe</th>
                            <th>Petunjuk Skala</th>
                            <th style="width:120px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($criteria as $c)
                            <tr>
                                <td class="text-center fw-semibold">{{ $c->kode }}</td>
                                <td>{{ $c->nama }}</td>
                                <td class="text-center">
                                    @if ($c->tipe === 'cost')
                                        <span class="badge bg-warning-subtle text-warning-emphasis">Cost</span>
                                    @else
                                        <span class="badge bg-info-subtle text-info-emphasis">Benefit</span>
                                    @endif
                                </td>
                                <td class="small text-secondary">{{ $c->petunjuk }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('kriteria.edit', $c) }}">
                                        <i class="bi bi-pencil"></i></a>
                                    <form method="POST" action="{{ route('kriteria.destroy', $c) }}" class="d-inline"
                                          onsubmit="return confirm('Hapus kriteria ini? Nilai kandidat dan perbandingan AHP terkait ikut terhapus.')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-secondary py-4">Belum ada kriteria.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
