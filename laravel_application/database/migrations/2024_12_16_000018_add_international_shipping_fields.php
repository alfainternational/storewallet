<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInternationalShippingFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add international shipping fields to auctions table
        Schema::table('auctions', function (Blueprint $table) {
            $table->string('origin_country', 2)->nullable()->after('delivery_address'); // ISO 3166-1 alpha-2
            $table->string('origin_city')->nullable()->after('origin_country');
            $table->string('destination_country', 2)->nullable()->after('origin_city');
            $table->string('destination_city')->nullable()->after('destination_country');
            $table->enum('package_type', ['documents', 'parcel', 'luggage', 'commercial', 'personal_effects'])->nullable()->after('package_dimensions');
            $table->text('customs_declaration')->nullable()->after('package_type');
            $table->boolean('requires_customs_clearance')->default(false)->after('customs_declaration');
            $table->decimal('estimated_customs_value', 15, 2)->nullable()->after('requires_customs_clearance');
            $table->string('customs_tracking_number')->nullable()->after('estimated_customs_value');
            $table->enum('customs_status', ['pending', 'cleared', 'held', 'inspection_required', 'rejected'])->nullable()->after('customs_tracking_number');
        });

        // Add international shipping support to products table
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('international_shipping_available')->default(false)->after('active');
            $table->json('shipping_countries')->nullable()->after('international_shipping_available'); // List of country codes
            $table->decimal('international_shipping_base_price', 10, 2)->nullable()->after('shipping_countries');
            $table->text('international_shipping_notes')->nullable()->after('international_shipping_base_price');
        });

        // Add international flag to orders table
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('is_international')->default(false)->after('status');
            $table->string('destination_country', 2)->nullable()->after('is_international');
            $table->text('customs_info')->nullable()->after('destination_country');
        });

        // Add service countries to shipping companies
        Schema::table('shipping_companies', function (Blueprint $table) {
            $table->json('service_countries')->nullable()->after('service_cities'); // List of country codes they serve
            $table->boolean('customs_clearance_service')->default(false)->after('service_countries');
            $table->decimal('customs_handling_fee', 10, 2)->nullable()->after('customs_clearance_service');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('shipping_companies', function (Blueprint $table) {
            $table->dropColumn(['service_countries', 'customs_clearance_service', 'customs_handling_fee']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['is_international', 'destination_country', 'customs_info']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'international_shipping_available',
                'shipping_countries',
                'international_shipping_base_price',
                'international_shipping_notes'
            ]);
        });

        Schema::table('auctions', function (Blueprint $table) {
            $table->dropColumn([
                'origin_country',
                'origin_city',
                'destination_country',
                'destination_city',
                'package_type',
                'customs_declaration',
                'requires_customs_clearance',
                'estimated_customs_value',
                'customs_tracking_number',
                'customs_status'
            ]);
        });
    }
}
