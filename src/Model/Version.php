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
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use Phramz\Component\ComposerRepositoryModel\Model\Visitor\VisitorInterface;

/**
 * Class Version
 * @package Phramz\Component\ComposerRepositoryModel\Model
 */
class Version implements VersionInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $version;

    /**
     * @var Target
     */
    protected $source;

    /**
     * @var Target
     */
    protected $dist;

    /**
     * original composer.json
     * @var string
     */
    protected $raw;

    public function serializeToJson()
    {
        return $this->raw;
    }

    public function deserializeFromJson(JsonDeserializationVisitor $visitor, $data, Context $context)
    {
        $class = get_class($this);
        $visitor->startVisitingObject(
            new ClassMetadata($class),
            $this,
            array(),
            $context
        );

        $metadata = new PropertyMetadata($class, 'name');
        $metadata->setType('string');

        $visitor->visitProperty($metadata, $data, $context);

        $metadata = new PropertyMetadata($class, 'version');
        $metadata->setType('string');

        $visitor->visitProperty($metadata, $data, $context);

        $metadata = new PropertyMetadata($class, 'source');
        $metadata->setType('Phramz\Component\ComposerRepositoryModel\Model\Target');

        $visitor->visitProperty($metadata, $data, $context);

        $metadata = new PropertyMetadata($class, 'dist');
        $metadata->setType('Phramz\Component\ComposerRepositoryModel\Model\Target');

        $visitor->visitProperty($metadata, $data, $context);

        $visitor->endVisitingObject(
            new ClassMetadata($class),
            $this,
            array(),
            $context
        );

        $this->raw = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function accept(VisitorInterface $visitor)
    {
        $visitor->visitVersion($this);

        if (null !== $this->getSource()) {
            $this->getSource()->accept($visitor);
        }

        if (null !== $this->getDist()) {
            $this->getDist()->accept($visitor);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDist()
    {
        return $this->dist;
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
    public function getSource()
    {
        return $this->source;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    public function getRaw()
    {
        return $this->raw;
    }
}
