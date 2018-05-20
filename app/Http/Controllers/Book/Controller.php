<?php

namespace App\Http\Controllers\Book;

use App\Book;
use App\BookEvaluations;
use App\Category;
use App\Library;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use  Maatwebsite\Excel\Facades\Excel;

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
                $book = Book::with(['library'])->find($id);
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
            $books = Book::orderBy($filter[0], $filter[1]);
            if ($request->input('provider') == 'LIBRARY') {
                $library = Library::where('id', '=', Auth::user()->id)->first();
                if ($library)
                    $books = $books->where('library_id', '=', $library->id);
            }

            $books = $books->paginate($request->input('per_page', DEFAULT_BOOK_PAGINATION_NUMBER));
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
     * store a collection of books by uploading excel files which has
     * all information about new books
     *
     * insertion done by EXCEL file
     *
     * @param Request $request
     * @return $this
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'books' => 'required|mimes:csv,xlsx,xls',
        ], [
            'books.required' => trans('lang.books_required'),
            'books.mimes' => trans('lang.books_mimes')
        ]);

        if ($validator->fails()) {
            return error(trans($validator->errors()));
        }
        $path = $request->file('books')->getRealPath();
        try {
            $data = Excel::load($path, function ($reader) {
            })->get();
            if (!empty($data) && $data->count()) {
                foreach ($data->toArray() as $key => $value) {
                    if (!empty($value)) {
                        foreach ($value as $v) {
                            $inserts[] = ['publisher' => $v['publisher'],
                                'name' => $v['name'],
                                'arrange' => $v['arrange'],
                                'amount' => $v['amount'],
                                'writer' => $v['writer'],
                                'publish_date' => $v['publish_date'],
                                'description' => $v['description'],
                                'price' => $v['price'],
                                'category' => $v['category'],
                                'library' => $v['library'],
                                'inquisitor' => $v['inquisitor']
                            ];
                        }
                    }
                }
                foreach ($inserts as $book) {
                    $category = Category::where(['name' => $book['category']])->first();
                    if (!$category) {
                        return error(trans('lang.category_not_found'));
                    }
                    $library = Library::where(['name' => $book['library']])->first();
                    if (!$library) {
                        return error(trans('lang.library_not_found'));
                    }
                    $newBook = new Book();
                    $newBook->fill($book);
                    $newBook->category_id = $category->id;
                    $newBook->library_id = $library->id;
                    $newBook->save();
                }
            }

            return success(trans('lang.book_stored_successfully'));
        } catch (\Exception $exception) {
            return error(trans('lang.book_store_error'));
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
     * evaluate books is done by inserting book's evaluation in separated table
     *
     * if book has been evaluated before by the same user, evaluation will be rejected
     *
     * @param Request $request
     * @return $this
     *
     */
    public function evaluate(Request $request)
    {

        $validator = Validator::make($request->all(), $this->rules(), $this->messages());
        if ($validator->fails())
            return error($validator->errors());
        try {
            $bookEvaluation = BookEvaluations::where(['client_id' => Auth::user()->id, 'book_id' => $request->input('book_id')])->first();
            if ($bookEvaluation)
                return error(trans('lang.evaluated_before'));
            $request['client_id'] = Auth::user()->id;
            $evaluation = BookEvaluations::create($request->all());
            unset($request['client_id']); // hide user's id
            return success($evaluation);
        } catch (\Exception $exception) {
            return error(trans('lang.book_evaluation_error'));
        }
    }


    /**
     *
     * show all book evaluations
     *
     *
     * @param Request $request
     * @param null $id
     * @return $this
     */
    public function showEvaluations(Request $request, $id = null)
    {
        try {
            $bookEvaluations = BookEvaluations::where(['book_id' => $id])->get();
            return success($bookEvaluations);
        } catch (\Exception $exception) {
            return error(trans('lang.book_evaluations_show_error'));
        }
    }


    /**
     *
     * evaluation validation's rules
     *
     *
     * @return array
     */
    private function rules()
    {
        return [
            'book_id' => 'required|exists:books,id',
            'evaluate' => 'required|integer|between:0,5'
        ];
    }

    /**
     *
     * evaluation validation's messages
     *
     *
     * @return array
     */
    private function messages()
    {
        return [
            'book_id.required' => trans('lang.book_id_required'),
            'book_id.exists' => trans('lang.book_id_exists'),
            'evaluate.required' => trans('lang.evaluation_required'),
            'evaluate.integer' => trans('lang.evaluation_integer'),
            'evaluate.between' => trans('lang.evaluation_between'),
        ];
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
