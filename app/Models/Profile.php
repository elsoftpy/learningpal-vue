<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Vite;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Profile extends Model implements HasMedia
{
    /** @use HasFactory<\Database\Factories\ProfileFactory> */
    use HasFactory, InteractsWithMedia;

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
        'birth_date',
    ];

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->useFallbackUrl(Vite::asset('resources/js/images/default-avatar.png'))
            ->registerMediaConversions(function () {
                $this->addMediaConversion('thumb')
                    ->width(100)
                    ->height(100)
                    ->sharpen(10);
                
                $this->addMediaConversion('medium')
                    ->width(300)
                    ->height(300);
            });
        
        $this->addMediaCollection('payment_receipt')
            ->singleFile();
    }

}
