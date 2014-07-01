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

use JMS\Serializer\Context;
use JMS\Serializer\JsonDeserializationVisitor;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Metadata\ClassMetadata;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class AbstractReferenceCollection
 * @package Phramz\Component\ComposerRepositoryModel\Model
 */
abstract class AbstractReferenceCollection extends ArrayCollection
{
    public function serializeToJson(JsonSerializationVisitor $visitor, $data, Context $context)
    {
        $data = array();

        foreach ($this as $name => $ref) {
            $meta = $context->accept($ref, array('name' => $this->getType()));
            $data[$name] = $meta;
        }

        return $data;
    }

    public function deserializeFromJson(JsonDeserializationVisitor $visitor, $data, Context $context)
    {
        $class = get_class($this);
        $visitor->startVisitingObject(new ClassMetadata($class), $this, array(), $context);

        foreach ($data as $name => $meta) {
            /** @var Reference $ref */
            $ref = $context->accept($meta, array('name' => $this->getType()));
            $ref->setName($name);

            $this->set($name, $ref);
        }

        $visitor->endVisitingObject(new ClassMetadata($class), $this, array(), $context);
    }

    /**
     * Returns the type of collection items
     * @return string
     */
    abstract protected function getType();
}
