@extends('layouts.app')
@section('judul', 'Edit Kandidat')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-5">
        <div class="card kartu">
            <div class="card-header">Edit Kandidat {{ $alternative->kode }}</div>
            <div class="card-body">
                <form method="POST" action="{{ route('kandidat.update', $alternative) }}">
                    @csrf @method('PUT')
                    <div class="mb-2">
                        <label class="form-label">Periode</label>
                        <select name="period_id" class="form-select @error('period_id') is-invalid @enderror" required>
                            @foreach ($periods as $p)
                                <option value="{{ $p->id }}" {{ old('period_id', $alternative->period_id) === $p->id ? 'selected' : '' }}>
                                    {{ $p->nama }} {{ $p->aktif ? '(aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Kode kandidat</label>
                        <input name="kode" class="form-control @error('kode') is-invalid @enderror"
                               value="{{ old('kode', $alternative->kode) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama lengkap</label>
                        <input name="nama" class="form-control @error('nama') is-invalid @enderror"
                               value="{{ old('nama', $alternative->nama) }}" required>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-sm">Simpan</button>
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('kandidat.index') }}">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
