<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'display_name'];

    // 1 role có thể được gán cho nhiều user
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
