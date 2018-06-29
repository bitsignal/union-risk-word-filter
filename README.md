# union-risk-word-filter
基于TrieTree实现的中文敏感词检测，对中英文（UTF-8格式）语句实现单个词、组合词的检测。

组合词：用'|'分隔，必须全部包含。

## 词示例
```
中国
足球|世界杯
```

# 注意
使用异步任务建立TrieTree

# 使用示例
```
$wordArr = [
    '中国',
    '中国|世界杯',
    '单Asd词',
    '国家|足球',
    '国家|法国',
    '国家队|意大利'
];

//建立TrieTree
$svc = new \App\Service\RiskWordFilterService;
$wordTrieTree  = $svc->buildWordTree('words_database_type', $wordArr);

$title = '中国和德国踢足球';
$filterWords = $svc->searchWord($title);
var_dump($filterWords);exit;
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
```

# 功能备注 
1. 使用Trie树建立索引
2. 处理单个词、组合词
3. 动态增删词【延后】
4. 支持多个词库【延后】
5. 支持单词优先、组合词优先【统一处理，不区分】
6. 词库处理【1.单词格式、组合词格式。2.单词的重复。3.单词与组合词的重复。4.组合词与组合词的重复】
7. 组合词的频度调整，词下面的组合词量级均衡
8. 英文的大小写处理【统一转小写】
