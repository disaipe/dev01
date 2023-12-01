<?php

use App\Models\Company;
use App\Models\PriceList;
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
        Schema::table('price_lists', function (Blueprint $table) {
            $table->dropConstrainedForeignIdFor(Company::class);
        });

        Schema::create('price_list_companies', function (Blueprint $table) {
            $table->foreignIdFor(PriceList::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Company::class)->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_price_list');

        Schema::table('price_lists', function (Blueprint $table) {
            $table->foreignIdFor(Company::class)->nullable()->constrained()->cascadeOnDelete();
        });
    }
};
