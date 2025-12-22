<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('shipping_company_id')->constrained()->onDelete('restrict');
            $table->string('tracking_number')->unique();
            $table->enum('status', ['pending', 'picked_up', 'in_transit', 'out_for_delivery', 'delivered', 'failed'])->default('pending');
            $table->decimal('shipping_cost', 10, 2);
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->text('pickup_address');
            $table->text('delivery_address');
            $table->decimal('pickup_lat', 10, 8)->nullable();
            $table->decimal('pickup_lng', 11, 8)->nullable();
            $table->decimal('delivery_lat', 10, 8)->nullable();
            $table->decimal('delivery_lng', 11, 8)->nullable();
            $table->decimal('current_lat', 10, 8)->nullable();
            $table->decimal('current_lng', 11, 8)->nullable();
            $table->text('driver_notes')->nullable();
            $table->string('driver_phone')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'shipping_company_id', 'status']);
            $table->index('tracking_number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipments');
    }
};
