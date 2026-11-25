<?php

namespace App\Http\Controllers;

use App\Models\Period;
use App\Models\Result;
use App\Models\ResultBatch;
use App\Services\SpkService;
use Illuminate\Http\Request;

class ProsesController extends Controller
{
    public function __construct(private SpkService $spk)
    {
    }

    /** Jalankan Weighted Product utk periode aktif/terpilih dan simpan sebagai batch hasil. */
    public function store(Request $request)
    {
        $data = $request->validate(['period_id' => ['nullable', 'exists:periods,id']]);
        $periode = !empty($data['period_id'])
            ? Period::findOrFail($data['period_id'])
            : (Period::aktif()->first() ?: Period::orderBy('nama')->first());

        if (!$periode) {
            return back()->with('peringatan', 'Belum ada periode. Buat periode terlebih dahulu.');
        }

        $cek = $this->spk->bobotPeriode($periode);
        if (!$cek['kons']['konsisten']) {
            return back()->with('peringatan', sprintf(
                'Perhitungan ditolak: matriks AHP TIDAK KONSISTEN (CR = %.4f > 0,10). Perbaiki matriks pada menu Bobot AHP.',
                $cek['kons']['cr']
            ));
        }

        $output = $this->spk->prosesPeriode($periode);
        if ($output === null) {
            return back()->with('peringatan', 'Perhitungan belum bisa dijalankan: lengkapi kriteria (min. 2), perbandingan AHP, dan nilai seluruh kandidat.');
        }

        $batch = ResultBatch::create([
            'period_id' => $periode->id,
            'lambda_max' => $output['kons']['lambda_max'],
            'ci' => $output['kons']['ci'],
            'cr' => $output['kons']['cr'],
            'konsisten' => $output['kons']['konsisten'],
            'jumlah_alternatif' => count($output['hasil']),
            'weights' => array_values($output['w']),
        ]);
        foreach ($output['hasil'] as $h) {
            Result::create([
                'batch_id' => $batch->id,
                'alternative_id' => $h['id'],
                'kode' => $h['kode'],
                'nama' => $h['nama'],
                'si' => $h['si'],
                'vi' => $h['vi'],
                'rank' => $h['rank'],
                'rekomendasi' => $h['rekomendasi'],
            ]);
        }

        return redirect()->route('hasil.show', $batch)
            ->with('sukses', 'Perhitungan selesai. Hasil tersimpan pada riwayat.');
    }
}
