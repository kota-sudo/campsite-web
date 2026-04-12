<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable(['user_id', 'name', 'description', 'type', 'capacity', 'price_per_night', 'weekend_multiplier', 'is_active', 'latitude', 'longitude', 'address'])]
class Campsite extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'capacity'           => 'integer',
            'price_per_night'    => 'integer',
            'weekend_multiplier' => 'float',
            'is_active'          => 'boolean',
            'latitude'           => 'float',
            'longitude'          => 'float',
        ];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(CampsiteImage::class)->orderBy('sort_order');
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'campsite_amenity');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class)->latest();
    }

    public function prices(): HasMany
    {
        return $this->hasMany(CampsitePrice::class)->orderBy('start_date');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(CampsiteQuestion::class)->orderBy('created_at');
    }

    public function plans(): HasMany
    {
        return $this->hasMany(CampsitePlan::class)->orderBy('sort_order');
    }

    public function activePlans(): HasMany
    {
        return $this->hasMany(CampsitePlan::class)->where('is_active', true)->orderBy('sort_order');
    }

    public function blockouts(): HasMany
    {
        return $this->hasMany(CampsiteBlockout::class)->orderBy('start_date');
    }

    /**
     * 指定日程がブラックアウト期間と重複するか確認
     */
    public function hasBlockoutConflict(string $checkIn, string $checkOut): bool
    {
        return $this->blockouts()
            ->whereDate('start_date', '<', $checkOut)
            ->whereDate('end_date',   '>', $checkIn)
            ->exists();
    }

    public function averageRating(): float
    {
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }

    /** 10点満点スコア（Agoda表記）: 4.2 → 8.4 */
    public function averageRatingOutOf10(): float
    {
        return round(($this->reviews()->avg('rating') ?? 0) * 2, 1);
    }

    /** スコアラベル（10点満点ベース） */
    public function ratingLabel(): ?string
    {
        if ($this->reviews()->doesntExist()) return null;
        $score = $this->averageRatingOutOf10();
        return match(true) {
            $score >= 9.0 => '最高',
            $score >= 8.0 => 'とても良い',
            $score >= 7.0 => '良い',
            $score >= 6.0 => 'まずまず',
            default       => '普通',
        };
    }

    /**
     * 指定日の適用料金を返す（特別価格がなければ基本料金）
     */
    public function getPriceForDate(string|\DateTimeInterface $date): int
    {
        $d = is_string($date) ? \Carbon\Carbon::parse($date) : \Carbon\Carbon::instance($date);

        $special = $this->prices()
            ->whereDate('start_date', '<=', $d->toDateString())
            ->whereDate('end_date',   '>=', $d->toDateString())
            ->orderByDesc('price_per_night') // 複数ヒット時は高い方を優先
            ->first();

        if ($special) {
            return $special->price_per_night;
        }

        // 土日は weekend_multiplier を適用
        $base       = $this->price_per_night;
        $multiplier = $this->weekend_multiplier ?? 1.0;
        $isWeekend  = $d->isWeekend(); // Carbon:土=6 / 日=0

        return $isWeekend && $multiplier > 1.0
            ? (int) round($base * $multiplier)
            : $base;
    }

    /** 週末料金（倍率適用後）*/
    public function weekendPrice(): int
    {
        $m = $this->weekend_multiplier ?? 1.0;
        return $m > 1.0 ? (int) round($this->price_per_night * $m) : $this->price_per_night;
    }

    /** 週末料金設定があるか */
    public function hasWeekendSurcharge(): bool
    {
        return ($this->weekend_multiplier ?? 1.0) > 1.0;
    }

    /**
     * チェックイン〜チェックアウト間の合計料金を計算する
     */
    public function calculateTotalPrice(string $checkIn, string $checkOut): int
    {
        $cur   = \Carbon\Carbon::parse($checkIn);
        $end   = \Carbon\Carbon::parse($checkOut);
        $total = 0;

        while ($cur->lt($end)) {
            $total += $this->getPriceForDate($cur);
            $cur->addDay();
        }

        return $total;
    }

    /**
     * 指定月の日付→料金マップを返す（カレンダー用）
     */
    public function getPriceMapForMonth(int $year, int $month): array
    {
        $start = \Carbon\Carbon::create($year, $month, 1);
        $end   = $start->copy()->endOfMonth();

        $specials = $this->prices()
            ->whereDate('start_date', '<=', $end->toDateString())
            ->whereDate('end_date',   '>=', $start->toDateString())
            ->get();

        $map = [];
        $cur = $start->copy();
        while ($cur->lte($end)) {
            $dayStr  = $cur->toDateString();
            $special = $specials->first(fn ($p) =>
                $p->start_date->lte($cur) && $p->end_date->gte($cur)
            );
            $map[$cur->day] = [
                'price'      => $special ? $special->price_per_night : $this->price_per_night,
                'is_special' => $special !== null,
                'label'      => $special?->label,
            ];
            $cur->addDay();
        }

        return $map;
    }
}
