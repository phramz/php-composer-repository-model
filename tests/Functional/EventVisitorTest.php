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
namespace Phramz\Component\ComposerRepositoryModel\Tests\Functional;

use Phramz\Component\ComposerRepositoryModel\Event\VisitRepositoryEvent;
use Phramz\Component\ComposerRepositoryModel\Event\VisitPackageCollectionEvent;
use Phramz\Component\ComposerRepositoryModel\Event\VisitReferenceCollectionEvent;
use Phramz\Component\ComposerRepositoryModel\Event\VisitReferenceEvent;
use Phramz\Component\ComposerRepositoryModel\Event\VisitTargetEvent;
use Phramz\Component\ComposerRepositoryModel\Event\VisitVersionCollectionEvent;
use Phramz\Component\ComposerRepositoryModel\Event\VisitVersionEvent;
use Phramz\Component\ComposerRepositoryModel\Model\RepositoryInterface;
use Phramz\Component\ComposerRepositoryModel\Model\Visitor\EventVisitor;
use Phramz\Component\ComposerRepositoryModel\Service\RepositoryService;
use Phramz\Component\ComposerRepositoryModel\Tests\AbstractTestCase;

/**
 * Class EventVisitorTest
 * @package Phramz\Component\ComposerRepositoryModel\Tests\Model\Visitor
 * @coversNothing
 */
