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
        Schema::create('campsite_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campsite_id')->constrained()->cascadeOnDelete();
            $table->string('label')->nullable()->comment('例: GW特別料金・週末料金');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedInteger('price_per_night');
            $table->timestamps();

            $table->index(['campsite_id', 'start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campsite_prices');
    }
};
