<?php

namespace App\Http\Controllers\Advertisements;

use App\Advertisement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\URL;

class Controller extends BaseController
{

    /**
     *
     * get all available advertisements
     *
     * @param Request $request
     * @param null $ads_id
     * @return $this
     */
    public function show(Request $request, $ads_id = null)
    {
        try {
            $advertisements = Advertisement::whereDate('start_publish', '<=', Carbon::now())
                ->whereDate('end_publish', '>=', Carbon::now())->orderBy('arrange', 'asc');
            if ($ads_id) {
                $advertisements = $advertisements->where(['id' => $ads_id])->first();
                if (!$advertisements)
                    return error(trans('lang.ads_is_not_found'));
                if ($advertisements->image) {
                    $advertisements['image'] = URL::to('/') . $advertisements->image;
                }
            } else {
                $advertisements = $advertisements->get()->map(function ($item) {
                    if ($item->image) {
                        $item['image'] = URL::to('/') . $item->image;
                    }
                    return $item;
                });
            }
            return success($advertisements, 200, $advertisements instanceof Advertisement ? 1 : $advertisements->count());
        } catch (\Exception $exception) {
            return error(trans('lang.ads_show_error'));
        }
    }


}
