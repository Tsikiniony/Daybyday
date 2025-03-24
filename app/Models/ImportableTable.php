<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportableTable extends Model
{
    protected $fillable = ['table_name', 'display_name', 'is_active'];
}