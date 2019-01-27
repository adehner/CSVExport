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

if (get_option('csv_export_single_value')) {
    $headersList = array();
    $allElements = (bool) get_option('csv_export_all_elements');
    if ($allElements) {
        foreach ($headers as $header => $count) {
            // Keep at least one header, even if the column is empty.
            $headersList[] = $header;
            for ($i = 1; $i < $count; $i++) {
                $headersList[] = $header;
            }
        }
    } else {
        foreach ($headers as $header => $count) {
            for ($i = 1; $i <= $count; $i++) {
                $headersList[] = $header;
            }
        }
    }

    fputcsv($file, $headersList, $delimiter, $enclosure);

    foreach ($result as $data) {
        $row = array();
        foreach ($data as $header => $values) {
            if (in_array($header, $headersList)) {
                $values = array_values($values);
                $max = $headers[$header];
                for ($i = 0; $i < $max; $i++) {
                    $row[] = isset($values[$i]) ? $values[$i] : null;
                }
            }
        }
        fputcsv($file, $row, $delimiter, $enclosure);
    }
} else {
    $headers = reset($result);
    fputcsv($file, array_keys($headers), $delimiter, $enclosure);

    foreach ($result as $data) {
        fputcsv($file, $data, $delimiter, $enclosure);
    }
}

fclose($file);
exit;
