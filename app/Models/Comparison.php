<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comparison extends Model
{
    protected $fillable = ['period_id', 'criterion_a_id', 'criterion_b_id', 'nilai'];

    protected $casts = ['nilai' => 'float'];

    public function period(): BelongsTo
    {
        return $this->belongsTo(Period::class);
    }

    public function criterionA(): BelongsTo
    {
        return $this->belongsTo(Criterion::class, 'criterion_a_id');
    }

    public function criterionB(): BelongsTo
    {
        return $this->belongsTo(Criterion::class, 'criterion_b_id');
    }
}
