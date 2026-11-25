<?php

namespace App\Services;

use App\Models\Comparison;
use App\Models\Criterion;
use App\Models\Period;
use Illuminate\Support\Collection;

/**
 * Mesin perhitungan SPK metode AHP + Weighted Product (WP).
 * Porting langsung dari lembar kerja Excel "Perhitungan AHP-WP"
 * (Persamaan (1) s.d. (6) pada draf artikel Kelompok 4).
 */
class SpkService
{
    /** Random Index (RI) Saaty untuk n = 1..10; RI n=6 = 1,24 (Pers. 4). */
    public const RI = [
        1 => 0.0, 2 => 0.0, 3 => 0.58, 4 => 0.90, 5 => 1.12,
        6 => 1.24, 7 => 1.32, 8 => 1.41, 9 => 1.45, 10 => 1.49,
    ];

    /**
     * Susun matriks perbandingan berpasangan penuh (n x n) dari segitiga atas.
     * $criteria terurut (urutan, id); $comparisons = pasangan [a_id, b_id, nilai]
     * dengan a mendahului b (segitiga atas). Segitiga bawah = 1/nilai (resiprokal).
     */
    public function matriksPenuh(Collection $criteria, array $comparisons): array
    {
        $ids = $criteria->pluck('id')->all();
        $n = count($ids);
        $m = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $m[$i][$j] = ($i === $j) ? 1.0 : null;
            }
        }
        $pos = array_flip($ids);
        foreach ($comparisons as [$aId, $bId, $nilai]) {
            if (!isset($pos[$aId], $pos[$bId])) {
                continue; // kriteria tidak lagi ada
            }
            $i = $pos[$aId];
            $j = $pos[$bId];
            $nilai = (float) $nilai;
            if ($i === $j) {
                continue;
            }
            if ($i < $j) {
                $m[$i][$j] = $nilai;
                $m[$j][$i] = 1.0 / $nilai;
            } else {
                $m[$j][$i] = $nilai;
                $m[$i][$j] = 1.0 / $nilai;
            }
        }
        // pasangan tanpa input diasumsikan sama penting (1)
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                if ($m[$i][$j] === null) {
                    $m[$i][$j] = 1.0;
                }
            }
        }
        return $m;
    }

    /** Normalisasi per kolom (aij / Σ aij — bagian Pers. 2). */
    public function normalisasi(array $m): array
    {
        $n = count($m);
        $jumlah = array_fill(0, $n, 0.0);
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $jumlah[$j] += $m[$i][$j];
            }
        }
        $norm = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $norm[$i][$j] = $jumlah[$j] != 0.0 ? $m[$i][$j] / $jumlah[$j] : 0.0;
            }
        }
        return $norm;
    }

    /** Bobot prioritas Wj = rata-rata baris matriks ternormalisasi (Pers. 2). */
    public function bobot(array $norm): array
    {
        $n = count($norm);
        $w = [];
        for ($i = 0; $i < $n; $i++) {
            $w[$i] = array_sum($norm[$i]) / $n;
        }
        return $w;
    }

    /** λmaks = rata-rata (A·W)i / Wi (Pers. 3). */
    public function lambdaMax(array $m, array $w): float
    {
        $n = count($m);
        $rata = [];
        for ($i = 0; $i < $n; $i++) {
            if ($w[$i] == 0.0) {
                $rata[$i] = 0.0;
                continue;
            }
            $aw = 0.0;
            for ($j = 0; $j < $n; $j++) {
                $aw += $m[$i][$j] * $w[$j];
            }
            $rata[$i] = $aw / $w[$i];
        }
        return array_sum($rata) / $n;
    }

    /** Uji konsistensi lengkap: λmaks, CI, RI, CR, dan status (Pers. 3-4). */
    public function konsistensi(array $m, array $w): array
    {
        $n = count($m);
        $lambdaMax = $this->lambdaMax($m, $w);
        $ci = $n > 1 ? ($lambdaMax - $n) / ($n - 1) : 0.0;
        $ri = self::RI[min($n, 10)] ?? 1.49;
        $cr = $ri > 0 ? $ci / $ri : 0.0;
        return [
            'n' => $n,
            'lambda_max' => $lambdaMax,
            'ci' => $ci,
            'ri' => $ri,
            'cr' => $cr,
            'konsisten' => $cr <= 0.10,
        ];
    }

    /**
     * Weighted Product (Pers. 5-6).
     * $baris = [ ['id'=>..,'kode'=>..,'nama'=>..,'nilai'=>[v1..vn]], ... ]
     * $w = bobot terurut; $tipe = ['benefit'|'cost'] terurut.
     * Pangkat ±Wj (cost negatif) -> Si = ∏ Xij^±Wj -> Vi = Si / Σ Si.
     */
    public function hitungWP(array $baris, array $w, array $tipe, int $kuota): array
    {
        $si = [];
        foreach ($baris as $k => $b) {
            $hasil = 1.0;
            foreach ($b['nilai'] as $j => $v) {
                $pangkat = ($tipe[$j] === 'cost') ? -$w[$j] : $w[$j];
                $hasil *= pow((float) $v, $pangkat);
            }
            $si[$k] = $hasil;
        }
        $total = array_sum($si);

        $out = [];
        foreach ($baris as $k => $b) {
            $out[$k] = [
                'id' => $b['id'],
                'kode' => $b['kode'],
                'nama' => $b['nama'],
                'si' => $si[$k],
                'vi' => $total > 0 ? $si[$k] / $total : 0.0,
            ];
        }

        // Peringkat menurun berdasarkan Vi (tie => rank sama, berikutnya dilompati, spt RANK Excel)
        $viSorted = array_map(fn ($r) => $r['vi'], $out);
        rsort($viSorted);
        foreach ($out as &$r) {
            $rank = 1;
            foreach ($viSorted as $v) {
                if ($v > $r['vi'] + PHP_FLOAT_EPSILON) {
                    $rank++;
                } else {
                    break;
                }
            }
            $r['rank'] = $rank;
            $r['rekomendasi'] = $rank <= $kuota;
        }
        unset($r);

        usort($out, fn ($a, $b) => ($a['rank'] <=> $b['rank']) ?: strcmp($a['kode'], $b['kode']));
        return $out;
    }

    /** Ambil perbandingan tersimpan sebuah periode sebagai pasangan [aId, bId, nilai]. */
    public function perbandinganPeriode(Period $period): array
    {
        return Comparison::where('period_id', $period->id)
            ->get(['criterion_a_id', 'criterion_b_id', 'nilai'])
            ->map(fn ($c) => [$c->criterion_a_id, $c->criterion_b_id, (float) $c->nilai])
            ->all();
    }

    /** Kriteria global terurut. */
    public function kriteriaTerurut(): Collection
    {
        return Criterion::orderBy('urutan')->orderBy('id')->get();
    }

    /**
     * Hitung bobot AHP dari perbandingan tersimpan sebuah periode.
     * Return [m, norm, w, kons, criteria].
     */
    public function bobotPeriode(Period $period): array
    {
        $criteria = $this->kriteriaTerurut();
        $m = $this->matriksPenuh($criteria, $this->perbandinganPeriode($period));
        $norm = $this->normalisasi($m);
        $w = $this->bobot($norm);
        $kons = $this->konsistensi($m, $w);
        return ['m' => $m, 'norm' => $norm, 'w' => $w, 'kons' => $kons, 'criteria' => $criteria];
    }

    /**
     * Jalankan WP utk semua kandidat sebuah periode menggunakan bobot AHP tersimpan.
     * Return [hasil, kons, criteria]; null bila data belum lengkap.
     */
    public function prosesPeriode(Period $period): ?array
    {
        $criteria = $this->kriteriaTerurut();
        $perbandingan = $this->perbandinganPeriode($period);
        if ($criteria->count() < 2 || count($perbandingan) === 0) {
            return null;
        }
        $m = $this->matriksPenuh($criteria, $perbandingan);
        $norm = $this->normalisasi($m);
        $w = $this->bobot($norm);
        $kons = $this->konsistensi($m, $w);

        $baris = [];
        foreach ($period->alternatives()->orderBy('kode')->get() as $alt) {
            $nilai = [];
            foreach ($alt->nilaiTerurut() as $score) {
                if ($score === null || $score->nilai === null) {
                    return null; // nilai belum lengkap
                }
                $nilai[] = (float) $score->nilai;
            }
            $baris[] = [
                'id' => $alt->id,
                'kode' => $alt->kode,
                'nama' => $alt->nama,
                'nilai' => $nilai,
            ];
        }
        if (count($baris) === 0) {
            return null;
        }
        $tipe = $criteria->pluck('tipe')->all();
        $hasil = $this->hitungWP($baris, $w, $tipe, (int) $period->kuota);
        return ['hasil' => $hasil, 'kons' => $kons, 'criteria' => $criteria, 'w' => $w];
    }
}
