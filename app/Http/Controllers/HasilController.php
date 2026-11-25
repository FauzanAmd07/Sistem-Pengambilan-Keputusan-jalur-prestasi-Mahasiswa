<?php

namespace App\Http\Controllers;

use App\Models\ResultBatch;
use App\Services\SpkService;
use Illuminate\Http\Request;

class HasilController extends Controller
{
    public function __construct(private SpkService $spk)
    {
    }

    /** Hasil terbaru periode aktif. */
    public function index()
    {
        $batch = ResultBatch::orderByDesc('created_at')->orderByDesc('id')->first();
        if (!$batch) {
            return redirect()->route('riwayat.index')
                ->with('peringatan', 'Belum ada hasil. Jalankan proses perhitungan terlebih dahulu.');
        }
        return redirect()->route('hasil.show', $batch);
    }

    /** Detail satu batch hasil (peringkat, bobot, rekomendasi). */
    public function show(ResultBatch $batch)
    {
        $batch->load(['results', 'period']);
        $criteria = $this->spk->kriteriaTerurut();
        // pakai snapshot bobot saat batch dibuat; jatuh ke perhitungan ulang bila kosong
        $w = $batch->weights ?: $this->spk->bobotPeriode($batch->period)['w'];
        return view('hasil.show', compact('batch', 'criteria', 'w'));
    }
}
