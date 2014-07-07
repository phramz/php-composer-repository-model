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
namespace Phramz\Component\ComposerRepositoryModel\Model\Visitor;

use Phramz\Component\ComposerRepositoryModel\Model\ReferenceCollectionInterface;
use Phramz\Component\ComposerRepositoryModel\Model\ReferenceInterface;
use Phramz\Component\ComposerRepositoryModel\Model\TargetInterface;
use Phramz\Component\ComposerRepositoryModel\Model\VersionCollectionInterface;
use Phramz\Component\ComposerRepositoryModel\Model\PackageCollectionInterface;
use Phramz\Component\ComposerRepositoryModel\Model\VersionInterface;
use Phramz\Component\ComposerRepositoryModel\Model\RepositoryInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class AbstractVisitor
 * @package Phramz\Component\ComposerRepositoryModel\Model\Visitor
 */
abstract class AbstractVisitor implements VisitorInterface, LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var \SplStack
     */
    protected $stack;

    public function __construct()
    {
        $this->logger = new NullLogger();
        $this->stack = new \SplStack();
    }

    /**
     * {@inheritdoc}
     */
    public function visitReferenceCollection(ReferenceCollectionInterface $collection)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function visitVersionCollection(VersionCollectionInterface $collection)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function visitPackageCollection(PackageCollectionInterface $collection)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function visitRepository(RepositoryInterface $index)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function visitTarget(TargetInterface $repository)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function visitVersion(VersionInterface $version)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function visitReference(ReferenceInterface $reference)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function startVisiting($property, $data)
    {
        $this->stack->push(array($property, $data));
    }

    /**
     * {@inheritdoc}
     */
    public function endVisiting($property, $data)
    {
        return $this->stack->pop();
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        $current = $this->stack->pop();
        $parent = $this->stack->top();

        $this->stack->push($current);

        return $parent;
    }

    /**
     * {@inheritdoc}
     */
    public function getParentByType($type)
    {
        $stack = new \SplStack();

        $found = false;
        while ($item = $this->stack->pop()) {
            $stack->push($item);
            if ($item instanceof $type) {
                $found = true;
                break;
            }
        }

        foreach ($stack as $itemToPush) {
            $this->stack->push($itemToPush);
        }

        return $found ? $item : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}
