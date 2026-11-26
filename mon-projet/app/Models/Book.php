<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    //
protected $fillable = ['title','isbn','year','available','author_id','summary'];
protected $casts = ['available' => 'boolean'];
public function author() { return $this->belongsTo(Author::class); }
public function borrows() { return $this->hasMany(Borrow::class); }
}
