<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;

class Role extends Model
{
    use HasFactory;

     /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'reg_status',
        'created_by',
        'updated_by'
    ];

    //protected $visible = ['id', 'name', 'description'];

    protected $hidden = [
        'reg_status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'created_by',
        'pivot'
    ];





    public function getCreatedAtAttribute($date)
    {
        // h -> 12h H -> 24h
        return Carbon::parse($date)->format("d-m-Y h:i:s");
    }

    public function getUpdatedAtAttribute($date)
    {
        return Carbon::parse($date)->format("d-m-Y H:i:s");
    }

   /* public function users()
    {
    //return $this->belongsToMany(RelatedModel, pivot_table_name, foreign_key_of_current_model_in_pivot_table, foreign_key_of_other_model_in_pivot_table);
    return $this->belongsToMany(
            User::class,
            'user_roles',
            'role_id',
            'user_id')->withTimestamps();
    }*/

    public function permissions()
    {
    //return $this->belongsToMany(RelatedModel, pivot_table_name, foreign_key_of_current_model_in_pivot_table, foreign_key_of_other_model_in_pivot_table);
    return $this->belongsToMany(
            Permission::class,
            'role_permissions',
            'role_id',
            'permission_id')->withTimestamps();
    }

}




