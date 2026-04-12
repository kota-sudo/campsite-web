<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amenities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        Schema::create('campsite_amenity', function (Blueprint $table) {
            $table->foreignId('campsite_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amenity_id')->constrained()->cascadeOnDelete();
            $table->primary(['campsite_id', 'amenity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campsite_amenity');
        Schema::dropIfExists('amenities');
    }
};
