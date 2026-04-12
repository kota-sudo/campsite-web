<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampsitePrice extends Model
{
    protected $fillable = ['campsite_id', 'label', 'start_date', 'end_date', 'price_per_night'];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function campsite()
    {
        return $this->belongsTo(Campsite::class);
    }
}
