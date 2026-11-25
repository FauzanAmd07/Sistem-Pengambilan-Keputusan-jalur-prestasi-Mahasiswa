<?php

namespace App\Http\Controllers;

use App\Models\Alternative;
use App\Models\Criterion;
use App\Models\Period;
use App\Models\ResultBatch;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $periodeAktif = Period::aktif()->orderBy('id')->first();
        $stat = [
            'periode' => Period::count(),
            'periode_aktif' => $periodeAktif,
            'kandidat' => $periodeAktif ? $periodeAktif->alternatives()->count() : 0,
            'kriteria' => Criterion::count(),
            'batch_terakhir' => ResultBatch::orderByDesc('created_at')->first(),
        ];
        return view('dashboard', compact('stat'));
    }
}
