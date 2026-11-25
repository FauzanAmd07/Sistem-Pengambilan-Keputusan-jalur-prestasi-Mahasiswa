<?php

namespace App\Http\Controllers;

use App\Models\Comparison;
use App\Models\Period;
use App\Services\SpkService;
use Illuminate\Http\Request;

class AhpController extends Controller
{
    public function __construct(private SpkService $spk)
    {
    }

    /** Form matriks perbandingan berpasangan + hasil bobot & uji konsistensi terkini. */
    public function edit(Request $request)
    {
        $periodId = $request->query('periode');
        $periods = Period::withCount('alternatives')->orderByDesc('aktif')->orderBy('nama')->get();
        $periode = $periodId
            ? $periods->firstWhere('id', (int) $periodId)
            : ($periods->firstWhere('aktif', true) ?: $periods->first());

        if (!$periode) {
            return view('ahp.edit', [
                'periods' => $periods, 'periode' => null, 'criteria' => collect(),
                'terisi' => [], 'hasil' => null,
            ]);
        }

        $criteria = $this->spk->kriteriaTerurut();
        $terisi = Comparison::where('period_id', $periode->id)
            ->get()->keyBy(fn ($c) => $c->criterion_a_id . '_' . $c->criterion_b_id);
        $hasil = $criteria->count() >= 2 ? $this->spk->bobotPeriode($periode) : null;

        return view('ahp.edit', compact('periods', 'periode', 'criteria', 'terisi', 'hasil'));
    }

    /** Simpan segitiga atas matriks lalu uji konsistensi (Pers. 3-4). */
    public function store(Request $request)
    {
        $data = $request->validate([
            'period_id' => ['required', 'exists:periods,id'],
            'banding' => ['required', 'array'],
        ]);

        $criteria = $this->spk->kriteriaTerurut();
        $ids = $criteria->pluck('id')->all();
        $nilai = [];

        // segitiga atas: banding[a_b] = nilai Saaty
        foreach ($ids as $i => $a) {
            foreach ($ids as $j => $b) {
                if ($j <= $i) {
                    continue;
                }
                $kunci = $a . '_' . $b;
                $v = $data['banding'][$kunci] ?? null;
                if ($v === null || $v === '' || !is_numeric($v) || (float) $v <= 0 || (float) $v > 9) {
                    return back()->withErrors([
                        'banding' => "Nilai perbandingan untuk pasangan {$criteria[$i]->kode}-{$criteria[$j]->kode} harus angka skala Saaty 1-9 (boleh pecahan, mis. 0.3333 utk 1/3).",
                    ])->withInput();
                }
                $nilai[] = [(int) $a, (int) $b, (float) $v];
            }
        }

        // pratinjau konsistensi SEBELUM menyimpan
        $matriks = $this->spk->matriksPenuh($criteria, $nilai);
        $norm = $this->spk->normalisasi($matriks);
        $w = $this->spk->bobot($norm);
        $kons = $this->spk->konsistensi($matriks, $w);

        foreach ($nilai as [$a, $b, $v]) {
            Comparison::updateOrCreate(
                ['period_id' => $data['period_id'], 'criterion_a_id' => $a, 'criterion_b_id' => $b],
                ['nilai' => $v]
            );
        }

        $pesan = sprintf(
            'Matriks disimpan. λmaks = %.4f | CI = %.4f | RI = %.2f | CR = %.4f — %s (Pers. 3-4).',
            $kons['lambda_max'], $kons['ci'], $kons['ri'], $kons['cr'],
            $kons['konsisten'] ? 'KONSISTEN (CR <= 0,10)' : 'TIDAK KONSISTEN (CR > 0,10), perbaiki matriks sebelum memproses WP'
        );

        $redirect = redirect()->route('ahp.edit', ['periode' => $data['period_id']]);
        return $kons['konsisten']
            ? $redirect->with('sukses', $pesan)
            : $redirect->with('peringatan', $pesan)->with('cr_gagal', true);
    }
}
