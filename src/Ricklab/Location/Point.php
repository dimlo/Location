<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Point
 *
 * @author rick
 */
namespace Ricklab\Location;

class Point {

    protected $_longitude, $_latitude;

    /**
     *
     * @param Number $long Longitude coordinates 
     * @param Number $lat Latitude coordinates
     */
    public function __construct($lat, $long) {
        if (!is_numeric($long)) {
            throw new InvalidArgumentException('$long must be a valid number');
        }

        if (!is_numeric($lat)) {
            throw new InvalidArgumentException('$lat must be a valid number');
        }

        $this->_longitude = $long;
        $this->_latitude = $lat;
    }

    /**
     * Get the latitude in Rads
     * @return Number Latitude in Rads 
     */
    public function latitudeToRad() {
        return deg2rad($this->_latitude);
    }

    /**
     * Get the longitude in Rads
     * @return Number Longitude in Rads 
     */
    public function longitudeToRad() {
        return deg2rad($this->_longitude);
    }

    /**
     *
     * @return String 
     */
    public function __toString() {
        return $this->_latitude . ' ' . $this->_longitude;
    }

    public function getLatitude() {
        return $this->_latitude;
    }

    public function getLongitude() {
        return $this->_longitude;
    }

    /**
     * Find distance to another point
     * @param Point $point2
     * @return Distance 
     */
    public function distanceTo(Point $point2) {
        $distance = new Distance($this, $point2);
        return $distance;
    }

    public function __get($request) {
        $request = strtolower($request);
        if (in_array($request, array('x', 'lon', 'long', 'longitude'))) {
            return $this->_longitude;
        } elseif (in_array($request, array('y', 'lat', 'latitude'))) {
            return $this->_latitude;
        } else {
            throw new InvalidArgumentException('Unexpected value for retrieval');
        }
    }

    /**
     * Find a location a distance and bearing from this one
     * 
     * @param Number $distance distance to other point
     * @param Number $bearing to other point
     * @param String $unit The unit the distance is in
     * @return Point
     */
    public function getRelativePoint($distance, $bearing, $unit = 'km') {
        $rad = Earth::radius($unit);
        $lat1 = $this->latitudeToRad();
        $lon1 = $this->longitudeToRad();
        $bearing = deg2rad($bearing);

        $lat2 = sin($lat1) * cos($distance / $rad) +
                cos($lat1) * sin($distance / $rad) * cos($bearing);
        $lat2 = asin($lat2);

        $lon2y = sin($bearing) * sin($distance / $rad) * cos($lat1);
        $lon2x = cos($distance / $rad) - sin($lat1) * sin($lat2);
        $lon2 = $lon1 + atan2($lon2y, $lon2x);
        return new Point(rad2deg($lat2), rad2deg($lon2));
    }

    /**
     * 
     * @param Point $point2
     * @return Number 
     */
    public function bearingTo(Point $point2) {
        return $this->lineTo($point2)->getBearing();
    }

    /**
     * Create a line between this point and another point
     * @param Point $point
     * @return Line 
     */
    public function lineTo(Point $point) {
        return new Line($this, $point);
    }

    public function getMbr($distance, $unit = 'km') {
        return new Mbr($this, $distance, $unit);
    }

    public function toSql() {
        return 'POINT('.$this->__toString().')';
    }

}