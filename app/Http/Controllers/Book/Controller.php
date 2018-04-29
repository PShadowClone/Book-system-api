<?php

namespace App\Http\Controllers\Book;

use App\Book;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;

class Controller extends BaseController
{


    /**
     *
     * show all registered books
     *
     * @param Request $request
     * @param null $id
     * @return $this
     */
    public function show(Request $request, $id = null)
    {
        try {
            $filter = $this->filterConditions()[$request->input('filter', 1)];

            if ($id) {
                $book = Book::find($id);
                if (!$book)
                    return error(trans('lang.book_not_found'), NOT_FOUND);
                $book['image'] = env('ASSETS_URL') . $book->image;
                $book['evaluations'] = $book->evaluations()->avg('user_evaluations.evaluate');
                return success($book);
            }
            if ($request->input('search')) {
                $result = $this->showSearchedBooks($request);
                return $result['status']($result['data']);
            }
            $books = Book::orderBy($filter[0], $filter[1])->paginate($request->input('per_page', DEFAULT_BOOK_PAGINATION_NUMBER));
            $books->map(function ($item) {
                $item['image'] = env('ASSETS_URL') . $item->image;
                $item['evaluations'] = $item->evaluations()->avg('user_evaluations.evaluate');
                return $item;
            });
            return success($books);
        } catch (\Exception $exception) {
            return error(trans('lang.book_show_error'));
        }
    }

    /**
     *
     * *************************************
     *          Searched Books
     * *************************************
     *
     * this function returns all searched books that user want to get according to search type
     *
     * 1 => ByCategory
     * 2 => ByLibrary
     * 3 => ByPublisher
     * 4 => ByWriter
     * 5 => ByInquisitor
     * 6 => ByCity
     *
     *
     * this function changes search dynamically according to search type
     * and value that are prepared using searchConditions() function
     *
     * @param Request $request
     * @return array
     */
    private function showSearchedBooks(Request $request)
    {
        try {
            $conditions = 'searchBy' . $this->searchConditions()[$request->input('search')];
        } catch (\Exception $exception) {
            return ['data' => trans('lang.invalid_search_option'), 'status' => ERROR];
        }
        try {
            $filters = $this->filterConditions()[$request->input('filter', 1)];
        } catch (\Exception $exception) {
            $filters = $this->filterConditions()[1];
        }
        $content = Book::$conditions($request->input('value'));
        if (!$content)
            return ['data' => $content, 'status' => SUCCESS];
        $content = $content->orderBy($filters[0], $filters[1])->paginate($request->input('per_page', DEFAULT_BOOK_PAGINATION_NUMBER));
        $content->map(function ($item) {
            $item['image'] = env('ASSETS_URL') . $item->image;
            return $item;
        });
        return ['data' => $content, 'status' => SUCCESS];
    }

    /**
     *
     * this function returns search conditions
     * that user want to get list of books according to them
     * @default is none
     *
     * @return array
     */
    private function searchConditions()
    {
        return [
            1 => 'Category',
            2 => 'Library',
            3 => 'Publisher',
            4 => 'Writer',
            6 => 'Inquisitor',
            7 => 'City',

        ];
    }

    /**
     *
     * this function returns the type of required filters
     * @default is 1
     *
     * @return array
     */
    private static function filterConditions()
    {
        return [
            4 => [0 => 'books.price', 1 => 'asc'],
            3 => [0 => 'books.price', 1 => 'desc'],
            2 => [0 => 'books.id', 1 => 'asc'],
            1 => [0 => 'books.id', 1 => 'desc'],
        ];
    }
}
