<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credentials extends Model
{
    use HasFactory;
    protected $fillable = ['name','authorized_origin','redirect_uri','client_api_key'];
    public function app()
    {
        return $this->belongsTo(App::class);
    }
}
