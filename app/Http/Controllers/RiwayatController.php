<?php

namespace App\Http\Controllers;

use App\Models\ResultBatch;
use Illuminate\Http\Request;

class RiwayatController extends Controller
{
    public function index()
    {
        $batches = ResultBatch::with('period')->withCount('results')
            ->orderByDesc('created_at')->orderByDesc('id')->get();
        return view('riwayat.index', compact('batches'));
    }

    public function destroy(ResultBatch $batch)
    {
        $batch->delete();
        return redirect()->route('riwayat.index')->with('sukses', 'Riwayat perhitungan dihapus.');
    }
}
