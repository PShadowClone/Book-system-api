<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LibraryPayment extends Model
{
    use SoftDeletes;
    public $table = 'library_payed';
    public $primaryKey = 'id';
    public $fillable = ['id', 'money', 'library_id', 'type'];
    public $dates = ['created_at', 'updated_at', 'deleted_at'];


    /**
     * Type = 1 , cache
     * Type = 2 , Bank Transaction
     */


    /**
     *  check if type is one of the given types or not
     * @param string $type
     * @return string
     * @throws \Exception
     */
    public function setType($type = '1')
    {
        if ($type != '1' && $type != '3')
            throw new \Exception('unsupported types');
        return $type;
    }

    /**
     *  get the summation of money that library has  payed
     * @param null $library_id
     * @return mixed
     */
    static function libraryPayments($library_id = null)
    {
        return LibraryPayment::where(['library_id' => $library_id, 'deleted_at' => null])->sum('money');
    }

    public function library()
    {
        return $this->belongsTo(Library::class, 'library_id', 'id');
    }
}
