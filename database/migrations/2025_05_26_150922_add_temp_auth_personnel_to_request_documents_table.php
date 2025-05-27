<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::table('request_documents', function (Blueprint $table) {
        $table->unsignedBigInteger('temp_auth_personnel')->nullable()->after('custom_fields');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_documents', function (Blueprint $table) {
            //
        });
    }
};
