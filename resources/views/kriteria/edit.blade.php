@extends('layouts.app')
@section('judul', 'Edit Kriteria')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-5">
        <div class="card kartu">
            <div class="card-header">Edit Kriteria {{ $criterion->kode }}</div>
            <div class="card-body">
                <form method="POST" action="{{ route('kriteria.update', $criterion) }}">
                    @csrf @method('PUT')
                    <div class="row g-2">
                        <div class="col-4">
                            <label class="form-label">Kode</label>
                            <input name="kode" class="form-control @error('kode') is-invalid @enderror"
                                   value="{{ old('kode', $criterion->kode) }}" required>
                        </div>
                        <div class="col-8">
                            <label class="form-label">Urutan</label>
                            <input type="number" name="urutan" min="1" max="99" class="form-control @error('urutan') is-invalid @enderror"
                                   value="{{ old('urutan', $criterion->urutan) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Nama kriteria</label>
                            <input name="nama" class="form-control @error('nama') is-invalid @enderror"
                                   value="{{ old('nama', $criterion->nama) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Tipe</label>
                            <select name="tipe" class="form-select @error('tipe') is-invalid @enderror">
                                <option value="benefit" {{ old('tipe', $criterion->tipe) === 'benefit' ? 'selected' : '' }}>Benefit</option>
                                <option value="cost" {{ old('tipe', $criterion->tipe) === 'cost' ? 'selected' : '' }}>Cost</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Petunjuk skala</label>
                            <input name="petunjuk" class="form-control @error('petunjuk') is-invalid @enderror"
                                   value="{{ old('petunjuk', $criterion->petunjuk) }}">
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-primary btn-sm">Simpan</button>
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('kriteria.index') }}">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
