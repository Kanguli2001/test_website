<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;

class Chirp extends Model
{
    use Notifiable;

    protected $fillable = [
        'message',
    ];

    public function user (): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chirps():HasMany
    {
        return $this->hasMany(Chirp::class);
    }
}
