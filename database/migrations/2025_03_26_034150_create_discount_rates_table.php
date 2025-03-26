<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscountRatesTable extends Migration
{
    public function up()
    {
        Schema::create('discount_rates', function (Blueprint $table) {
            $table->bigIncrements('id');  // Explicitement utiliser bigIncrements
            $table->decimal('rate', 5, 2);
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('discount_rates');
    }
}