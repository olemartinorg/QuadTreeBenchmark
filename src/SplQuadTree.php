<?php

class SplQuadTree {

    const BB_CENTER_X = 0;
    const BB_CENTER_Y = 1;
    const BB_WIDTH = 2;
    const BB_HEIGHT = 3;
    const BB_POINTS = 4;
    const BB_NORTH_WEST = 5;
    const BB_NORTH_EAST = 6;
    const BB_SOUTH_WEST = 7;
    const BB_SOUTH_EAST = 8;

    private $boundingBox = [];

    private $maxPoints = 4;

    public function __construct($centerX, $centerY, $width = 1.0, $height = 1.0) {
        $this->boundingBox = new \SplFixedArray(9);
        $this->boundingBox[self::BB_CENTER_X] = $centerX;
        $this->boundingBox[self::BB_CENTER_Y] = $centerY;
        $this->boundingBox[self::BB_WIDTH] = $width;
        $this->boundingBox[self::BB_HEIGHT] = $height;
        $this->boundingBox[self::BB_POINTS] = new \SplFixedArray($this->maxPoints);
    }

    private function startX(\SplFixedArray $boundingBox) {
        return $boundingBox[self::BB_CENTER_X] - $boundingBox[self::BB_WIDTH] / 2;
    }

    private function startY(\SplFixedArray $boundingBox) {
        return $boundingBox[self::BB_CENTER_Y] - $boundingBox[self::BB_HEIGHT] / 2;
    }

    private function endX(\SplFixedArray $boundingBox) {
        return $boundingBox[self::BB_CENTER_X] + $boundingBox[self::BB_WIDTH] / 2;
    }

    private function endY(\SplFixedArray $boundingBox) {
        return $boundingBox[self::BB_CENTER_Y] + $boundingBox[self::BB_HEIGHT] / 2;
    }

    private function isXinRange($x, \SplFixedArray$boundingBox) {
        return $x >= $this->startX($boundingBox) && $x <= $this->endX($boundingBox);
    }

    private function isYinRange($y, \SplFixedArray $boundingBox) {
        return $y >= $this->startY($boundingBox) && $y <= $this->endY($boundingBox);
    }

    private function containsPoint($x, $y, \SplFixedArray $boundingBox) {
        return $this->isXinRange($x, $boundingBox) && $this->isYinRange($y, $boundingBox);
    }

    private function intersects(\SplFixedArray $thatBoundingBox, \SplFixedArray$myBoundingBox) {
        return
            $this->isXinRange($this->startX($thatBoundingBox), $myBoundingBox) ||
            $this->isXinRange($this->endX($thatBoundingBox), $myBoundingBox) ||
            $this->isYinRange($this->startY($thatBoundingBox), $myBoundingBox) ||
            $this->isYinRange($this->endY($thatBoundingBox), $myBoundingBox);
    }

    private function encompasses(\SplFixedArray $thatBoundingBox, \SplFixedArray $myBoundingBox) {
        return
            $this->startX($thatBoundingBox) <= $this->startX($myBoundingBox) &&
            $this->endX($thatBoundingBox) >= $this->endX($myBoundingBox) &&
            $this->startY($thatBoundingBox) <= $this->startY($myBoundingBox) &&
            $this->endY($thatBoundingBox) >= $this->endY($myBoundingBox);
    }

