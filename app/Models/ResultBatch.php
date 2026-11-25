<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResultBatch extends Model
{
    protected $fillable = ['period_id', 'lambda_max', 'ci', 'cr', 'konsisten', 'jumlah_alternatif', 'weights'];

    protected $casts = [
        'lambda_max' => 'float',
        'ci' => 'float',
        'cr' => 'float',
        'konsisten' => 'boolean',
        'weights' => 'array',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class, 'batch_id')->orderBy('rank');
    }
}
