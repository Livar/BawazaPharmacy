<?php

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function routeUrl(string $route, array $params = []): string
{
    $query = array_merge(['route' => $route], $params);
    return 'index.php?' . http_build_query($query);
}
