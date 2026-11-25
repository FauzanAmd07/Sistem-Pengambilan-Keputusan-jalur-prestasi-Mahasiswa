@extends('layouts.app')
@section('judul', 'Nilai Kandidat ' . $alternative->kode)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card kartu">
            <div class="card-header">
                Input Nilai — {{ $alternative->kode }}: {{ $alternative->nama }}
                <span class="text-secondary small ms-2">(nilai hasil konversi skala, Tabel 1 draf artikel)</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('kandidat.nilai.simpan', $alternative) }}">
                    @csrf @method('PUT')
                    <div class="table-responsive">
                        <table class="table tabel align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:90px">Kriteria</th>
                                    <th style="width:130px">Nilai</th>
                                    <th>Catatan jawaban asli (opsional)</th>
                                    <th style="width:34%">Petunjuk skala</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($criteria as $c)
                                    @php($s = $scores->get($c->id))
                                    <tr>
                                        <td class="fw-semibold">{{ $c->kode }}
                                            @if ($c->tipe === 'cost')
                                                <span class="badge bg-warning-subtle text-warning-emphasis ms-1">Cost</span>
                                            @endif
                                        </td>
                                        <td>
                                            <input name="nilai[{{ $c->id }}]" type="number" step="any"
                                                   class="form-control form-control-sm text-center @error('nilai.' . $c->id) is-invalid @enderror"
                                                   value="{{ old('nilai.' . $c->id, $s?->nilai) }}" required>
                                        </td>
                                        <td>
                                            <input name="catatan[{{ $c->id }}]" class="form-control form-control-sm"
                                                   value="{{ old('catatan.' . $c->id, $s?->catatan) }}"
                                                   placeholder="mis. TKA 86 / UTBK 560 / Tidak diisi">
                                        </td>
                                        <td class="small text-secondary">{{ $c->petunjuk }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-primary btn-sm">Simpan Nilai</button>
                        <a class="btn btn-outline-secondary btn-sm" href="{{ route('kandidat.index') }}">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
