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
namespace Phramz\Component\ComposerRepositoryModel\Model;

use JMS\Serializer\Annotation as Serialisation;
use Phramz\Component\ComposerRepositoryModel\Model\Visitor\VisitorInterface;
use Phramz\Component\ComposerRepositoryModel\Service\RepositoryServiceInterface;

/**
 * Class Reference
 * @package Phramz\Component\ComposerRepositoryModel\Model
 * @Serialisation\ExclusionPolicy("all")
 */
class Reference implements ReferenceInterface
{
    /**
     * @Serialisation\Type("string")
     * @Serialisation\SerializedName("sha1")
     * @Serialisation\Expose
     */
    protected $sha1;

    /**
     * @Serialisation\Type("string")
     * @Serialisation\SerializedName("sha256")
     * @Serialisation\Expose
     */
    protected $sha256;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var string
     */
    protected $filename;

    /**
     * @var RepositoryInterface
     */
    protected $index;

    /**
     * @var HierarchyInterface
     */
    protected $parent;

    /**
     * @var RepositoryServiceInterface
     */
    protected $repositoryService;

    protected function init()
    {
        if (null == $this->index) {
            if (null === $this->repositoryService) {
                throw new \LogicException(
                    "unable to initialize due to missing dependency! " .
                    "try to inject repositoryService before accessing any properties."
                );
            }

            $hash = '';
            if (null !== $this->sha1 && '' !== $this->sha1) {
                $hash = $this->sha1;
            }

            if (null !== $this->sha256 && '' !== $this->sha256) {
                $hash = $this->sha256;
            }

            $this->index = $this->repositoryService->buildModel(
                $this->baseUrl,
                $this->filename,
                array(
                    RepositoryServiceInterface::PARAM_HASH => $hash,
                    RepositoryServiceInterface::PARAM_PACKAGE => $this->name
                ),
                $this
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function accept(VisitorInterface $visitor)
    {
        $visitor->visitReference($this);

        $this->init();
        $this->index->accept($visitor);
    }

    /**
     * @param RepositoryServiceInterface $repositoryService
     */
    public function setRepositoryService(RepositoryServiceInterface $repositoryService)
    {
        $this->repositoryService = $repositoryService;
    }

    /**
     * {@inheritdoc}
     */
    public function setParent(HierarchyInterface $parent)
    {
        $this->parent = $parent;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * {@inheritdoc}
     */
    public function getSha1()
    {
        return $this->sha1;
    }

    /**
     * {@inheritdoc}
     */
    public function getSha256()
    {
        return $this->sha256;
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * {@inheritdoc}
     */
    public function getPackages()
    {
        $this->init();

        return $this->index->getPackages();
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderIncludes()
    {
        $this->init();

        return $this->index->getProviderIncludes();
    }

    /**
     * {@inheritdoc}
     */
    public function getProviders()
    {
        $this->init();

        return $this->index->getProviders();
    }

    /**
     * {@inheritdoc}
     */
    public function getProvidersUrl()
    {
        $this->init();

        return $this->index->getProvidersUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function getIncludes()
    {
        $this->init();

        return $this->index->getIncludes();
    }

    /**
     * {@inheritdoc}
     */
    public function getNotify()
    {
        $this->init();

        return $this->index->getNotify();
    }

    /**
     * {@inheritdoc}
     */
    public function getNotifyBatch()
    {
        $this->init();

        return $this->index->getNotifyBatch();
    }

    /**
     * {@inheritdoc}
     */
    public function getSearch()
    {
        $this->init();

        return $this->index->getSearch();
    }
}
