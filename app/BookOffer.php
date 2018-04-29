<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BookOffer extends Model
{
    public $table = 'book_offers';
    public $primaryKey = 'id';
    public $fillable = ['offer_id', 'all_book', 'book_id', 'library_id', 'created_at', 'updated_at'];

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id', 'id');
    }

    public function offer()
    {
        return $this->belongsTo(Offer::class, 'offer_id', 'id');
    }

    public function library()
    {
        return $this->belongsTo(Library::class, 'library_id', 'id');
    }
}
