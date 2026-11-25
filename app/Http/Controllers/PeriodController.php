<?php

namespace App\Http\Controllers;

use App\Models\Period;
use Illuminate\Http\Request;

class PeriodController extends Controller
{
    public function index()
    {
        $periods = Period::withCount('alternatives')->orderByDesc('aktif')->orderBy('nama')->get();
        return view('periode.index', compact('periods'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'kuota' => ['required', 'integer', 'min:1', 'max:100'],
            'aktif' => ['nullable', 'boolean'],
        ], [
            'nama.required' => 'Nama periode wajib diisi.',
            'kuota.required' => 'Kuota wajib diisi.',
            'kuota.min' => 'Kuota minimal 1.',
        ]);
        $period = Period::create($this->dataPeriode($data));
        if ($period->aktif) {
            Period::whereKeyNot($period->id)->update(['aktif' => false]);
        }
        return redirect()->route('periode.index')->with('sukses', 'Periode berhasil ditambahkan.');
    }

    public function edit(Period $period)
    {
        return view('periode.edit', compact('period'));
    }

    public function update(Request $request, Period $period)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'kuota' => ['required', 'integer', 'min:1', 'max:100'],
            'aktif' => ['nullable', 'boolean'],
        ]);
        $period->update($this->dataPeriode($data));
        if ($period->aktif) {
            Period::whereKeyNot($period->id)->update(['aktif' => false]);
        }
        return redirect()->route('periode.index')->with('sukses', 'Periode berhasil diperbarui.');
    }

    public function destroy(Period $period)
    {
        $period->delete();
        return redirect()->route('periode.index')->with('sukses', 'Periode beserta datanya dihapus.');
    }

    private function dataPeriode(array $data): array
    {
        return [
            'nama' => $data['nama'],
            'kuota' => (int) $data['kuota'],
            'aktif' => (bool) ($data['aktif'] ?? false),
        ];
    }
}
