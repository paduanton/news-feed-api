<?php

namespace App\Services\Interfaces;

interface NYTimesAPIInterface
{
    public function getArticles(
        array $categories,
        array $sources,
        string $keyword,
        string $dateSort = 'newest'
    );

}
