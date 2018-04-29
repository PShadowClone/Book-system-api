<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserEvaluations extends Model
{

    public $table = 'user_evaluations';
    public $primaryKey = 'id';
    public $fillable = ['id', 'client_id', 'request_id', 'driver_id', 'evaluate','book_id', 'note'];
    public $dates = ['created_at', 'updated_at'];

    /**
     * get userEvaluations' client (who evaluates driver)
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function client()
    {
        return $this->hasOne(User::class, 'id', 'client_id');
    }


    /**
     * get userEvaluations' driver (who is been evaluated)
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function driver()
    {
        return $this->hasOne(User::class, 'id', 'driver_id');
    }


    /**
     * get evaluation's request
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function request()
    {
        return $this->belongsTo(Request::class, 'request_id', 'id');
    }


    /**
     * get all user evaluations according to the given request's info
     *
     * @param $driver_id
     * @param null $request_indent
     * @return $this
     */
    public static function byRequest($request_indent = null, $driver_id = null)
    {

        $userEvaluations = UserEvaluations::join('requests', 'requests.id', '=', 'user_evaluations.request_id')
            ->where('user_evaluations.driver_id', '=', $driver_id)
            ->where('requests.request_identifier', 'like', '%' . $request_indent . '%')
            ->orWhere('requests.created_at', 'like', '%' . $request_indent . '%')
            ->select(['user_evaluations.*']);
        return $userEvaluations;
    }

    /**
     * get all user evaluations according to the given client's info
     *
     * @param null $client_info
     * @return $this
     */
    public static function byClient($client_info = null, $driver_id = null)
    {
        $userEvaluations = UserEvaluations::join('users', 'users.id', '=', 'user_evaluations.client_id')
            ->where('user_evaluations.driver_id', '=', $driver_id)
            ->where('users.name', 'like', '%' . $client_info . '%')
            ->orWhere('users.phone', 'like', '%' . $client_info . '%')
            ->select(['user_evaluations.*']);
        return $userEvaluations;
    }

}
