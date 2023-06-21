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
        Schema::create('marketplace_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketplace_listing_id')->index()->constrained('marketplace_listings');
            $table->string('amount');
            $table->string('price');
            $table->foreignId('wallet_id')->index()->nullable()->constrained('wallets');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketplace_sales');
    }
};
