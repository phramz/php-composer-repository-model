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
namespace Phramz\Component\ComposerRepositoryModel\Tests\Service;

use Phramz\Component\ComposerRepositoryModel\Model\RepositoryInterface;
use Phramz\Component\ComposerRepositoryModel\Model\PackageCollectionInterface;
use Phramz\Component\ComposerRepositoryModel\Model\Reference;
use Phramz\Component\ComposerRepositoryModel\Model\TargetInterface;
use Phramz\Component\ComposerRepositoryModel\Model\VersionInterface;
use Phramz\Component\ComposerRepositoryModel\Service\RepositoryService;
use Phramz\Component\ComposerRepositoryModel\Tests\AbstractTestCase;

/**
 * Class RepositoryServiceTest
 * @package Phramz\Component\ComposerRepositoryModel\Tests\Service
 * @covers Phramz\Component\ComposerRepositoryModel\Service\RepositoryService
 */
class RepositoryServiceTest extends AbstractTestCase
{
    /**
     * @var RepositoryService
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;

    protected function setUp()
    {
        parent::setUp();

        $this->clientMock = $this->getMockBuilder('Guzzle\Http\ClientInterface')
            ->getMockForAbstractClass();

        $this->requestMock = $this->getMockBuilder('Guzzle\Http\Message\RequestInterface')
            ->getMockForAbstractClass();

        $this->responseMock = $this->getMockBuilder('Guzzle\Http\Message\Response')
            ->setMethods(array('getBody'))
            ->disableOriginalClone()
            ->disableOriginalConstructor()
            ->getMock();

        $this->service = new RepositoryService($this->clientMock);
    }

    public function testBuildModel()
    {
        $this->clientMock->expects($this->once())
            ->method('get')
            ->with('packages.json')
            ->will($this->returnValue($this->requestMock));

        $this->clientMock->expects($this->once())
            ->method('setBaseUrl')
            ->with('http://localhost')
            ->will($this->returnValue($this->clientMock));

        $this->requestMock->expects($this->once())
            ->method('send')
            ->will($this->returnValue($this->responseMock));

        $this->responseMock->expects($this->once())
            ->method('getBody')
            ->with(true)
            ->will($this->returnValue($this->loadFixture('packages.json')));

        $model = $this->service->buildModel('http://localhost', 'packages.json');

        $this->assertInstanceOf('Phramz\Component\ComposerRepositoryModel\Model\RepositoryInterface', $model);

        return $model;
    }

    /**
     * @depends testBuildModel
     */
    public function testProviders(RepositoryInterface $model)
    {
        $collection = $model->getProviders();
        $this->assertInstanceOf(
            'Phramz\Component\ComposerRepositoryModel\Model\ReferenceCollectionInterface',
            $collection
        );

        $assertions = array(
            array(
                'name' => 'foo/bar',
                'filename' => '/%package%_%hash%.json',
                'sha256' => '6c7710a1ca26d3c0f9dfc4c34bc3d6e71ed88d8783847ed82079601401e29b18'
            ),
            array(
                'name' => 'foo/bazz',
                'filename' => '/%package%_%hash%.json',
                'sha256' => 'b5bc3eac62b9f38814b140be994c268b7c1f3d9d32bea4dfc53ca15def1f27e1'
            ),
            array(
                'name' => 'foo/foobar',
                'filename' => '/%package%_%hash%.json',
                'sha256' => '1d40832ecd5e35426edf44c04846f0230cb76be72c5df808a397597cfbe36f65'
            ),
        );

        $this->assertCount(count($assertions), $collection);

        /** @var Reference $reference */
        foreach ($collection as $reference) {
            $test = array_shift($assertions);

            $this->assertInstanceOf(
                'Phramz\Component\ComposerRepositoryModel\Model\ReferenceInterface',
                $reference
            );
            $this->assertEquals($test['name'], $reference->getName());
            $this->assertEquals($test['filename'], $reference->getFilename());
            $this->assertEquals('http://localhost', $reference->getBaseUrl());
            $this->assertNull($reference->getSha1());
            $this->assertEquals(
                $test['sha256'],
                $reference->getSha256()
            );
        }

    }

