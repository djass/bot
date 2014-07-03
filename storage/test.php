<?php
ini_set('display_errors', 1);
include 'simple_html_dom.php';
$html = file_get_html('http://windows.microsoft.com/fr-fr/windows-vista/tips-for-solving-problems-with-usb-devices');

// Find all article blocks
foreach($html->find('div.procedure') as $procedure) { 
    $procedures[] = $procedure;
}
echo "<pre>";
print_r($procedures);

echo "</pre>";
