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

use Phramz\Component\ComposerRepositoryModel\Model\Visitor\VisitorInterface;

/**
 * Class Repository
 * @package Phramz\Component\ComposerRepositoryModel\Model
 */
class Repository implements RepositoryInterface
{
    /**
     * @var PackageCollectionInterface
     */
    protected $packages;

    /**
     * @var ReferenceCollectionInterface
     */
    protected $includes;

    /**
     * @var ReferenceCollectionInterface
     */
    protected $providerIncludes;

    /**
     * @var ReferenceCollectionInterface
     */
    protected $providers;

    /**
     * @var string
     */
    protected $providersUrl;

    /**
     * @var string
     */
    protected $notify;

    /**
     * @var string
     */
    protected $notifyBatch;

    /**
     * @var string
     */
    protected $search;

    /**
     * @var HierarchyInterface
     */
    protected $parent;

    /**
     * {@inheritdoc}
     */
    public function accept(VisitorInterface $visitor)
    {
        $visitor->visitIndex($this);

        if (null !== $this->getPackages()) {
            $this->getPackages()->accept($visitor);
        }

        if (null !== $this->getIncludes()) {
            $this->getIncludes()->accept($visitor);
        }

        if (null !== $this->getProviderIncludes()) {
            $this->getProviderIncludes()->accept($visitor);
        }

        if (null !== $this->getProviders()) {
            $this->getProviders()->accept($visitor);
        }
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
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderIncludes()
    {
        return $this->providerIncludes;
    }

    /**
     * {@inheritdoc}
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * {@inheritdoc}
     */
    public function getProvidersUrl()
    {
        return $this->providersUrl;
    }

    /**
     * {@inheritdoc}
     */
    public function getIncludes()
    {
        return $this->includes;
    }

    /**
     * {@inheritdoc}
     */
    public function getNotify()
    {
        return $this->notify;
    }

    /**
     * {@inheritdoc}
     */
    public function getNotifyBatch()
    {
        return $this->notifyBatch;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearch()
    {
        return $this->search;
    }
}
