<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'number', 'floor', 'capacity', 'amenities', 'type', 'status', 'rent_amount'
    ];

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }
}