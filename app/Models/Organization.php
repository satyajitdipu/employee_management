<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;
    protected $fillable = ['name','email','phone','address','description'];
    public function apps()
    {
        return $this->hasMany(App::class);
    }
}
