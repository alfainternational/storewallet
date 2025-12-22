<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('merchants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('shop_name');
            $table->string('shop_name_ar');
            $table->text('shop_description')->nullable();
            $table->text('shop_description_ar')->nullable();
            $table->string('shop_logo')->nullable();
            $table->string('shop_banner')->nullable();
            $table->string('business_license')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->decimal('commission_rate', 5, 2)->default(5.00);
            $table->decimal('total_sales', 12, 2)->default(0);
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('reviews_count')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->index(['is_verified', 'is_active', 'rating']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('merchants');
    }
};
