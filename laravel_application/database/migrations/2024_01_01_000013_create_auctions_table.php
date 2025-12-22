<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->string('title_ar');
            $table->text('description');
            $table->text('description_ar');
            $table->enum('bid_type', ['lowest_bid', 'highest_bid'])->default('lowest_bid');
            $table->decimal('start_price', 10, 2);
            $table->decimal('current_bid', 10, 2)->nullable();
            $table->decimal('reserve_price', 10, 2)->nullable();
            $table->foreignId('winner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled'])->default('pending');
            $table->integer('bids_count')->default(0);
            $table->text('terms')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['merchant_id', 'status', 'bid_type']);
            $table->index(['start_time', 'end_time', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('auctions');
    }
};
