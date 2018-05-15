<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\HelperController;
use App\Library;
use App\LibraryPayment;
use App\Request as BookRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class Controller extends BaseController
{

    /**
     *
     * ************************************
     *          show Libraries
     * ************************************
     *
     * this function is used to get library/es according to the given data
     * @if library_id exists, function returns single library's info
     * @elseif library_id is not exist, function returns {fixed number} of nearest library
     * @else returns all registered libraries
     *
     * @note distance attribute is unified for all function's returned modals
     *
     * @param Request $request
     * @param null $id
     * @return $this
     */
    public function show(Request $request, $id = null)
    {
        try {
            if ($id) {
                $library = Library::find($id);
                if (!$library)
                    return error(trans('lang.library_not_found'));
                $library['distance'] = 0;
                return success($library);
            }
            if ($request->input('latitude') && $request->input('longitude')) {
                $destinations = nearestDistances($request->input('latitude'), $request->input('longitude'), 'libraries', $request->input('limit', LIMIT_ROWS));
                $destinations = Library::hydrate($destinations);
                return success($destinations);
            }
            $libraries = Library::paginate($request->input('per_page', DEFAULT_LIBRARY_PAGINATION_NUMBER));
            $libraries->map(function ($item) {
                $item['distance'] = 0;
            });
            return success($libraries);
        } catch (\Exception $exception) {
            return error(trans('lang.show_library_error'));
        }

    }

    /**
     *
     * get sales detail for authenticated library
     *
     *
     * @param Request $request
     * @return $this
     */
    public function sales(Request $request)
    {
        if (!$request->input('provider') || $request->input('provider') != 'LIBRARY') {
            return error(trans('lang.not_authorized_access'));
        }
        $library = Library::find(Auth::user()->id);
        if (!$library)
            return error(trans('lang.library_not_found'));
        $data['total_sales'] = LibraryPayment::libraryPayments($library->id);
        $data['inst_profits'] = $this->calculateInstitutionProfits($library->instProfitRate, $library->total_profits);
        $data['resetPayment'] = $data['inst_profits'] - $data['total_sales'];
        $data['pureProfits'] = $library->total_profits - LibraryPayment::libraryPayments($library->id);
        return success($data);
    }

    /**
     *
     * calculate institution profits form the each library's rate
     *
     * @param $rate
     * @param $total
     * @return float|int
     */
    private function calculateInstitutionProfits($rate, $total)
    {
        return ($rate * $total) / 100;
    }

    /**
     *
     * get sales profits details for authenticated library
     *
     *
     * @param Request $request
     * @return $this
     */

    public function salesDetails(Request $request)
    {
        if (!$request->input('provider') || $request->input('provider') != 'LIBRARY') {
            return error(trans('lang.not_authorized_access'));
        }
        $library = Library::find(Auth::user()->id);
        if (!$library)
            return error(trans('lang.library_not_found'));
        try {
            $libraryPaymentsDetails = LibraryPayment::where(['library_id' => $library->id])->get();
            return success($libraryPaymentsDetails);
        } catch (\Exception $exception) {
            return error(trans('lang.show_payments_details_error'));
        }
    }

    /**
     *
     * returns all library's requests
     *
     *
     * @param Request $request
     * @return $this
     */
    public function requests(Request $request)
    {
        if (!$request->input('provider') || $request->input('provider') != 'LIBRARY') {
            return error(trans('lang.not_authorized_access'));
        }
        $library = Library::find(Auth::user()->id);
        if (!$library)
            return error(trans('lang.library_not_found'));
        try {
            $requests = BookRequest::with(['book', 'client'])->where('library_id', '=', $library->id);
            if ($request->input('status') == '1')
                $requests = $requests->where('status', '=', FOR_CONFIRMING);
            $requests = $requests->paginate($request->input('per_page', 6));
            return success($requests);
        } catch (\Exception $exception) {
            return error(trans('lang.show_confirming_requests_error'));
        }
    }


    public function updateRequestStatus(Request $request, $requestId = null)
    {

        if (!$request->input('provider') || $request->input('provider') != 'LIBRARY') {
            return error(trans('lang.not_authorized_access'));
        }
        $library = Library::find(Auth::user()->id);
        if (!$library)
            return error(trans('lang.library_not_found'));
        $validations = Validator::make($request->all(), $this->rules(), $this->messages());
        if ($validations->fails())
            return error($validations->errors());
        $bookRequest = BookRequest::find($requestId);
        if (!$bookRequest)
            return error(trans('lang.request_not_found'));
        $status = $request->input('status');
        $bookRequest->status = $status;
        if ($status == '2') {
            $bookRequest->confirming_date = Carbon::now();
        }
        try {
            $bookRequest = $bookRequest->update();
            return success(trans('lang.request_status_successfully_updated'));
        } catch (\Exception $exception) {
            return error(trans('lang.request_status_changed_error'));
        }
    }


    private function rules()
    {
        return [
            'status' => 'required|in:2,9'
        ];
    }

    private function messages()
    {
        return [
            'status.required' => trans('lang.status_required'),
            'status.in' => trans('lang.status_in')

        ];
    }

}
