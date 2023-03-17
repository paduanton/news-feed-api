<?php

namespace App\Services\Interfaces;

interface NewsAPIOrgInterface
{

    public function getArticles(
        array $categories,
        array $sources,
        array $authors,
        string $keyword,
        string $dateSort = 'newest'
    );
    public function getSources(
        array $categories,
        array $sources,

    );
}
