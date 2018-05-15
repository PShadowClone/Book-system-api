<?php

namespace App\Http\Controllers\Request;

use App\Book;
use App\Cart;
use App\PromoCodes;
use App\Request as BookRequest;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class Controller extends BaseController
{
    /**
     *
     * store request with all conditions
     *
     * @NULL type  means request has not sent to library for confirming yet.
     *
     * @param Request $request
     * @return $this
     */
    public function store(Request $request)
    {

        $validations = Validator::make($request->all(), $this->rules($request->input('type')), $this->messages($request->input('type')));
        if ($validations->fails()) {
            return error($validations->errors());
        }
        try {

            $book = Book::find($request->input('book_id'));
            $request['library_id'] = $book->library_id;
            $request['status'] = $request->input('type') == REQUEST_DONE ? FOR_CONFIRMING : NOT_SENT_TO_CONFIRMED; // set status of request FOR_CONFIRMING means it's need to be confirmed from library
            $request['client_id'] = Auth::user()->id; // set client id
            $request['request_identifier'] = str_random(REQUEST_IDENTIFIER_LENGTH);
            $bookRequest = BookRequest::create($request->all());
            Cart::create([
                'client_id' => Auth::user()->id,
                'request_id' => $bookRequest->id,
                'book_id' => $request->input('book_id'),
                'library_id' => Book::find($request->input('book_id'))->library()->first()->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            return success($bookRequest);
        } catch (\Exception $exception) {
            return error(trans('lang.request_store_error'));
        }

    }

    /**
     * change request's status to be FOR_CONFIRMING
     *
     * library should confirm if library has books or not
     *
     *
     * @param Request $request
     * @return $this
     */
    public function confirming(Request $request)
    {
        $client_id = Auth::user()->id;
        $where = [];
        $where['client_id'] = $client_id;
        if ($request_id = $request->input('request_id'))
            $where['request_id'] = $request_id;
        Cart::with(['request'])->where($where)->get()->map(function ($item) {
            if ($item->request->status == NULL || $item->request->status == FOR_CONFIRMING) {
                $item->request->status = FOR_CONFIRMING;
                $item->request->update();
            }
            return $item;
        });
        return success(trans('lang.confirming_request_sent_for_libraries'));
    }


    /**
     *
     * show user's requests or single request according to the given id
     *
     *
     * @param Request $request
     * @param null $id
     * @return $this
     */
    public function show(Request $request, $id = null)
    {
        try {
            if ($id) {
                $bookRequest = BookRequest::find($id);
                if (!$bookRequest)
                    return error(trans('lang.request_not_found'));
                if ($bookRequest['book'] = $bookRequest->book()->first()) {
                    $bookRequest['book']['image'] = env('ASSETS_URL') . $bookRequest['book']->image;
                }

                return success($bookRequest);
            }
            if (Auth::user()->type == CLIENT) {
                $requests = Auth::user()->client_requests()->orderBy('id', 'desc')->paginate($request->input('per_page', COMMON_PAGINATION));
                $requests->map(function ($item) {
                    $item->book['image'] = env('ASSETS_URL') . $item->book->image;
                    return $item;
                });
                return success($requests);
            }
            $requests = Auth::user()->driver_request()->orderBy('id', 'desc')->paginate($request->input('per_page', COMMON_PAGINATION));
            $requests->map(function ($item) {
                $item->book['image'] = env('ASSETS_URL') . $item->book->image;
                return $item;
            });
            return success($requests);
        } catch (\Exception $exception) {
            return error(trans('lang.request_show_error'));
        }
    }


    /**
     *
     * get nearest driver to assign request to him
     *
     * turn
     * @param $latitude
     * @param $longitude
     * @return null
     * @throws \Exception
     */
    private function getNearestDriver($latitude, $longitude)
    {

        $results = DB::select(DB::raw('SELECT id,latitude ,longitude , ( 3959 * acos( cos( radians(' . $latitude . ') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians(' . $longitude . ') ) + sin( radians(' . $latitude . ') ) * sin( radians(latitude) ) ) ) AS distance FROM users where type = ' . DRIVER . ' ORDER BY distance asc'));
        try {
            if (sizeof($results) <= 0)
                return null;
            return $results[0];
        } catch (\Exception $exception) {
            throw new \Exception(trans('lang.nearest_driver_error'));
        }

    }

    /**
     * validation rules
     *
     *
     * @param string $request_status
     * @return array
     */
    private function rules($request_status = FOR_CONFIRMING)
    {
        $rules = [
            'book_id' => 'required|exists:books,id',
            'book_amount' => 'required|numeric',
        ];
        if ($request_status != NOT_SENT_TO_CONFIRMED) {
            $rules['longitude'] = 'required|numeric';
            $rules['latitude'] = 'required|numeric';
            $rules['quarter_id'] = 'required|exists:quarters,id';
        }

        return $rules;

    }

    /**
     *
     * validation messages
     *
     *
     * @return array
     */
    private function messages()
    {
        return [
            'book_id.required' => trans('lang.book_id_required'),
            'book_id.exists' => trans('lang.book_id_exists'),
            'book_amount.required' => trans('lang.amount_required'),
            'book_amount.numeric' => trans('lang.amount_numeric'),
            'longitude.required' => trans('lang.longitude_required'),
            'longitude.numeric' => trans('lang.longitude_numeric'),
            'latitude.required' => trans('lang.latitude_required'),
            'latitude.numeric' => trans('lang.latitude_numeric'),
            'quarter_id.required' => trans('lang.quarter_id_required'),
            'quarter_id.numeric' => trans('lang.quarter_id_numeric'),
            'quarter_id.exists' => trans('lang.quarter_id_exists'),
        ];
    }
}
