<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Alternative extends Model
{
    protected $fillable = ['period_id', 'kode', 'nama'];

    protected $with = ['scores'];

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    /** Nilai kriteria terurut berdasarkan urutan kriteria; null bila belum diisi. */
    public function nilaiTerurut(): array
    {
        $map = $this->scores->keyBy('criterion_id');
        return Criterion::orderBy('urutan')->orderBy('id')->get()
            ->map(fn (Criterion $c) => $map->get($c->id))
            ->all();
    }
}
