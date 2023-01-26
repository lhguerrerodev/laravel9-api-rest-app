<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'reg_status',
        'created_by',
        'updated_by'
    ];

    protected $visible = ['id', 'name', 'description'];
}
