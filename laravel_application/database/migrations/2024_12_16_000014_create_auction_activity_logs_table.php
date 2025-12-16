<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuctionActivityLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auction_activity_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('auction_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->string('action'); // bid_placed, price_updated, status_changed, etc.
            $table->text('description');
            $table->json('metadata')->nullable(); // Additional data about the action
            $table->string('ip_address')->nullable();
            $table->timestamps();

            // Foreign Keys
            $table->foreign('auction_id')->references('id')->on('auctions')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            // Index
            $table->index(['auction_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auction_activity_logs');
    }
}
