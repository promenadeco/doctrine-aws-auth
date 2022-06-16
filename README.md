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
       'driver' => 'mysqli',
       'driverOptions' => [
            'flags' => MYSQLI_CLIENT_SSL,
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
LIBMYSQL_ENABLE_CLEARTEXT_PLUGIN=1
```

## Limitations

For MySQL, IAM authentication appears to be only possible with `mysqli` [driver](https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/configuration.html#driver).
More common `pdo_mysql` driver cannot be used because of an unfortunate bug [#78467](https://bugs.php.net/bug.php?id=78467).

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