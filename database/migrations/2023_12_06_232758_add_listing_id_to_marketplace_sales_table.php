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
            $table->string('listing_chain_id')->index()->after('id');
        });

        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->renameColumn('listing_id', 'listing_chain_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->renameColumn('listing_chain_id', 'listing_id');
        });

        Schema::table('marketplace_sales', function (Blueprint $table) {
            $table->dropColumn('listing_chain_id');
        });
    }
};
