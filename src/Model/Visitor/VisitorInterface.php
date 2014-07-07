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
use Phramz\Component\ComposerRepositoryModel\Model\RepositoryInterface;
use Phramz\Component\ComposerRepositoryModel\Model\PackageCollectionInterface;
use Phramz\Component\ComposerRepositoryModel\Model\ReferenceInterface;
use Phramz\Component\ComposerRepositoryModel\Model\TargetInterface;
use Phramz\Component\ComposerRepositoryModel\Model\VersionCollectionInterface;
use Phramz\Component\ComposerRepositoryModel\Model\VersionInterface;

/**
 * Interface VisitorInterface
 * @package Phramz\Component\ComposerRepositoryModel\Model\Visitor
 */
interface VisitorInterface
{
    /**
     * @param VersionCollectionInterface $collection
     * @return void
     */
    public function visitVersionCollection(VersionCollectionInterface $collection);

    /**
     * @param PackageCollectionInterface $collection
     * @return void
     */
    public function visitPackageCollection(PackageCollectionInterface $collection);

    /**
     * @param ReferenceCollectionInterface $collection
     * @return void
     */
    public function visitReferenceCollection(ReferenceCollectionInterface $collection);

    /**
     * @param RepositoryInterface $index
     * @return void
     */
    public function visitRepository(RepositoryInterface $index);

    /**
     * @param TargetInterface $repository
     * @return void
     */
    public function visitTarget(TargetInterface $repository);

    /**
     * @param VersionInterface $version
     * @return void
     */
    public function visitVersion(VersionInterface $version);

    /**
     * @param ReferenceInterface $version
     * @return void
     */
    public function visitReference(ReferenceInterface $version);

    /**
     * @param string $property
     * @param mixed $data
     * @return void
     */
    public function startVisiting($property, $data);

    /**
     * @param string $property
     * @param mixed $data
     * @return array of [property, data]
     */
    public function endVisiting($property, $data);

    /**
     * @return mixed
     */
    public function getParent();

    /**
     * @param string $type
     * @return mixed
     */
    public function getParentByType($type);
}
