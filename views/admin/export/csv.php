<?php
$title = option('site_title');
$title = urlencode($title);
date_default_timezone_set('America/Los_Angeles');

if ($search)
{
	
	$fileName = $title.'Export'.date("Y-m-d").'T'.date("H:i:s").'.csv';
}
else
{
	$fileName = $title.'FullExport'.date("Y-m-d").'T'.date("H:i:s").'.csv';
}

header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header('Content-Description: File Transfer');
header("Content-type: text/csv");
header("Content-Disposition: attachment; filename={$fileName}");
header("Expires: 0");
header("Pragma: public");
 
$file = fopen( 'php://output', 'w' );

 
$header = false;
 
foreach ( $result as $data ) 
{
    if ( !$header ) 
    {
        fputcsv($file, array_keys($data), ',', '"');
        $header = true;
    }
 
    fputcsv($file, $data, ',', '"');
}

fclose($file);
exit;

?>