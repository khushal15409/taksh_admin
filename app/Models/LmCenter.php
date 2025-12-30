<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class LmCenter extends Model
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
        'owner_id',
        'aadhaar_card',
        'aadhaar_number',
        'aadhaar_verified',
        'pan_card',
        'pan_card_number',
        'pan_verified',
        'bank_name',
        'bank_account_number',
        'bank_ifsc_code',
        'bank_branch',
        'bank_holder_name',
        'bank_document',
        'document',
        'state',
        'city',
        'zone_id',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
        'zone_id' => 'integer',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function fmRtCenters(): BelongsToMany
    {
        return $this->belongsToMany(FmRtCenter::class, 'lm_center_fm_rt_center', 'lm_center_id', 'fm_rt_center_id')->withTimestamps();
    }

    public function pincodes(): BelongsToMany
    {
        return $this->belongsToMany(Pincode::class, 'lm_center_pincode', 'lm_center_id', 'pincode_id')->withTimestamps();
    }
}

