<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('auction_bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->boolean('is_winning')->default(false);
            $table->boolean('is_auto_bid')->default(false);
            $table->timestamps();

            $table->index(['auction_id', 'user_id', 'is_winning']);
            $table->index(['amount', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('auction_bids');
    }
};
