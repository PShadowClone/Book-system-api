<?php

namespace App;

use App\Request as BookRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cart extends Model
{

    use SoftDeletes;

    public $table = 'cart';
    public $fillable = ['request_id', 'book_id', 'client_id', 'library_id', 'created_at', 'updated_at', 'deleted_at'];

    public function book()
    {
        return $this->hasOne(Book::class, 'id', 'book_id');
    }

    public function library()
    {
        return $this->hasOne(Library::class, 'id', 'library_id');
    }

    public function request()
    {
        return $this->hasOne(BookRequest::class, 'id', 'request_id');
    }
}
