# Nettrine ODM

[Doctrine/ODM](https://www.doctrine-project.org/projects/mongodb-odm) to Nette Framework.


## Content
- [Setup](#setup)
- [Relying](#relying)
- [Configuration](#configuration)
- [Mapping](#mapping)
  - [Annotations](#annotations)
  - [XML](#xml)
  - [Helpers](#helpers)
- [Examples](#examples)


## Setup

Install package

```bash
composer require nettrine/odm
```

Register extension

```yaml
extensions:
  nettrine.odm: Nettrine\ODM\DI\OdmExtension
```


## Relying

Take advantage of enpowering this package with 3 extra packages:

- `mongodb/mongodb`
- `doctrine/cache`
- `symfony/console`


### `mongodb/mongodb`

This package relies on `mongodb/mongodb`, use prepared [nettrine/mongodb](https://github.com/nettrine/mongodb) integration.

```bash
composer require nettrine/mongodb
```

```yaml
extensions:
  nettrine.mongodb: Nettrine\MongoDB\DI\MongoDBExtension
```

[Doctrine ODM](https://www.doctrine-project.org/projects/mongodb-odm) needs [MongoDB PHP Library](https://github.com/mongodb/mongo-php-library) to be configured. If you register `nettrine/mongodb` extension it will detect it automatically.

> MongoDB PHP Library provides a high-level abstraction around the lower-level PHP driver (mongodb extension).


### `doctrine/cache`

This package relies on `doctrine/cache`, use prepared [nettrine/cache](https://github.com/nettrine/cache) integration.

```bash
composer require nettrine/cache
```

```yaml
extensions:
  nettrine.cache: Nettrine\Cache\DI\CacheExtension
```

[Doctrine ODM](https://www.doctrine-project.org/projects/mongodb-odm) needs [Doctrine Cache](https://www.doctrine-project.org/projects/cache.html) to be configured. If you register `nettrine/cache` extension it will detect it automatically.

`CacheExtension` sets up cache for all important parts: `metadataCache`.

This is the default configuration, it uses the autowired driver.

```yaml
extensions:
  nettrine.odm: Nettrine\ODM\DI\OdmExtension
  nettrine.odm.cache: Nettrine\ODM\DI\OdmCacheExtension
```

You can also specify a single driver or change the `nettrine.odm.cache.defaultDriver` for specific ones.

```yaml
nettrine.odm.cache:
  defaultDriver: App\DefaultOdmCacheDriver
  metadataCache: @cacheDriver
```

### `symfony/console`

This package relies on `symfony/console`, use prepared [contributte/console](https://github.com/contributte/console) integration.

```bash
composer require contributte/console
```

```yaml
extensions:
  contributte.console: Contributte\Console\DI\ConsoleExtension(%consoleMode%)

  nettrine.odm: Nettrine\ODM\DI\OdmExtension
  nettrine.odm.console: Nettrine\ODM\DI\OdmConsoleExtension(%consoleMode%)
```

Since this moment when you type `bin/console`, there'll be registered commands from Doctrine DBAL.

```sh
 odm
  odm:clear-cache:metadata    Clear all metadata cache of the various cache drivers.
  odm:generate:hydrators      Generates hydrator classes for document classes.
  odm:generate:proxies        Generates proxy classes for document classes.
  odm:query                   Query mongodb and inspect the outputted results from your document classes.
  odm:schema:create           Create databases, collections and indexes for your documents
  odm:schema:drop             Drop databases, collections and indexes for your documents
  odm:schema:update           Update indexes for your documents
```

## Configuration

**Schema definition**

 ```yaml
nettrine.odm:
  configurationClass: <class>
  configuration:
    autoGenerateProxyClasses: <boolean>
    defaultDB: <string>
    proxyDir: <path>
    proxyNamespace: <string>
    hydratorDir: <path>
    hydratorNamespace: <string>
    metadataDriverImpl: <service>
    classMetadataFactoryName: <string>
    repositoryFactory: <class>

  types: <class[]>
```

**Under the hood**

Minimal configuration could look like this:

```yaml
nettrine.odm:
  configuration:
    autoGenerateProxyClasses: %debugMode%
```

**Side notes**

1. The compiler extensions would be so big that we decided to split them into more separate files / compiler extensions.

2. At this time we support only 1 connection, the **default** connection. If you need more connections (more databases?), please open an issue or send a PR. Thanks.


## Mapping

Doctrine ODM needs to know where your entities are located and how they are described (mapping).

Additional metadata provider needs to be registered. We provide bridges for these drivers:

- **annotations** (`Nettrine\ODM\DI\OdmAnnotationsExtension`)
- **xml** (`Nettrine\ODM\DI\OdmXmlExtension`)


### Annotations

Are you using annotations in your entities?

```php
/**
 * @ODM\Document
 */
class Article
{
}
```

This feature relies on `doctrine/annotations`, use prepared [nettrine/annotations](https://github.com/nettrine/annotations) integration.

```bash
composer require nettrine/annotations
```

```yaml
extensions:
  nettrine.annotations: Nettrine\Annotations\DI\AnnotationsExtension
```

You will also appreciate ODM => Annotations bridge, use `OdmAnnotationsExtension`. This is the default configuration, it uses an autowired cache driver.

```yaml
extensions:
  nettrine.odm: Nettrine\ODM\DI\OdmExtension
  nettrine.odm.annotations: Nettrine\ODM\DI\OdmAnnotationsExtension

nettrine.odm.annotations:
  paths: []
  excludePaths: []
```

### XML

Are you using XML mapping for your entities?

You will also appreciate ODM => XML bridge, use `OdmXmlExtension`. This is the default configuration:

```yaml
extensions:
  nettrine.odm: Nettrine\ODM\DI\OdmExtension
  nettrine.odm.xml: Nettrine\ODM\DI\OdmXmlExtension

nettrine.odm.xml:
  paths: []
  fileExtension: .dcm.xml
```

## Other

This repository is inspired by these packages.

- https://gitlab.com/nettrine/orm

Thank you guys.


## Examples

You can find more examples in [planette playground](https://github.com/planette/playground) repository.
