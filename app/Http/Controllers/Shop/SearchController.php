<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Genre;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Display the search page.
     */
    public function index(Request $request)
    {
        $genres = Genre::orderBy('name')->get();
        $authors = Author::orderBy('name')->get();

        return view('shop.search', compact('genres', 'authors'));
    }
}
