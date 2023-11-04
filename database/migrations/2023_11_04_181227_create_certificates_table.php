<?php

use App\Models\Chamber;
use App\Models\Company;
use App\Models\Expert;
use App\Models\Organization;
use App\Models\Type;
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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Type::class)->constrained()->nullOnDelete();
            $table->foreignIdFor(Chamber::class)->constrained()->nullOnDelete();
            $table->foreignIdFor(Organization::class)->constrained()->nullOnDelete();
            $table->foreignIdFor(Company::class)->constrained()->nullOnDelete();
            $table->foreignIdFor(Expert::class)->constrained()->nullOnDelete();
            $table->boolean('scan_issued')->nullable()->default(false);
            $table->boolean('invoice_issued')->nullable()->default(false);
            $table->boolean('paid')->nullable()->default(false);
            $table->date('date');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
