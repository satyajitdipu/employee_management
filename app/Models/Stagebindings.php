<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stagebindings extends Model
{
    protected $fillable=['type','stage','order','Evaluate_when_flow_is_planned','Evaluate_when_stage_is_run','Invalid_response_behavior','Policy_engine_mode'];
    public function Flows()
    {
        return $this->belongsTo(Flows::class);
    }
    public function Stages()
    {
        return $this->belongsTo(Stages::class);
    }
    use HasFactory;
}
