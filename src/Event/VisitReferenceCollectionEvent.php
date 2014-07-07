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
namespace Phramz\Component\ComposerRepositoryModel\Event;

use Phramz\Component\ComposerRepositoryModel\Model\ReferenceCollectionInterface;
use Phramz\Component\ComposerRepositoryModel\Model\Visitor\VisitorInterface;

/**
 * Class VisitReferenceCollectionEvent
 * @package Phramz\Component\ComposerRepositoryModel\Event
 */
class VisitReferenceCollectionEvent extends AbstractVisitCollectionEvent
{
    const BEFORE = 'phramz.composer_repository_model.visit_before.reference_collection';
    const VISIT = 'phramz.composer_repository_model.visit.reference_collection';
    const AFTER = 'phramz.composer_repository_model.visit_after.reference_collection';

    public function __construct(VisitorInterface $visitor, ReferenceCollectionInterface $collection)
    {
        parent::__construct($visitor, $collection);
    }
}
