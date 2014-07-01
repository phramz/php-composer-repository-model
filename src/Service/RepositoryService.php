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
namespace Phramz\Component\ComposerRepositoryModel\Service;

use Guzzle\Http\ClientInterface;
use JMS\Serializer\SerializerInterface;
use Phramz\Component\ComposerRepositoryModel\Model\HierarchyInterface;
use Phramz\Component\ComposerRepositoryModel\Model\RepositoryInterface;
use Phramz\Component\ComposerRepositoryModel\Model\Reference;
use Phramz\Component\ComposerRepositoryModel\Model\ReferenceCollectionInterface;
use Phramz\Component\ComposerRepositoryModel\Model\ReferenceInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class RepositoryServiceInterface
 * @package Phramz\Component\ComposerRepositoryModel\Service
 */
class RepositoryService implements RepositoryServiceInterface, LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    public function __construct(ClientInterface $client, SerializerInterface $serializer)
    {
        $this->client = $client;
        $this->serializer = $serializer;

        $this->logger = new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public function buildModel(
        $baseUrl,
        $filename = 'packages.json',
        array $substitutions = array(),
        HierarchyInterface $parent = null
    ) {
        $filename = $this->substituteParameters($filename, $substitutions);

        // fetch packages.json
        $data = $this->retrieveData($baseUrl, $filename);

        // parse json
        $index = $this->deserializeData($data);

        // set parent
        if (null !== $parent && $index instanceof HierarchyInterface) {
            $index->setParent($parent);
        }

        // prepare include references
        if (null !== $index->getIncludes()) {
            $this->buildReferences($index->getIncludes(), $index, $baseUrl);
        }

        // prepare provider-includes references
        if (null !== $index->getProviderIncludes()) {
            $this->buildReferences($index->getProviderIncludes(), $index, $baseUrl);
        }

        // prepare providers references
        if (null !== $index->getProviders()) {
            $this->buildReferences($index->getProviders(), $index, $baseUrl, $this->retrieveProvidersUrl($index));
        }

        return $index;
    }

    /**
     * @param string $baseUrl
     * @param string $filename
     * @return string
     */
    protected function retrieveData($baseUrl, $filename)
    {
        return $this->client
            ->setBaseUrl($baseUrl)
            ->get($filename)
            ->send()
            ->getBody(true);
    }

    /**
     * @param HierarchyInterface|null $index
     * @return null|string
     */
    protected function retrieveProvidersUrl(HierarchyInterface $index = null)
    {
        // do not work on References cuz they're just proxies
        if ($index instanceof ReferenceInterface) {
            return $this->retrieveProvidersUrl($index->getParent());
        }

        // Indexes are the only valid objects holding any provider-urls
        if ($index instanceof RepositoryInterface) {
            $url = $index->getProvidersUrl();

            if (null !== $url) {
                return $url;
            }
        }

        // nothing yet? try to get a parent
        if ($index instanceof HierarchyInterface) {
            return $this->retrieveProvidersUrl($index->getParent());
        }

        return null;
    }

    /**
     * @param ReferenceCollectionInterface $collection
     * @param RepositoryInterface $parent
     * @param string $baseUrl
     * @param string|null $filename
     */
    protected function buildReferences(
        ReferenceCollectionInterface $collection,
        RepositoryInterface $parent,
        $baseUrl,
        $filename = null
    ) {
        foreach ($collection as $name => $reference) {
            if ($reference instanceof HierarchyInterface) {
                $reference->setParent($parent);
            }

            if ($reference instanceof ReferenceInterface) {
                $reference->setBaseUrl($baseUrl);
                $reference->setName($name);
                $reference->setFilename($name);

                if (null !== $filename) {
                    $reference->setFilename($filename);
                }
            }

            if ($reference instanceof Reference) {
                $reference->setRepositoryService($this);
            }
        }
    }

    /**
     * @param string $filename
     * @param array $substitutions array of <string:search, string:replace>
     * @return string
     */
    protected function substituteParameters($filename, array $substitutions)
    {
        foreach ($substitutions as $search => $replace) {
            $filename = str_replace($search, $replace, $filename);
        }

        return $filename;
    }

    /**
     * @param string $data
     * @return RepositoryInterface
     */
    protected function deserializeData($data)
    {
        return $this->serializer->deserialize(
            $data,
            'Phramz\Component\ComposerRepositoryModel\Model\Repository',
            'json'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