    private function subdivide(\SplFixedArray $boundingBox) {
        $width = $boundingBox[self::BB_WIDTH] / 2;
        $height = $boundingBox[self::BB_HEIGHT] / 2;
        $centerX = $boundingBox[self::BB_CENTER_X];
        $centerY = $boundingBox[self::BB_CENTER_Y];

        $boundingBox[self::BB_NORTH_WEST] = new \SplFixedArray(9);
        $boundingBox[self::BB_NORTH_WEST][self::BB_CENTER_X] = $centerX - $width / 2;
        $boundingBox[self::BB_NORTH_WEST][self::BB_CENTER_Y] = $centerY + $height / 2;
        $boundingBox[self::BB_NORTH_WEST][self::BB_WIDTH] = $width;
        $boundingBox[self::BB_NORTH_WEST][self::BB_HEIGHT] = $height;
        $boundingBox[self::BB_NORTH_WEST][self::BB_POINTS] = new \SplFixedArray($this->maxPoints);

        $boundingBox[self::BB_NORTH_EAST] = new \SplFixedArray(9);
        $boundingBox[self::BB_NORTH_EAST][self::BB_CENTER_X] = $centerX + $width / 2;
        $boundingBox[self::BB_NORTH_EAST][self::BB_CENTER_Y] = $centerY + $height / 2;
        $boundingBox[self::BB_NORTH_EAST][self::BB_WIDTH] = $width;
        $boundingBox[self::BB_NORTH_EAST][self::BB_HEIGHT] = $height;
        $boundingBox[self::BB_NORTH_EAST][self::BB_POINTS] = new \SplFixedArray($this->maxPoints);

        $boundingBox[self::BB_SOUTH_WEST] = new \SplFixedArray(9);
        $boundingBox[self::BB_SOUTH_WEST][self::BB_CENTER_X] = $centerX - $width / 2;
        $boundingBox[self::BB_SOUTH_WEST][self::BB_CENTER_Y] = $centerY - $height / 2;
        $boundingBox[self::BB_SOUTH_WEST][self::BB_WIDTH] = $width;
        $boundingBox[self::BB_SOUTH_WEST][self::BB_HEIGHT] = $height;
        $boundingBox[self::BB_SOUTH_WEST][self::BB_POINTS] = new \SplFixedArray($this->maxPoints);

        $boundingBox[self::BB_SOUTH_EAST] = new \SplFixedArray(9);
        $boundingBox[self::BB_SOUTH_EAST][self::BB_CENTER_X] = $centerX + $width / 2;
        $boundingBox[self::BB_SOUTH_EAST][self::BB_CENTER_Y] = $centerY - $height / 2;
        $boundingBox[self::BB_SOUTH_EAST][self::BB_WIDTH] = $width;
        $boundingBox[self::BB_SOUTH_EAST][self::BB_HEIGHT] = $height;
        $boundingBox[self::BB_SOUTH_EAST][self::BB_POINTS] = new \SplFixedArray($this->maxPoints);
    }

    private function _insert(\SplFixedArray $newPoint, \SplFixedArray $boundingBox) {
        if (!$this->containsPoint($newPoint[0], $newPoint[1], $boundingBox)) {
            return false;
        }

        /* @var \SplFixedArray $pointsArray */
        $pointsArray = $boundingBox[self::BB_POINTS];

        if ($pointsArray[$this->maxPoints - 1] === null) {
            $pointsArray[$pointsArray->getSize()-1] = $newPoint;
            return true;
        } elseif ($boundingBox[self::BB_NORTH_WEST] === null) {
            $this->subdivide($boundingBox);
        }

        if ($this->_insert($newPoint, $boundingBox[self::BB_NORTH_WEST]) ||
            $this->_insert($newPoint, $boundingBox[self::BB_NORTH_EAST]) ||
            $this->_insert($newPoint, $boundingBox[self::BB_SOUTH_WEST]) ||
            $this->_insert($newPoint, $boundingBox[self::BB_SOUTH_EAST]))
        {
            return true;
        }

        throw new OutOfBoundsException('Point is outside bounding box');
    }

    public function insert($x, $y, array $extraData=[]) {
        $newPoint = new \SplFixedArray(3);
        $newPoint[0] = $x;
        $newPoint[1] = $y;
        $newPoint[2] = $extraData;

        return $this->_insert($newPoint, $this->boundingBox);
    }

    private function _search(\SplFixedArray $thatBoundingBox, \SplFixedArray $myBoundingBox)
    {
        $results = [];
        if ($this->encompasses($thatBoundingBox, $myBoundingBox) ||
            $this->intersects($thatBoundingBox, $myBoundingBox))
        {
            foreach ($myBoundingBox[self::BB_POINTS] as $point) {
                if ($point === null) {
                    continue;
                }
                $x = $point[0];
                $y = $point[1];
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
        $searchBoundingBox = new \SplFixedArray(4);
        $searchBoundingBox[self::BB_CENTER_X] = $centerX;
        $searchBoundingBox[self::BB_CENTER_Y] = $centerY;
        $searchBoundingBox[self::BB_WIDTH] = $width;
        $searchBoundingBox[self::BB_HEIGHT] = $height;

        return $this->_search($searchBoundingBox, $this->boundingBox);
    }

}