    /**
     * @depends testBuildModel
     */
    public function testIncludes(RepositoryInterface $model)
    {
        $collection = $model->getIncludes();
        $this->assertInstanceOf(
            'Phramz\Component\ComposerRepositoryModel\Model\ReferenceCollectionInterface',
            $collection
        );
        $this->assertCount(1, $collection);

        /** @var Reference $reference */
        $reference = $collection->first();
        $this->assertInstanceOf(
            'Phramz\Component\ComposerRepositoryModel\Model\ReferenceInterface',
            $reference
        );
        $this->assertEquals('foo/packages_%hash%.json', $reference->getName());
        $this->assertEquals('foo/packages_%hash%.json', $reference->getFilename());
        $this->assertEquals('http://localhost', $reference->getBaseUrl());
        $this->assertNull($reference->getSha256());
        $this->assertEquals(
            'b995d2684331fc3ecffea17b07f0171666311951',
            $reference->getSha1()
        );
    }

    /**
     * @depends testBuildModel
     */
    public function testProviderIncludes(RepositoryInterface $model)
    {
        $collection = $model->getProviderIncludes();
        $this->assertInstanceOf(
            'Phramz\Component\ComposerRepositoryModel\Model\ReferenceCollectionInterface',
            $collection
        );
        $this->assertCount(1, $collection);

        /** @var Reference $reference */
        $reference = $collection->first();
        $this->assertInstanceOf(
            'Phramz\Component\ComposerRepositoryModel\Model\ReferenceInterface',
            $reference
        );
        $this->assertEquals('foo/provider_%hash%.json', $reference->getName());
        $this->assertEquals('foo/provider_%hash%.json', $reference->getFilename());
        $this->assertEquals('http://localhost', $reference->getBaseUrl());
        $this->assertNull($reference->getSha1());
        $this->assertEquals(
            '87b607fd28d2cb0623df0429e6348ee443045734fcbe151dd0330787cdbf8eb1',
            $reference->getSha256()
        );
    }

    /**
     * @depends testBuildModel
     */
    public function testPackages(RepositoryInterface $model)
    {
        // check packages
        $packages = $model->getPackages();

        $this->assertInstanceOf('Phramz\Component\ComposerRepositoryModel\Model\PackageCollectionInterface', $packages);
        $this->assertArrayHasKey('vendor/package', $packages);

        $this->assertInstanceOf(
            'Phramz\Component\ComposerRepositoryModel\Model\VersionCollectionInterface',
            $packages['vendor/package']
        );
        $this->assertArrayHasKey('v4.0-stable', $packages['vendor/package']);

        return $packages;
    }

    /**
     * @depends testPackages
     */
    public function testVersion(PackageCollectionInterface $packages)
    {
        // check packages.version
        /** @var VersionInterface $version */
        $version = $packages['vendor/package']['v4.0-stable'];

        $this->assertInstanceOf('Phramz\Component\ComposerRepositoryModel\Model\VersionInterface', $version);
        $this->assertEquals("vendor/package", $version->getName());
        $this->assertEquals("v4.0-stable", $version->getVersion());

        return $version;
    }

    /**
     * @depends testVersion
     */
    public function testTargetSource(VersionInterface $version)
    {
        // check packages.version.source
        /** @var TargetInterface $source */
        $source = $version->getSource();
        $this->assertInstanceOf('Phramz\Component\ComposerRepositoryModel\Model\TargetInterface', $source);
        $this->assertEquals("git", $source->getType());
        $this->assertEquals("https://example.com/vendor/package.git", $source->getUrl());
        $this->assertEquals("43bca164cde8f5a98e054e1aea53406aa3b805cc", $source->getReference());
        $this->assertNull($source->getShasum());
    }

    /**
     * @depends testVersion
     */
    public function testTargetDist(VersionInterface $version)
    {
        // check packages.version.dist
        /** @var TargetInterface $dist */
        $dist = $version->getDist();
        $this->assertInstanceOf('Phramz\Component\ComposerRepositoryModel\Model\TargetInterface', $dist);
        $this->assertEquals("zip", $dist->getType());
        $this->assertEquals(
            "http://example.com/dist/vendor-package-43bca164cde8f5a98e054e1aea53406aa3b805cc-zip-78640c.zip",
            $dist->getUrl()
        );
        $this->assertEquals("43bca164cde8f5a98e054e1aea53406aa3b805cc", $dist->getReference());
        $this->assertEquals("56a8de2da3caac4b95c578c33968deb1a23f84b7", $dist->getShasum());
    }
}
