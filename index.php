<?php
require_once __DIR__ . '/classes/JsonUpload.php';
require_once __DIR__ . '/classes/GapminderAnalyzer.php';

$geoQs = [
    'How many country-year records are in the uploaded dataset?',
    'How many unique countries appear in the dataset?',
    'What is the average life expectancy across all countries in 2007?',
    'What is the median life expectancy across all countries in 2007?',
    'Which country had the highest life expectancy in 2007?',
    'Which country had the highest GDP per capita in 2007?',
    'What was the total population of Africa in 2007?',
    'What percentage of countries had life expectancy of at least 70 years in 2007?',
    'Which continent had the highest average life expectancy in 2007?',
    'How much did the average life expectancy in Asia change between 1952 and 2007?'
];

// htmlspecialchars escapes all dynamically displayed text to prevent HTML injection.
function escapeText(string $safeText): string {
    return htmlspecialchars($safeText, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$ans = [];
$msg = 'Choose the Gapminder JSON dataset, then click Analyze Dataset.';
$hasErr = false;
$tmestmp = null;
$runMs = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $t0 = microtime(true);
    $tmestmp = date('Y-m-d H:i:s T');
    try {
        if (!isset($_FILES['dataset'])) {
            throw new RuntimeException('No file was selected.');
        }
        $jsonReader = new JsonUpload();
        $geoRows = $jsonReader->read($_FILES['dataset']);
        $geoStats = new GapminderAnalyzer($geoRows);
        $ans = $geoStats->analyze();
        $rowCnt = count($geoRows);
        $msg = "Successfully analyzed $rowCnt country-year records.";
    } catch (Throwable $problem) {
        $hasErr = true;
        $msg = $problem->getMessage();
    }
    $runMs = (microtime(true) - $t0) * 1000;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gapminder | PHP Data Analysis</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main>
    <header><p class="eyebrow">SWE 432 · Homework 3</p><h1>Explore the World <span>✦</span></h1>
        <p class="subtitle">Upload the public Gapminder JSON dataset to explore population, health, and economic trends around the world.</p></header>
    <section class="upload-panel">
        <h2>Upload your dataset</h2>
        <form method="post" enctype="multipart/form-data">
            <label for="dataset">Choose a JSON file</label>
            <input id="dataset" name="dataset" type="file" accept=".json,application/json" required>
            <button type="submit">Analyze Dataset</button>
        </form>
        <p class="notice <?php echo $hasErr ? 'error' : ''; ?>" role="status"><?php echo escapeText($msg); ?></p>
    </section>
    <section class="processing" aria-label="Processing information">
        <h2>Processing information</h2>
        <p><strong>Timestamp:</strong> <?php echo $tmestmp === null ? 'No file processed yet' : escapeText($tmestmp); ?></p>
        <p><strong>Processing time:</strong> <?php echo $runMs === null ? '—' : escapeText(number_format($runMs, 3)) . ' milliseconds'; ?></p>
    </section>
    <section class="results"><h2>Questions &amp; statistics</h2>
        <?php foreach ($geoQs as $qIdx => $geoQ): ?>
        <article class="question"><div class="q-number"><?php echo $qIdx + 1; ?></div>
            <div><h3><?php echo escapeText($geoQ); ?></h3>
                <p class="answer"><?php echo isset($ans[$qIdx]) ? escapeText((string)$ans[$qIdx]) : 'Upload a dataset to calculate'; ?></p>
            </div></article>
        <?php endforeach; ?>
    </section>
    <footer>Data source: Plotly / Gapminder · Analysis performed on the server with PHP</footer>
</main>
</body>
</html>
