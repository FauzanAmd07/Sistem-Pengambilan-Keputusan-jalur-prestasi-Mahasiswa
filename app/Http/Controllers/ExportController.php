<?php

namespace App\Http\Controllers;

use App\Models\ResultBatch;
use App\Services\SpkService;
use Dompdf\Dompdf;
use Illuminate\Http\Request;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;

class ExportController extends Controller
{
    public function __construct(private SpkService $spk)
    {
    }

    /** Unduh hasil perhitungan sebagai PDF (A4 landscape) via dompdf. */
    public function pdf(Request $request, ResultBatch $batch)
    {
        $batch->load(['results', 'period']);
        $criteria = $this->spk->kriteriaTerurut();
        $w = $batch->weights ?: $this->spk->bobotPeriode($batch->period)['w'];
        $kons = [
            'n' => count($w), 'lambda_max' => $batch->lambda_max,
            'ci' => $batch->ci, 'ri' => SpkService::RI[count($w) <= 10 ? max(count($w), 1) : 10],
            'cr' => $batch->cr, 'konsisten' => $batch->konsisten,
        ];
        $html = view('hasil.pdf', [
            'batch' => $batch,
            'criteria' => $criteria,
            'w' => $w,
            'kons' => $kons,
            'nilaiPerAlt' => $this->nilaiPerAlternatif($batch, $criteria),
        ])->render();

        $dompdf = new Dompdf(['isRemoteEnabled' => false]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('a4', 'landscape');
        $dompdf->render();
        $namaFile = 'hasil-seleksi-' . $batch->id . '-' . now()->format('Ymd-His') . '.pdf';
        return $dompdf->stream($namaFile, ['Attachment' => false]);
    }

    /** Unduh hasil perhitungan sebagai Excel (.xlsx) via openspout. */
    public function xlsx(ResultBatch $batch)
    {
        $batch->load(['results', 'period']);
        $criteria = $this->spk->kriteriaTerurut();
        $w = $batch->weights ?: $this->spk->bobotPeriode($batch->period)['w'];
        $ri = SpkService::RI[count($w) <= 10 ? max(count($w), 1) : 10];

        $writer = new Writer();
        $namaFile = 'hasil-seleksi-' . $batch->id . '-' . now()->format('Ymd-His') . '.xlsx';
        $writer->openToBrowser($namaFile);

        $writer->addRow(Row::fromValues(['HASIL SELEKSI PENERIMAAN MAHASISWA BARU JALUR PRESTASI (AHP + WEIGHTED PRODUCT)']));
        $writer->addRow(Row::fromValues(['Periode', $batch->period->nama]));
        $writer->addRow(Row::fromValues(['Kuota penerimaan', $batch->period->kuota]));
        $writer->addRow(Row::fromValues(['Waktu perhitungan', $batch->created_at->format('d-m-Y H:i:s')]));
        $writer->addRow(Row::fromValues([
            'Konsistensi AHP',
            sprintf('lambda maks = %.4f | CI = %.4f | RI = %.2f | CR = %.4f (%s)',
                $batch->lambda_max, $batch->ci, $ri, $batch->cr,
                $batch->konsisten ? 'KONSISTEN' : 'TIDAK KONSISTEN'),
        ]));
        $writer->addRow(Row::fromValues(['']));

        $header = ['Rank', 'Kode', 'Nama'];
        foreach ($criteria as $c) {
            $header[] = $c->kode . ' - ' . $c->nama;
        }
        $header[] = 'Si';
        $header[] = 'Vi';
        $header[] = 'Vi (%)';
        $header[] = 'Rekomendasi';
        $writer->addRow(Row::fromValues($header));

        // nilai kriteria per kandidat (Xij)
        $nilaiPerAlt = [];
        foreach ($batch->results as $r) {
            if ($r->alternative_id) {
                $alt = \App\Models\Alternative::with('scores')->find($r->alternative_id);
                if ($alt) {
                    $map = $alt->scores->keyBy('criterion_id');
                    $nilaiPerAlt[$r->id] = $criteria
                        ->map(fn ($c) => $map->has($c->id) ? (float) $map[$c->id]->nilai : '')
                        ->all();
                }
            }
        }

        $jmlKriteria = $criteria->count();
        foreach ($batch->results as $r) {
            $baris = [$r->rank, $r->kode, $r->nama];
            foreach (($nilaiPerAlt[$r->id] ?? []) as $v) {
                $baris[] = $v === '' ? '' : $v;
            }
            for ($i = count($baris); $i < 3 + $jmlKriteria; $i++) {
                $baris[] = '';
            }
            $baris[] = round($r->si, 6);
            $baris[] = round($r->vi, 6);
            $baris[] = round($r->vi * 100, 2) . '%';
            $baris[] = $r->rekomendasi ? 'Direkomendasikan Lulus' : 'Tidak Lulus';
            $writer->addRow(Row::fromValues($baris));
        }

        $writer->addRow(Row::fromValues(['']));
        $writer->addRow(Row::fromValues(['Bobot Wj (AHP):']));
        foreach ($criteria as $i => $c) {
            $writer->addRow(Row::fromValues([$c->kode . ' - ' . $c->nama, round($w[$i], 6)]));
        }

        $writer->close();
        exit;
    }

    /** Peta nilai kriteria (Xij) per hasil — dipakai PDF & Excel. */
    private function nilaiPerAlternatif(ResultBatch $batch, $criteria): array
    {
        $out = [];
        foreach ($batch->results as $r) {
            if (!$r->alternative_id) {
                continue;
            }
            $alt = \App\Models\Alternative::with('scores')->find($r->alternative_id);
            if ($alt) {
                $map = $alt->scores->keyBy('criterion_id');
                $out[$r->id] = $criteria
                    ->map(fn ($c) => $map->has($c->id) ? (float) $map[$c->id]->nilai : '')
                    ->all();
            }
        }
        return $out;
    }
}
