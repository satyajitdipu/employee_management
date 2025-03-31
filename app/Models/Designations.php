<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Venturecraft\Revisionable\RevisionableTrait;
use Illuminate\Database\Eloquent\Model;



class Designations extends Model
{
    use HasFactory;
    use SoftDeletes;
    use RevisionableTrait;


    protected $cascadeDeletes = ['employees'];


  protected $fillable = [
        'name',
        'department_id'
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

     public function department()
    {
        return $this->belongsTo(Departments::class);
    }
}
