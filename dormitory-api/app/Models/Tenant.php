<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'room_id', 'phone', 'emergency_contact', 
        'emergency_phone', 'check_in_date', 'check_out_date', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}