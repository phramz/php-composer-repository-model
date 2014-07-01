php-composer-repository-model
=============================

composer repository (aka `packages.json`) model builder component

With this library you'll be able to
- retrieve a `packages.json` from any repository (e.g. [Packagist](packagist.org) or a private [Satis](https://github.com/composer/satis)) reachable via HTTP
- serialize or deserialize the json-data
- build up an object-oriented model
- browse through the model, including referenced files like `includes` and `providers`

*This library is early alpha so keep in mind that anything may change in future releases!*

## Requirements

- PHP 5.3.3 or higher

## Installation

The best way to install is using [Composer](https://getcomposer.org):

- either edit your `composer.json` any add an requirement

``` json
"require": {
    "phramz/composer-repository-model": "dev-master"
}
```

- or let composer do that for you

```
php composer.phar require phramz/composer-repository-model
```

## License

This library is licensed under the MIT license. For further information see LICENSE file.

## Configuration

There's no configuration at all, just use it.

## Usage

```php
use Guzzle\Http\Client;
use Phramz\Component\ComposerRepositoryModel\Helper\Serializer\SerializerFactory;
use Phramz\Component\ComposerRepositoryModel\Service\RepositoryService;

// create an instance of the RepositoryService
$service = new RepositoryService(new Client(), SerializerFactory::create());

// fetch and parse the `packages.json` from `https://packagist.org`
echo $service
    // [packages.json]
    ->buildModel(
        'https://packagist.org',
        'packages.json'
    )
    // [packages.json][provider-includes]
    ->getProviderIncludes()
    // [packages.json][provider-includes][p/provider-archived$%hash%.json]
    ->get('p/provider-archived$%hash%.json')
    // [packages.json][provider-includes][p/provider-archived$0123456789.json][providers]
    ->getProviders()
    // [packages.json][provider-includes][...][providers][p/phramz/doctrine-annotation-scanner$0123456789.json]
    ->get("phramz/doctrine-annotation-scanner")
    // [packages.json][provider-includes][...][providers][...][packages]
    ->getPackages()
    // [packages.json][provider-includes][...][providers][...][packages][phramz/doctrine-annotation-scanner]
    ->first()
    // [packages.json][provider-includes][...][providers][...][packages][...][v1.0.0]
    ->last()
    // [packages.json][provider-includes][...][providers][...][packages][...][v1.0.0][source]
    ->getSource()
    // [packages.json][provider-includes][...][providers][...][packages][...][v1.0.0][source][url]
    ->getUrl();

// ... will output `https://github.com/phramz/doctrine-annotation-scanner.git`
```

## Known issues

- None, so far ...
