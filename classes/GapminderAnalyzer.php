<?php
require_once __DIR__ . '/StatisticsCalculator.php';

// has the ten gapminder questions and their answers.
class GapminderAnalyzer extends StatisticsCalculator {
    public function analyze(): array {
        $rows07 = array_values(array_filter($this->records, function ($entry) {
            return $entry['year'] === 2007;
        }));
        $rows52 = array_values(array_filter($this->records, function ($entry) {
            return $entry['year'] === 1952;
        }));
        if (count($rows07) === 0 || count($rows52) === 0) {
            throw new RuntimeException('The uploaded dataset needs records for 1952 and 2007.');
        }

        $countryList = array_map(function ($entry) { return $entry['country']; }, $this->records);
        $lifeVals07 = array_map(function ($entry) { return $entry['lifeExp']; }, $rows07);
        $ans = [];
        array_push($ans, $this->formatNumber(count($this->records), 0));
        array_push($ans, $this->formatNumber(count(array_unique($countryList)), 0));
        array_push($ans, $this->formatNumber($this->average($lifeVals07)) . ' years');
        array_push($ans, $this->formatNumber($this->median($lifeVals07)) . ' years');

        $bestLife = $rows07[0];
        $bestGdp = $rows07[0];
        foreach ($rows07 as $entry) {
            if ($entry['lifeExp'] > $bestLife['lifeExp']) $bestLife = $entry;
            if ($entry['gdpPercap'] > $bestGdp['gdpPercap']) $bestGdp = $entry;
        }
        array_push($ans, $bestLife['country'] . ' (' . $this->formatNumber($bestLife['lifeExp']) . ' years)');
        array_push($ans, $bestGdp['country'] . ' ($' . $this->formatNumber($bestGdp['gdpPercap']) . ')');

        $africaRows = array_values(array_filter($rows07, function ($entry) {
            return $entry['continent'] === 'Africa';
        }));
        $africaPop = 0;
        // sum Africa's population.
        for ($i = 0; $i < count($africaRows); $i++) {
            $africaPop += $africaRows[$i]['pop'];
        }
        array_push($ans, $this->formatNumber($africaPop, 0) . ' people');

        $lifeOver70 = array_filter($rows07, function ($entry) {
            return $entry['lifeExp'] >= 70;
        });
        array_push($ans, $this->formatNumber(count($lifeOver70) / count($rows07) * 100) . '%');

        // groups countries by continent then compare unweighted country avgs
        $continentBuckets = [];
        foreach ($rows07 as $entry) {
            $region = $entry['continent'];
            if (!isset($continentBuckets[$region])) $continentBuckets[$region] = [];
            array_push($continentBuckets[$region], $entry['lifeExp']);
        }
        $topRegion = '';
        $topAvg = -1;
        foreach ($continentBuckets as $region => $lifeVals) {
            $regionAvg = $this->average($lifeVals);
            if ($regionAvg > $topAvg) {
                $topRegion = $region;
                $topAvg = $regionAvg;
            }
        }
        array_push($ans, $topRegion . ' (' . $this->formatNumber($topAvg) . ' years)');

        $asiaVals52 = array_map(function ($entry) { return $entry['lifeExp']; }, array_filter($rows52, function ($entry) {
            return $entry['continent'] === 'Asia';
        }));
        $asiaVals07 = array_map(function ($entry) { return $entry['lifeExp']; }, array_filter($rows07, function ($entry) {
            return $entry['continent'] === 'Asia';
        }));
        $lifeChange = $this->average($asiaVals07) - $this->average($asiaVals52);
        array_push($ans, $this->formatNumber($lifeChange) . ' years (' . ($lifeChange >= 0 ? 'increase' : 'decrease') . ')');
        return $ans;
    }
}
