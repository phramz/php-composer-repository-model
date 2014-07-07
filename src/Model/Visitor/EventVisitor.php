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

use Phramz\Component\ComposerRepositoryModel\Event\AbstractVisitEvent;
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
     * {@inheritdoc}
     */
    public function visitVersionCollection(VersionCollectionInterface $collection)
    {
        $this->visit('[versions]', $collection);
    }

    /**
     * {@inheritdoc}
     */
    public function visitReferenceCollection(ReferenceCollectionInterface $collection)
    {
        $this->visit('[references]', $collection);
    }

    /**
     * {@inheritdoc}
     */
    public function visitPackageCollection(PackageCollectionInterface $collection)
    {
        $this->visit('[packages]', $collection);
    }

    /**
     * {@inheritdoc}
     */
    public function visitRepository(RepositoryInterface $repository)
    {
        $this->visit('[root]', $repository);
    }

    /**
     * {@inheritdoc}
     */
    public function visitTarget(TargetInterface $repository)
    {
        $this->visit('[repository:"'.$repository->getUrl().'"]', $repository);
    }

    /**
     * {@inheritdoc}
     */
    public function visitVersion(VersionInterface $version)
    {
        $this->visit('[version:"'.$version->getName().'"]', $version);
    }

    /**
     * {@inheritdoc}
     */
    public function visitReference(ReferenceInterface $reference)
    {
        $this->visit('[reference:"'.$reference->getName().'"]', $reference);
    }

    /**
     * {@inheritdoc}
     */
    public function startVisiting($property, $data)
    {
        parent::startVisiting($property, $data);

        $event = $this->newEvent($this, $data);
        if ($event instanceof Event) {
            $name = get_class($event) . '::BEFORE';

            $this->eventDispatcher->dispatch(
                defined($name) ? constant($name) : AbstractVisitEvent::BEFORE,
                $event
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function endVisiting($property, $data)
    {
        $event = $this->newEvent($this, $data);

        if ($event instanceof Event) {
            $name = get_class($event) . '::AFTER';

            $this->eventDispatcher->dispatch(
                defined($name) ? constant($name) : AbstractVisitEvent::AFTER,
                $event
            );
        }

        return parent::endVisiting($property, $data);
    }

    /**
     * @param VisitorInterface $visitor
     * @param mixed $data
     * @return Event|null
     */
    private function newEvent(VisitorInterface $visitor, $data)
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

    /**
     * @param string $property
     * @param mixed $data
     */
    private function visit($property, $data)
    {
        $this->startVisiting($property, $data);

        $event = $this->newEvent($this, $data);
        if ($event instanceof Event) {
            $name = get_class($event) . '::VISIT';

            $this->eventDispatcher->dispatch(
                defined($name) ? constant($name) : AbstractVisitEvent::VISIT,
                $event
            );
        }

        $this->endVisiting($property, $data);
    }
}
