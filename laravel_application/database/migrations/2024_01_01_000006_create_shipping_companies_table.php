<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shipping_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('company_name');
            $table->string('company_name_ar')->nullable();
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->string('license_number')->nullable();
            $table->json('coverage_cities')->nullable();
            $table->decimal('base_rate', 10, 2)->default(0);
            $table->decimal('per_km_rate', 10, 2)->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->integer('reviews_count')->default(0);
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('user_id');
            $table->index(['is_verified', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('shipping_companies');
    }
};
