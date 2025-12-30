<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Miniwarehouse extends Model
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

    public function lmCenters(): BelongsToMany
    {
        return $this->belongsToMany(LmCenter::class, 'miniwarehouse_lm_center', 'miniwarehouse_id', 'lm_center_id')->withTimestamps();
    }

    public function fmRtCenters(): BelongsToMany
    {
        return $this->belongsToMany(FmRtCenter::class, 'miniwarehouse_fm_rt_center', 'miniwarehouse_id', 'fm_rt_center_id')->withTimestamps();
    }
}
