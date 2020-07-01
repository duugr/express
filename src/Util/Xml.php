<?php


namespace Express\Util;


class Xml
{

	public static function toObj($xml, $notObj = true)
	{
		$obj = simplexml_load_string($xml, "SimpleXMLElement", LIBXML_NOCDATA);
		return json_decode(json_encode($obj), $notObj);
	}

	/**
	 * 转换XML属性为数组
	 *
	 * @param SimpleXMLElement $xml
	 *
	 * @return array
	 */
	public static function xmlToArray(\SimpleXMLElement $xml, $collection = [])
	{
		$attributes = $xml->attributes();
		$nodes      = $xml->children();
		if ($attributes->count() > 0) {
			if ($xml->__toString()) {
				$collection[strtolower($xml->getName())] = $xml->__toString();
			}
			foreach ($attributes as $attrName => $attrValue) {
				$collection[strtolower($attrName)] = strval($attrValue);
			}
		}

		if (0 === $xml->count()) {
			$collection[strtolower($xml->getName())] = $xml->__toString();
		}

		if (0 === $nodes->count()) {
			return $collection;
		}
		else {
			foreach ($nodes as $nodeName => $nodeValue) {
				if (count($nodeValue->xpath('../' . $nodeName)) < 2) {
					$collection[strtolower($nodeName)] = self::xmlToArray($nodeValue);
					continue;
				}

				$collection[strtolower($nodeName)][] = self::xmlToArray($nodeValue);
			}
		}

		return $collection;
	}


	/**
	 * 数组转为xML
	 *
	 * @param $var  数组
	 * @param $type xml的根节点
	 * @param $tag
	 *              返回xml格式
	 */
	public static function arrayToXml($var, $type = 'root', $tag = '')
	{
		$ret = '';
		if (!is_int($type)) {
			if ($tag) {
				return self::arrayToXml([$tag => $var], 0, $type);
			}
			else {
				$tag  .= $type;
				$type = 0;
			}
		}
		$level  = $type;
		$indent = str_repeat("\t", $level);
		if (!is_array($var)) {
			$ret .= $indent . '<' . $tag;
			$var = strval($var);
			if ('' == $var) {
				$ret .= ' />';
			}
			elseif (!preg_match('/[^0-9a-zA-Z@\._:\/-]/', $var)) {
				$ret .= '>' . $var . '</' . $tag . '>';
			}
			else {
				$ret .= "><![CDATA[{$var}]]></{$tag}>";
			}
			$ret .= "\n";
		}
		elseif (!(is_array($var) && count($var) && (array_keys($var) !== range(
						0,
						sizeof($var) - 1))) && !empty($var)) {
			foreach ($var as $tmp) {
				$ret .= self::arrayToXml($tmp, $level, $tag);
			}

		}
		else {
			$ret .= $indent . '<' . $tag;
			if (0 == $level) {
				$ret .= '';
			}

			if (isset($var['@attributes'])) {
				foreach ($var['@attributes'] as $k => $v) {
					if (!is_array($v)) {
						$ret .= sprintf(' %s="%s"', $k, $v);
					}
				}
				unset($var['@attributes']);
			}
			$ret .= ">\n";
			foreach ($var as $key => $val) {
				$ret .= self::arrayToXml($val, $level + 1, $key);
			}
			$ret .= "{$indent}</{$tag}>\n";
		}

		return $ret;
	}

}
