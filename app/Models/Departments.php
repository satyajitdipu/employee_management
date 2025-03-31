<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Departments extends Model
{
    use HasFactory;

 protected $fillable = [
        'name',
        'description',
        'parent_id',
    ];

    public function parent_department()
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    public function child_departments()
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

     public function designations()
    {
        return $this->hasMany(Designation::class);
    }
}
