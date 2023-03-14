<?php

use GuzzleHttp\Client as GuzzleClient;
use App\Services\Interfaces\ArticleInterface;

class ArticleServiceAPI implements ArticleInterface
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
            $this->newYorkTimesAPIBaseURL,
            [
                'query' => [
                    'api-key' => $this->newYorkTimesAPIKey,
                    'fq' => $generalFilterQuery,
                    'q' => $keyword,
                    'sort' => $dateSort,
                ],
            ]
        );

        $response = $httpResponse->getBody();
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
