<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Intern extends Model
{
    protected $guarded = ['id'];

    public function presences(): MorphMany
    {
        return $this->morphMany(Presence::class, 'user');
    }
}
