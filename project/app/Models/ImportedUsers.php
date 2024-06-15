<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportedUsers extends Model
{
    protected $table = 'imported_users';
    protected $fillable = ['name', 'date'];
}
