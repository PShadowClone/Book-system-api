<?php

namespace App\Http\Controllers\Cart;

use App\Book;
use App\BookOffer;
use App\Cart;
use App\Library;
use App\Offer;
use App\Request as BookRequest;
use App\Setting;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use MongoDB\Driver\Exception\ExecutionTimeoutException;

class Controller extends BaseController
{


    private $cartPrice = 0;
    private $totalPrice = 0;

    public function show()
    {
        try {

//            dd($this->checkOffers());

//            $result = DB::select('SELECT books.* FROM cart
//                                INNER JOIN books ON cart.book_id = books.id
//                                INNER JOIN libraries ON libraries.id = books.library_id
//                                GROUP BY cart.library_id');

//            dd($result);
//            $books = collect();
//            $cart = Cart::with(['library', 'book'])->where(['client_id' => Auth::user()->id])->get()->map(function ($item, $books) {
//                $item->library()->get()->map(function ($library) use ($item, $books) {
//                    $library['book'] = array();
////                    $item->book()->get()->map(function ($book) use ($item, $library, $books) {
////                        if ($library->id == $book->library_id) {
////                            array_push($library['book'], $book);
////                        }
////                        return $book;
////                    });
//                    $books->push($library);
//                    return $library;
//                });
//                return $item;

//            });
            $cartLibraryIds = Cart::where('client_id', '=', Auth::user()->id)->distinct()->pluck('library_id')->toArray();

//            dd($cartLibraryIds);

            $offer = $this->getOffer();
            $books = $this->getBooks();
            if ($offer) {
                $result = $this->checkMajorOfferType($offer, $books);
                $result = $this->drawReturnedResult($result);
//                dd($result);
            }
            return success($result);
        } catch (\Exception $exception) {
            dd($exception->getMessage());
            return error('lang.cart_show_error');
        }
    }

    private function getLibraries()
    {
        return Cart::join('libraries', 'libraries.id', '=', 'cart.library_id')
            ->select(['libraries.*'])
            ->distinct()
            ->get();
    }

    private function getBooks()
    {
        return Cart::join('books', 'books.id', '=', 'cart.book_id')
            ->select(['books.*'])
            ->distinct();
    }

    private function getOffer()
    {
        return Offer::whereDate('start_date', '<=', Carbon::now())->whereDate('expire_date', '>=', Carbon::now())->orderBy('id', 'desc')->first();
    }

    private function checkMajorOfferType(Offer $offer, $books)
    {
        if ($offer->type == BOOK_OFFER) {
            return $this->checkBOOK_OFFER($offer, $books);
        }
        return null;
    }

    private function checkBOOK_OFFER(Offer $offer, $books)
    {
        if ($offer->book_offer_type == FREE_DELIVERING) {
            return $this->checkFREE_DELIVERING($offer, $books);
        }
        return $this->checkPRICE_DISCOUNT($offer, $books);

    }

    private function checkFREE_DELIVERING(Offer $offer, $books)
    {
        $book_ids = $books->pluck('id');
        $book_total_price = $books->sum('price');
        $offeredBooks = BookOffer::where(['offer_id' => $offer->id])
            ->whereIn('book_id', $book_ids);
        $countOfOfferedBooks = $offeredBooks->count();
        if ($countOfOfferedBooks >= $offer->from_book && $book_total_price >= $offer->book_more_than) {
            return $books->get()->map(function ($item) use ($offer) {
                $result = BookOffer::where(['offer_id' => $offer->id])
                    ->where(['book_id' => $item->id])->first();
                if ($result)
                    $item['delivery_price'] = null;
                else
                    $item['delivery_price'] = $this->getBookDeliveryPrice($item);
                return $item;
            });
        }
        return $books;
    }

    private function getBookDeliveryPrice($book)
    {
        $library = $book->library()->first();
        $cart = Cart::join('requests', 'requests.id', '=', 'cart.request_id')
            ->where('cart.book_id', '=', $book->id)->first();
        if (!$cart)
            return delivery_price(FALSE);
        $request = $cart->request()->first();
        if (!$request)
            return delivery_price(FALSE);
        if (!$library)
            $delivery_price = delivery_price(FALSE);
        else
            $delivery_price = delivery_price($library->quarter_id == $request->quarter_id);
        return $delivery_price;
    }


    private function checkPRICE_DISCOUNT($offer, $books)
    {
        return $books->get()->map(function ($item) use ($offer) {
            $result = BookOffer::where(['offer_id' => $offer->id])
                ->where(['book_id' => $item->id])->first();
            if ($result)
                $item['discounted_price'] = $this->calculateBookDiscount($offer, $item);
            else
                $item['discounted_price'] = null;
            return $item;
        });
    }

    private function calculateBookDiscount($offer, $book)
    {
        return $book->price - (($book->price * $offer->book_discount_rate) / 100);
    }

    private function getDeliveryPrice(BookRequest $request)
    {
        $library = $request->library()->first();
        if (!$library)
            $delivery_price = delivery_price(FALSE);
        else
            $delivery_price = delivery_price($library->quarter_id == $request->quarter_id);
        $delivery_price = $this->getPromoCodeDiscount($request, $delivery_price);
        return $delivery_price;
    }


    private function checkBUY_OFFER($offer, $books)
    {
        $totalBooksPrice = $books->sum('price');
        if ($offer->buy_offer_type == ALL_LIBRARY_BOOKS)
            if ($totalBooksPrice >= $offer->more_than) {
                $this->totalPrice = $this->calculateTotalPriceDiscount($offer, $totalBooksPrice);
            }
    }

