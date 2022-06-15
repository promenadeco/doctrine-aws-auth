<?php
declare(strict_types = 1);

namespace Promenade\Doctrine\Aws\Auth\Token;

use Aws\Rds\AuthTokenGenerator;

/**
 * Temporary access token for RDS database
 */
class RdsToken implements TokenProvider
{
    private AuthTokenGenerator $generator;
    
    private int $lifetime;

    /**
     * @param AuthTokenGenerator $generator
     * @param int $lifetime Token TTL in minutes
     */
    public function __construct(
        AuthTokenGenerator $generator,
        int $lifetime = 15
    ) {
        $this->generator = $generator;
        $this->lifetime = $lifetime;
    }

    public function getToken(string $endpoint, string $region, string $username): string
    {
        return $this->generator->createToken($endpoint, $region, $username, $this->lifetime);
    }
}