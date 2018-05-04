<?php

namespace App\Http\Controllers\Library;

use App\Http\Controllers\HelperController;
use App\Library;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Auth;

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
}
