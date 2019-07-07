<?php

if (is_readable('vendor/autoload.php')) {
    require 'vendor/autoload.php';
} else {
    require_once('IO/Zip.php');
}
$options = getopt("f:hvtdR");

if ((isset($options['f']) === false) || (($options['f'] !== "-") && is_readable($options['f']) === false)) {
    fprintf(STDERR, "Usage: php zipdump.php -f <zip_file> [-h]\n");
    fprintf(STDERR, "ex) php zipdump.php -f input.zip -h \n");
    exit(1);
}

$filename = $options['f'];
if ($filename === '-')  {
   $filename = 'php://stdin';
}
$opts['hexdump'] = isset($options['h']);

$zlibdata = file_get_contents($filename);
$zip = new IO_Zip();
$zip->parse($zlibdata);
$zip->dump();
