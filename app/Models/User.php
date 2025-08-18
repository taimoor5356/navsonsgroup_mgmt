<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Request;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

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
        'password' => 'hashed',
    ];

    // Mutator for "name"
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = strtolower($value);
    }

    static public function customer()
    {
        $records = User::with('role')->whereHas('role', function ($q) {
            $q->where('name', 'customer');
        });
        return $records;
    }

    static public function user()
    {
        $records = User::with('role')->whereHas('role', function ($q) {
            $q->where('name', 'user')->orWhere('name', 'admin')->orWhere('name', 'manager')->orWhere('name', 'employee');
        });
        return $records;
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', 1);
    }

    // Scope for customer role
    public function scopeCustomer(Builder $query)
    {
        return $query->with('role')->whereHas('role', function ($q) {
            $q->where('name', 'customer');
        });
    }

    // Scope for customer role
    public function scopeUser(Builder $query)
    {
        return $query->with('role')->whereHas('role', function ($q) {
            $q->where('name', 'user')->orWhere('name', 'admin')->orWhere('name', 'manager')->orWhere('name', 'employee');
        });
    }

    // Relations
    public function role()
    {
        return $this->belongsTo(Role::class, 'user_type', 'id');
    }
    
    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'user_id', 'id');
    }
    
    public function expenses()
    {
        return $this->hasMany(Expense::class, 'user_id', 'id');
    }
}
