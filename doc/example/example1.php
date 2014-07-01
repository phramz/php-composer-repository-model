<?php
/**
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
namespace Phramz\Component\ComposerRepositoryModel\Example;

require __DIR__ . '/../../bootstrap.php';

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
