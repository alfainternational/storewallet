<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuctionAutoBidsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auction_auto_bids', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('auction_id');
            $table->unsignedInteger('user_id');

            // Auto-bid Settings
            $table->decimal('max_bid_amount', 15, 2);
            $table->decimal('bid_increment', 10, 2)->default(10);
            $table->boolean('active')->default(true);

            // Conditions
            $table->boolean('only_last_hour')->default(false);
            $table->decimal('max_price_limit', 15, 2)->nullable();

            // Statistics
            $table->integer('bids_placed')->default(0);
            $table->decimal('current_bid_amount', 15, 2)->default(0);

            $table->timestamps();

            // Foreign Keys
            $table->foreign('auction_id')->references('id')->on('auctions')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Unique constraint - one auto-bid per user per auction
            $table->unique(['auction_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auction_auto_bids');
    }
}
