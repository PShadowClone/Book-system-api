<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Request extends Model
{
    public $table = 'requests';
    public $primaryKey = 'id';
    public $fillable = ['id', 'client_id', 'driver_id', 'book_id','library_id', 'quarter_id', 'delivery_time',
        'status', 'book_amount', 'promo_code', 'request_identifier', 'created_at', 'updated_at', 'longitude', 'latitude','confirming_date'];
    public $dates = ['created_at', 'updated_at'];


    public function client()
    {
        return $this->belongsTo(User::class, 'client_id', 'id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id', 'id');
    }

    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id', 'id');
    }

    public function quarter()
    {
        return $this->belongsTo(Quarter::class, 'quarter_id', 'id');
    }

    /**
     *
     *  get request according to requests' client's info
     * @param $client_info
     * @return mixed
     */
    public static function byClient($client_info , $id = null)
    {
        $requests = Request::
        join('users', 'users.id', '=', 'requests.client_id')
            ->where('type', '=', CLIENT)
            ->where('users.phone', 'like', '%' . $client_info . '%')
            ->orWhere('users.name', 'like', '%' . $client_info . '%')
            ->select(['requests.*']);
        return $requests;

    }

    /**
     *
     *  get request according to requests' driver's info
     * @param $driver_info
     * @return mixed
     */
    public static function byDriver($driver_info)
    {
        $requests = Request::
        join('users', 'users.id', '=', 'requests.driver_id')
            ->where('type', '=', DRIVER)
            ->where('users.phone', 'like', '%' . $driver_info . '%')
            ->orWhere('users.name', 'like', '%' . $driver_info . '%')
            ->select(['requests.*']);
        return $requests;

    }

    /**
     *
     * get all libraries requests according to requests' status (if null, get all library's requests)
     * @param null $request_status
     * @return $this
     */
    public static function libraryRequests($id = null, $request_status = null)
    {

        $userRequests = Request::join('books', 'books.id', '=', 'requests.book_id')
            ->join('drivers', 'drivers.id', '=', 'requests.driver_id')
            ->join('clients', 'clients.id', '=', 'requests.client_id')
            ->join('libraries', 'libraries.id', '=', 'books.library_id')
            ->where('libraries.id' , '=' , $id);
//        if ($id)
//            $userRequests = $userRequests->where(['libraries.id' => $id]);
        if ($request_status && $request_status != '-1')
            $userRequests = $userRequests->where(['requests.status' => $request_status]);
        return $userRequests->select(['requests.*']);

    }
}
