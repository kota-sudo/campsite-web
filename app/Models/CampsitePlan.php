<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['campsite_id', 'name', 'description', 'image_path', 'price_per_night', 'capacity', 'stock', 'is_active', 'sort_order'])]
class CampsitePlan extends Model
{
    protected function casts(): array
    {
        return [
            'price_per_night' => 'integer',
            'capacity'        => 'integer',
            'stock'           => 'integer',
            'sort_order'      => 'integer',
            'is_active'       => 'boolean',
        ];
    }

    public function campsite(): BelongsTo
    {
        return $this->belongsTo(Campsite::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * 指定日程で予約可能かどうか (stock と重複予約数を比較)
     */
    public function isAvailableFor(string $checkIn, string $checkOut): bool
    {
        $booked = $this->reservations()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('check_in_date', '<', $checkOut)
            ->where('check_out_date', '>', $checkIn)
            ->count();

        return $booked < $this->stock;
    }

    /** 残り予約可能数 */
    public function remainingStockFor(string $checkIn, string $checkOut): int
    {
        $booked = $this->reservations()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('check_in_date', '<', $checkOut)
            ->where('check_out_date', '>', $checkIn)
            ->count();

        return max(0, $this->stock - $booked);
    }
}
