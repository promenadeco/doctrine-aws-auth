<?php
declare(strict_types = 1);

namespace Promenade\Doctrine\Aws\Auth\Driver;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Middleware;
use Promenade\Doctrine\Aws\Auth\Token\TokenProvider;

/**
 * Middleware wrapping DBAL driver into IAM authentication decorator
 */
class IamMiddleware implements Middleware
{
    private TokenProvider $tokenProvider;

    public function __construct(TokenProvider $tokenProvider)
    {
        $this->tokenProvider = $tokenProvider;
    }
    
    public function wrap(Driver $driver): Driver
    {
        if (getenv('RDS_USE_IAM')) {
            $region = $this->getRegion('us-east-1');
            $driver = new IamDecorator($driver, $this->tokenProvider, $region);
        }
        return $driver;
    }
    
    protected function getRegion(string $default): string
    {
        return getenv('AWS_REGION')
            ?: getenv('AWS_DEFAULT_REGION')
            ?: $default;
    }
}