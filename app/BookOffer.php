<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    public static function getOfferedBooks($book_ids = array())
    {

        $offers = DB::table('book_offers')->join('offers', 'book_offers.id', '=', 'offers.id')
            ->join('books', 'book_offers.id', '=', 'books.id')
            ->whereDate('start_date', '<=', Carbon::now())
            ->whereDate('expire_date', '>=', Carbon::now())
            ->whereIn('books.id', $book_ids)
            ->select(['offers.*', 'books.*'])
            ->get();
        return $offers;
    }
}
