<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PromoCodes extends Model
{
    public $table = 'promocodes';
    public $primaryKey = 'id';
    public $fillable = ['code', 'admin_id', 'discount_rate'];
    public $with = ['admin'];

    /**
     * get admin who  is responsible for generating this code
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }


    /**
     *
     * get promocodes according client's name
     * @param $client_name
     * @return mixed
     */
    public static function byClient($client_name)
    {
        return DB::table('client_promocodes as client_promo')
            ->join('users as user', 'user.id', '=', 'client_promo.id')
            ->join('promocodes' ,'promocodes.id' ,'=','client_promo.promo_id')
            ->where('user.name', 'like', '%' . $client_name . '%')
            ->select(['promocodes.*']);
    }
}
