<?php

namespace App\Models;

use App\Filament\Resources\AppResource;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class App extends Model
{
    use HasFactory;
    protected $fillable = ['name','description','url','organization_id'];
    public function oauths()
    {
        return $this->hasMany(OAuthClient::class);
    }
    public function organizations()
    {
        return $this->belongsTo(Organization::class,'organization_id');
    }
}