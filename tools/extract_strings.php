<?php

if (php_sapi_name() != 'cli') exit();

require('../../../components.php');

$load = OBFLoad::get_instance();
$db = OBFDB::get_instance();

$dir_js = 'js/';
$dir_php_1 = 'controllers/';
$dir_php_2 = 'models/';
$dir_html = 'html/';

$t_strings = [];

extract_slashslasht_matches($dir_js, $t_strings);
extract_slashslasht_matches($dir_php_1, $t_strings);
extract_slashslasht_matches($dir_php_2, $t_strings);
extract_datat_matches($dir_html, $t_strings);

$t_strings = array_values(array_unique($t_strings));

var_dump($t_strings);

function extract_slashslasht_matches ($directory, &$t_strings) {
  foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
    if (!$file->isFile()) continue;
    if ($file->getExtension() != 'php' && $file->getExtension() != 'js') continue;

    $contents = file_get_contents($file->getPathname());
    $matches = array();
    preg_match_all('|//T .*|', $contents, $matches);
    foreach ($matches[0] as $match) {
      $t_strings[] = trim(substr($match, 4));
    }
  }
}

function extract_datat_matches ($directory, &$t_strings) {
  foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file) {
    if (!$file->isFile()) continue;
    if ($file->getExtension() != 'html' && $file->getExtension() != 'htm') continue;

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML(file_get_contents($file->getPathname()));
    $xpath = new DOMXpath($dom);
    foreach ($xpath->query('//*[@data-t]') as $element) {
      $t_strings[] = trim($element->textContent);
    }
  }
}
