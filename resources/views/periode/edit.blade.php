@extends('layouts.app')
@section('judul', 'Edit Periode')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-5">
        <div class="card kartu">
            <div class="card-header">Edit Periode</div>
            <div class="card-body">
                <form method="POST" action="{{ route('periode.update', $period) }}">
                    @csrf @method('PUT')
                    <div class="mb-2">
                        <label class="form-label">Nama periode</label>
                        <input name="nama" class="form-control @error('nama') is-invalid @enderror"
                               value="{{ old('nama', $period->nama) }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Kuota penerimaan</label>
                        <input type="number" name="kuota" min="1" max="100" class="form-control @error('kuota') is-invalid @enderror"
                               value="{{ old('kuota', $period->kuota) }}" required>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="aktif" value="1" id="aktif"
                               {{ old('aktif', $period->aktif) ? 'checked' : '' }}>
                        <label class="form-check-label" for="aktif">Periode aktif (menonaktifkan periode lain)</label>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-sm">Simpan</button>
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('periode.index') }}">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
