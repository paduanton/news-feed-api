<?php

namespace App\Services;

use GuzzleHttp\Client as GuzzleClient;
use App\Services\Interfaces\NYTimesAPIInterface;

// TODO
/*
    - Create a Business layer to put all logic and businesses rules inside of it, this way we can implement a better the SOLID principles
      and remove business logic from Services and Controllers

    The flow should be: Controller -> Business -> Model/Services
*/

class NYTimesAPIService implements NYTimesAPIInterface
{
    private $newYorkTimesAPIKey;
    private $newYorkTimesAPIBaseURL;
    private $guzzleClient;

    public function __construct()
    {
        $this->guzzleClient = new GuzzleClient();
        $this->newYorkTimesAPIKey = config('app.ny_times_api_key');
        $this->newYorkTimesAPIBaseURL = config('app.ny_times_api_base_url');
    }

    public function getArticles(
        array $categories,
        array $sources,
        string $keyword,
        string $dateSort = 'newest'
    ) {
        $parsedCategories = $this->parseQueryStringNYTimesAPIPattern(
            $categories
        );
        $parsedSources = $this->parseQueryStringNYTimesAPIPattern($sources);
        $filterQueryCategories = 'news_desk:(' . $parsedCategories . ')';
        $filterQuerySources = 'source:(' . $parsedSources . ')';
        $generalFilterQuery = '';

        if (!empty($categories) && !empty($sources)) {
            $generalFilterQuery = $filterQueryCategories . " AND " . $filterQuerySources;
        } elseif (!empty($categories)) {
            $generalFilterQuery = $filterQueryCategories;
        } elseif (!empty($sources)) {
            $generalFilterQuery = $filterQuerySources;
        }

        $httpResponse = $this->guzzleClient->request(
            'GET',
            $this->newYorkTimesAPIBaseURL . "/articlesearch.json",
            [
                'query' => [
                    'api-key' => $this->newYorkTimesAPIKey,
                    'fq' => $generalFilterQuery,
                    'q' => $keyword,
                    'sort' => $dateSort,
                ],
            ]
        );

        return $this->parseArticlesResponseBody($httpResponse->getBody());
    }

    private function formatArticleContent($articleContent) {
        $articleHasImage = count($articleContent["multimedia"]) > 0;

        if($articleHasImage) {
            $articleMainImage = "https://www.nytimes.com/" . $articleContent["multimedia"][0]["url"];
        } else {
            $articleMainImage = null;

        }

        return array
        (
            'title' => $articleContent["headline"]["main"],
            'section' => $articleContent["section_name"],
            'image_url' => $articleMainImage,
            'category' => $articleContent["news_desk"],
            'author' => $articleContent["byline"]["original"],
            'source' => $articleContent["source"],
            'source_url' => $articleContent["web_url"],
            'published_at' => $articleContent["pub_date"],
        );
    }

    private function parseArticlesResponseBody($responseBody)
    {
        $body = json_decode($responseBody, true);
        $articles = $body["response"]["docs"];

        $formattedArticles = array_map(array($this, 'formatArticleContent'), $articles);

        return $formattedArticles;
    }

    private function parseQueryStringNYTimesAPIPattern(array $content): string
    {
        $formattedContent = '';

        if (!empty($content)) {
            $formattedContent = `"` . implode('", "', $content) . `"`;
        }

        return $formattedContent;
    }
}
