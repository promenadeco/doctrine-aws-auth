Doctrine AWS Authentication
===========================

This library provides Amazon RDS database authentication using IAM for [Doctrine DBAL](https://github.com/doctrine/dbal) / ORM.

**Features:**
- RDS auth via IAM using short-lived tokens 
- Token caching (for 10 min by default)
- Support of EC2 and ECS environments

## Usage

Install the package using Composer:
```bash
composer require promenadeco/doctrine-aws-auth
```

### Doctrine ORM

Register the DBAL driver middleware in Doctrine ORM:

```php
use Aws\Credentials\CredentialProvider;
use Aws\Rds\AuthTokenGenerator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Promenade\Doctrine\Aws\Auth\Driver\IamMiddleware;
use Promenade\Doctrine\Aws\Auth\Token\CachingProxy;
use Promenade\Doctrine\Aws\Auth\Token\RdsToken;

// ...

$ormConfig = ORMSetup::createAnnotationMetadataConfiguration([
    'src/Entity',
]);

$tokenGenerator = new AuthTokenGenerator(CredentialProvider::defaultProvider());
$tokenProvider = new RdsToken($tokenGenerator);
$tokenProvider = new CachingProxy($tokenProvider, $ormConfig->getMetadataCache());
$ormConfig->setMiddlewares([
    new IamMiddleware($tokenProvider),
]);

$entityManager = EntityManager::create(
    [
       'host' => 'example-db.abcdefghijkl.us-east-1.rds.amazonaws.com',
       'port' => 3306,
       'user' => 'iam_user',
       'dbname' => 'test_db',
       'driver' => 'mysqli',
       'driverOptions' => [
            'flags' => MYSQLI_CLIENT_SSL,
            MYSQLI_READ_DEFAULT_FILE => 'vendor/promenadeco/doctrine-aws-auth/my.cnf',
        ],
    ],
    $ormConfig
);
```

Activate token caching to stay within rate limits and improve performance:
```php
use Promenade\Doctrine\Aws\Auth\Token\CachingProxy;

// ...

$tokenProvider = new CachingProxy($tokenProvider, $ormConfig->getMetadataCache());
```

Enable IAM authentication using the following environment variables:
```ini
AWS_REGION=us-east-1
RDS_USE_IAM=1
```

## Contributing

Pull Requests with fixes and improvements are welcome!

## License

Copyright © Promenade Group. All rights reserved.

Licensed under the [Apache License, Version 2.0](https://github.com/promenadeco/doctrine-aws-auth/blob/main/LICENSE.txt).