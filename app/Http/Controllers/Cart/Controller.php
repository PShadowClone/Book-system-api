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

    /**
     *
     * *********************************************
     *             Show all request
     * *********************************************
     *
     * this function is considard as interface function that
     * handles the complicated offer operations
     *
     *
     * @return $this
     */
    public function show()
    {
        try {

            $offer = $this->getOffer();
            $books = $this->getBooks();
            if ($offer) {
                $result = $this->checkMajorOfferType($offer, $books);
                if ($offer->type == BOOK_OFFER)
                    $result = $this->drawReturnedResult($result);
            } else {
                $result = $result = $this->drawReturnedResult($books);
            }
            return success($result);
        } catch (\Exception $exception) {
            return error('lang.cart_show_error');
        }
    }

    /**
     *
     * get cart's libraries
     *
     *
     * @return array
     */
    private function getLibraries()
    {
        $resultAsArray = [];
        $result = DB::select('SELECT libraries.* FROM cart JOIN libraries ON cart.library_id = libraries.id WHERE client_id = ' . Auth::user()->id . ' GROUP BY cart.library_id');
        foreach ($result as $item) {
            $item = (array)$item;
            array_push($resultAsArray, $item);
        }
        return $resultAsArray;
    }

    /**
     * get all cart's books
     *
     * @return $this
     */
    private function getBooks()
    {
        return Cart::join('books', 'books.id', '=', 'cart.book_id')
            ->select(['books.*'])
            ->distinct();
    }

    /**
     *
     * *********************************************
     *             AVAILABLE OFFER
     * *********************************************
     *
     * this function returned the (last) available offers
     *
     *
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    private function getOffer()
    {
        return Offer::whereDate('start_date', '<=', Carbon::now())->whereDate('expire_date', '>=', Carbon::now())->orderBy('id', 'desc')->first();
    }

    /**
     * *********************************************
     *             MAJOR_CHECKER OFFER
     * *********************************************
     *
     *  this function checks the type off the available offer
     * @IF offer.type == BOOK_OFFER
     * @then
     *  call checkBOOK_OFFER()
     * @else
     *  call checkBUY_OFFER()
     *
     * @param Offer $offer
     * @param $books
     * @return Controller|\Illuminate\Support\Collection|mixed
     */
    private function checkMajorOfferType(Offer $offer, $books)
    {
        if ($offer->type == BOOK_OFFER) {
            return $this->checkBOOK_OFFER($offer, $books);
        }
        return $this->checkBUY_OFFER($offer, $books);
    }

    /**
     *
     * *********************************************
     *             AVAILABLE OFFER
     * *********************************************
     *
     *  this function handles all operations BOOK_OFFER's operations as (function interface)
     *
     * @param Offer $offer
     * @param $books
     * @return mixed
     */
    private function checkBOOK_OFFER(Offer $offer, $books)
    {
        if ($offer->book_offer_type == FREE_DELIVERING) {
            return $this->checkFREE_DELIVERING($offer, $books);
        }
        return $this->checkPRICE_DISCOUNT($offer, $books);

    }

    /**
     *
     * *********************************************
     *             FREE_DELIVERING OFFER
     * *********************************************
     *
     * @if offer.type == BOOK_OFFER @and offer.book_offer_type == FREE_DELIVERING
     * @then this function that adopts all required offer operations will be fired
     *
     * @param Offer $offer
     * @param $books
     * @return mixed
     */
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

    /**
     *
     * *********************************************
     *             DELIVERING_PRICE
     * *********************************************
     *
     * this function returns delivering price according to
     * the details of delivering, that user has submitted before
     *
     *
     * @param $book
     * @return mixed
     */
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


    /**
     *
     * *********************************************
     *              PRICE_DISCOUNT Operations
     * *********************************************
     * this function calculates the price discount that should be offered
     * when there is an PRICE_DISCOUNT offer
     *
     * @param $offer
     * @param $books
     * @return mixed
     */
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

    /**
     *
     * @if Offer.type == BOOK_DISCOUNT @and Offer.book_offer_type == FREE_DELIVERING
     * @then
     *  function will be fired
     *
     * @param $offer
     * @param $book
     * @return float|int
     */
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


    /**
     *
     * *********************************************
     *              BUY_OFFER Operations
     * *********************************************
     *  this function handles the operations that must be done when
     * offer.type == BUY_OFFER
     *
     * @if Offer.type == BUY_OFFER
     * @if offer.buy_offer_type == SINGLE_LIBRARY  @then
     *  the discount will be happened for single library but function makes usre that all required books
     *  have lied in the same book spot (same library)
     *
     * @else
     *  the discount will be happened for all required libraries
     *
     * @param Offer $offer
     * @param $books
     * @return Controller|\Illuminate\Support\Collection
     */
    private function checkBUY_OFFER(Offer $offer, $books)
    {
        $totalBooksPrice = $books->sum('price');
        $numberOfCartLibraries = Library::hydrate($this->getLibraries())->count();
        if ($offer->buy_offer_type == SINGLE_LIBRARY && $numberOfCartLibraries == 1 && $totalBooksPrice >= $offer->more_than) {
            $returnedResult = $this->drawReturnedResult($books->get());
            $firstObject = $returnedResult->first();
            if ($firstObject) {
                $firstObject['offered_price'] = $this->calculateTotalPriceDiscount($offer, $totalBooksPrice);
            }

            return $returnedResult;
        }
        $returnedResult = $this->drawReturnedResult($books->get());
        $returnedResult->map(function ($item) use ($offer, $totalBooksPrice) {
            if ($item) {
                $item['offered_price'] = $this->calculateTotalPriceDiscount($offer, $totalBooksPrice);
            }
        });

        return $returnedResult;
    }

    /**
     *
     * calculate the result of price after discounting
     *
     *
     * @param $offer
     * @param $total_book_price
     * @return float|int
     */
    private function calculateTotalPriceDiscount($offer, $total_book_price)
    {
        return $total_book_price - (($total_book_price * $offer->buy_discount_rate) / 100);
    }

    /**
     *
     * *********************************************
     *              Returned json
     * *********************************************
     *
     * this function shapes the returned json to make sure that is in appropriate shope
     *
     *
     * @param $books
     * @return \Illuminate\Support\Collection|static
     *
     */
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


}
