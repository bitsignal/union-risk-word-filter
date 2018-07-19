<?php
/*
* author bitsignal
* 2018/6/29
*/

require "../src/RiskWordFilterService.php";

//敏感词表
$dataFile = 'black_words.txt';

$file = fopen($dataFile, 'r');
$wordArr = [];
while(!feof($file)){
    $line = trim(fgets($file));
    if(empty($line)){continue;}

    $wordArr[] = $line;
}
fclose($file);


//建立TrieTree
$svc = new \App\Service\RiskWordFilterService;
$wordTrieTree  = $svc->buildWordTree($wordArr);

$title = '中国和德国踢足球';
$filterWords = $svc->searchWord($title);
/*
array(2) {
  ["中国"]=>
  string(6) "中国"
  ["德国,足球"]=>
  array(2) {
    [0]=>
    string(6) "德国"
    [1]=>
    string(6) "足球"
  }
}
*/
var_dump($filterWords);exit;