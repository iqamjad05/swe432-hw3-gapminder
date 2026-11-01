<?php
// stores uploaded records and shows shared calculations
class StatisticsCalculator {
    protected array $geoRows;

    public function __construct(array $geoRows) {
        $this->records = $geoRows;
    }

    protected function average(array $vals): float {
        if (count($vals) === 0) {
            throw new RuntimeException('Can''t calculate average of an empty group.');
        }
        $sum = 0;
        foreach ($vals as $val) {
            $sum += $val;
        }
        return $sum / count($vals);
    }

    protected function median(array $vals): float {
        if (count($vals) === 0) {
            throw new RuntimeException('Can''t calculate median of an empty group.');
        }
        sort($vals, SORT_NUMERIC);
        $mid = intdiv(count($vals), 2);
        if (count($vals) % 2 === 1) {
            return (float) $vals[$mid];
        }
        return ($vals[$mid - 1] + $vals[$mid]) / 2;
    }

    protected function formatNumber(float|int $num, int $places = 2): string {
        return number_format($num, $places, '.', ',');
    }
}
