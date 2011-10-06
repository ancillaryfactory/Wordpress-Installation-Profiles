<?php
$file = 'profiles/' . $_GET['file'];

/*
header('Content-disposition: attachment; filename='.$file);
header('Content-type: application/octet-stream');
readfile($file);
*/

if(!file_exists($file)) {
    die('Error: File not found.');
} else {
     // Set headers
     header("Cache-Control: public");
     header("Content-Description: File Transfer");
     header("Content-Disposition: attachment; filename=$file");
     header("Content-Type: application/octet-stream");
     header("Content-Transfer-Encoding: binary");
    
     // Read the file from disk
     readfile($file);
 }


?> 