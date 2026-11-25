@extends('layouts.app')
@section('judul', 'Riwayat Perhitungan')

@section('content')
<div class="card kartu">
    <div class="card-header">Riwayat Perhitungan (multi-periode)</div>
    <div class="table-responsive">
        <table class="table table-hover tabel mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th class="text-center">#</th>
                    <th>Periode</th>
                    <th class="text-center">Waktu</th>
                    <th class="text-center">Kandidat</th>
                    <th class="text-center">λ maks</th>
                    <th class="text-center">CR</th>
                    <th class="text-center">Status</th>
                    <th style="width:150px"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($batches as $b)
                    <tr>
                        <td class="text-center">{{ $b->id }}</td>
                        <td>{{ $b->period->nama ?? '-' }}</td>
                        <td class="text-center">{{ $b->created_at->format('d-m-Y H:i') }}</td>
                        <td class="text-center">{{ $b->jumlah_alternatif }}</td>
                        <td class="text-center">{{ number_format($b->lambda_max, 4, ',', '.') }}</td>
                        <td class="text-center">{{ number_format($b->cr, 4, ',', '.') }}</td>
                        <td class="text-center">
                            <span class="badge {{ $b->konsisten ? 'badge-lulus' : 'badge-gagal' }}">
                                {{ $b->konsisten ? 'KONSISTEN' : 'TIDAK KONSISTEN' }}</span>
                        </td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('hasil.show', $b) }}">
                                <i class="bi bi-eye me-1"></i>Detail</a>
                            <form method="POST" action="{{ route('riwayat.destroy', $b) }}" class="d-inline"
                                  onsubmit="return confirm('Hapus riwayat perhitungan ini?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-secondary py-4">
                        Belum ada perhitungan. Jalankan dari menu <a href="{{ route('kandidat.index') }}">Kandidat &amp; Nilai</a>.
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
