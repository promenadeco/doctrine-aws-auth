<?php
declare(strict_types = 1);

namespace Promenade\Doctrine\Aws\Auth\Token;

/**
 * Provider of temporary access token
 */
interface TokenProvider
{
    public function getToken(string $endpoint, string $region, string $username): string;
}