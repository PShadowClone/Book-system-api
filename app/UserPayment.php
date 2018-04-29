<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPayment extends Model
{

    use SoftDeletes;
    public $table = 'user_payed';
    public $primaryKey = 'id';
    public $fillable = ['id','money','user_id' , 'type'];
    public $dates = ['created_at' , 'updated_at' , 'deleted_at'];

    /**
     *  check if type is one of the given types or not
     * @param string $type
     * @return string
     * @throws \Exception
     */
    public function setType($type = '1'){
        if($type != '1' && $type != '3')
            throw new \Exception(UNSUPPORTED_TYPE);
        return $type;
    }

    /**
     *  get the summation of money that library has  payed
     * @param null $user_id
     * @return mixed
     */
    static function userPayments($user_id = null){
        return UserPayment::where(['user_id' => $user_id , 'deleted_at' => null])->sum('money');
    }


    public function user(){
        return $this->belongsTo(User::class, 'user_id' , 'id');
    }

}
