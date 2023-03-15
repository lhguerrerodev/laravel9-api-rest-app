<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProApp extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'created_at',
        'updated_at'
    ];

    protected $table = 'pro_apps';

    protected $primaryKey = 'id';
}
