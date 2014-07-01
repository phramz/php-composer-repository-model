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
use JMS\Serializer\Annotation as Serialisation;
use JMS\Serializer\Context;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;

/**
 * Class ReferenceCollection
 * @package Phramz\Component\ComposerRepositoryModel\Model
 */
class ReferenceCollection extends AbstractReferenceCollection implements ReferenceCollectionInterface
{
    /**
     * @param VisitorInterface $visitor
     */
    public function accept(VisitorInterface $visitor)
    {
        $visitor->visitReferenceCollection($this);

        /** @var ReferenceInterface $reference */
        foreach ($this as $reference) {
            $reference->accept($visitor);
        }
    }

    /**
     * @Serialisation\HandlerCallback("json", direction = "serialization")
     */
    public function serializeToJson(JsonSerializationVisitor $visitor, $data, Context $context)
    {
        return parent::serializeToJson($visitor, $data, $context);
    }

    /**
     * @Serialisation\HandlerCallback("json", direction = "deserialization")
     */
    public function deserializeFromJson(JsonDeserializationVisitor $visitor, $data, Context $context)
    {
        parent::deserializeFromJson($visitor, $data, $context);
    }

    /**
     * {@inheritdoc}
     */
    protected function getType()
    {
        return 'Phramz\Component\ComposerRepositoryModel\Model\Reference';
    }
}
