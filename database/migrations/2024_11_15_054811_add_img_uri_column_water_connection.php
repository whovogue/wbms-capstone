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
        Schema::table('water_connections', function (Blueprint $table) {
            $table->longText('img_uri_spending')->nullable();
            $table->longText('img_uri_consumption')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('water_connections', function (Blueprint $table) {
            //
        });
    }
};
