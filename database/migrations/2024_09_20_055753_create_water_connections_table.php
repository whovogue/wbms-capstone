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
        Schema::create('water_connections', function (Blueprint $table) {
            $table->id();
            $table->string('reference_id')->unique();
            $table->date('connected_date')->nullable();
            $table->string('address');
            $table->string('name');
            $table->string('status');
            $table->string('purok');
            $table->string('phone_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('water_connections');
    }
};
