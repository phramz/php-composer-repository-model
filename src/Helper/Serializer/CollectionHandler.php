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
namespace Phramz\Component\ComposerRepositoryModel\Helper\Serializer;

use Doctrine\Common\Collections\Collection;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\VisitorInterface;

/**
 * Class CollectionHandler
 * @package Phramz\Component\ComposerRepositoryModel\Helper\Serializer
 */
class CollectionHandler implements SubscribingHandlerInterface
{
    protected static $classMap = array(
        'VersionCollection' => 'Phramz\Component\ComposerRepositoryModel\Model\VersionCollection',
        'PackageCollection' => 'Phramz\Component\ComposerRepositoryModel\Model\PackageCollection',
    );

    /**
     * {@inheritdoc}
     */
    public static function getSubscribingMethods()
    {
        $methods = array();
        foreach (static::$classMap as $type => $class) {
            $methods[] = array (
                'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => $type,
                'method' => 'serializeCollection',
            );

            $methods[] = array (
                'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                'format' => 'json',
                'type' => $type,
                'method' => 'deserializeCollection',
            );
        }

        return $methods;
    }

    public function serializeCollection(
        VisitorInterface $visitor,
        Collection $collection,
        array $type,
        Context $context
    ) {
        $type['name'] = 'array';

        return $visitor->visitArray($collection->toArray(), $type, $context);
    }

    public function deserializeCollection(
        VisitorInterface $visitor,
        $data,
        array $type,
        Context $context
    ) {
        $class = static::$classMap[$type['name']];
        $type['name'] = 'array';

        return new $class($visitor->visitArray($data, $type, $context));
    }
}
