<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('code', 3)->unique(); // ISO 4217 currency code
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('symbol', 10);
            $table->string('symbol_ar', 10)->nullable();
            $table->decimal('exchange_rate_to_usd', 15, 6)->default(1); // Rate relative to USD
            $table->integer('decimal_places')->default(2);
            $table->boolean('is_default')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamp('rate_updated_at')->nullable();
            $table->timestamps();

            $table->index('code');
            $table->index('is_default');
        });

        // Add currency support to existing tables
        Schema::table('products', function (Blueprint $table) {
            $table->string('currency_code', 3)->default('SDG')->after('price');
            $table->foreign('currency_code')->references('code')->on('currencies')->onDelete('restrict');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('currency_code', 3)->default('SDG')->after('total_amount');
            $table->foreign('currency_code')->references('code')->on('currencies')->onDelete('restrict');
        });

        Schema::table('auctions', function (Blueprint $table) {
            $table->string('currency_code', 3)->default('SDG')->after('starting_price');
            $table->foreign('currency_code')->references('code')->on('currencies')->onDelete('restrict');
        });

        Schema::table('shipping_companies', function (Blueprint $table) {
            $table->string('currency_code', 3)->default('SDG')->after('base_price');
            $table->foreign('currency_code')->references('code')->on('currencies')->onDelete('restrict');
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
            $table->dropForeign(['currency_code']);
            $table->dropColumn('currency_code');
        });

        Schema::table('auctions', function (Blueprint $table) {
            $table->dropForeign(['currency_code']);
            $table->dropColumn('currency_code');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['currency_code']);
            $table->dropColumn('currency_code');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['currency_code']);
            $table->dropColumn('currency_code');
        });

        Schema::dropIfExists('currencies');
    }
}
