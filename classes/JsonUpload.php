
<?php
// one class handles reading + validating the json file that's uploaded
class JsonUpload {
    public function read(array $pickedFile): array {
        if (!isset($pickedFile['error']) || $pickedFile['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('Upload failed. Please choose a JSON file and try again.');
        }

        if ($pickedFile['size'] <= 0 || $pickedFile['size'] > 5 * 1024 * 1024) {
            throw new RuntimeException("The file can't be empty or larger than 5 MB.");
        }

        $ext = strtolower(pathinfo($pickedFile['name'], PATHINFO_EXTENSION));
        if ($ext !== 'json') {
            throw new RuntimeException('Please upload a file with the .json extension.');
        }

        if (!is_uploaded_file($pickedFile['tmp_name'])) {
            throw new RuntimeException("The uploaded file couldn't be verified.");
        }

        $rawJson = file_get_contents($pickedFile['tmp_name']);
        if ($rawJson === false) {
            throw new RuntimeException("Couldn't read the uploaded file.");
        }

        // json_decode converts json text into php arrays
        $geoRows = json_decode($rawJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON: ' . json_last_error_msg());
        }

        if (!is_array($geoRows) || !array_is_list($geoRows)) {
            throw new RuntimeException('JSON must have an array of country-year records.');
        }

        if (count($geoRows) < 1000) {
            throw new RuntimeException('The dataset must have at least 1,000 records.');
        }

        foreach ($geoRows as $entry) {
            if (!is_array($entry) || !isset($entry['country'], $entry['continent'], $entry['year'], $entry['lifeExp'], $entry['pop'], $entry['gdpPercap'])
                || !is_string($entry['country']) || !is_string($entry['continent'])
                || !is_int($entry['year']) || !is_numeric($entry['lifeExp']) || !is_numeric($entry['pop']) || !is_numeric($entry['gdpPercap'])
                || !is_finite((float) $entry['lifeExp']) || !is_finite((float) $entry['pop']) || !is_finite((float) $entry['gdpPercap'])) {
                throw new RuntimeException('Dataset has missing or invalid Gapminder fields.');
            }
        }

        return $geoRows;
    }
}
