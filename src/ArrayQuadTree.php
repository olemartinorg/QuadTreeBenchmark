<?php

class ArrayQuadTree {

    const BB_CENTER_X = 0;
    const BB_CENTER_Y = 1;
    const BB_WIDTH = 2;
    const BB_HEIGHT = 3;
    const BB_POINTS = 5;
    const BB_NORTH_WEST = 6;
    const BB_NORTH_EAST = 7;
    const BB_SOUTH_WEST = 8;
    const BB_SOUTH_EAST = 9;

    private $boundingBox = [];

    private $maxPoints = 4;

    public function __construct($centerX, $centerY, $width = 1.0, $height = 1.0) {
        $this->boundingBox = [
            self::BB_CENTER_X => $centerX,
            self::BB_CENTER_Y => $centerY,
            self::BB_WIDTH    => $width,
            self::BB_HEIGHT   => $height,
            self::BB_POINTS   => []
        ];
    }

    private function startX(array $boundingBox) {
        return $boundingBox[self::BB_CENTER_X] - $boundingBox[self::BB_WIDTH] / 2;
    }

    private function startY(array $boundingBox) {
        return $boundingBox[self::BB_CENTER_Y] - $boundingBox[self::BB_HEIGHT] / 2;
    }

    private function endX(array $boundingBox) {
        return $boundingBox[self::BB_CENTER_X] + $boundingBox[self::BB_WIDTH] / 2;
    }

    private function endY(array $boundingBox) {
        return $boundingBox[self::BB_CENTER_Y] + $boundingBox[self::BB_HEIGHT] / 2;
    }

    private function isXinRange($x, array $boundingBox) {
        return $x >= $this->startX($boundingBox) && $x <= $this->endX($boundingBox);
    }

    private function isYinRange($y, array $boundingBox) {
        return $y >= $this->startY($boundingBox) && $y <= $this->endY($boundingBox);
    }

    private function containsPoint($x, $y, array $boundingBox) {
        return $this->isXinRange($x, $boundingBox) && $this->isYinRange($y, $boundingBox);
    }

    private function intersects(array $thatBoundingBox, array $myBoundingBox) {
        return
            $this->isXinRange($this->startX($thatBoundingBox), $myBoundingBox) ||
            $this->isXinRange($this->endX($thatBoundingBox), $myBoundingBox) ||
            $this->isYinRange($this->startY($thatBoundingBox), $myBoundingBox) ||
            $this->isYinRange($this->endY($thatBoundingBox), $myBoundingBox);
    }

    private function encompasses(array $thatBoundingBox, array $myBoundingBox) {
        return
            $this->startX($thatBoundingBox) <= $this->startX($myBoundingBox) &&
            $this->endX($thatBoundingBox) >= $this->endX($myBoundingBox) &&
            $this->startY($thatBoundingBox) <= $this->startY($myBoundingBox) &&
            $this->endY($thatBoundingBox) >= $this->endY($myBoundingBox);
    }

    private function subdivide(array &$boundingBox) {
        $width = $boundingBox[self::BB_WIDTH] / 2;
        $height = $boundingBox[self::BB_HEIGHT] / 2;
        $centerX = $boundingBox[self::BB_CENTER_X];
        $centerY = $boundingBox[self::BB_CENTER_Y];

        $boundingBox[self::BB_NORTH_WEST] = [
            self::BB_CENTER_X => $centerX - $width / 2,
            self::BB_CENTER_Y => $centerY + $height / 2,
            self::BB_WIDTH => $width,
            self::BB_HEIGHT => $height,
            self::BB_POINTS => [],
        ];

        $boundingBox[self::BB_NORTH_EAST] = [
            self::BB_CENTER_X => $centerX + $width / 2,
            self::BB_CENTER_Y => $centerY + $height / 2,
            self::BB_WIDTH => $width,
            self::BB_HEIGHT => $height,
            self::BB_POINTS => [],
        ];

        $boundingBox[self::BB_SOUTH_WEST] = [
            self::BB_CENTER_X => $centerX - $width / 2,
            self::BB_CENTER_Y => $centerY - $height / 2,
            self::BB_WIDTH => $width,
            self::BB_HEIGHT => $height,
            self::BB_POINTS => [],
        ];

        $boundingBox[self::BB_SOUTH_EAST] = [
            self::BB_CENTER_X => $centerX + $width / 2,
            self::BB_CENTER_Y => $centerY - $height / 2,
            self::BB_WIDTH => $width,
            self::BB_HEIGHT => $height,
            self::BB_POINTS => [],
        ];
    }

    private function _insert($x, $y, array $extraData, array &$boundingBox) {
        if (!$this->containsPoint($x, $y, $boundingBox)) {
            return false;
        }

        if (count($boundingBox[self::BB_POINTS]) < $this->maxPoints) {
            $boundingBox[self::BB_POINTS][] = [$x, $y, $extraData];
            return true;
        } elseif (!isset($boundingBox[self::BB_NORTH_WEST])) {
            $this->subdivide($boundingBox);
        }

        if ($this->_insert($x, $y, $extraData, $boundingBox[self::BB_NORTH_WEST]) ||
            $this->_insert($x, $y, $extraData, $boundingBox[self::BB_NORTH_EAST]) ||
            $this->_insert($x, $y, $extraData, $boundingBox[self::BB_SOUTH_WEST]) ||
            $this->_insert($x, $y, $extraData, $boundingBox[self::BB_SOUTH_EAST]))
        {
            return true;
        }

        throw new OutOfBoundsException('Point is outside bounding box');
    }

    public function insert($x, $y, array $extraData=[]) {
        return $this->_insert($x, $y, $extraData, $this->boundingBox);
    }

    private function _search(array $thatBoundingBox, array $myBoundingBox)
    {
        $results = array();
        if ($this->encompasses($thatBoundingBox, $myBoundingBox) ||
            $this->intersects($thatBoundingBox, $myBoundingBox))
        {
            foreach ($myBoundingBox[self::BB_POINTS] as $point) {
                list($x, $y, ) = $point;
                if ($this->containsPoint($x, $y, $thatBoundingBox)) {
                    $results[] = $point;
                }
            }

            if (isset($myBoundingBox[self::BB_NORTH_WEST])) {
                return array_merge(
                    $results,
                    $this->_search($thatBoundingBox, $myBoundingBox[self::BB_NORTH_WEST]),
                    $this->_search($thatBoundingBox, $myBoundingBox[self::BB_NORTH_EAST]),
                    $this->_search($thatBoundingBox, $myBoundingBox[self::BB_SOUTH_WEST]),
                    $this->_search($thatBoundingBox, $myBoundingBox[self::BB_SOUTH_EAST])
                );
            }
        }
        return $results;
    }

    public function search($centerX, $centerY, $width=1.0, $height=1.0) {
        return $this->_search(
            [
                self::BB_CENTER_X => $centerX,
                self::BB_CENTER_Y => $centerY,
                self::BB_WIDTH    => $width,
                self::BB_HEIGHT   => $height,
            ],
            $this->boundingBox
        );
    }

}
