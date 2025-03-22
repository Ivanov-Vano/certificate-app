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
        Schema::table('certificates', function (Blueprint $table){
            $table->dropForeign('certificates_sign_id_foreign');
            $table->dropColumn('sign_id');
        });
        Schema::dropIfExists('signs');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('signs', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
        Schema::table('certificates', function (Blueprint $table){
            $table->foreignId('sign_id')->nullable();
            $table->foreign('sign_id')->references('id')->on('signs');
        });
    }
};
