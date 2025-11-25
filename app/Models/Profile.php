<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Profile extends Model
{
    /** @use HasFactory<\Database\Factories\ProfileFactory> */
    use HasFactory;

    protected $table = 'profiles';

    protected $fillable = [
        'type',
        'first_name',
        'last_name',
        'company_name',
        'full_name',
        'personal_id',
        'ruc',
        'email',
        'phone',
        'address',
        'gender',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

}
