<?php

namespace App\BusinessHandler;

use App\Services\NYTimesAPIService;
use App\Services\TheGuardianAPIService;
use App\Services\NewsAPIOrgService;

class ArticlesBusinessHandler
{

    protected $newYorkTimesAPIService, $newsAPIOrgService, $theGuardianAPIService;

    public function __construct(
        NYTimesAPIService $newYorkTimesAPIService,
        NewsAPIOrgService $newsAPIOrgService,
        TheGuardianAPIService $theGuardianAPIService,

    ) {
        $this->newYorkTimesAPIService = $newYorkTimesAPIService;
        $this->theGuardianAPIService = $theGuardianAPIService;
        $this->newsAPIOrgService = $newsAPIOrgService;
    }

    public function getAggregatedArticles(
        $categories,
        $sources,
        $authors,
        $keyword,
        $dateSort
    )
    {
        $sources = $sources ? explode(",", $sources) : [];
        $categories = $categories ? explode(",", $categories) : [];
        $authors = $authors ? explode(",", $authors) : [];

        $newYorkTimesArticles = $this->newYorkTimesAPIService->getArticles(
            $categories,
            $sources,
            $keyword,
            $dateSort
        );
        $theGuardianArticles = $this->theGuardianAPIService->getArticles(
            $categories,
            $keyword,
            $dateSort
        );
        $newsAPIOrgArticles = $this->newsAPIOrgService->getArticles(
            $categories,
            $sources,
            $authors,
            $keyword,
            $dateSort
        );

        $agregatedArticles = array_merge($newYorkTimesArticles, $theGuardianArticles, $newsAPIOrgArticles);

       return $agregatedArticles;
    }
}
