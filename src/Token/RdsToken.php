<?php
declare(strict_types = 1);

namespace Promenade\Doctrine\Aws\Auth\Token;

use Aws\Credentials\CredentialProvider;
use Aws\Rds\AuthTokenGenerator;

/**
 * Temporary access token for RDS database
 */
class RdsToken implements TokenProvider
{
    private AuthTokenGenerator $generator;
    
    private int $lifetime;

    /**
     * @param AuthTokenGenerator|null $generator
     * @param int $lifetime Token TTL in minutes
     */
    public function __construct(
        AuthTokenGenerator $generator = null,
        int $lifetime = 15
    ) {
        $this->generator = $generator ?: new AuthTokenGenerator(CredentialProvider::defaultProvider());
        $this->lifetime = $lifetime;
    }

    public function getToken(string $endpoint, string $region, string $username): string
    {
        return $this->generator->createToken($endpoint, $region, $username, $this->lifetime);
    }
}