<?php

use App\Core\Report\ExpressionType\QueryExpressionType;
use App\Models\Indicator;
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
        Schema::table('indicators', function (Blueprint $table) {
            $table->string('type', 256);
        });

        Indicator::query()->update(['type' => class_basename(QueryExpressionType::class)]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropColumns('indicators', ['type']);
    }
};
