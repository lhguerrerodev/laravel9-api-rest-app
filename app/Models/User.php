<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'middle_name',
        'last_name',
        'email',
        'password',
        'reg_status',
        'created_by',
        'updated_by'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
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

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function roles()
    {
    //return $this->belongsToMany(RelatedModel, pivot_table_name, foreign_key_of_current_model_in_pivot_table, foreign_key_of_other_model_in_pivot_table);
      return $this->belongsToMany(
                Role::class,
                'user_roles',
                'user_id',
                'role_id');
    }

    public function permissions()
    {
    //return $this->belongsToMany(RelatedModel, pivot_table_name, foreign_key_of_current_model_in_pivot_table, foreign_key_of_other_model_in_pivot_table);
      return $this->belongsToMany(
                Permission::class,
                'user_permissions',
                'user_id',
                'permission_id');
    }

    public function allPermissions()
    {
    
        $permissions = DB::select(' SELECT * FROM (SELECT p.id, p.name
                    FROM permissions p
                    JOIN role_permissions rp ON rp.permission_id = p.id
                    JOIN roles r ON r.id = rp.role_id
                    JOIN user_roles ur ON ur.role_id = r.id
                    WHERE ur.user_id = ?  && r.reg_status <> \'99\'
                    UNION 
                    SELECT p.id, p.name
                    FROM permissions p
                    JOIN user_permissions up ON up.permission_id = p.id
                    WHERE up.user_id = ? ) a order by a.id ', [$this->id, $this->id]);

        $arrayPer = array();

        foreach ($permissions as $per) {
            array_push($arrayPer, $per->name);
        }

        return $arrayPer;
    }

    /*public function getPassword($pass)
    {
        return Crypt::decrypt($pass);
 
    }*/

    protected function password(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Crypt::decrypt($value),
        );
    }
}
