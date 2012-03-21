<?php
/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\ORMBundle\Types;

use Jasny\ORM\Property\Point;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Mapping type for spatial POINT objects.
 * 
 * @see http://codeutopia.net/blog/2011/02/19/using-spatial-data-in-doctrine-2/
 */
class PointType extends Type
{
    const POINT = 'point';
 
    /**
     * Gets the name of this type.
     *
     * @return string
     */
    public function getName()
    {
        return self::POINT;
    }
 
    /**
     * Gets the SQL declaration snippet for a field of this type.
     *
     * @param array $fieldDeclaration The field declaration.
     * @param AbstractPlatform $platform The currently used database platform.
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        switch ($platform->getName()) {
            case 'mysql': return 'POINT';
            default: throw new \Exception("Point type is not supported for the " . $platform->getName() . " platform");
        }
    }
 
    /**
     * Convert from a database value.
     *
     * @param string $value The packed value
     * @param AbstractPlatform $platform The currently used database platform.
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        // Null fields come in as empty strings
        if ($value == '') {
            return null;
        }
 
        $data = unpack('x/x/x/x/corder/Ltype/dlat/dlon', $value);
        return new Point($data['lat'], $data['lon']);
    }
 
    /**
     * Convert to a database value.
     *
     * @param Point $value
     * @param AbstractPlatform $platform The currently used database platform.
     * @return string
     */
    public function convertToDatabaseValue(Point $value, AbstractPlatform $platform)
    {
        if (!$value) return '';
        return pack('xxxxcLdd', '0', 1, $value->getLatitude(), $value->getLongitude());
    }
}