<?php
/*
 * This file is part of the Jasny extension on Symfony.
 *
 * (c) Arnold Daniels <arnold@jasny.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jasny\ORMBundle\Properties;

use Symfony\Component\HttpFoundation\File\File as FsFile;
use Symfony\Component\HttpFoundation\File\UploadFile;

/**
 * Point object for spatial mapping.
 * 
 * @see http://codeutopia.net/blog/2011/02/19/using-spatial-data-in-doctrine-2/
 */
class Point implements Geom
{
    private $latitude;
    private $longitude;
 
    /**
     * Point object for spatial mapping
     * 
     * @param float $latitude
     * @param float $longitude 
     */
    public function __construct($latitude, $longitude)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }
 
    /**
     * Set latitude
     * 
     * @param float $x 
     */
    public function setLatitude($x)
    {
        $this->latitude = $x;
    }
 
    /**
     * Get latitude
     * 
     * @return float 
     */
    public function getLatitude()
    {
        return $this->latitude;
    }
 
    /**
     * Set longitude
     * 
     * @param float $y 
     */
    public function setLongitude($y)
    {
        $this->longitude = $y;
    }
 
    /**
     * Get longitude
     * 
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }
 
    /**
     * Cast object to string (for DQL)
     * 
     * @return string
     */
    public function __toString()
    {
        //Output from this is used with POINT_STR in DQL so must be in specific format
        return sprintf('POINT(%f %f)', $this->latitude, $this->longitude);
    }
}
