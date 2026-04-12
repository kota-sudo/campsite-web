<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['campsite_id', 'start_date', 'end_date', 'reason'])]
class CampsiteBlockout extends Model
{
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date'   => 'date',
        ];
    }

    public function campsite(): BelongsTo
    {
        return $this->belongsTo(Campsite::class);
    }
}
