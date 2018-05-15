<?php

namespace App\Http\Controllers\Driver;

use App\UserPayment;
use Illuminate\Http\Request;
use App\Request as BookRequests;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{

    /**
     *
     * show all driver's requests
     *
     *
     * @param Request $request
     * @return $this
     */
    public function requests(Request $request, $request_id = null)
    {
        try {
            $bookRequests = BookRequests::where(['driver_id' => Auth::user()->id]);
            if ($request_id) {
                $bookRequests = $bookRequests->with(['client', 'book', 'library'])->where(['id' => $request_id])->first();
                if ($bookRequests->book)
                    $bookRequests->book['image'] = env('ASSETS_URL') . $bookRequests->book->image;
                return success($bookRequests);
            }
            $bookRequests = $bookRequests->paginate($request->input('per_page', DEFAULT_DRIVER_REQUESTS_PAGINATION_NUMBER));
            return success($bookRequests);
        } catch (\Exception $exception) {
            return error(trans('lang.show_driver_requests'));
        }
    }

    /**
     * get all driver'sfinancial information
     *
     * @param Request $request
     * @return $this
     */

    public function sales(Request $request)
    {

        $driver = Auth::user();
        try {
            $data['instPayedProfits'] = UserPayment::userPayments($driver->id);
            $data['instProfits'] = $this->calculateInstitutionProfits($driver->instRate, $driver->total_profits);
            $data['resetPayment'] = $data['instProfits'] - $data['instPayedProfits'];
            $data['pureProfits'] = $driver->total_profits - UserPayment::userPayments($driver->id);
            return success($data);
        } catch (\Exception $exception) {
            return error(trans('lang.show_driver_profits_error'));
        }
    }


    /**
     *
     * get the details of driver's profits
     *
     * @param Request $request
     * @return $this
     */
    public function saleDetails(Request $request)
    {
        try {
            $userPayments = UserPayment::where(['user_id' => Auth::user()->id])->paginate($request->input('per_page', DEFAULT_DRIVER_PROFITS_PAGINATION_NUMBER));
            return success($userPayments);
        } catch (\Exception $exception) {
            return error(trans('lang.show_driver_profit_details'));
        }
    }

    /**
     * calculates institution's reserved money
     *
     * @param $rate
     * @param $total
     * @return float|int
     */
    private function calculateInstitutionProfits($rate, $total)
    {
        return ($rate * $total) / 100;
    }
}
