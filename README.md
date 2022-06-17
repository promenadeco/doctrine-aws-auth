Doctrine AWS Authentication
===========================

This library provides Amazon RDS database authentication using IAM for [Doctrine DBAL](https://github.com/doctrine/dbal) / ORM.

**Features:**
- RDS auth via IAM using short-lived tokens 
- Token caching (for 10 min by default)
- Support of EC2 and ECS environments
- Support of PDO and MySQLi [drivers](https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#driver)

## Usage

Install the package using Composer:
```bash
composer require promenadeco/doctrine-aws-auth
```

Enable IAM authentication in cleartext using the following environment variables:
```ini
AWS_REGION=us-east-1
RDS_USE_IAM=1
LIBMYSQL_ENABLE_CLEARTEXT_PLUGIN=1
```

### Doctrine ORM

Register the DBAL driver middleware in Doctrine ORM:

```php
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Promenade\Doctrine\Aws\Auth\Driver\IamMiddleware;
use Promenade\Doctrine\Aws\Auth\Token\RdsToken;

// ...

$ormConfig = ORMSetup::createAnnotationMetadataConfiguration([
    'src/Entity',
]);

$tokenProvider = new RdsToken();
$ormConfig->setMiddlewares([
    new IamMiddleware($tokenProvider),
]);

$entityManager = EntityManager::create(
    [
        'host' => 'example-db.abcdefghijkl.us-east-1.rds.amazonaws.com',
        'port' => 3306,
        'user' => 'iam_user',
        'dbname' => 'test_db',
        'driver' => 'pdo_mysql',
        'driverOptions' => [
            PDO::MYSQL_ATTR_SSL_CA => '/etc/ssl/certs/ca-certificates.crt',
            PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
        ],
    ],
    $ormConfig
);
```

#### Encryption

Connection encryption is necessary to secure transmission of credentials as cleartext.

The SSL configuration differs between drivers, for example:
```php
[
    // ...
    'driver' => 'mysqli',
    'driverOptions' => [
        'flags' => MYSQLI_CLIENT_SSL,
    ],
]
```

#### Caching

Activate token caching to stay within rate limits and improve performance:
```php
use Promenade\Doctrine\Aws\Auth\Token\CachingProxy;

// ...

$tokenProvider = new CachingProxy($tokenProvider, $ormConfig->getMetadataCache());
```

By default, tokens are good for 15 min and are cached for 10 min to be renewed well ahead of their expiration.

### Symfony

Register the DBAL driver middleware in `config/services.yaml`:

```yaml
services:
    Promenade\Doctrine\Aws\Auth\Token\TokenProvider:
        class: Promenade\Doctrine\Aws\Auth\Token\RdsToken

    Promenade\Doctrine\Aws\Auth\Driver\IamMiddleware:
        tags: ['doctrine.middleware']
```

#### Caching

Activate the token caching and adjust its lifetime as needed:

```yaml
services:
    Promenade\Doctrine\Aws\Auth\Driver\IamMiddleware:
        arguments:
            $tokenProvider: '@Promenade\Doctrine\Aws\Auth\Token\CachingProxy'

    Promenade\Doctrine\Aws\Auth\Token\RdsToken:
        arguments:
            $lifetime: 15

    Promenade\Doctrine\Aws\Auth\Token\CachingProxy:
        arguments:
            $lifetime: 14
```

Make sure tokens are valid some time beyond their cache expiration to compensate for potential clock drift.

## Limitations

IAM authentication relies on database client sending credentials in cleartext without hashing.

The implementation has only been tested on MySQL. Other RDBMS may have their own unique limitations.

## Resources

Related resources with useful information:
- [sators/connect.php](https://gist.github.com/sators/38dbe25f655f1c783cb2c49e9873d58a)
- [lead/doctrine-rds-iam-auth](https://github.com/Ulv/doctrine-aws-iam-rds-auth)
- [AWS Documentation](https://docs.aws.amazon.com/AmazonRDS/latest/UserGuide/UsingWithRDS.IAMDBAuth.html)
- [AWS Knowledge Center](https://aws.amazon.com/premiumsupport/knowledge-center/users-connect-rds-iam/)
- [AWS User Guide](https://docs.amazonaws.cn/en_us/AmazonRDS/latest/UserGuide/UsingWithRDS.IAMDBAuth.Connecting.AWSCLI.html)
- [MySQL Documentation](https://dev.mysql.com/doc/mysql-security-excerpt/5.7/en/cleartext-pluggable-authentication.html)

## Contributing

Pull Requests with fixes and improvements are welcome!

## License

Copyright © Promenade Group. All rights reserved.

Licensed under the [Apache License, Version 2.0](https://github.com/promenadeco/doctrine-aws-auth/blob/main/LICENSE.txt).