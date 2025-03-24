<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImportableTablesTable extends Migration
{
    public function up()
    {
        Schema::create('importable_tables', function (Blueprint $table) {
            $table->id();
            $table->string('table_name');
            $table->string('display_name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('importable_tables');
    }
}