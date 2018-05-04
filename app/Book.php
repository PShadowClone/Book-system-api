<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    public $table = 'books';
    public $primaryKey = 'id';
    public $fillable = ['id', 'name', 'arrange', 'image', 'amount', 'writer', 'publisher', 'publish_date', 'inquisitor', 'description', 'price', 'category_id', 'library_id'];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function evaluations()
    {
        return $this->hasMany(UserEvaluations::class, 'book_id', 'id');
    }

    public function library()
    {
        return $this->belongsTo(Library::class, 'library_id', 'id');
    }

    public function getImage()
    {
        return env('ASSETS_URL') . $this->image;

    }

    public function book_evaluations()
    {
        return $this->hasMany(BookEvaluations::class, 'book_id', 'id');

    }


    /**
     * get list of books according to category's info
     *
     *
     * @param null $categoryInfo
     * @return $this
     */
    public static function searchByCategory($categoryInfo = null)
    {
        return Book::join('categories', 'categories.id', '=', 'books.category_id')
            ->where('categories.name', 'like', '%' . $categoryInfo . '%')
            ->select(['books.*']);
    }

    /**
     * get list of books according to library's info
     *
     *
     * @param null $library_info
     * @return $this
     */
    public static function searchByLibrary($library_info = null)
    {
        return Book::join('libraries', 'libraries.id', '=', 'books.library_id')
            ->where('libraries.name', 'like', '%' . $library_info . '%')
            ->select(['books.*']);
    }

    /**
     * get list of books according to publisher's info
     *
     *
     * @param null $publisherValue
     * @return $this
     */
    public static function searchByPublisher($publisherValue = null)
    {
        return Book::where('publisher', 'like', '%' . $publisherValue . '%');
    }

    /**
     * get list of books according to writer's info
     *
     *
     * @param null $writer
     * @return $this
     */
    public static function searchByWriter($writer = null)
    {
        return Book::where('writer', 'like', '%' . $writer . '%');
    }

    /**
     * get list of books according to inquisitor's info
     *
     *
     * @param null $inquisitor
     * @return $this
     */
    public static function searchByInquisitor($inquisitor = null)
    {
        return Book::where('inquisitor', 'like', '%' . $inquisitor . '%');
    }

    /**
     * get list of books according to library's city
     *
     *
     * @param null $city
     * @return $this
     */
    public static function searchByCity($city = null)
    {
        return Book::join('libraries', 'libraries.id', '=', 'books.library_id')
            ->join('quarters', 'quarters.id', '=', 'libraries.quarter_id')
            ->join('cities', 'cities.id', '=', 'quarters.cityId')
            ->where('cities.name', 'like', '%' . $city . '%')
            ->select(['books.*']);
    }
}
