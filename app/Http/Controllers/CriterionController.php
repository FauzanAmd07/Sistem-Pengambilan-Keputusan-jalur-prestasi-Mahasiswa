<?php

namespace App\Http\Controllers;

use App\Models\Criterion;
use Illuminate\Http\Request;

class CriterionController extends Controller
{
    public function index()
    {
        $criteria = Criterion::orderBy('urutan')->orderBy('id')->get();
        return view('kriteria.index', compact('criteria'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kode' => ['required', 'string', 'max:10', 'unique:criteria,kode'],
            'nama' => ['required', 'string', 'max:100'],
            'tipe' => ['required', 'in:benefit,cost'],
            'petunjuk' => ['nullable', 'string', 'max:255'],
            'urutan' => ['required', 'integer', 'min:1', 'max:99'],
        ], [
            'kode.unique' => 'Kode kriteria sudah dipakai.',
        ]);
        Criterion::create($data);
        return redirect()->route('kriteria.index')->with('sukses', 'Kriteria berhasil ditambahkan.');
    }

    public function edit(Criterion $criterion)
    {
        return view('kriteria.edit', compact('criterion'));
    }

    public function update(Request $request, Criterion $criterion)
    {
        $data = $request->validate([
            'kode' => ['required', 'string', 'max:10', 'unique:criteria,kode,' . $criterion->id],
            'nama' => ['required', 'string', 'max:100'],
            'tipe' => ['required', 'in:benefit,cost'],
            'petunjuk' => ['nullable', 'string', 'max:255'],
            'urutan' => ['required', 'integer', 'min:1', 'max:99'],
        ]);
        $criterion->update($data);
        return redirect()->route('kriteria.index')->with('sukses', 'Kriteria berhasil diperbarui.');
    }

    public function destroy(Criterion $criterion)
    {
        $criterion->delete();
        return redirect()->route('kriteria.index')->with('sukses', 'Kriteria beserta nilai dan perbandingannya dihapus.');
    }
}
