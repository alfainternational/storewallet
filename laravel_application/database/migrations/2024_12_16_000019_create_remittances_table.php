<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRemittancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('remittances', function (Blueprint $table) {
            $table->increments('id');
            $table->string('remittance_number')->unique();

            // Sender information (Expatriate)
            $table->unsignedInteger('sender_user_id');
            $table->string('sender_name');
            $table->string('sender_country', 2); // Country where sender is located
            $table->string('sender_phone')->nullable();
            $table->string('sender_email')->nullable();

            // Receiver information (In Sudan)
            $table->unsignedInteger('receiver_user_id')->nullable(); // Can be null if receiver doesn't have account
            $table->string('receiver_name');
            $table->string('receiver_phone');
            $table->string('receiver_email')->nullable();
            $table->string('receiver_national_id')->nullable();
            $table->string('receiver_city')->nullable();
            $table->text('receiver_address')->nullable();

            // Amount details
            $table->decimal('send_amount', 15, 2); // Amount sent in sender's currency
            $table->string('send_currency', 3);
            $table->decimal('exchange_rate', 15, 6);
            $table->decimal('receive_amount', 15, 2); // Amount received in SDG
            $table->string('receive_currency', 3)->default('SDG');
            $table->decimal('service_fee', 15, 2)->default(0);
            $table->string('fee_currency', 3);
            $table->decimal('total_charged', 15, 2); // send_amount + service_fee

            // Delivery method
            $table->enum('delivery_method', [
                'wallet',           // Direct to wallet
                'bank_transfer',    // To bank account
                'cash_pickup',      // Cash pickup from agent
                'mobile_money',     // Mobile money transfer
                'home_delivery'     // Cash delivery to home
            ])->default('wallet');

            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('mobile_money_provider')->nullable();
            $table->string('mobile_money_number')->nullable();
            $table->string('pickup_location')->nullable();
            $table->string('pickup_code')->nullable();

            // Status tracking
            $table->enum('status', [
                'pending',
                'processing',
                'approved',
                'in_transit',
                'ready_for_pickup',
                'completed',
                'cancelled',
                'failed',
                'refunded'
            ])->default('pending');

            $table->text('status_notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // Transaction references
            $table->string('sender_transaction_id')->nullable(); // From wallet
            $table->string('receiver_transaction_id')->nullable(); // To wallet
            $table->string('payment_reference')->nullable();
            $table->string('external_reference')->nullable(); // If using third-party service

            // Security and compliance
            $table->text('purpose')->nullable(); // Purpose of remittance
            $table->boolean('compliance_checked')->default(false);
            $table->text('compliance_notes')->nullable();
            $table->boolean('requires_verification')->default(false);
            $table->string('verification_document')->nullable();

            // Metadata
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('remittance_number');
            $table->index('sender_user_id');
            $table->index('receiver_user_id');
            $table->index('status');
            $table->index('delivery_method');
            $table->index('created_at');

            // Foreign keys
            $table->foreign('sender_user_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('receiver_user_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('send_currency')->references('code')->on('currencies')->onDelete('restrict');
            $table->foreign('receive_currency')->references('code')->on('currencies')->onDelete('restrict');
        });

        // Create remittance agents table (cash pickup locations)
        Schema::create('remittance_agents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('agent_code')->unique();
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('phone');
            $table->string('email')->nullable();
            $table->unsignedInteger('city_id')->nullable();
            $table->text('address');
            $table->text('address_ar')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('working_hours')->nullable();
            $table->boolean('active')->default(true);
            $table->decimal('max_payout_limit', 15, 2)->nullable();
            $table->decimal('available_cash', 15, 2)->default(0);
            $table->timestamps();

            $table->index('city_id');
            $table->index('active');
        });

        // Create remittance status history table
        Schema::create('remittance_status_history', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('remittance_id');
            $table->string('status');
            $table->text('notes')->nullable();
            $table->unsignedInteger('updated_by')->nullable(); // User or admin who updated
            $table->timestamp('created_at');

            $table->foreign('remittance_id')->references('id')->on('remittances')->onDelete('cascade');
            $table->index('remittance_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('remittance_status_history');
        Schema::dropIfExists('remittance_agents');
        Schema::dropIfExists('remittances');
    }
}
