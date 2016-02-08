<?php

    list(, $class, $longitude, $latitude, $width, $height) = $argv + array(NULL, 'ArrayQuadTree', -2.5, 55, 9, 10);
    include(__DIR__."/$class.php");

    $startTime = microtime(true);

    /* @var $quadTree ArrayQuadTree */
    $quadTree = new $class(0, 0, 360, 180);

    echo "Loading cities: ";
    $cityFile = new \SplFileObject('/mb-quadtrees/data/citylist.csv');
    $cityFile->setFlags(\SplFileObject::READ_CSV | \SplFileObject::DROP_NEW_LINE | \SplFileObject::SKIP_EMPTY);

    //  Populate our new QuadTree with cities from around the world
    $cityCount = 0;
    foreach($cityFile as $cityData) {
        if (!empty($cityData[0])) {
            if ($cityCount % 1000 == 0) echo '.';
            $quadTree->insert($cityData[3], $cityData[2], [$cityData[0], $cityData[1]]);
            $cityCount++;
        }
    }
    echo PHP_EOL, "Added $cityCount cities to $class", PHP_EOL;

    $endTime = microtime(true);

    $callTime = $endTime - $startTime;

    echo 'Load Time: ', sprintf('%.4f',$callTime), ' s', PHP_EOL;
    echo 'Current Memory: ', sprintf('%.2f',(memory_get_usage(false) / 1024 )), ' k', PHP_EOL;
    echo 'Peak Memory: ', sprintf('%.2f',(memory_get_peak_usage(false) / 1024 )), ' k', PHP_EOL, PHP_EOL;

    $startTime = microtime(true);

    $searchResult = $quadTree->search((float) $longitude, (float) $latitude, (float) $width, (float) $height);

    usort(
        $searchResult,
        function($a, $b) {
            return strnatcmp($a[2][1], $b[2][1]);
        }
    );

    //  Display the results
    echo 'Cities in range', PHP_EOL;

    if (empty($searchResult)) {
        echo 'No matches found', PHP_EOL;
    } else {
        foreach($searchResult as list($x, $y, list($country, $city))) {
            echo '    ', $city, ', ',
            $country, ' => Lat: ',
            sprintf('%+07.2f', $y), ' Long: ',
            sprintf('%+07.2f', $x), PHP_EOL;
        }
    }
    echo PHP_EOL;

    $endTime = microtime(true);
    $callTime = $endTime - $startTime;

    echo 'Search Time: ', sprintf('%.4f',$callTime), ' s', PHP_EOL;
    echo 'Current Memory: ', sprintf('%.2f',(memory_get_usage(false) / 1024 )), ' k', PHP_EOL;
    echo 'Peak Memory: ', sprintf('%.2f',(memory_get_peak_usage(false) / 1024 )), ' k', PHP_EOL;
