# QuadTreeBenchmark

This was an experiment I did to find the fastest (and most memory efficient) QuadTree implementation in PHP. I theorized
that an array-based implementation would beat the OOP implementation
in [MarkBaker/QuadTrees](https://github.com/MarkBaker/QuadTrees), but i proved myself thoroughly wrong.

### Test result

This is the output I get on my laptop:

**PHP 5.6: OOP implementation by MarkBaker**

```
Loading cities: .......................
Added 22977 cities to QuadTree
Load Time: 1.2774 s
Current Memory: 25556.29 k
Peak Memory: 25574.85 k

Cities in range
    Latitude: +48.500000 -> +51.500000
    Longitude: +48.500000 -> +51.500000

    Kazakhstan, Oral => Lat: +051.23 Long: +051.37

Search Time: 0.0062 s
Current Memory: 25557.91 k
Peak Memory: 25574.85 k
```

**PHP 5.6: Array-based implementation**

```
Loading cities: .......................
Added 22977 cities to ArrayQuadTree
Load Time: 1.3734 s
Current Memory: 42477.94 k
Peak Memory: 42486.11 k

Cities in range
    Kazakhstan, Oral => Lat: +051.23 Long: +051.37

Search Time: 0.0062 s
Current Memory: 42479.28 k
Peak Memory: 42491.25 k
```

**PHP 5.6: SplFixedArray-based implementation**

```
Loading cities: .......................
Added 22977 cities to SplQuadTree
Load Time: 2.2058 s
Current Memory: 73684.83 k
Peak Memory: 73692.92 k

Cities in range
    Kazakhstan, Oral => Lat: +051.23 Long: +051.37

Search Time: 0.0143 s
Current Memory: 73686.18 k
Peak Memory: 73698.30 k
```

**PHP 7: OOP implementation by MarkBaker**

```
Loading cities: .......................
Added 22977 cities to QuadTree
Load Time: 0.5745 s
Current Memory: 12382.30 k
Peak Memory: 12400.04 k

Cities in range
    Latitude: +48.500000 -> +51.500000
    Longitude: +48.500000 -> +51.500000

    Kazakhstan, Oral => Lat: +051.23 Long: +051.37

Search Time: 0.0024 s
Current Memory: 12382.86 k
Peak Memory: 12400.04 k
```

**PHP 7: Array-based implementation**

```
Loading cities: .......................
Added 22977 cities to ArrayQuadTree
Load Time: 0.6627 s
Current Memory: 29960.22 k
Peak Memory: 29961.24 k

Cities in range
    Kazakhstan, Oral => Lat: +051.23 Long: +051.37

Search Time: 0.0028 s
Current Memory: 29960.61 k
Peak Memory: 29962.61 k
```

**PHP 7: SplFixedArray-based implementation**

```
Loading cities: .......................
Added 22977 cities to SplQuadTree
Load Time: 1.1465 s
Current Memory: 44436.87 k
Peak Memory: 44437.89 k

Cities in range
    Kazakhstan, Oral => Lat: +051.23 Long: +051.37

Search Time: 0.0052 s
Current Memory: 44437.26 k
Peak Memory: 44439.24 k
```

**PHP 7: \Ds\Vector-based implementation**

```
Loading cities: .......................
Added 22977 cities to VectorQuadTree
Load Time: 0.8049 s
Current Memory: 24487.13 k
Peak Memory: 24488.16 k

Cities in range
    Kazakhstan, Oral => Lat: +051.23 Long: +051.37

Search Time: 0.0032 s
Current Memory: 24487.52 k
Peak Memory: 24489.40 k
```