    private function calculateTotalPriceDiscount($offer, $total_book_price)
    {
        return $total_book_price - (($total_book_price * $offer->buy_discount_rate) / 100);
    }

    private function drawReturnedResult($books)
    {
        $books = Book::hydrate($books->toArray());
        $cartLibraryIds = Cart::where('client_id', '=', Auth::user()->id)->distinct()->pluck('library_id')->toArray();
        return Library::whereIn('id', $cartLibraryIds)->get()->map(function ($item) use ($books) {
            $library_books = collect();
            $books->map(function ($book) use ($item, $library_books) {
                if ($book->library_id == $item->id) {
                    $library_books->push($book);
                }
                return $book;
            });
            $item['books'] = $library_books;
            return $item;
        });
    }

    /**
     * private function checkOffers()
     * {
     * //        $offeredBooks = BookOffer::getOfferedBooks();
     * //        return $offeredBooks;
     * //        dd($offeredBooks);
     * $availableOffers = Offer::whereDate('start_date', '<=', Carbon::now())->whereDate('expire_date', '>=', Carbon::now())->first();
     * if ($availableOffers->type == BUY_OFFER) {
     *
     * } else {
     * if ($availableOffers->book_offer_type == PRICE_DISCOUNT) {
     * $resultOfDiscount = $this->getOfferDiscount($availableOffers->id);
     * dd($resultOfDiscount);
     * $totalBookPrice = $this->coreOfOfferSearch($availableOffers->id)->sum('books.price');
     * if ($resultOfDiscount->count() == $availableOffers->from_book && $totalBookPrice > $availableOffers->book_more_than) {
     * dd('GOT It');
     *
     * } else {
     * dd('ERROR');
     *
     * }
     * }
     *
     * //            dd($result);
     * }
     * $offeredOnLibrary = $availableOffers->library()->first();
     * //        if ($availableOffers == BOOK_OFFER) {
     * //
     * //        } else {
     * //
     * //        }
     * //
     * //        if ($offeredOnLibrary && ($offeredOnLibrary->id == $request->library_id)) {
     * //            if ($offeredOnLibrary->all_book == 1) {
     * ////                return $availableOffers->
     * //            }
     * //        }
     * //
     * //        if ($availableOffers->type == FREE_DELIVERING) {
     * //            $offeredBook = $availableOffers->offeredBooks()->get();
     * //        }
     * //        $library = $request->library()->first();
     * //
     * //        dd($availableOffers);
     * }
     *
     * private function freeDelivering(Book $book)
     * {
     * $offer_book = BookOffer::join('books', 'books.id', '=', 'book_offers.book_id')
     * ->join('offers', 'offers.id', '=', 'book_offers.offer_id')
     * ->where(['books.id' => $book->id])
     * ->orderBy('book_offers.created_at', 'desc')
     * ->select(['books.*', 'offers.*'])
     * ->first();
     * $offer = Offer::hydrate($offer_book);
     * $book = Book::hydrate($offer_book);
     * }
     *
     * private function getDeliveryPrice(BookRequest $request)
     * {
     * $library = $request->library()->first();
     * if (!$library)
     * $delivery_price = delivery_price(FALSE);
     * else
     * $delivery_price = delivery_price($library->quarter_id == $request->quarter_id);
     * $delivery_price = $this->getPromoCodeDiscount($request, $delivery_price);
     * return $delivery_price;
     *
     *
     * }
     *
     * /**
     *
     * get the discount of promo code
     *
     *
     * @param BookRequest $request
     * @param $delivery_price
     * @return float|int
     */
    private function getPromoCodeDiscount(BookRequest $request, $delivery_price)
    {
        if ($promo_code = $request->promo_code()) {
            $delivery_price = $delivery_price - ($delivery_price * $promo_code->discount_rate) / 100;
        }
        return $delivery_price;
    }

    /**
     * private function saleFromSingleLibrary()
     * {
     *
     * }
     *
     * private function getOfferDiscount($offer_id)
     * {
     * $result = $this->coreOfOfferSearch($offer_id);
     * return $result->get();
     * }
     *
     * private function coreOfOfferSearch($offer_id)
     * {
     * return DB::table('offers')
     * ->join('book_offers', 'book_offers.offer_id', '=', 'offers.id')
     * ->join('books', 'books.id', '=', 'book_offers.book_id')
     * ->join('cart', 'cart.book_id', '=', 'book_offers.book_id')
     * ->join('libraries', 'libraries.id', '=', 'books.library_id')
     * ->where('cart.client_id', '=', Auth::user()->id)
     * ->where('offers.id', '=', $offer_id)
     * //            ->havingRaw('offers.id > 0')
     * ->select('books.*' ,DB::raw('IF(offers.book_offer_type = '.PRICE_DISCOUNT.' , 0 , 100) as delivery_price'))
     * ->distinct();
     *
     * //        IF(offers.book_offer_type = '.PRICE_DISCOUNT.', ?, 100 ) as price', ["1"]
     * }
     *
     * private function getCartOfferLibrary($offer_id)
     * {
     * return DB::table('offers')
     * ->join('book_offers', 'book_offers.offer_id', '=', 'offers.id')
     * ->join('books', 'books.id', '=', 'book_offers.book_id')
     * ->join('cart', 'cart.book_id', '=', 'book_offers.book_id')
     * ->join('libraries', 'libraries.id', '=', 'books.library_id')
     * ->where('cart.client_id', '=', Auth::user()->id)
     * ->where('offers.id', '=', $offer_id)
     * ->select(['libraries.*'])
     * ->distinct();
     * }
     *
     * private function drawRequiredShap()
     * {
     *
     * }
     * **/

}
