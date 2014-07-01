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

use Phramz\Component\ComposerRepositoryModel\Event\VisitRepositoryEvent;
use Phramz\Component\ComposerRepositoryModel\Event\VisitPackageCollectionEvent;
use Phramz\Component\ComposerRepositoryModel\Event\VisitReferenceCollectionEvent;
use Phramz\Component\ComposerRepositoryModel\Event\VisitReferenceEvent;
use Phramz\Component\ComposerRepositoryModel\Event\VisitTargetEvent;
use Phramz\Component\ComposerRepositoryModel\Event\VisitVersionCollectionEvent;
use Phramz\Component\ComposerRepositoryModel\Event\VisitVersionEvent;
use Phramz\Component\ComposerRepositoryModel\Model\ReferenceCollectionInterface;
use Phramz\Component\ComposerRepositoryModel\Model\RepositoryInterface;
use Phramz\Component\ComposerRepositoryModel\Model\PackageCollectionInterface;
use Phramz\Component\ComposerRepositoryModel\Model\ReferenceInterface;
use Phramz\Component\ComposerRepositoryModel\Model\TargetInterface;
use Phramz\Component\ComposerRepositoryModel\Model\VersionCollectionInterface;
use Phramz\Component\ComposerRepositoryModel\Model\VersionInterface;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class EventVisitor
 * @package Phramz\Component\ComposerRepositoryModel\Model\Visitor
 */
class EventVisitor extends AbstractVisitor
{
    protected $nsModel = 'Phramz\Component\ComposerRepositoryModel\Model';
    protected $nsEvent = 'Phramz\Component\ComposerRepositoryModel\Event';
    protected $eventMap = array(
        'VersionCollectionInterface' => 'VisitVersionCollectionEvent',
        'ReferenceCollectionInterface' => 'VisitReferenceCollectionEvent',
        'PackageCollectionInterface' => 'VisitPackageCollectionEvent',
        'TargetInterface' => 'VisitTargetEvent',
        'ReferenceInterface' => 'VisitReferenceEvent',
        'VersionInterface' => 'VisitVersionEvent',
        'RepositoryInterface' => 'VisitRepositoryEvent',
    );

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct();

        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param VersionCollectionInterface $collection
     */
    public function visitVersionCollection(VersionCollectionInterface $collection)
    {
        $property = '[versions]';
        $this->startVisiting($property, $collection);

        $this->eventDispatcher->dispatch(
            VisitVersionCollectionEvent::VISIT,
            new VisitVersionCollectionEvent($this, $collection)
        );

        $this->endVisiting($property, $collection);
    }

    /**
     * {@inheritdoc}
     */
    public function visitReferenceCollection(ReferenceCollectionInterface $collection)
    {
        $property = '[references]';
        $this->startVisiting($property, $collection);

        $this->eventDispatcher->dispatch(
            VisitReferenceCollectionEvent::VISIT,
            new VisitReferenceCollectionEvent($this, $collection)
        );

        $this->endVisiting($property, $collection);
    }

    public function visitPackageCollection(PackageCollectionInterface $collection)
    {
        $property = '[packages]';
        $this->startVisiting($property, $collection);

        $this->eventDispatcher->dispatch(
            VisitPackageCollectionEvent::VISIT,
            new VisitPackageCollectionEvent($this, $collection)
        );

        $this->endVisiting($property, $collection);
    }

    /**
     * @param RepositoryInterface $index
     */
    public function visitIndex(RepositoryInterface $index)
    {
        $property = '[root]';
        $this->startVisiting($property, $index);

        $this->eventDispatcher->dispatch(
            VisitRepositoryEvent::VISIT,
            new VisitRepositoryEvent($this, $index)
        );

        $this->endVisiting($property, $index);
    }

    /**
     * @param TargetInterface $repository
     */
    public function visitTarget(TargetInterface $repository)
    {
        $property = '[repository:"'.$repository->getUrl().'"]';
        $this->startVisiting($property, $repository);

        $this->eventDispatcher->dispatch(
            VisitTargetEvent::VISIT,
            new VisitTargetEvent($this, $repository)
        );

        $this->endVisiting($property, $repository);
    }

    /**
     * @param VersionInterface $version
     */
    public function visitVersion(VersionInterface $version)
    {
        $property = '[version:"'.$version->getName().'"]';
        $this->startVisiting($property, $version);

        $this->eventDispatcher->dispatch(
            VisitVersionEvent::VISIT,
            new VisitVersionEvent($this, $version)
        );

        $this->endVisiting($property, $version);
    }

    /**
     * {@inheritdoc}
     */
    public function visitReference(ReferenceInterface $reference)
    {
        $property = '[reference:"'.$reference->getName().'"]';
        $this->startVisiting($property, $reference);

        $this->eventDispatcher->dispatch(
            VisitReferenceEvent::VISIT,
            new VisitReferenceEvent($this, $reference)
        );

        $this->endVisiting($property, $reference);
    }

    /**
     * {@inheritdoc}
     */
    public function startVisiting($property, $data)
    {
        parent::startVisiting($property, $data);

        $event = $this->newEvent($this, $data);
        $this->eventDispatcher->dispatch($event::BEFORE, $event);
    }

    /**
     * {@inheritdoc}
     */
    public function endVisiting($property, $data)
    {
        $event = $this->newEvent($this, $data);
        $this->eventDispatcher->dispatch($event::AFTER, $event);

        return parent::endVisiting($property, $data);
    }

    /**
     * @param VisitorInterface $visitor
     * @param $data
     * @return Event|null
     */
    protected function newEvent(VisitorInterface $visitor, $data)
    {
        foreach ($this->eventMap as $typeClass => $eventClass) {
            $fqcnType = "{$this->nsModel}\\{$typeClass}";
            $fqcnEvent = "{$this->nsEvent}\\{$eventClass}";

            if ($data instanceof $fqcnType) {
                return new $fqcnEvent($visitor, $data);
            }
        }

        return null;
    }
}
