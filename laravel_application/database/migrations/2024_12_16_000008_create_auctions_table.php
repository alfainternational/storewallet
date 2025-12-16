<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuctionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auctions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('auction_number')->unique();

            // Auction Type
            $table->enum('type', ['product', 'delivery', 'international'])->default('product');

            // Ownership
            $table->unsignedInteger('user_id'); // Seller or requester
            $table->unsignedInteger('category_id')->nullable();

            // Product Auction Fields
            $table->string('title');
            $table->text('description');
            $table->unsignedInteger('product_id')->nullable(); // If linked to existing product
            $table->enum('condition', ['new', 'used', 'refurbished'])->nullable();

            // Delivery Auction Fields
            $table->string('pickup_address')->nullable();
            $table->string('pickup_city')->nullable();
            $table->string('delivery_address')->nullable();
            $table->string('delivery_city')->nullable();
            $table->string('delivery_country')->default('Sudan');
            $table->decimal('package_weight', 8, 2)->nullable();
            $table->string('package_type')->nullable();
            $table->decimal('declared_value', 10, 2)->nullable();
            $table->boolean('requires_insurance')->default(false);
            $table->boolean('is_fragile')->default(false);

            // Pricing
            $table->decimal('starting_price', 15, 2);
            $table->decimal('reserve_price', 15, 2)->nullable(); // Minimum acceptable price
            $table->decimal('current_price', 15, 2);
            $table->decimal('buy_now_price', 15, 2)->nullable(); // Instant buy price
            $table->decimal('min_bid_increment', 10, 2)->default(10);

            // Bidding Type (for delivery/international)
            $table->boolean('is_reverse_auction')->default(false); // True for delivery auctions (lower price wins)

            // Timing
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->integer('duration_minutes')->default(4320); // 3 days default
            $table->boolean('auto_extend')->default(true); // Extend if bid in last 5 minutes
            $table->integer('extension_minutes')->default(5);

            // Shipping/Delivery
            $table->enum('shipping_method', ['free', 'buyer_pays', 'fixed'])->default('buyer_pays');
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->string('pickup_location')->nullable();
            $table->enum('delivery_speed', ['standard', 'express', 'urgent'])->nullable();

            // Status
            $table->enum('status', [
                'draft',
                'scheduled',
                'active',
                'ended',
                'completed',
                'cancelled',
                'expired'
            ])->default('draft');

            // Statistics
            $table->integer('view_count')->default(0);
            $table->integer('bid_count')->default(0);
            $table->integer('watcher_count')->default(0);
            $table->integer('question_count')->default(0);

            // Winner
            $table->unsignedInteger('winner_user_id')->nullable();
            $table->decimal('winning_price', 15, 2)->nullable();
            $table->timestamp('won_at')->nullable();

            // Payment & Escrow
            $table->string('escrow_trx')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'released', 'refunded'])->default('pending');

            // Options
            $table->boolean('featured')->default(false);
            $table->boolean('allow_questions')->default(true);
            $table->boolean('private_auction')->default(false); // Only invited users can bid
            $table->boolean('verified_bidders_only')->default(false);

            // Additional Info
            $table->json('specifications')->nullable(); // Product specs, delivery requirements, etc.
            $table->text('terms_and_conditions')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('auction_categories')->onDelete('set null');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            $table->foreign('winner_user_id')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('auction_number');
            $table->index('type');
            $table->index('status');
            $table->index(['status', 'end_time']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auctions');
    }
}
