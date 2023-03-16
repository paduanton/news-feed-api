<?php

use Illuminate\Support\Str;
use GuzzleHttp\Client as GuzzleClient;
use App\Services\Interfaces\NewsAPIOrgInterface;

// TODO
/*
    - Create a Business layer to put all logic and businesses rules inside of it, this way we can implement a better the SOLID principles
      and remove business logic from Services and Controllers

    The flow should be: Controller -> Business -> Model/Services
*/

class NewsAPIOrgService implements NewsAPIOrgInterface
{
    private $newsOrgAPIKey;
    private $newsOrgAPIBaseURL;
    private $guzzleClient;

    public function __construct()
    {
        $this->guzzleClient = new GuzzleClient();
        $this->newsOrgAPIKey = config('app.news_api_org_api_key');
        $this->newsOrgAPIBaseURL = config('app.news_api_org_api_base_url');
    }

    public function getArticles(
        array $categories,
        array $sources,
        array $authors,
        string $keyword,
        string $dateSort = 'newest'
    ) {
        $articles = array();

        $sourcesList = $this->getSources($categories, $sources);
        $parsedSources = implode(",", $sourcesList);

        if($dateSort === "newest") {
            $sortBy = "publishedAt";
        } else {
            $sortBy = "popularity";
        }

        $httpResponse = $this->guzzleClient->request(
            'GET',
            $this->newsOrgAPIBaseURL . "/everything",
            [
                'query' => [
                    'apiKey' => $this->newsOrgAPIKey,
                    'q' => $keyword,
                    'sources' => $parsedSources,
                    'sortBy' => $sortBy,
                    'pageSize' => 20,
                ],
            ]
        );

        $articlesResponseBody = $this->parseArticlesResponseBody($httpResponse->getBody());

        foreach ($articlesResponseBody as $article) {
            $containsAuthorName = Str::contains($article["author"], $authors);

            if($containsAuthorName) {
                array_push($articles, $article);

            }
        }

        return $articles;
    }

    public function getSources(
        array $categories,
        array $sources,

    ) {
        $sourcesList = array();

        foreach ($categories as $category) {
            $httpResponse = $this->guzzleClient->request(
                'GET',
                $this->newsOrgAPIBaseURL . "/top-headlines/sources",
                [
                    'query' => [
                        'apiKey' => $this->newsOrgAPIKey,
                        'category' => $category,
                    ],
                ]
            );

            $sourcesResponseBody = $this->parseSourcesResponseBody($httpResponse->getBody());
            $sourcesResponseBody = $sourcesResponseBody["sources"];

            if(!empty($sources)) {
                foreach ($sourcesResponseBody as $source) {
                    $containsSourceName = Str::contains($source["name"], $sources);

                    if($containsSourceName) {
                        array_push($sourcesList, $source["id"]);

                    }
                }
            } else {
                foreach ($sourcesResponseBody as $source) {
                    array_push($sourcesList, $source["id"]);
                }
            }

        }

        return $sourcesList;
    }

    private function parseArticlesResponseBody($responseBody)
    {
        return json_decode($responseBody, true);
    }

    private function parseSourcesResponseBody($responseBody)
    {
        return json_decode($responseBody, true);
    }
}
