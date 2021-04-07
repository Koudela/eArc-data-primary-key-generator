# eArc-data-primary-key-generator

Primary key generator for the [earc/data](https://github.com/Koudela/eArc-data)
abstraction.

## table of contents

- [installation](#installation)
- [basic usage](#basic-usage)
    - [bootstrapping the primary key generator](#bootstrapping-the-primary-key-generator)
        - [using a redis server](#using-a-redis-server)
        - [using the filesystem](#using-the-filesystem)
    - [determine the key generation strategy](#determine-the-key-generation-strategy)
- [advanced usage](#advanced-usage)
    - [naming of the redis hash key](#naming-of-the-redis-hash-key)
    - [naming of the filesystem directory](#naming-of-the-filesystem-directory)
- [releases](#releases)
    - [release 0.0](#release-00)
   
## installation

Install the earc/data-primary-key-generator library via composer.

```bash
$ composer require earc/data-primary-key-generator
```

## basic usage

### bootstrapping the primary key generator

Initialize the earc/data abstraction in your index.php, bootstrap or configuration
script.

```php
use eArc\Data\Initializer;

Initializer::init();
```

Then register the earc/data-primary-key-generator to the earc/data `onAutoPrimaryKey`
event.

```php
use eArc\Data\ParameterInterface;
use eArc\DataPrimaryKeyGenerator\PrimaryKeyGenerator;

di_tag(ParameterInterface::TAG_ON_AUTO_PRIMARY_KEY, PrimaryKeyGenerator::class);
```

Now earc/data is ready to use earc/data-primary-key-generator to generate UUIDs
as primary keys for your entities.

If you want to generate incremental primary keys, you have to decide where to cache
the maximal keys of the entity classes. You can choose between the filesystem and 
a redis server.

#### using a redis server

To use the redis server, set the infrastructure parameter to `USE_REDIS`.

```php
use eArc\DataPrimaryKeyGenerator\ParameterInterface;
use eArc\DataPrimaryKeyGenerator\PrimaryKeyGenerator;

di_set_param(ParameterInterface::INFRASTRUCTURE, PrimaryKeyGenerator::USE_REDIS);
```

By default, earc/data-primary-key-generator uses `localhost` and the defaults
of the php-redis-extension. You can overwrite these defaults:

```php
use eArc\DataPrimaryKeyGenerator\ParameterInterface;

di_set_param(ParameterInterface::REDIS_CONNECTION, ['127.0.0.1', 6379]);
```

This array is handed to the `Redis::connect()` method as arguments. Consult the
[phpredis documentation](https://github.com/phpredis/phpredis/#connect-open) for
valid values and configuration options.

Now earc/data is ready to use the earc/data-primary-key-generator to generate incremental
primary keys for your entities.

#### using the filesystem

To use the filesystem, set the infrastructure parameter to `USE_FILESYSTEM`.

```php
use eArc\DataPrimaryKeyGenerator\ParameterInterface;
use eArc\DataPrimaryKeyGenerator\PrimaryKeyGenerator;

di_set_param(ParameterInterface::INFRASTRUCTURE, PrimaryKeyGenerator::USE_FILESYSTEM);
```

Then configure the data filesystem path for the
[earc/data-filesystem](https://github.com/Koudela/eArc-data-filesystem) bridge.

```php
use eArc\DataFilesystem\ParameterInterface;

di_set_param(ParameterInterface::DATA_PATH, '/path/to/save/the/entity/data');
```

Now earc/data is ready to use the earc/data-primary-key-generator to generate 
incremental primary keys for your entities.

### determine the key generation strategy

There are two supported primary key generation strategies.
1. using [UUIDs](https://de.wikipedia.org/wiki/Universally_Unique_Identifier)
2. incrementing a positive integer for each entity class

Each has its own advantages and downsides:
1. The UUIDs are globally unique.
2. The incremented integer keys require less space and give the entities a natural
   order, but this strategy requires an infrastructure to cache the maximal 
   primary key for the classes.
   
The key generation strategy can be determined individually by implementing the
`AutoincrementPrimaryKeyInterface` or the `AutoUUIDPrimaryKeyInterface` in the
entity class.

```php
use eArc\Data\Entity\AbstractEntity;
use eArc\DataPrimaryKeyGenerator\AutoincrementPrimaryKeyInterface;
use eArc\DataPrimaryKeyGenerator\AutoUUIDPrimaryKeyInterface;

class MyEntityUUID extends AbstractEntity implements AutoUUIDPrimaryKeyInterface
{
    public function setPrimaryKey(?string $primaryKey): void
    {
        $this->primaryKey = $primaryKey;
    }
}

class MyEntityAutoincrementPK extends AbstractEntity implements AutoincrementPrimaryKeyInterface
{
    public function setPrimaryKey(?string $primaryKey): void
    {
        $this->primaryKey = $primaryKey;
    }
}
```

Or it can be determined globally by setting the `DEFAULT_INTERFACE` parameter:

```php
use eArc\DataPrimaryKeyGenerator\AutoincrementPrimaryKeyInterface;
use eArc\DataPrimaryKeyGenerator\ParameterInterface;

di_set_param(ParameterInterface::DEFAULT_INTERFACE, AutoincrementPrimaryKeyInterface::class);
```

This provides a fallback if no interface is present. Of course the `AutoPrimaryKeyInterface`
of the earc/data library has to be implemented to trigger the `onAutoPrimaryKey`
event.

```php
use eArc\Data\Entity\AbstractEntity;
use eArc\Data\Entity\Interfaces\PrimaryKey\AutoPrimaryKeyInterface;

class MyEntityAutoincrementPK extends AbstractEntity implements AutoPrimaryKeyInterface
{
    public function setPrimaryKey(?string $primaryKey): void
    {
        $this->primaryKey = $primaryKey;
    }
}
```

## advanced usage

### naming of the redis hash key

If you use the increment strategy together with the redis server, 
earc/data-primary-key-generator uses [redis hashes](https://redis.io/commands#hash) 
to cache the maximal keys of the entity classes. By default, the hash-key is 
named `earc-data-pk-gen`. If you need another name to manage the redis namespace, 
you can overwrite the default:

```php
use eArc\DataPrimaryKeyGenerator\ParameterInterface;

di_set_param(ParameterInterface::HASH_KEY_NAME, 'my-hash-key-name');
```

### naming of the filesystem directory

If you use the increment strategy together with the filesystem,
earc/data-primary-key-generator uses the `@earc-data-pk-gen` postfix to extend
the filesystem entity path of earc/data-filesystem to cache the maximal primary
key of the entity class. You can change this by setting the `DIR_NAME_POSTFIX` 
parameter.

```php
use eArc\DataPrimaryKeyGenerator\ParameterInterface;

di_set_param(ParameterInterface::DIR_NAME_POSTFIX, '@my-dir-name-postfix');
```

## releases

### release 0.0

* the first official release
* PHP ^8.0
