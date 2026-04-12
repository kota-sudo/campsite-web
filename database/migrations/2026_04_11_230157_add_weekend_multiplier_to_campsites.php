<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('campsites', function (Blueprint $table) {
            $table->decimal('weekend_multiplier', 4, 2)->default(1.00)->after('price_per_night')
                  ->comment('土日料金倍率 (例: 1.50 = 150%)');
        });
    }

    public function down(): void
    {
        Schema::table('campsites', function (Blueprint $table) {
            $table->dropColumn('weekend_multiplier');
        });
    }
};
