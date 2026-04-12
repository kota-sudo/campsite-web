<?php

namespace Tests\Unit;

use App\Models\Campsite;
use App\Models\CampsitePrice;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampsitePriceTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // getPriceForDate()
    // ---------------------------------------------------------------

    public function test_get_price_for_date_returns_base_price_on_weekday(): void
    {
        $campsite = Campsite::factory()->create([
            'price_per_night'    => 5000,
            'weekend_multiplier' => 1.5,
        ]);

        // 2026-04-13 は月曜日
        $price = $campsite->getPriceForDate('2026-04-13');

        $this->assertSame(5000, $price);
    }

    public function test_get_price_for_date_applies_weekend_multiplier_on_saturday(): void
    {
        $campsite = Campsite::factory()->create([
            'price_per_night'    => 10000,
            'weekend_multiplier' => 1.5,
        ]);

        // 2026-04-11 は土曜日
        $price = $campsite->getPriceForDate('2026-04-11');

        $this->assertSame(15000, $price);
    }

    public function test_get_price_for_date_applies_weekend_multiplier_on_sunday(): void
    {
        $campsite = Campsite::factory()->create([
            'price_per_night'    => 10000,
            'weekend_multiplier' => 2.0,
        ]);

        // 2026-04-12 は日曜日
        $price = $campsite->getPriceForDate('2026-04-12');

        $this->assertSame(20000, $price);
    }

    public function test_get_price_for_date_ignores_multiplier_when_1(): void
    {
        $campsite = Campsite::factory()->create([
            'price_per_night'    => 8000,
            'weekend_multiplier' => 1.0,
        ]);

        // 土曜でも倍率1.0なら base price のまま
        $price = $campsite->getPriceForDate('2026-04-11');

        $this->assertSame(8000, $price);
    }

    public function test_get_price_for_date_returns_special_price_over_base(): void
    {
        $campsite = Campsite::factory()->create([
            'price_per_night'    => 5000,
            'weekend_multiplier' => 1.0,
        ]);

        CampsitePrice::create([
            'campsite_id'     => $campsite->id,
            'label'           => 'GW',
            'start_date'      => '2026-05-03',
            'end_date'        => '2026-05-06',
            'price_per_night' => 12000,
        ]);

        $campsite->load('prices');

        $this->assertSame(12000, $campsite->getPriceForDate('2026-05-04'));
        $this->assertSame(5000,  $campsite->getPriceForDate('2026-05-02')); // 前日は通常料金
        $this->assertSame(5000,  $campsite->getPriceForDate('2026-05-07')); // 翌日も通常料金
    }

    public function test_get_price_for_date_special_price_takes_precedence_over_weekend(): void
    {
        // 2026-05-09 は土曜日
        $campsite = Campsite::factory()->create([
            'price_per_night'    => 5000,
            'weekend_multiplier' => 2.0,
        ]);

        // 1日だけの特別料金ルール（start == end == test_date の境界条件）
        $campsite->prices()->create([
            'label'           => '特別',
            'start_date'      => '2026-05-09',
            'end_date'        => '2026-05-09',
            'price_per_night' => 7000,
        ]);

        // 特別料金が週末倍率(5000×2=10000)より低くても優先される
        $this->assertSame(7000, $campsite->getPriceForDate('2026-05-09'));
    }

    // ---------------------------------------------------------------
    // calculateTotalPrice()
    // ---------------------------------------------------------------

    public function test_calculate_total_price_for_weekday_stay(): void
    {
        $campsite = Campsite::factory()->create([
            'price_per_night'    => 5000,
            'weekend_multiplier' => 1.5,
        ]);

        // 2026-04-13(月) 〜 2026-04-15(水): 2泊, 両日とも平日
        $total = $campsite->calculateTotalPrice('2026-04-13', '2026-04-15');

        $this->assertSame(10000, $total);
    }

    public function test_calculate_total_price_includes_weekend_surcharge(): void
    {
        $campsite = Campsite::factory()->create([
            'price_per_night'    => 10000,
            'weekend_multiplier' => 1.5,
        ]);

        // 2026-04-10(金) 〜 2026-04-12(日): 2泊
        //   4/10 金: 10000, 4/11 土: 15000 → 合計 25000
        $total = $campsite->calculateTotalPrice('2026-04-10', '2026-04-12');

        $this->assertSame(25000, $total);
    }

    public function test_calculate_total_price_respects_special_dates(): void
    {
        $campsite = Campsite::factory()->create([
            'price_per_night'    => 5000,
            'weekend_multiplier' => 1.0,
        ]);

        $campsite->prices()->create([
            'label'           => 'お盆',
            'start_date'      => '2026-08-12',
            'end_date'        => '2026-08-14',
            'price_per_night' => 20000,
        ]);

        // start_date境界(8/12)・中間(8/13)・end_date境界(8/14)すべて特別料金になることを確認
        // 8/12(特別20000) + 8/13(特別20000) + 8/14(特別20000) = 60000
        $total = $campsite->calculateTotalPrice('2026-08-12', '2026-08-15');

        $this->assertSame(60000, $total);
    }

    // ---------------------------------------------------------------
    // hasWeekendSurcharge() / weekendPrice()
    // ---------------------------------------------------------------

    public function test_has_weekend_surcharge_returns_true_when_multiplier_above_1(): void
    {
        $campsite = Campsite::factory()->create(['weekend_multiplier' => 1.3]);

        $this->assertTrue($campsite->hasWeekendSurcharge());
    }

    public function test_has_weekend_surcharge_returns_false_when_multiplier_is_1(): void
    {
        $campsite = Campsite::factory()->create(['weekend_multiplier' => 1.0]);

        $this->assertFalse($campsite->hasWeekendSurcharge());
    }

    public function test_weekend_price_is_rounded_correctly(): void
    {
        $campsite = Campsite::factory()->create([
            'price_per_night'    => 3000,
            'weekend_multiplier' => 1.3,
        ]);

        // 3000 × 1.3 = 3900
        $this->assertSame(3900, $campsite->weekendPrice());
    }
}
