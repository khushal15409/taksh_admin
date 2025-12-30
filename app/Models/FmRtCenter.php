<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FmRtCenter extends Model
{
    use HasFactory;

    protected $fillable = [
        'center_name',
        'full_address',
        'location',
        'pincode',
        'latitude',
        'longitude',
        'email',
        'mobile_number',
        'owner_name',
        'owner_address',
        'owner_pincode',
        'owner_latitude',
        'owner_longitude',
        'owner_mobile',
        'owner_email',
        'aadhaar_card',
        'pan_card',
        'bank_name',
        'bank_account_number',
        'bank_ifsc_code',
        'bank_branch',
        'bank_holder_name',
        'images',
        'documents',
        'state',
        'city',
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
        return $this->belongsToMany(LmCenter::class, 'lm_center_fm_rt_center', 'fm_rt_center_id', 'lm_center_id')->withTimestamps();
    }

    public function pincodes(): BelongsToMany
    {
        return $this->belongsToMany(Pincode::class, 'fm_rt_center_pincode', 'fm_rt_center_id', 'pincode_id')->withTimestamps();
    }
}

