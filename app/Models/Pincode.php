<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Pincode extends Model
{
    use HasFactory;

    protected $fillable = [
        'circlename',
        'regionname',
        'divisionname',
        'officename',
        'pincode',
        'officetype',
        'delivery',
        'district',
        'statename',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function lmCenters(): BelongsToMany
    {
        return $this->belongsToMany(LmCenter::class, 'lm_center_pincode', 'pincode_id', 'lm_center_id')->withTimestamps();
    }
}
