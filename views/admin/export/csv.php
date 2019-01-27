<?php
/**
 * @var bool $search
 * @var array $headers
 * @var array $result
 * @var string $title
 */

$delimiter = get_option('csv_export_delimiter') ?: ',';
if ($delimiter === 'tab') {
    $delimiter = "\t";
    $extension = 'tsv';
    $mediaType = 'text/tab-separated-values';
} else {
    $extension = 'csv';
    $mediaType = 'text/csv';
}
$enclosure = get_option('csv_export_enclosure') ?: '"';

$title = option('site_title');
$title = preg_replace('/[^A-Za-z0-9-]/', '_', $title);
$title = preg_replace('/_+/', '_', $title);
$title = substr($title, 0, 16);
$title = urlencode($title);

// date_default_timezone_set('America/Los_Angeles');
$fileName = $title . '-' . ($search ? 'Export' : 'Full_Export') . '-' . date('Ymd-His') . '.' . $extension;

header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Content-Description: File Transfer');
header('Content-type: ' . $mediaType);
header('Content-Disposition: attachment; filename=' . $fileName);
header('Expires: 0');
header('Pragma: public');

$file = fopen('php://output', 'w');

fputcsv($file, $headers, $delimiter, $enclosure);

foreach ($result as $data) {
    fputcsv($file, $data, $delimiter, $enclosure);
}

fclose($file);
exit;
