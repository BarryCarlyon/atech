<?php

$response = '<201>
  <default-branch nil="true"></default-branch>
  <disk-usage type="integer">0</disk-usage>
  <last-commit-ref nil="true"></last-commit-ref>
  <clone-url>git@codebasehq.com:barrycarlyon/test/steve-6.git</clone-url>
  <name>steve</name>
  <permalink>steve-6</permalink>
  <scm>git</scm>
  <source nil="true"></source>
  <sync nil="true"></sync>
  <last-sync-at nil="true"></last-sync-at>
</201>';

//$response = str_replace('201', 'cake', $response);
$string = simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);
print_r($string) . "\n";
//var_dump($string) . "\n";
