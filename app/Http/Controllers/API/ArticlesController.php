<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\BusinessHandler\ArticlesBusinessHandler;

class ArticlesController extends Controller
{

    protected $articlesBusinessHandler;

    public function __construct(
        ArticlesBusinessHandler $articlesBusinessHandler,

    ) {
        $this->articlesBusinessHandler = $articlesBusinessHandler;
    }

    public function search(Request $request, $keyword)
    {

        $authors = $request->query('authors');
        $sources = $request->query('sources');
        $categories = $request->query('categories');
        $dateSort = $request->query('dateSort');

        return response()->json(
            $this->articlesBusinessHandler->getAgregatedArticles(
                 $categories,
                 $sources,
                 $authors,
                 $keyword,
                 $dateSort
            ),
            200
        );

    }
}
