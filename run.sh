#!/usr/bin/env bash

cd $(dirname $0)

if ! docker images | grep -q 'quadtree-test'; then
    docker build -f docker/php-5.6/Dockerfile -t quadtree-test:5.6 .
    docker build -f docker/php-7/Dockerfile -t quadtree-test:7 .
fi

HERE=$(pwd)

tput setaf 1; echo "PHP 5.6: OOP implementation by MarkBaker"; tput sgr0
docker run -it --rm -v "$HERE/src:/quadtrees" quadtree-test:5.6 bash -c \
    'cd /mb-quadtrees/examples/; php citySearch.php 50 50 3 3'

tput setaf 1; echo "PHP 5.6: Array-based implementation"; tput sgr0
docker run -it --rm -v "$HERE/src:/quadtrees" quadtree-test:5.6 bash -c \
    'cd /quadtrees/; php testCitySearch.php ArrayQuadTree 50 50 3 3'

tput setaf 1; echo "PHP 5.6: SplFixedArray-based implementation"; tput sgr0
docker run -it --rm -v "$HERE/src:/quadtrees" quadtree-test:5.6 bash -c \
    'cd /quadtrees/; php testCitySearch.php SplQuadTree 50 50 3 3'

tput setaf 1; echo "PHP 7: OOP implementation by MarkBaker"; tput sgr0
docker run -it --rm -v "$HERE/src:/quadtrees" quadtree-test:7 bash -c \
    'cd /mb-quadtrees/examples/; php citySearch.php 50 50 3 3'

tput setaf 1; echo "PHP 7: Array-based implementation"; tput sgr0
docker run -it --rm -v "$HERE/src:/quadtrees" quadtree-test:7 bash -c \
    'cd /quadtrees/; php testCitySearch.php ArrayQuadTree 50 50 3 3'

tput setaf 1; echo "PHP 7: SplFixedArray-based implementation"; tput sgr0
docker run -it --rm -v "$HERE/src:/quadtrees" quadtree-test:7 bash -c \
    'cd /quadtrees/; php testCitySearch.php SplQuadTree 50 50 3 3'

tput setaf 1; echo "PHP 7: \\Ds\\Vector-based implementation"; tput sgr0
docker run -it --rm -v "$HERE/src:/quadtrees" quadtree-test:7 bash -c \
    'cd /quadtrees/; php testCitySearch.php VectorQuadTree 50 50 3 3'

