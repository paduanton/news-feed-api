<?php

namespace App\Services;

use GuzzleHttp\Client as GuzzleClient;
use App\Services\Interfaces\TheGuardianAPIInterface;

// TODO
/*
    - Create a Business layer to put all logic and businesses rules inside of it, this way we can implement a better approach of SOLID principles
      and remove business logic from Services and Controllers

    The flow should be: Controller -> Business -> Model/Services
*/

class TheGuardianAPIService implements TheGuardianAPIInterface
{
    private $theGuardianAPIKey;
    private $theGuardianAPIBaseURL;
    private $guzzleClient;

    public function __construct()
    {
        $this->guzzleClient = new GuzzleClient();
        $this->theGuardianAPIKey = config('app.the_guardian_api_key');
        $this->theGuardianAPIBaseURL = config('app.the_guardian_api_base_url');
    }

    public function getArticles(
        array $categories,
        string $keyword,
        string $dateSort = 'newest'
    ) {
        $articles = array();

        foreach ($categories as $category) {
            $httpResponse = $this->guzzleClient->request(
                'GET',
                $this->theGuardianAPIBaseURL . "/search",
                [
                    'query' => [
                        'api-key' => $this->theGuardianAPIKey,
                        'q' => $keyword,
                        'tag' => $category . "/" . $category,
                        'order-by' => $dateSort,
                        'page-size' => 20,
                    ],
                ]
            );

            $articlesResponseBody = $this->parseArticlesResponseBody($httpResponse->getBody());
            $articles = array_merge($articles, $articlesResponseBody);
        }

        return $articles;
    }


    private function formatArticleContent($articleContent) {
        return array
        (
            'title' => $articleContent["webTitle"],
            'section' => $articleContent["pillarName"],
            'image_url' => null,
            'category' => $articleContent["sectionName"],
            'author' => null,
            'source' => "The Guardian",
            'source_url' => $articleContent["webUrl"],
            'published_at' => $articleContent["webPublicationDate"],
        );
    }

    private function parseArticlesResponseBody($responseBody)
    {
        $body = json_decode($responseBody, true);
        $articles = $body["response"]["results"];

        $formattedArticles = array_map(array($this, 'formatArticleContent'), $articles);

        return $formattedArticles;
    }
}
