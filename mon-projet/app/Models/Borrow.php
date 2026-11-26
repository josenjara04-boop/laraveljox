<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Borrow extends Model
{
    //
protected $fillable = ['user_name','book_id','borrowed_at','returned_at','payment_status'];
public function book() { return $this->belongsTo(Book::class); }
}
