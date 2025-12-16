<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tracking_number')->unique();

            // Order relation (if linked to an order)
            $table->unsignedInteger('order_id')->nullable();

            // Customer Information
            $table->unsignedInteger('customer_user_id');
            $table->string('sender_name');
            $table->string('sender_phone');
            $table->text('sender_address');
            $table->string('sender_city');

            $table->string('receiver_name');
            $table->string('receiver_phone');
            $table->text('receiver_address');
            $table->string('receiver_city');
            $table->string('receiver_country')->default('Sudan');

            // Package Details
            $table->string('package_type')->default('parcel'); // parcel, document, fragile, etc
            $table->decimal('weight', 8, 2)->default(0); // in KG
            $table->decimal('length', 8, 2)->nullable(); // in CM
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('declared_value', 10, 2)->default(0);
            $table->text('package_description')->nullable();
            $table->boolean('fragile')->default(false);
            $table->boolean('insurance')->default(false');

            // Shipping Company & Driver
            $table->unsignedInteger('company_id')->nullable();
            $table->unsignedInteger('driver_id')->nullable();

            // Pricing
            $table->decimal('shipping_cost', 10, 2);
            $table->decimal('insurance_cost', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2);
            $table->decimal('company_earning', 10, 2)->default(0);
            $table->decimal('driver_earning', 10, 2)->default(0);
            $table->decimal('platform_fee', 10, 2')->default(0);

            // Status
            $table->enum('status', [
                'pending',           // Waiting for company assignment
                'accepted',          // Company accepted
                'picked_up',         // Package picked up
                'in_transit',        // On the way
                'out_for_delivery',  // Driver out for delivery
                'delivered',         // Successfully delivered
                'failed',            // Delivery failed
                'returned',          // Returned to sender
                'cancelled'          // Cancelled
            ])->default('pending');

            // Timestamps
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('picked_up_at')->nullable();
            $table->timestamp('in_transit_at')->nullable();
            $table->timestamp('out_for_delivery_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Delivery Proof
            $table->string('delivery_signature')->nullable();
            $table->string('delivery_photo')->nullable();
            $table->text('delivery_notes')->nullable();

            // Customer Feedback
            $table->integer('rating')->nullable();
            $table->text('review')->nullable();

            // Payment
            $table->string('payment_method')->default('wallet');
            $table->boolean('paid')->default(false);
            $table->string('escrow_trx')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            $table->foreign('customer_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('shipping_companies')->onDelete('set null');
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('set null');

            // Indexes
            $table->index('tracking_number');
            $table->index('status');
            $table->index(['company_id', 'status']);
            $table->index(['driver_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipments');
    }
}
