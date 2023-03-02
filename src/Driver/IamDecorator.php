<?php
declare(strict_types = 1);

namespace Promenade\Doctrine\Aws\Auth\Driver;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\API\ExceptionConverter;
use Doctrine\DBAL\Driver\Connection as DriverConnection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Promenade\Doctrine\Aws\Auth\Token\TokenProvider;

/**
 * Driver decorator passing a temporary access token in place of a password
 */
class IamDecorator implements Driver
{
    private Driver $subject;

    private TokenProvider $tokenProvider;

    private string $region;

    public function __construct(
        Driver $subject,
        TokenProvider $tokenProvider,
        string $region
    ) {
        $this->subject = $subject;
        $this->tokenProvider = $tokenProvider;
        $this->region = $region;
    }
    
    public function connect(array $params): DriverConnection
    {
        $host = $params['host'] ?? 'localhost';
        $port = $params['port'] ?? 3306;
        $user = $params['user'] ?? 'root';
        
        $params['password'] = $this->tokenProvider->getToken("$host:$port", $this->region, $user);
        
        return $this->subject->connect($params);
    }

    public function getDatabasePlatform(): AbstractPlatform
    {
        return $this->subject->getDatabasePlatform();
    }

    public function getSchemaManager(Connection $conn, AbstractPlatform $platform): AbstractSchemaManager
    {
        return $this->subject->getSchemaManager($conn, $platform);
    }

    public function getExceptionConverter(): ExceptionConverter
    {
        return $this->subject->getExceptionConverter();
    }
}