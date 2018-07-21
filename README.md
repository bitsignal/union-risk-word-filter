# union-risk-word-filter
基于TrieTree实现的中文敏感词检测，对中英文（UTF-8格式）语句实现单个词、组合词的检测。

# 词库定义

词库中包含单个词、组合词。

**单个词**：

语句中包含了该词，即为命中。词中不可有'|'符号。

```
例：
中国

"其中，美国暂未表态" -- 未包含
"有很多中国人参加了此次盛会"  --包含

```

**组合词**：

用'|'分隔的多个单个词，语句中必须全部包含所有的单个词才算命中。

```
例：
中国|世界杯|夺冠

"对于世界杯，中国元素可谓是不少" -- 未包含，仅有'中国'、'世界杯'，没有'夺冠'
"中国力争在下次世界杯上实现夺冠"  --包含
```

# 示例
```
词库：
$wordArr = [
    '中国',
    '阿根廷',
    '足球|世界杯',
    '足球|德国'
];
待检测语句：
$title = '中国和德国踢足球，争夺世界杯';

//建立TrieTree
$svc = new \App\Service\RiskWordFilterService;
$wordTrieTree  = $svc->buildWordTree($wordArr);
//输出TrieTree
var_dump(json_encode($wordTrieTree));
/*
output:
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

//检测语句
$filterWords = $svc->searchWord($title);
//输出检测结果
var_dump($filterWords);exit;
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

//结果分析
该语句包含了单个词： '中国'，包含的组合词：'足球|德国' 和 '足球|世界杯'

```

# 注意
+ 对英文的大小写不敏感，且按照单个字母进行对比。
+ 暂未实现动态增删词库，可异步提前建立字典树。

# 待实现 
+ 对英文的处理优化：大小写敏感可配置，英文单词级别对比（多种形式）。
+ 对中文的拼音检查，例如：足qiu。
+ 可定义组合词符号。
+ 动态增删词。
+ 待检测语句的整理：UTF-8检查、特殊符号检查、标点符号过滤。
+ 重叠词的处理：待定。例如：'中国国人' 中检查'中国人'
 
