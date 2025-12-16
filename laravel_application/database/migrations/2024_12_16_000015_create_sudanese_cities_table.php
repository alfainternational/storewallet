<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSudaneseCitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sudanese_cities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name_ar'); // Arabic name
            $table->string('name_en'); // English name
            $table->unsignedInteger('state_id');
            $table->string('state_name_ar');
            $table->string('state_name_en');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->boolean('is_major_city')->default(false); // Capital cities and major hubs
            $table->boolean('has_airport')->default(false);
            $table->boolean('has_port')->default(false);
            $table->integer('population')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['state_id', 'active']);
            $table->index('is_major_city');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sudanese_cities');
    }
}
