<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function vehicle() {
        return $this->belongsTo(Vehicle::class, 'vehicle_id', 'id');
    }

    public function service_type() {
        return $this->belongsTo(ServiceType::class, 'service_type_id', 'id');
    }

    public function payment_mode() {
        return $this->belongsTo(PaymentMode::class, 'payment_mode_id', 'id');
    }
}
