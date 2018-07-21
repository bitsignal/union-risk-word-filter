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
var_dump(json_encode($wordTrieTree));
/*
{
    "中": {
        "child": {
            "国": {
                "child": [],
                "is_end": true,
                "word": "中国",
                "relate": [
                    []
                ]
            }
        },
        "is_end": false
    },
    "阿": {
        "child": {
            "根": {
                "child": {
                    "廷": {
                        "child": [],
                        "is_end": true,
                        "word": "阿根廷",
                        "relate": [
                            []
                        ]
                    }
                },
                "is_end": false
            }
        },
        "is_end": false
    },
    "足": {
        "child": {
            "球": {
                "child": [],
                "is_end": true,
                "word": "足球",
                "relate": [
                    {
                        "1": "世界杯"
                    },
                    {
                        "1": "德国"
                    }
                ]
            }
        },
        "is_end": false
    },
    "世": {
        "child": {
            "界": {
                "child": {
                    "杯": {
                        "child": [],
                        "is_end": true,
                        "word": "世界杯",
                        "relate": [
                            [
                                "足球"
                            ]
                        ]
                    }
                },
                "is_end": false
            }
        },
        "is_end": false
    },
    "德": {
        "child": {
            "国": {
                "child": [],
                "is_end": true,
                "word": "德国",
                "relate": [
                    [
                        "足球"
                    ]
                ]
            }
        },
        "is_end": false
    }
}
*/

$title = '中国和德国踢足球，争夺世界杯';
$filterWords = $svc->searchWord($title);
var_dump(json_encode($filterWords));exit;
/*
output:
{
    "中国": "中国",
    "德国,足球": [
        "德国",
        "足球"
    ],
    "世界杯,足球": [
        "世界杯",
        "足球"
    ]
}
*/
