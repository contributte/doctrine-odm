![Nettrine ODM](https://heatbadger.now.sh/github/readme/contributte/doctrine-odm/)

<p align=center>
	<a href="https://github.com/contributte/doctrine-odm/actions"><img src="https://badgen.net/github/checks/contributte/doctrine-odm/master"></a>
	<a href="https://coveralls.io/github/contributte/doctrine-odm"><img src="https://badgen.net/coveralls/c/github/contributte/doctrine-odm"></a>
	<a href="https://packagist.org/packages/nettrine/odm"><img src="https://badgen.net/packagist/dm/nettrine/odm"></a>
	<a href="https://packagist.org/packages/nettrine/odm"><img src="https://badgen.net/packagist/v/nettrine/odm"></a>
</p>
<p align=center>
	<a href="https://packagist.org/packages/nettrine/odm"><img src="https://badgen.net/packagist/php/nettrine/odm"></a>
	<a href="https://github.com/contributte/doctrine-odm"><img src="https://badgen.net/github/license/contributte/doctrine-odm"></a>
	<a href="https://bit.ly/ctteg"><img src="https://badgen.net/badge/support/gitter/cyan"></a>
	<a href="https://bit.ly/cttfo"><img src="https://badgen.net/badge/support/forum/yellow"></a>
	<a href="https://contributte.org/partners.html"><img src="https://badgen.net/badge/sponsor/donations/F96854"></a>
</p>

<p align=center>
Website 🚀 <a href="https://contributte.org">contributte.org</a> | Contact 👨🏻‍💻 <a href="https://f3l1x.io">f3l1x.io</a> | Twitter 🐦 <a href="https://twitter.com/contributte">@contributte</a>
</p>

Doctrine MongoDB ODM integration for Nette Framework applications.

## Versions

| State  | Version | Branch | Nette | PHP     |
|--------|---------|--------|-------|---------|
| dev    | `^0.4` | master | `3.3+` | `>=8.2` |
| stable | `^0.3` | master | `3.3+` | `>=8.2` |

## Installation

To install latest version of `nettrine/odm` use [Composer](https://getcomposer.org).

```bash
composer require nettrine/odm
```

## Content

- [Setup](#setup)
- [Relying](#relying)
- [Configuration](#configuration)
- [Mapping](#mapping)
  - [Attributes](#attributes)
  - [XML](#xml)
- [Examples](#examples)

## Setup

Register extension:

```yaml
extensions:
  nettrine.odm: Nettrine\ODM\DI\OdmExtension
```

## Relying

Take advantage of empowering this package with 3 extra packages:

- `mongodb/mongodb`
- `doctrine/cache`
- `symfony/console`

### `mongodb/mongodb`

This package relies on `mongodb/mongodb`, use prepared [nettrine/mongodb](https://github.com/contributte/doctrine-mongodb) integration.

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

This package relies on `doctrine/cache`, use prepared [nettrine/cache](https://github.com/contributte/doctrine-cache) integration.

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

Since this moment when you type `bin/console`, there will be registered commands from Doctrine ODM.

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

Doctrine ODM needs to know where your documents are located and how they are described (mapping).

Additional metadata provider needs to be registered. We provide bridges for these drivers:

- **attributes** (`Nettrine\ODM\DI\OdmAttributesExtension`)
- **xml** (`Nettrine\ODM\DI\OdmXmlExtension`)

### Attributes

Are you using attributes in your documents?

```php
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

#[ODM\Document]
class Article
{
}
```

You will also appreciate ODM => Attributes bridge, use `OdmAttributesExtension`.

```yaml
extensions:
  nettrine.odm: Nettrine\ODM\DI\OdmExtension
  nettrine.odm.attributes: Nettrine\ODM\DI\OdmAttributesExtension

nettrine.odm.attributes:
  paths: []
  excludePaths: []
```

### XML

Are you using XML mapping for your documents?

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

- https://github.com/contributte/playground (playground)
- https://contributte.org/examples.html (more examples)

## Development

See [how to contribute](https://contributte.org) to this package. This package is currently maintained by these authors.

<a href="https://github.com/solcik">
	<img width="80" height="80" src="https://avatars.githubusercontent.com/u/1543737?v=3&s=80">
</a>

-----

Consider to [support](https://contributte.org/partners.html) **contributte** development team.
Also thank you for using this package.
