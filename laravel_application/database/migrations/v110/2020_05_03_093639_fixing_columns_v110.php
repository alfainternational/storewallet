<?php
/**
 * File name: 2020_05_03_093639_fixing_columns_v110.php
 * Last modified: 2020.05.04 at 09:04:09
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2020
 *
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixingColumnsV110 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!\Doctrine\DBAL\Types\Type::hasType('double')) {
            \Doctrine\DBAL\Types\Type::addType('double', \Doctrine\DBAL\Types\FloatType::class);
            \Doctrine\DBAL\Types\Type::addType('tinyinteger', \Doctrine\DBAL\Types\SmallIntType::class);
        }

        if (Schema::hasTable('fields')) {
            Schema::table('fields', function (Blueprint $table) {
                $table->text('description')->nullable()->change();
            });
        }

        if (Schema::hasTable('markets')) {
            Schema::table('markets', function (Blueprint $table) {
                $table->double('admin_commission', 8, 2)->nullable()->default(0)->change();
                $table->double('delivery_fee', 8, 2)->nullable()->default(0)->change();
                $table->double('delivery_range', 8, 2)->nullable()->default(0)->change();
                $table->double('default_tax', 8, 2)->nullable()->default(0)->change();
                $table->boolean('closed')->nullable()->default(0)->change(); // //added
                $table->boolean('available_for_delivery')->nullable()->default(1)->change();
            });
        }
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->text('description')->nullable()->change();
            });
        }
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $table->double('discount_price', 8, 2)->nullable()->default(0)->change();
                $table->text('description')->nullable()->change();
                $table->double('capacity', 9, 2)->nullable()->default(0)->change();
                $table->double('package_items_count', 9, 2)->nullable()->default(0)->change();
                $table->string('unit', 127)->nullable()->change(); // added
                $table->boolean('featured')->nullable()->default(0)->change();
                $table->boolean('deliverable')->nullable()->default(1)->change(); // added
            });
        }
        if (Schema::hasTable('options')) {
            Schema::table('options', function (Blueprint $table) {
                $table->text('description')->nullable()->change();
                $table->double('price', 8, 2)->nullable()->default(0)->change();
            });
        }

        if (Schema::hasTable('galleries')) {
            Schema::table('galleries', function (Blueprint $table) {
                $table->text('description')->nullable()->change();
            });
        }

        if (Schema::hasTable('product_reviews')) {
            Schema::table('product_reviews', function (Blueprint $table) {
                $table->text('review')->nullable()->change();
                $table->unsignedTinyInteger('rate')->default(0)->change();
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('description', 255)->nullable()->change();
            });
        }

        if (Schema::hasTable('faqs')) {
            Schema::table('faqs', function (Blueprint $table) {
                $table->text('question')->nullable()->change();
                $table->text('answer')->nullable()->change();
            });
        }
        if (Schema::hasTable('market_reviews')) {
            Schema::table('market_reviews', function (Blueprint $table) {
                $table->text('review')->nullable()->change();
                $table->unsignedTinyInteger('rate')->default(0)->change();
            });
        }
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->double('tax', 5, 2)->nullable()->default(0)->change();
                $table->double('delivery_fee', 5, 2)->nullable()->default(0)->change();
                $table->text('hint')->nullable()->change();
            });
        }

        if (Schema::hasTable('currencies')) {
            Schema::table('currencies', function (Blueprint $table) {
                $table->unsignedTinyInteger('decimal_digits')->nullable()->change();
                $table->unsignedTinyInteger('rounding')->nullable()->change();
            });
        }

        if (Schema::hasTable('drivers_payouts')) {
            Schema::table('drivers_payouts', function (Blueprint $table) {
                $table->string('method', 127)->nullable()->change();
                $table->dateTime('paid_date')->nullable()->change();
                $table->text('note')->nullable()->change();
            });
        }

        if (Schema::hasTable('markets_payouts')) {
            Schema::table('markets_payouts', function (Blueprint $table) {
                $table->string('method', 127)->nullable()->change();
                $table->dateTime('paid_date')->nullable()->change();
                $table->text('note')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
