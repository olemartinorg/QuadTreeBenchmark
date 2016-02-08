<?php

    use Ds\Vector;

    class VectorQuadTree {

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
        $this->boundingBox = new Vector();
        $this->boundingBox->allocate(9);
        $this->boundingBox->insert(self::BB_CENTER_X, $centerX);
        $this->boundingBox->insert(self::BB_CENTER_Y, $centerY);
        $this->boundingBox->insert(self::BB_WIDTH, $width);
        $this->boundingBox->insert(self::BB_HEIGHT, $height);
        $pointsArray = new Vector();
        $pointsArray->allocate($this->maxPoints);
        $this->boundingBox->insert(self::BB_POINTS, $pointsArray);
    }

    private function startX(Vector $boundingBox) {
        return $boundingBox[self::BB_CENTER_X] - $boundingBox[self::BB_WIDTH] / 2;
    }

    private function startY(Vector $boundingBox) {
        return $boundingBox[self::BB_CENTER_Y] - $boundingBox[self::BB_HEIGHT] / 2;
    }

    private function endX(Vector $boundingBox) {
        return $boundingBox[self::BB_CENTER_X] + $boundingBox[self::BB_WIDTH] / 2;
    }

    private function endY(Vector $boundingBox) {
        return $boundingBox[self::BB_CENTER_Y] + $boundingBox[self::BB_HEIGHT] / 2;
    }

    private function isXinRange($x, Vector $boundingBox) {
        return $x >= $this->startX($boundingBox) && $x <= $this->endX($boundingBox);
    }

    private function isYinRange($y, Vector $boundingBox) {
        return $y >= $this->startY($boundingBox) && $y <= $this->endY($boundingBox);
    }

    private function containsPoint($x, $y, Vector $boundingBox) {
        return $this->isXinRange($x, $boundingBox) && $this->isYinRange($y, $boundingBox);
    }

    private function intersects(Vector $thatBoundingBox, Vector $myBoundingBox) {
        return
            $this->isXinRange($this->startX($thatBoundingBox), $myBoundingBox) ||
            $this->isXinRange($this->endX($thatBoundingBox), $myBoundingBox) ||
            $this->isYinRange($this->startY($thatBoundingBox), $myBoundingBox) ||
            $this->isYinRange($this->endY($thatBoundingBox), $myBoundingBox);
    }

    private function encompasses(Vector $thatBoundingBox, Vector $myBoundingBox) {
        return
            $this->startX($thatBoundingBox) <= $this->startX($myBoundingBox) &&
            $this->endX($thatBoundingBox) >= $this->endX($myBoundingBox) &&
            $this->startY($thatBoundingBox) <= $this->startY($myBoundingBox) &&
            $this->endY($thatBoundingBox) >= $this->endY($myBoundingBox);
    }

    private function subdivide(Vector $boundingBox) {
        $width = $boundingBox[self::BB_WIDTH] / 2;
        $height = $boundingBox[self::BB_HEIGHT] / 2;
        $centerX = $boundingBox[self::BB_CENTER_X];
        $centerY = $boundingBox[self::BB_CENTER_Y];

        $nw = new Vector();
        $nw->allocate(9);
        $nw->insert(self::BB_CENTER_X, $centerX - $width / 2);
        $nw->insert(self::BB_CENTER_Y, $centerY + $height / 2);
        $nw->insert(self::BB_WIDTH, $width);
        $nw->insert(self::BB_HEIGHT, $height);
        $nwPoints = new Vector();
        $nwPoints->allocate($this->maxPoints);
        $nw->insert(self::BB_POINTS, $nwPoints);
        $boundingBox->insert(self::BB_NORTH_WEST, $nw);

        $ne = new Vector();
        $ne->allocate(9);
        $ne->insert(self::BB_CENTER_X, $centerX + $width / 2);
        $ne->insert(self::BB_CENTER_Y, $centerY + $height / 2);
        $ne->insert(self::BB_WIDTH, $width);
        $ne->insert(self::BB_HEIGHT, $height);
        $nePoints = new Vector();
        $nePoints->allocate($this->maxPoints);
        $ne->insert(self::BB_POINTS, $nePoints);
        $boundingBox->insert(self::BB_NORTH_EAST, $ne);

        $sw = new Vector();
        $sw->allocate(9);
        $sw->insert(self::BB_CENTER_X, $centerX - $width / 2);
        $sw->insert(self::BB_CENTER_Y, $centerY - $height / 2);
        $sw->insert(self::BB_WIDTH, $width);
        $sw->insert(self::BB_HEIGHT, $height);
        $swPoints = new Vector();
        $swPoints->allocate($this->maxPoints);
        $sw->insert(self::BB_POINTS, $swPoints);
        $boundingBox->insert(self::BB_SOUTH_WEST, $sw);

        $se = new Vector();
        $se->allocate(9);
        $se->insert(self::BB_CENTER_X, $centerX + $width / 2);
        $se->insert(self::BB_CENTER_Y, $centerY - $height / 2);
        $se->insert(self::BB_WIDTH, $width);
        $se->insert(self::BB_HEIGHT, $height);
        $sePoints = new Vector();
        $sePoints->allocate($this->maxPoints);
        $se->insert(self::BB_POINTS, $sePoints);
        $boundingBox->insert(self::BB_SOUTH_EAST, $se);
    }

    private function _insert(Vector $newPoint, Vector $boundingBox) {
        if (!$this->containsPoint($newPoint[0], $newPoint[1], $boundingBox)) {
            return false;
        }

        /* @var Vector $pointsArray */
        $pointsArray = $boundingBox[self::BB_POINTS];

        if ($pointsArray->count() < $this->maxPoints) {
            $pointsArray->push($newPoint);
            return true;
        } elseif ($boundingBox->count() === self::BB_NORTH_WEST) {
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
        $newPoint = new Vector();
        $newPoint->allocate(3);
        $newPoint->insert(0, $x);
        $newPoint->insert(1, $y);
        $newPoint->insert(2, $extraData);

        return $this->_insert($newPoint, $this->boundingBox);
    }

    private function _search(Vector $thatBoundingBox, Vector $myBoundingBox)
    {
        $results = [];
        if ($this->encompasses($thatBoundingBox, $myBoundingBox) ||
            $this->intersects($thatBoundingBox, $myBoundingBox))
        {
            foreach ($myBoundingBox[self::BB_POINTS] as $point) {
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
        $searchBoundingBox = new Vector();
        $searchBoundingBox->allocate(4);
        $searchBoundingBox->insert(self::BB_CENTER_X, $centerX);
        $searchBoundingBox->insert(self::BB_CENTER_Y, $centerY);
        $searchBoundingBox->insert(self::BB_WIDTH, $width);
        $searchBoundingBox->insert(self::BB_HEIGHT, $height);

        return $this->_search($searchBoundingBox, $this->boundingBox);
    }

}
