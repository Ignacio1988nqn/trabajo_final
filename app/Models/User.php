<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage; // 👈 importante

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // ✅ Agregado para mostrar el avatar automáticamente
    protected $appends = ['avatar_url'];

    // public function getAvatarUrlAttribute()
    // {
    //     if ($this->avatar_path) {
    //         return Storage::disk('public')->url($this->avatar_path);
    //     }

    //     // fallback: genera iniciales con color
    //     return 'https://ui-avatars.com/api/?name='
    //         . urlencode($this->name ?? 'U')
    //         . '&background=0F3D3E&color=ffffff&size=128';
    // }
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar_path) {
            return asset('storage/' . $this->avatar_path);
        }
        return 'https://ui-avatars.com/api/?name='
            . urlencode($this->name ?? 'U')
            . '&background=0F3D3E&color=ffffff&size=128';
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
