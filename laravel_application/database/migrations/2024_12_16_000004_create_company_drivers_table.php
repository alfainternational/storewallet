<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompanyDriversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('company_drivers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('driver_id');
            $table->enum('employment_type', ['full_time', 'part_time', 'contract'])->default('full_time');
            $table->date('joined_date');
            $table->date('left_date')->nullable();
            $table->boolean('active')->default(true);
            $table->decimal('commission_rate', 5, 2)->default(0); // % of delivery fee
            $table->timestamps();

            // Foreign Keys
            $table->foreign('company_id')->references('id')->on('shipping_companies')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');

            // Unique constraint - driver can only belong to one company at a time
            $table->unique(['driver_id', 'company_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_drivers');
    }
}
