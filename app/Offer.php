<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{

    public $table = 'offers';
    public $primaryKey = 'id';
    public $fillable = ['title', 'start_date', 'expire_date', 'type', 'all_book', 'library_id', 'book_discount_rate', 'from_book', 'book_offer_type', 'buy_discount_rate', 'more_than', 'buy_offer_type', 'created_at', 'updated_at'];


    /**
     *
     * set offer's type
     * @param $type
     * @throws \Exception
     */
    public function setType($type)
    {
        if ($type != FREE_DELIVERING && $type != PRICE_DISCOUNT && $type != ALL_OFFER)
            throw new \Exception(UNSUPPORTED_TYPE, UNSUPPORTED_OFFER_TYPES_CODE);
        $this->attributes['type'] = $type;
    }


    /**
     *
     * set offer's expiration date
     * @param $expire_date
     * @throws \Exception
     */
    public function setExpireDate($expire_date)
    {
        $parsedExpireDate = Carbon::parse($expire_date);
        $parsedStartDate = Carbon::parse($this->attributes['start_date']);
        if ($parsedStartDate->gt($parsedExpireDate))
            throw new \Exception(INCORRECT_EXPIRE_DATE, INCORRECT_EXPIRE_DATE_CODE);
        $this->attributes['$expire_date'] = $expire_date;
    }


    public function library()
    {
        return $this->belongsTo(Library::class, 'library_id', 'id');
    }

    public function offeredBooks()
    {
        return $this->hasMany(BookOffer::class, 'offer_id', 'id');
    }
}
