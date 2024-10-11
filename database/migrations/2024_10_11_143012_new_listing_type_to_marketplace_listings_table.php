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
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->renameColumn('start_block', 'auction_start_block');
            $table->renameColumn('end_block', 'auction_end_block');
            $table->unsignedInteger('offer_expiration')->nullable()->after('auction_end_block');
            $table->unsignedInteger('counter_offer_count')->nullable()->after('offer_expiration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->renameColumn('auction_start_block', 'start_block');
            $table->renameColumn('auction_end_block', 'end_block');
            $table->dropColumn(['offer_expiration', 'counter_offer_count']);
        });
    }
};
