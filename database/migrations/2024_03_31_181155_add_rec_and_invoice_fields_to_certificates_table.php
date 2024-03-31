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
        Schema::table('certificates', function (Blueprint $table) {
            $table->boolean('rec')
                ->nullable()
                ->default(false)
                ->comment('признак РЭЦ');
            $table->boolean('second_invoice')
                ->nullable()
                ->default(false)
                ->comment('признак 2с/ф');
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn(['rec','second_invoice']);
        });
    }
};
