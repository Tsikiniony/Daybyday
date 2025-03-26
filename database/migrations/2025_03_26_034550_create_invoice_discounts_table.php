<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceDiscountsTable extends Migration
{
    public function up()
    {
        Schema::create('invoice_discounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('invoice_id');
            $table->unsignedBigInteger('discount_rate_id');  // Changé pour correspondre au type de discount_rates
            $table->decimal('original_amount', 10, 2);
            $table->decimal('discounted_amount', 10, 2);
            $table->timestamps();
            $table->softDeletes();
    
            $table->foreign('invoice_id')
                  ->references('id')
                  ->on('invoices')
                  ->onDelete('cascade');
    
            $table->foreign('discount_rate_id')
                  ->references('id')
                  ->on('discount_rates')
                  ->onDelete('cascade');
        });
    }   

    public function down()
    {
        Schema::dropIfExists('invoice_discounts');
    }
}