class EventVisitorTest extends AbstractTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $clientMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventDispatcherMock;

    /**
     * @var EventVisitor
     */
    protected $visitor;

    protected function setUp()
    {
        parent::setUp();

        $this->eventDispatcherMock = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
            ->getMockForAbstractClass();

        $this->visitor = new EventVisitor($this->eventDispatcherMock);
    }

    public function testFunctional()
    {
        $this->clientMock = $this->getMockBuilder('Guzzle\Http\ClientInterface')
            ->getMockForAbstractClass();

        /** @var \PHPUnit_Framework_MockObject_MockObject $repositoryService */
        $repositoryService = $this->getMockBuilder('Phramz\Component\ComposerRepositoryModel\Service\RepositoryService')
            ->setMethods(array('retrieveData'))
            ->setConstructorArgs(array($this->clientMock, $this->serializer))
            ->getMockForAbstractClass();

        $self = $this;
        $repositoryService->expects($this->atLeastOnce())
            ->method('retrieveData')
            ->willReturnCallback(
                function ($baseUrl, $filename) use ($self) {
                    return $self->loadFixture($filename);
                }
            );

        /** @var RepositoryInterface $model */
        /** @var RepositoryService $repositoryService */
        $model = $repositoryService->buildModel('foo', 'packages.json');

        $eventNames = new \SplStack();
        $eventObjects = new \SplStack();

        // [packages.json]
        $this->visitRepository($eventNames, $eventObjects);

        // [packages.json][packages]
        $this->visitPackages($eventNames, $eventObjects);

        // [packages.json][includes]
        $this->visitIncludes($eventNames, $eventObjects);

        // [packages.json][provider-includes]
        $this->visitProviderIncludes($eventNames, $eventObjects);

        // [packages.json][providers]
        $this->visitProviders($eventNames, $eventObjects);

        $expectedCount = count($eventNames);
        $this->eventDispatcherMock->expects($this->exactly($expectedCount))
            ->method('dispatch')
            ->with(
                $this->callback(
                    function ($eventName) use ($eventNames, &$expectedCount) {
                        // workaround for https://github.com/sebastianbergmann/phpunit-mock-objects/pull/179
                        if ($expectedCount <= 0) {
                            return true;
                        }

                        $expectedEvent = $eventNames->isEmpty() ? null : $eventNames->shift();

                        return $eventName === $expectedEvent;
                    }
                ),
                $this->callback(
                    function ($eventObject) use ($eventObjects, &$expectedCount) {
                        // workaround for https://github.com/sebastianbergmann/phpunit-mock-objects/pull/179
                        if ($expectedCount-- <= 0) {
                            return true;
                        }

                        $expectedClass = $eventObjects->isEmpty() ? null : $eventObjects->shift();

                        return $eventObject instanceof $expectedClass;
                    }
                )
            );

        $model->accept($this->visitor);
    }

    /**
     * @param \SplStack $eventNames
     * @param \SplStack $eventObjects
     */
    private function visitProvider(\SplStack $eventNames, \SplStack $eventObjects)
    {
        $this->visitReference($eventNames, $eventObjects);

        // [...][providers][%package%_%hash%.json][packages]
        $this->visitPackages($eventNames, $eventObjects);
    }

    /**
     * @param \SplStack $eventNames
     * @param \SplStack $eventObjects
     */
    private function visitRepository(\SplStack $eventNames, \SplStack $eventObjects)
    {
        $eventNames->push(VisitRepositoryEvent::BEFORE);
        $eventObjects->push('Phramz\Component\ComposerRepositoryModel\Event\VisitRepositoryEvent');
        $eventNames->push(VisitRepositoryEvent::VISIT);
        $eventObjects->push('Phramz\Component\ComposerRepositoryModel\Event\VisitRepositoryEvent');
        $eventNames->push(VisitRepositoryEvent::AFTER);
        $eventObjects->push('Phramz\Component\ComposerRepositoryModel\Event\VisitRepositoryEvent');
    }

    /**
     * @param \SplStack $eventNames
     * @param \SplStack $eventObjects
     */
    private function visitReference(\SplStack $eventNames, \SplStack $eventObjects)
    {
        $eventNames->push(VisitReferenceEvent::BEFORE);
        $eventObjects->push('Phramz\Component\ComposerRepositoryModel\Event\VisitReferenceEvent');
        $eventNames->push(VisitReferenceEvent::VISIT);
        $eventObjects->push('Phramz\Component\ComposerRepositoryModel\Event\VisitReferenceEvent');
        $eventNames->push(VisitReferenceEvent::AFTER);
        $eventObjects->push('Phramz\Component\ComposerRepositoryModel\Event\VisitReferenceEvent');

        $this->visitRepository($eventNames, $eventObjects);
    }

    /**
     * @param \SplStack $eventNames
     * @param \SplStack $eventObjects
     */
    private function visitVersion(\SplStack $eventNames, \SplStack $eventObjects)
    {
        // [...][packages][%package%][%version%]
        $eventNames->push(VisitVersionEvent::BEFORE);
        $eventObjects->push('Phramz\Component\ComposerRepositoryModel\Event\VisitVersionEvent');
        $eventNames->push(VisitVersionEvent::VISIT);
        $eventObjects->push('Phramz\Component\ComposerRepositoryModel\Event\VisitVersionEvent');
        $eventNames->push(VisitVersionEvent::AFTER);
        $eventObjects->push('Phramz\Component\ComposerRepositoryModel\Event\VisitVersionEvent');

        // [...][packages][%package%][%version%][source]
        $this->visitTarget($eventNames, $eventObjects);

        // [...][packages][%package%][%version%][dist]
        $this->visitTarget($eventNames, $eventObjects);
    }

    /**
     * @param \SplStack $eventNames
     * @param \SplStack $eventObjects
     */
    private function visitPackages(\SplStack $eventNames, \SplStack $eventObjects)
    {
        // [...][packages]
        $eventNames->push(VisitPackageCollectionEvent::BEFORE);
        $eventObjects->push('Phramz\Component\ComposerRepositoryModel\Event\VisitPackageCollectionEvent');
        $eventNames->push(VisitPackageCollectionEvent::VISIT);
        $eventObjects->push('Phramz\Component\ComposerRepositoryModel\Event\VisitPackageCollectionEvent');
        $eventNames->push(VisitPackageCollectionEvent::AFTER);
        $eventObjects->push('Phramz\Component\ComposerRepositoryModel\Event\VisitPackageCollectionEvent');

        // [...][packages][%package%]
        $eventNames->push(VisitVersionCollectionEvent::BEFORE);
        $eventObjects->push('Phramz\Component\ComposerRepositoryModel\Event\VisitVersionCollectionEvent');
        $eventNames->push(VisitVersionCollectionEvent::VISIT);
        $eventObjects->push('Phramz\Component\ComposerRepositoryModel\Event\VisitVersionCollectionEvent');
        $eventNames->push(VisitVersionCollectionEvent::AFTER);
        $eventObjects->push('Phramz\Component\ComposerRepositoryModel\Event\VisitVersionCollectionEvent');

        // [...][packages][%package%][%version%]
        $this->visitVersion($eventNames, $eventObjects);

        // [...][packages][%package%][%version%]
        $this->visitVersion($eventNames, $eventObjects);
    }

    /**
     * @param \SplStack $eventNames
     * @param \SplStack $eventObjects
     */
    private function visitIncludes(\SplStack $eventNames, \SplStack $eventObjects)
    {
        // [...][includes]
        $this->visitReferenceCollection($eventNames, $eventObjects);

        // [...][includes][foo/packages_%hash%.json]
        $this->visitReference($eventNames, $eventObjects);

        // [...][includes][foo/packages_%hash%.json][packages]
        $this->visitPackages($eventNames, $eventObjects);
    }

    /**
     * @param \SplStack $eventNames
     * @param \SplStack $eventObjects
     */
    private function visitTarget(\SplStack $eventNames, \SplStack $eventObjects)
    {
        $eventNames->push(VisitTargetEvent::BEFORE);
        $eventObjects->push('Phramz\Component\ComposerRepositoryModel\Event\VisitTargetEvent');
        $eventNames->push(VisitTargetEvent::VISIT);
        $eventObjects->push('Phramz\Component\ComposerRepositoryModel\Event\VisitTargetEvent');
        $eventNames->push(VisitTargetEvent::AFTER);
        $eventObjects->push('Phramz\Component\ComposerRepositoryModel\Event\VisitTargetEvent');
    }

    /**
     * @param \SplStack $eventNames
     * @param \SplStack $eventObjects
     */
    private function visitProviderIncludes(\SplStack $eventNames, \SplStack $eventObjects)
    {
        // [...][provider-includes]
        $this->visitReferenceCollection($eventNames, $eventObjects);

        // [...][provider-includes][foo/provider_%hash%.json]
        $this->visitReference($eventNames, $eventObjects);

        // [...][provider-includes][foo/provider_%hash%.json][providers]
        $this->visitProviders($eventNames, $eventObjects);
    }

    /**
     * @param \SplStack $eventNames
     * @param \SplStack $eventObjects
     */
    private function visitProviders(\SplStack $eventNames, \SplStack $eventObjects)
    {
        // [...][providers]
        $this->visitReferenceCollection($eventNames, $eventObjects);

        // [...][providers][foo/bar_%hash%.json]
        $this->visitProvider($eventNames, $eventObjects);

        // [...][providers][foo/bazz_%hash%.json]
        $this->visitProvider($eventNames, $eventObjects);

        // [...][providers][foo/foobar_%hash%.json]
        $this->visitProvider($eventNames, $eventObjects);
    }

    /**
     * @param \SplStack $eventNames
     * @param \SplStack $eventObjects
     */
    private function visitReferenceCollection(\SplStack $eventNames, \SplStack $eventObjects)
    {
        $eventNames->push(VisitReferenceCollectionEvent::BEFORE);
        $eventObjects->push('Phramz\Component\ComposerRepositoryModel\Event\VisitReferenceCollectionEvent');
        $eventNames->push(VisitReferenceCollectionEvent::VISIT);
        $eventObjects->push('Phramz\Component\ComposerRepositoryModel\Event\VisitReferenceCollectionEvent');
        $eventNames->push(VisitReferenceCollectionEvent::AFTER);
        $eventObjects->push('Phramz\Component\ComposerRepositoryModel\Event\VisitReferenceCollectionEvent');
    }
}
