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

    public function getArticles(
        string $categories,
        string $sources,
        string $authors,
        string $keyword,
        string $dateSort
    )
    {
        $sources = explode(",", $sources);
        $categories = explode(",", $categories);
        $authors = explode(",", $authors);

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
        $newsAPIOrgArticles= $this->newsAPIOrgService->getArticles(
            $categories,
            $sources,
            $authors,
            $keyword,
            $dateSort
        );
        
        $allArticles = array_merge($newYorkTimesArticles, $theGuardianArticles, $newsAPIOrgArticles);

       return $allArticles;
    }
}
