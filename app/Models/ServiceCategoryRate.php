<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCategoryRate extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function service_type()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id', 'id');
    }

    public function vehicle_category()
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_category_id', 'id');
    }
}
