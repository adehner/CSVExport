<?php
$title = option('site_title');
$title = urlencode($title);
// date_default_timezone_set('America/Los_Angeles');

$delimiter = get_option('csv_export_delimiter') ?: ',';
if ($delimiter === 'tab') $delimiter = "\t";
$enclosure = get_option('csv_export_enclosure') ?: '"';

if ($search) {
    $fileName = $title . 'Export' . date("Y-m-d") .'T' . date("H:i:s") . '.csv';
} else {
    $fileName = $title . 'FullExport' . date("Y-m-d") . 'T' . date("H:i:s") . '.csv';
}

header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header('Content-Description: File Transfer');
header("Content-type: text/csv");
header("Content-Disposition: attachment; filename={$fileName}");
header("Expires: 0");
header("Pragma: public");

$file = fopen( 'php://output', 'w' );

$headers = reset($result);
fputcsv($file, array_keys($headers), $delimiter, $enclosure);

foreach ($result as $data) {
    fputcsv($file, $data, $delimiter, $enclosure);
}

fclose($file);
exit;
