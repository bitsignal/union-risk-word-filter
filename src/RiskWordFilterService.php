<?php
/**
 * author bitsignal 20180628
*/

namespace App\Service;

/**
 * 支持UTF-8中文的敏感词检测
 * Class RiskWordFilterService
 * @package App\Service
*/
class RiskWordFilterService{

	//词典树
	private $trieTree = [];

	/**
	 * 建立索引树
	 * @param $wordArr array 词条数组
	 * @return boolean|array
	 */
	public function buildWordTree( $wordArr ){
		if( empty($wordArr) ){
			return false;
		}

		foreach($wordArr as $word){
			$word = trim($word);
			if( empty($word) ){
				continue;
			}

			if( strpos($word, '|') === false ){
				//切分单字
				$singleArr = $this->splitWord($word);

				$this->appendWord($singleArr);

			}else{
				//组合词
				$unionArr = $this->formatUnionWord($word);

				foreach($unionArr as $singleWord){
					$singleArr = $this->splitWord($singleWord);

					$this->appendWord($singleArr, array_diff($unionArr, [$singleWord]));
				}

			}

		}

		//保存词典树
		return $this->trieTree;
	}

	/**
	 * 用索引树过滤语句
	 * @param $str string 需要检测的语句
	 * @return array
	 */
	public function searchWord( $str ){
		$singleArr = $this->splitWord($str);
		$countWord = count($singleArr);

		$tree = &$this->trieTree;
		//匹配到的词
		$ret = [];
		for($i=0; $i < $countWord; $i++){
			$j = $i;

			//按词移步
			while( $j < $countWord && isset($tree[$singleArr[$j]]) ){
				//已到结尾词
				if( $tree[$singleArr[$j]]['is_end'] ){

					if( isset($ret[$tree[$singleArr[$j]]['word']]) ){
						$ret[$tree[$singleArr[$j]]['word']] = array_merge($ret[$tree[$singleArr[$j]]['word']], $tree[$singleArr[$j]]['relate']);
					}else{
						$ret[$tree[$singleArr[$j]]['word']] = $tree[$singleArr[$j]]['relate'];
					}
				}

				$tree = &$tree[$singleArr[$j]]['child'];
				$j++;
			}

			$tree = &$this->trieTree;
		}
		
		//匹配到的词，进行组合词筛选
		$retWords = [];
		if( !empty($ret) ){
			//已存在的全部单词
			$vacantWordArr = array_keys($ret);

			foreach($ret as $wordKey=>$relate){

				foreach($relate as $unionWordArr){

					if( empty($unionWordArr) ){
						//相关为空时为单字
						$retWords[$wordKey] = $wordKey;
					}else{
						//相关词都已存在
						if( count($unionWordArr) == count(array_intersect($unionWordArr, $vacantWordArr)) ){

							$unionWordItems = array_merge([$wordKey], $unionWordArr);
							sort($unionWordItems);
							$retKey = implode(',', $unionWordItems);

							if( !isset($retWords[$retKey]) ){
								$retWords[$retKey] = $unionWordItems;
							}
							
						}
					}
				}
			}
		}

		return $retWords;
	}

	/**
	 * 整理组合词的格式
	 * @param $str string 组合词
	 * @return array
	 */
	private function formatUnionWord($str){
		$arr = explode('|', $str);

		$ret = [];
		foreach($arr as $k=>$v){
			$v = trim($v);
			if( empty($v) ){
				continue;
			}

			$ret[] = $v;
		}

		return $ret;
	}

	/**
	 * 建立字典树
	 * @param $singleArr string 词
	 * @param $relateArr array 相关词列表
	 * @return boolean
	 */
	private function appendWord($singleArr, $relateArr=[]){
		$tree = &$this->trieTree;
		$wordCount = count($singleArr);

		foreach( $singleArr as $k => $singleWord){
			if( !isset($tree[$singleWord]) ){
				$tree[$singleWord] = [
					'child' => [],
					'is_end' => false, //是否结尾字，false - 不是，true - 是
				];
			}

			//到了结尾
			if( $k + 1 == $wordCount ){
				$tree[$singleWord]['is_end'] = true;
				$tree[$singleWord]['word'] = implode('', $singleArr); //该路径上的完整词

				$tree[$singleWord]['relate'][] = $relateArr; //相关词
			}

			//下一个节点
			$tree = &$tree[$singleWord]['child'];
		}

		return true;
	}

	/**
	 * 切分为单字
	 * @param $str string 词句
	 * @return array
	 */
	private function splitWord($str){
		//字节长度
		$strlen = strlen($str);

		$arr = [];
		for($i=0; $i<$strlen; $i++){
			//获取ascii码值
			$s = ord($str[$i]);

			if( $s >> 7 == 0 ){
				//一个字节, 0xxx xxxx
				$arr[] = strtolower($str[$i]);
			}elseif( $s >> 4 == 15 ){
				//四个字节, 1111 0xxx 10xx xxxx ....
				if( $i + 3 < $strlen ){
					$arr[] = $str[$i] . $str[$i+1] . $str[$i+2] . $str[$i+3];

					$i += 2;
				}
			}elseif( $s >> 5 == 7 ){
				//三个字节, 1110 xxxx 10xx xxxx ....
				if( $i + 2 < $strlen ){
					$arr[] = $str[$i] . $str[$i+1] . $str[$i+2];

					$i += 1;
				}
			}elseif( $s >> 6 == 3 ){
				//二个字节, 110x xxxx 10xx xxxx
				if( $i + 1 < $strlen ){
					$arr[] = $str[$i] . $str[$i+1];
				}
			}
		}

		return $arr;
	}

}