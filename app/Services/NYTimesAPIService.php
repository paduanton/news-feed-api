<?php

use GuzzleHttp\Client as GuzzleClient;
use App\Services\Interfaces\NYTimesAPIInterface;

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
        $filterQuerySources = 'news_desk:(' . $parsedCategories . ')';
        $filterQueryCategories = 'source:(' . $parsedSources . ')';
        $generalFilterQuery = '';

        if (!empty($categories) && !empty($sources)) {
            $generalFilterQuery = $filterQueryCategories . " AND " . $filterQuerySources;
        } elseif (!empty($categories)) {
            $generalFilterQuery = $filterQueryCategories;
        } elseif (!empty($sources)) {
            $generalFilterQuery = $filterQueryCategories;
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

    private function parseArticlesResponseBody($responseBody)
    {
        return json_decode($responseBody, true);
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
