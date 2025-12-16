<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShippingCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_companies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('business_name')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('tax_number')->nullable();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('phone_secondary')->nullable();
            $table->string('website')->nullable();
            $table->text('address');
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('country')->default('Sudan');
            $table->string('postal_code')->nullable();

            // Manager Information
            $table->unsignedInteger('manager_user_id');

            // Service Areas
            $table->enum('service_type', ['local', 'national', 'international', 'all'])->default('local');
            $table->json('service_cities')->nullable(); // Cities they serve
            $table->json('service_countries')->nullable(); // For international

            // Pricing
            $table->enum('pricing_model', ['per_km', 'per_weight', 'flat_rate', 'custom'])->default('per_km');
            $table->decimal('base_price', 10, 2)->default(0);
            $table->decimal('price_per_km', 10, 2)->nullable();
            $table->decimal('price_per_kg', 10, 2)->nullable();

            // Wallet Integration
            $table->unsignedBigInteger('wallet_merchant_id')->nullable();

            // Status & Rating
            $table->boolean('verified')->default(false);
            $table->boolean('active')->default(true);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('total_shipments')->default(0);
            $table->integer('completed_shipments')->default(0);

            // Financial
            $table->decimal('total_earnings', 15, 2)->default(0);
            $table->decimal('pending_balance', 15, 2)->default(0);

            // Documents
            $table->string('license_document')->nullable();
            $table->string('insurance_document')->nullable();
            $table->date('license_expiry')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Foreign Keys
            $table->foreign('manager_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipping_companies');
    }
}
