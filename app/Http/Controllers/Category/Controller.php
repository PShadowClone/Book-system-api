<?php

namespace App\Http\Controllers\Category;

use App\Category;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;

class Controller extends BaseController
{

    /**
     *
     * bookNumber means number of books that should be retrieved with each category
     * per_page means number of result that will be returned each time in pagination.
     *
     * @param null $id
     * @return $this
     *
     */
    public function show(Request $request, $id = null)
    {
        try {
            if ($id) {
                $category = Category::where(['id' => $id])->orWhere('name', 'like', '%' . $id . '%')->get();
                if (!$category) {
                    return error(trans('lang.category_not_found'), 404);
                }
                $category = $this->getCategoryById($request, $category, $id);
                return success($category);
            }
            $categories = Category::orderBy('id', 'desc')->paginate($request->input('per_page', DEFAULT_CATEGORY_PAGINATION_NUMBER));
            $data = $categories->map(function ($item) use ($request) {
                $item['image'] = env('ASSETS_URL') . $item->image;
                $item['books'] = $item->books()->orderBy('books.id', 'desc')->limit($request->input('book_number', DEFAULT_CATEGORY_BOOKS_NUMBER))->get()->map(function ($item) {
                    $item->image = env('ASSETS_URL') . $item->image;

                    return $item;
                });
                return $item;
            });

            return success($categories);
        } catch (\Exception $exception) {
            return error(trans('lang.show_category_error'));
        }
    }


    /**
     *
     * get the info of single category or even the result of searching
     *
     *
     * @param Request $request
     * @param $category
     * @param $id
     * @return mixed
     */
    private function getCategoryById(Request $request, $category, $id)
    {
        $orderConditions = $this->handelSortConditions($request->input('book_sort_by', 1));
        $category = $category->map(function ($item) use ($orderConditions) {
            $item['image'] = env('ASSETS_URL') . $item->image;
            $books = $item->books()->orderBy($orderConditions[0], $orderConditions[1])->paginate(1);
            $item['books'] = $books->map(function ($item) {
                $item->image = env('ASSETS_URL') . $item->image;
                return $item;
            });

            return $item;
        });
        $tempCategory = Category::find($id);
        if ($tempCategory)
            return $category->first();
        return $category;
    }

    /**
     *
     * this function is used for handling all sort conditions the user may use
     *
     * @param null $sortBy
     * @return array
     */
    private function handelSortConditions($sortBy = null)
    {
        switch ($sortBy) {
            case 1:
                return [0 => 'id', 1 => 'desc'];
            case 2:
                return [0 => 'id', 1 => 'asc'];
            case 3:
                return [0 => 'arrange', 1 => 'asc'];
            case 4:
                return [0 => 'arrange', 1 => 'desc'];
            case 5:
                return [0 => 'name', 1 => 'asc'];
            case 6 :
                return [0 => 'name', 1 => 'desc'];
            case 7:
                return [0 => 'price', 1 => 'desc'];
            case 8:
                return [0 => 'price', 1 => 'asc'];
            default:
                return [0 => 'id', 1 => 'desc'];
        }
    }
}
