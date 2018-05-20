<?php

namespace App\Http\Controllers\Offer;

use App\Offer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;

class Controller extends BaseController
{

    /**
     *
     * show all offers
     * @if available == 1 @then get all available offers
     * @else get all offers
     *
     * @param Request $request
     * @param null $id
     * @return $this
     */
    public function show(Request $request, $id = null)
    {
        try {
            if ($id) {
                $offer = Offer::find($id);
                if (!$offer)
                    return error(trans('lang.offer_no_found'));
                $offer['offeredBooks'] = $offer->offeredBooks()->get()->map(function ($item) {
                    $book = $item->book;
                    if ($book)
                        $item['book_name'] = $book->name;
                    unset($item->book); // unset book modal just for simplifying the returned json
                    return $item;
                });
                return success($offer);
            }
            $offers = Offer::where([]);
            if ($request->input('status') == '1')
                $offers = $offers->whereDate('start_date', '<=', Carbon::now())->whereDate('expire_date', '>=', Carbon::now())->orderBy('id', 'desc');
            $offers = $offers->orderBy('id', 'desc')->paginate($request->input('per_page', DEFAULT_OFFER_PAGINATION_NUMBER));
            return success($offers);
        } catch (\Exception $exception) {
            return error(trans('lang.show_offers_error'));
        }
    }
}
