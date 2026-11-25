<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Period extends Model
{
    protected $fillable = ['nama', 'kuota', 'aktif'];

    protected $casts = ['aktif' => 'boolean'];

    public function alternatives(): HasMany
    {
        return $this->hasMany(Alternative::class);
    }

    public function comparisons(): HasMany
    {
        return $this->hasMany(Comparison::class);
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ResultBatch::class);
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }
}
