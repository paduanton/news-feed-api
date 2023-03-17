<?php

namespace App\Services\Interfaces;

interface TheGuardianAPIInterface
{
    public function getArticles(
        array $categories,
        string $keyword,
        string $dateSort = 'newest'
    );
}
