<?php

use Illuminate\Support\Str;
use GuzzleHttp\Client as GuzzleClient;
use App\Services\Interfaces\TheGuardianAPIInterface;

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
            $articles = array_merge($articles, $articlesResponseBody["response"]["results"]);
        }

        return $articles;
    }


    private function parseArticlesResponseBody($responseBody)
    {
        return json_decode($responseBody, true);
    }
}
