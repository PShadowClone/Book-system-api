<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BookEvaluations extends Model
{
    public $table = 'book_evaluations';
    public $primaryKey = 'id';
    public $fillable = ['book_id', 'client_id', 'evaluate', 'created_at', 'updated_at'];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id', 'id');
    }

    public function book()
    {
        return $this->belongsTo(User::class, 'book_id', 'id');
    }


}
