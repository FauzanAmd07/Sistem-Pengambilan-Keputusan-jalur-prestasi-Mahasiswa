<?php

namespace Tests\Unit;

use App\Services\SpkService;
use PHPUnit\Framework\TestCase;

class SpkServiceTest extends TestCase
{
    private SpkService $spk;

    protected function setUp(): void
    {
        $this->spk = new SpkService();
    }

    /** Kriteria C1..C6 (id 1..6) dan perbandingan asumsi penyusun (sama dgn seeder/Excel). */
    private function kriteria(): \Illuminate\Support\Collection
    {
        return collect([
            ['id' => 1, 'kode' => 'C1'], ['id' => 2, 'kode' => 'C2'], ['id' => 3, 'kode' => 'C3'],
            ['id' => 4, 'kode' => 'C4'], ['id' => 5, 'kode' => 'C5'], ['id' => 6, 'kode' => 'C6'],
        ]);
    }

    private function perbandingan(): array
    {
        return [
            [1, 2, 1], [1, 3, 3], [1, 4, 5], [1, 5, 3], [1, 6, 5],
            [2, 3, 3], [2, 4, 5], [2, 5, 3], [2, 6, 5],
            [3, 4, 3], [3, 5, 1], [3, 6, 3],
            [4, 5, 1 / 3], [4, 6, 2],
            [5, 6, 3],
        ];
    }

    public function test_ahp_menghasilkan_bobot_dan_konsistensi_sesuai_excel(): void
    {
        $criteria = $this->kriteria();
        $m = $this->spk->matriksPenuh($criteria, $this->perbandingan());
        $norm = $this->spk->normalisasi($m);
        $w = $this->spk->bobot($norm);
        $kons = $this->spk->konsistensi($m, $w);

        // bobot referensi: numpy (A / A.sum(axis=0)).mean(axis=1) — sama dengan Excel Tahap 4
        $ekspektasiW = [0.3155589, 0.3155589, 0.1295806, 0.0616273, 0.1295806, 0.0480935];
        foreach ($ekspektasiW as $i => $ew) {
            $this->assertEqualsWithDelta($ew, $w[$i], 1e-5, "Wj kriteria ke-" . ($i + 1));
        }
        $this->assertEqualsWithDelta(1.0, array_sum($w), 1e-9, 'Σ Wj harus 1');

        $this->assertEqualsWithDelta(6.1340504, $kons['lambda_max'], 1e-4);
        $this->assertEqualsWithDelta(0.0268101, $kons['ci'], 1e-4);
        $this->assertEqualsWithDelta(1.24, $kons['ri'], 1e-9);
        $this->assertEqualsWithDelta(0.0216210, $kons['cr'], 1e-5);
        $this->assertTrue($kons['konsisten'], 'CR = 0,0216 <= 0,10 harus KONSISTEN');
    }

    public function test_matriks_penuh_resiprokal_dan_diagonal(): void
    {
        $m = $this->spk->matriksPenuh($this->kriteria(), $this->perbandingan());
        $this->assertSame(1.0, $m[0][0]);
        $this->assertEqualsWithDelta(3.0, $m[0][2], 1e-9);   // C1 vs C3 = 3
        $this->assertEqualsWithDelta(1 / 3, $m[2][0], 1e-9); // resiprokal
        $this->assertEqualsWithDelta(1 / 3, $m[3][4], 1e-9); // C4 vs C5 = 1/3
        $this->assertEqualsWithDelta(3.0, $m[4][3], 1e-9);
    }

