<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'campsite_id', 'campsite_plan_id', 'check_in_date', 'check_out_date', 'num_guests', 'total_price', 'status', 'notes'])]
class Reservation extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'check_in_date'  => 'date:Y-m-d',
            'check_out_date' => 'date:Y-m-d',
            'num_guests'     => 'integer',
            'total_price'    => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function campsite(): BelongsTo
    {
        return $this->belongsTo(Campsite::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(CampsitePlan::class, 'campsite_plan_id');
    }

    public function nights(): int
    {
        return $this->check_in_date->diffInDays($this->check_out_date);
    }

    public function scopeOverlapping(Builder $query, string $checkIn, string $checkOut): Builder
    {
        return $query->whereIn('status', ['pending', 'confirmed'])
                     ->where('check_in_date', '<', $checkOut)
                     ->where('check_out_date', '>', $checkIn);
    }

    public function isCancellable(): bool
    {
        // キャンセル期限: チェックイン前日の23:59まで
        // つまりチェックイン日 > 今日であること (当日・過去はNG)
        return in_array($this->status, ['pending', 'confirmed'])
            && $this->check_in_date->gt(today());
    }

    /** キャンセル期限日 (チェックイン前日) */
    public function cancellationDeadline(): \Carbon\Carbon
    {
        return $this->check_in_date->copy()->subDay();
    }

    public function isReviewable(): bool
    {
        return $this->status === 'confirmed'
            && $this->check_out_date->isPast()
            && ! $this->review()->exists();
    }

    public function review(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Review::class);
    }
}
