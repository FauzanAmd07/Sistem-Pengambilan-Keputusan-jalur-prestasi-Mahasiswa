<?php

namespace App\Http\Controllers;

use App\Models\Alternative;
use App\Models\Criterion;
use App\Models\Period;
use App\Models\Score;
use Illuminate\Http\Request;

class AlternativeController extends Controller
{
    public function index(Request $request)
    {
        $periodId = $request->query('periode');
        $periods = Period::withCount('alternatives')->orderByDesc('aktif')->orderBy('nama')->get();
        $periodeTerpilih = $periodId
            ? $periods->firstWhere('id', (int) $periodId)
            : ($periods->firstWhere('aktif', true) ?: $periods->first());
        $alternatives = $periodeTerpilih
            ? $periodeTerpilih->alternatives()->with('scores')->orderBy('kode')->get()
            : collect();
        return view('kandidat.index', compact('periods', 'periodeTerpilih', 'alternatives'));
    }

    public function create()
    {
        $periods = Period::orderBy('nama')->get();
        $periodeAktif = Period::aktif()->first();
        return view('kandidat.create', compact('periods', 'periodeAktif'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'period_id' => ['required', 'exists:periods,id'],
            'kode' => ['required', 'string', 'max:10'],
            'nama' => ['required', 'string', 'max:100'],
        ], [
            'period_id.required' => 'Periode wajib dipilih.',
            'kode.required' => 'Kode kandidat wajib diisi.',
            'nama.required' => 'Nama kandidat wajib diisi.',
        ]);
        $sudahAda = Alternative::where('period_id', $data['period_id'])->where('kode', $data['kode'])->exists();
        if ($sudahAda) {
            return back()->withErrors(['kode' => 'Kode kandidat sudah dipakai pada periode ini.'])->withInput();
        }
        Alternative::create($data);
        return redirect()->route('kandidat.index')->with('sukses', 'Kandidat berhasil ditambahkan. Isi nilainya pada menu Nilai.');
    }

    public function edit(Alternative $alternative)
    {
        $periods = Period::orderBy('nama')->get();
        return view('kandidat.edit', compact('alternative', 'periods'));
    }

    public function update(Request $request, Alternative $alternative)
    {
        $data = $request->validate([
            'period_id' => ['required', 'exists:periods,id'],
            'kode' => ['required', 'string', 'max:10'],
            'nama' => ['required', 'string', 'max:100'],
        ]);
        $bentrok = Alternative::where('period_id', $data['period_id'])->where('kode', $data['kode'])
            ->whereKeyNot($alternative->id)->exists();
        if ($bentrok) {
            return back()->withErrors(['kode' => 'Kode kandidat sudah dipakai pada periode ini.'])->withInput();
        }
        $alternative->update($data);
        return redirect()->route('kandidat.index')->with('sukses', 'Kandidat berhasil diperbarui.');
    }

    public function destroy(Alternative $alternative)
    {
        $alternative->delete();
        return redirect()->route('kandidat.index')->with('sukses', 'Kandidat beserta nilainya dihapus.');
    }

    /** Form input nilai kandidat per kriteria. */
    public function nilai(Alternative $alternative)
    {
        $criteria = Criterion::orderBy('urutan')->orderBy('id')->get();
        $scores = $alternative->scores->keyBy('criterion_id');
        return view('kandidat.nilai', compact('alternative', 'criteria', 'scores'));
    }

    /** Simpan nilai kandidat per kriteria. */
    public function simpanNilai(Request $request, Alternative $alternative)
    {
        $criteria = Criterion::orderBy('urutan')->orderBy('id')->get();
        $aturan = [];
        foreach ($criteria as $c) {
            $aturan["nilai.{$c->id}"] = ['required', 'numeric'];
        }
        $pesan = [];
        foreach ($criteria as $c) {
            $pesan["nilai.{$c->id}.required"] = "Nilai {$c->kode} ({$c->nama}) wajib diisi.";
            $pesan["nilai.{$c->id}.numeric"] = "Nilai {$c->kode} harus berupa angka.";
        }
        $data = $request->validate($aturan, $pesan);

        foreach ($criteria as $c) {
            Score::updateOrCreate(
                ['alternative_id' => $alternative->id, 'criterion_id' => $c->id],
                [
                    'nilai' => $data['nilai'][$c->id],
                    'catatan' => $request->input("catatan.{$c->id}"),
                ]
            );
        }
        return redirect()->route('kandidat.index')->with('sukses', "Nilai kandidat {$alternative->kode} berhasil disimpan.");
    }
}
