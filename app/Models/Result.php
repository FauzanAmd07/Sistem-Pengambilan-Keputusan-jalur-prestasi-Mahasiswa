<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    protected $fillable = ['batch_id', 'alternative_id', 'kode', 'nama', 'si', 'vi', 'rank', 'rekomendasi'];

    protected $casts = [
        'si' => 'float',
        'vi' => 'float',
        'rank' => 'integer',
        'rekomendasi' => 'boolean',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ResultBatch::class, 'batch_id');
    }

    public function alternative(): BelongsTo
    {
        return $this->belongsTo(Alternative::class);
    }
}
