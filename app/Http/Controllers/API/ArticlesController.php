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

        $aggregatedArticles = $this->articlesBusinessHandler->getAggregatedArticles(
            $categories,
            $sources,
            $authors,
            $keyword,
            $dateSort
        );

        return response()->json([
            "total_results" => count($aggregatedArticles),
            "articles" => $aggregatedArticles,
        ],
            200
        );
    }
}
