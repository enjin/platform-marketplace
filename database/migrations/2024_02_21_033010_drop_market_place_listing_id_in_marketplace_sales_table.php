<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('marketplace_sales', function (Blueprint $table) {
            $table->dropForeign(['marketplace_listing_id']);
            $table->dropColumn('marketplace_listing_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketplace_sales', function (Blueprint $table) {
            $table->foreignId('marketplace_listing_id')->index()->constrained('marketplace_listings');
        });
    }
};