    public function test_weighted_product_sesuai_excel(): void
    {
        $criteria = $this->kriteria();
        $m = $this->spk->matriksPenuh($criteria, $this->perbandingan());
        $w = $this->spk->bobot($this->spk->normalisasi($m));

        // Matriks keputusan Xij (Tahap 2 Excel, 11 kandidat)
        $baris = [
            ['id' => 1, 'kode' => 'A001', 'nama' => 'Andi Agfirah Ramadhan',       'nilai' => [90.00, 1, 25, 4, 70.00, 4]],
            ['id' => 2, 'kode' => 'A002', 'nama' => 'JHIMMI',                      'nilai' => [95.00, 3, 87, 4, 86.00, 1]],
            ['id' => 3, 'kode' => 'A003', 'nama' => 'Muhammad Akmal Hidayat',      'nilai' => [90.56, 1,  8, 4, 56.00, 4]],
            ['id' => 4, 'kode' => 'A004', 'nama' => 'Wafiq Azizah Rahim',          'nilai' => [90.06, 3, 32, 4, 73.707142857142857, 5]],
            ['id' => 5, 'kode' => 'A005', 'nama' => 'Muh. Ridho Anugrah',          'nilai' => [92.00, 3, 25, 3, 73.707142857142857, 5]],
            ['id' => 6, 'kode' => 'A006', 'nama' => 'Vicalov Telah',               'nilai' => [83.50, 1, 25, 3, 73.707142857142857, 2]],
            ['id' => 7, 'kode' => 'A007', 'nama' => 'Ammar zahran',                'nilai' => [80.00, 1, 25, 4, 82.00, 2]],
            ['id' => 8, 'kode' => 'A008', 'nama' => 'Nur Aisyah',                  'nilai' => [94.64, 2, 11, 4, 86.70, 4]],
            ['id' => 9, 'kode' => 'A009', 'nama' => 'Adit Hidayatullah',           'nilai' => [92.00, 2, 25, 4, 50.00, 4]],
            ['id' => 10, 'kode' => 'A010', 'nama' => 'Nurfaizah',                  'nilai' => [88.00, 2,  5, 3, 73.707142857142857, 4]],
            ['id' => 11, 'kode' => 'A011', 'nama' => 'A Thirsa Shofiyah Ramadhani','nilai' => [90.31, 1, 31, 4, 85.25, 1]],
        ];
        $tipe = ['benefit', 'benefit', 'cost', 'benefit', 'benefit', 'benefit'];
        $hasil = $this->spk->hitungWP($baris, $w, $tipe, 5);

        // Si, Vi, rank referensi: Excel/verifikasi_semantik.py
        $ekspektasi = [
            'A010' => [8.2864901, 0.1149864, 1],
            'A008' => [7.9580146, 0.1104284, 2],
            'A005' => [7.8364157, 0.1087410, 3],
            'A004' => [7.6736854, 0.1064829, 4],
            'A009' => [6.6031281, 0.0916275, 5],
            'A002' => [6.4729320, 0.0898208, 6],
            'A003' => [6.2100582, 0.0861731, 7],
            'A001' => [5.5040233, 0.0763759, 8],
            'A007' => [5.2355859, 0.0726509, 9],
            'A011' => [5.1425951, 0.0713606, 10],
            'A006' => [5.1420204, 0.0713526, 11],
        ];
        $this->assertCount(11, $hasil);
        $this->assertSame('A010', $hasil[0]['kode'], 'Peringkat 1 = Nurfaizah');

        foreach ($hasil as $h) {
            [$si, $vi, $rank] = $ekspektasi[$h['kode']];
            $this->assertEqualsWithDelta($si, $h['si'], 1e-5, "Si {$h['kode']}");
            $this->assertEqualsWithDelta($vi, $h['vi'], 1e-5, "Vi {$h['kode']}");
            $this->assertSame($rank, $h['rank'], "Rank {$h['kode']}");
            $this->assertSame($rank <= 5, $h['rekomendasi'], "Rekomendasi {$h['kode']}");
        }

        $totalVi = array_sum(array_map(fn ($h) => $h['vi'], $hasil));
        $this->assertEqualsWithDelta(1.0, $totalVi, 1e-9, 'Σ Vi harus 1 (Pers. 6)');
    }

    public function test_matriks_tidak_konsisten_terdeteksi(): void
    {
        // pelanggaran transitivitas ekstrem: C1 > C2, C2 > C3, tetapi C1 < C3 (dst.)
        // -> CR = 1,4023 >> 0,10 (referensi numpy: matriks di bawah persis)
        $perbandingan = [
            [1, 2, 9], [1, 3, 1 / 9], [1, 4, 9], [1, 5, 1], [1, 6, 1 / 9],
            [2, 3, 9], [2, 4, 1], [2, 5, 1], [2, 6, 1],
            [3, 4, 1], [3, 5, 1], [3, 6, 1],
            [4, 5, 1], [4, 6, 9],
            [5, 6, 1],
        ];
        $m = $this->spk->matriksPenuh($this->kriteria(), $perbandingan);
        $w = $this->spk->bobot($this->spk->normalisasi($m));
        $kons = $this->spk->konsistensi($m, $w);
        $this->assertFalse($kons['konsisten'], 'Matriks yang melanggar transitivitas harus TIDAK KONSISTEN');
        $this->assertEqualsWithDelta(1.4023, $kons['cr'], 1e-3);
    }
}
