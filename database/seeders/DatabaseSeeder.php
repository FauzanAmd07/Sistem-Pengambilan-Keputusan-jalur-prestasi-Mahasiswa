<?php

namespace Database\Seeders;

use App\Models\Alternative;
use App\Models\Comparison;
use App\Models\Criterion;
use App\Models\Period;
use App\Models\Score;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed aplikasi:
     *  - 1 akun admin (admin@spk.test / admin123)
     *  - 6 kriteria seleksi (Tabel 1 draf artikel)
     *  - 1 periode aktif, kuota 5
     *  - 11 kandidat unik A001-A011 dari jawaban Google Form (nilai hasil konversi skala,
     *    kolom "catatan" menyimpan jawaban asli form utk pelacakan)
     *  - 15 perbandingan berpasangan AHP (asumsi penyusun; CR = 0,0216 konsisten)
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@spk.test'],
            ['name' => 'Administrator SPK', 'password' => Hash::make('admin123')]
        );

        // ---- Kriteria (Tabel 1 draf artikel) ----
        $kriteriaData = [
            ['kode' => 'C1', 'nama' => 'Rata-rata rapor', 'tipe' => 'benefit', 'urutan' => 1,
             'petunjuk' => 'Nilai rapor 0-100, mis. 90.50'],
            ['kode' => 'C2', 'nama' => 'Tingkat prestasi', 'tipe' => 'benefit', 'urutan' => 2,
             'petunjuk' => '1=Sekolah, 2=Kab/Kota, 3=Provinsi, 4=Nasional, 5=Internasional (tidak ada = 1)'],
            ['kode' => 'C3', 'nama' => 'Peringkat kelas', 'tipe' => 'cost', 'urutan' => 3,
             'petunjuk' => 'Angka peringkat, semakin kecil semakin baik (Cost); tanpa data = median peringkat tersedia'],
            ['kode' => 'C4', 'nama' => 'Akreditasi sekolah', 'tipe' => 'benefit', 'urutan' => 4,
             'petunjuk' => 'A=4, B=3, C=2, lainnya=1'],
            ['kode' => 'C5', 'nama' => 'Nilai tes/wawancara', 'tipe' => 'benefit', 'urutan' => 5,
             'petunjuk' => 'Skala 0-100 (nilai UTBK 0-1000 dibagi 10); urutan: tes -> TKA -> pengisi rata-rata 73,71'],
            ['kode' => 'C6', 'nama' => 'Peran organisasi', 'tipe' => 'benefit', 'urutan' => 6,
             'petunjuk' => '1=Tidak aktif, 2=Anggota, 3=Koordinator bidang, 4=Pengurus inti, 5=Ketua/wakil ketua'],
        ];
        $kriteria = collect($kriteriaData)
            ->map(fn ($d) => Criterion::updateOrCreate(['kode' => $d['kode']], $d))
            ->keyBy('kode');

        // ---- Periode aktif ----
        $periode = Period::updateOrCreate(
            ['nama' => 'Gelombang 1 - 2025/2026'],
            ['kuota' => 5, 'aktif' => true]
        );

        // ---- Kandidat A001-A011 (jawaban unik terakhir per nama) ----
        // [kode, nama, [C1, C2, C3, C4, C5, C6] hasil konversi, catatan per kriteria]
        $pengisiC5 = 73.707142857142857; // rata-rata nilai tes/TKA tersedia
        $kandidat = [
            ['A001', 'Andi Agfirah Ramadhan', [90.00, 1, 25, 4, 70.00, 4],
             ['rapor 90', 'Tidak ada', 'Tidak -> median 25', 'A', 'TKA 70', 'Pengurus']],
            ['A002', 'JHIMMI', [95.00, 3, 87, 4, 86.00, 1],
             ['rapor 95', 'Tingkat Provinsi', 'iya ,peringkat 87 -> 87', 'A', 'TKA 86', 'Tidak Aktif']],
            ['A003', 'Muhammad Akmal Hidayat', [90.56, 1, 8, 4, 56.00, 4],
             ['rapor 90,56', 'Tingkat Sekolah', 'peringkat 8', 'A', 'UTBK 560 -> 56', 'Pengurus']],
            ['A004', 'Wafiq Azizah Rahim', [90.06, 3, 32, 4, $pengisiC5, 5],
             ['rapor 90,06', 'Tingkat Provinsi', 'peringkat 32', 'A', 'tidak ada tes & TKA -> pengisi 73,71', 'Ketua atau wakil ketua']],
            ['A005', 'Muh. Ridho Anugrah', [92.00, 3, 25, 3, $pengisiC5, 5],
             ['rapor 92', 'Tingkat Provinsi', 'peringkat 25', 'B', 'Tidak ada tes TKA -> pengisi 73,71', 'Ketua atau wakil ketua']],
            ['A006', 'Vicalov Telah', [83.50, 1, 25, 3, $pengisiC5, 2],
             ['rapor 83,50', 'Tidak ada', 'Tidak -> median 25', 'B', 'baik (non-angka) -> pengisi 73,71', 'Anggota']],
            ['A007', 'Ammar zahran', [80.00, 1, 25, 4, 82.00, 2],
             ['rapor 80', 'Tidak ada', 'tidak -> median 25', 'A', 'TKA 82', 'Anggota']],
            ['A008', 'Nur Aisyah', [94.64, 2, 11, 4, 86.70, 4],
             ['rapor 94,64', 'Tingkat Kabupaten/Kota', 'Iya peringkat 11 umum -> 11', 'A', 'TKA 86,7', 'Pengurus']],
            ['A009', 'Adit Hidayatullah', [92.00, 2, 25, 4, 50.00, 4],
             ['rapor 92', 'Tingkat Kabupaten/Kota', 'Tidak -> median 25', 'A', 'nilai tes 50', 'Pengurus']],
            ['A010', 'Nurfaizah', [88.00, 2, 5, 3, $pengisiC5, 4],
             ['rapor 88', 'Tingkat Kabupaten/Kota', 'Iya peringkat 5 umum -> 5', 'B', 'Tidak TKA -> pengisi 73,71', 'Pengurus']],
            ['A011', 'A Thirsa Shofiyah Ramadhani', [90.31, 1, 31, 4, 85.25, 1],
             ['rapor 90,31', 'Tidak ada', 'peringkat 31', 'A', 'TKA 85,25', 'Tidak Aktif']],
        ];

        foreach ($kandidat as [$kode, $nama, $nilai, $catatan]) {
            $alt = Alternative::updateOrCreate(
                ['period_id' => $periode->id, 'kode' => $kode],
                ['nama' => $nama]
            );
            foreach ($kriteriaData as $idx => $kd) {
                Score::updateOrCreate(
                    ['alternative_id' => $alt->id, 'criterion_id' => $kriteria[$kd['kode']]->id],
                    ['nilai' => $nilai[$idx], 'catatan' => $catatan[$idx]]
                );
            }
        }

        // ---- Perbandingan berpasangan AHP (segitiga atas; asumsi penyusun, CR = 0,0216) ----
        // [kriteria a, kriteria b, nilai Saaty] — a mendahului b menurut urutan
        $perbandingan = [
            ['C1', 'C2', 1], ['C1', 'C3', 3], ['C1', 'C4', 5], ['C1', 'C5', 3], ['C1', 'C6', 5],
            ['C2', 'C3', 3], ['C2', 'C4', 5], ['C2', 'C5', 3], ['C2', 'C6', 5],
            ['C3', 'C4', 3], ['C3', 'C5', 1], ['C3', 'C6', 3],
            ['C4', 'C5', 1 / 3], ['C4', 'C6', 2],
            ['C5', 'C6', 3],
        ];
        foreach ($perbandingan as [$ka, $kb, $nilai]) {
            Comparison::updateOrCreate(
                [
                    'period_id' => $periode->id,
                    'criterion_a_id' => $kriteria[$ka]->id,
                    'criterion_b_id' => $kriteria[$kb]->id,
                ],
                ['nilai' => $nilai]
            );
        }
    }
}
