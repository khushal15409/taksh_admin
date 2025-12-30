<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'owner_name',
        'full_address',
        'location',
        'pincode',
        'latitude',
        'longitude',
        'state',
        'city',
        'email',
        'mobile_number',
        'images',
        'documents',
        'zone_id',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'zone_id' => 'integer',
        'images' => 'array',
        'documents' => 'array',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function miniwarehouses(): BelongsToMany
    {
        return $this->belongsToMany(Miniwarehouse::class, 'warehouse_miniwarehouse', 'warehouse_id', 'miniwarehouse_id')->withTimestamps();
    }

    public function lmCenters(): BelongsToMany
    {
        return $this->belongsToMany(LmCenter::class, 'warehouse_lm_center', 'warehouse_id', 'lm_center_id')->withTimestamps();
    }

    public function fmRtCenters(): BelongsToMany
    {
        return $this->belongsToMany(FmRtCenter::class, 'warehouse_fm_rt_center', 'warehouse_id', 'fm_rt_center_id')->withTimestamps();
    }

    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'warehouse_warehouse', 'warehouse_id', 'mapped_warehouse_id')->withTimestamps();
    }
}
