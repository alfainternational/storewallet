<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuctionBidsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auction_bids', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('auction_id');
            $table->unsignedInteger('user_id');

            // Bid Details
            $table->decimal('bid_amount', 15, 2);
            $table->decimal('max_bid_amount', 15, 2)->nullable(); // For auto-bidding
            $table->boolean('is_auto_bid')->default(false);

            // For Delivery Auctions (Company info if bidder is shipping company)
            $table->unsignedInteger('company_id')->nullable();
            $table->integer('estimated_delivery_hours')->nullable();
            $table->text('company_notes')->nullable();

            // Status
            $table->enum('status', ['active', 'outbid', 'winning', 'won', 'lost', 'retracted'])->default('active');
            $table->boolean('is_winning')->default(false);

            // Timestamps
            $table->timestamp('bid_time');
            $table->timestamp('retracted_at')->nullable();

            // IP and Device Info (for security)
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();

            // Foreign Keys
            $table->foreign('auction_id')->references('id')->on('auctions')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('shipping_companies')->onDelete('set null');

            // Indexes
            $table->index(['auction_id', 'bid_amount']);
            $table->index(['auction_id', 'user_id']);
            $table->index(['auction_id', 'is_winning']);
            $table->index('bid_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auction_bids');
    }
}
