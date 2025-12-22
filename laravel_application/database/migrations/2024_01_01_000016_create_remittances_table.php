<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('remittances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('recipient_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('remittance_number')->unique();
            $table->string('sender_name');
            $table->string('sender_country')->nullable();
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->foreignId('recipient_city_id')->nullable()->constrained('sudanese_cities')->onDelete('set null');
            $table->decimal('amount', 10, 2);
            $table->foreignId('currency_id')->constrained()->onDelete('restrict');
            $table->decimal('exchange_rate', 10, 4);
            $table->decimal('amount_in_sdg', 10, 2);
            $table->decimal('fees', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2);
            $table->enum('receiving_method', ['bank_transfer', 'mobile_wallet', 'cash_pickup', 'door_delivery']);
            $table->string('bank_account')->nullable();
            $table->string('pickup_code')->nullable();
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled', 'failed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['sender_id', 'recipient_id', 'status']);
            $table->index(['remittance_number', 'pickup_code']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('remittances');
    }
};
