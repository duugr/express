<?php

/**
 * 顺丰国际SDK，soapClient
 */
namespace Express;

use Psr\Log\LoggerInterface;

/**
 *
 */
class Sf
{
	/* 顺丰接口配置 */
	protected $accesscode = ''; //商户号码
	protected $checkword  = ''; //商户密匙
	protected $wsdlnl     = ''; //接口地址 */

	//返回信息
	protected $ret = [
		'head'  => false,
		'error' => '系统错误',
		'code'  => -1
	];

	private $xmlArray = [
		'@attributes' => [
			'service' => '',
			'lang'    => 'zh-CN'
		],
		'Head'        => "",
		'Body'        => []
	];

	public $result;
	private $logger;

	public function __construct($accesscode, $checkword)
	{
		$this->accesscode = $accesscode;
		$this->checkword  = $checkword;
		$this->wsdlnl     = $_SERVER['EXP_SF_URI'];
		set_time_limit(0);
		$this->xmlArray['Head'] = $this->accesscode;
	}

	public function SetLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	/**
	 * 顺丰BSP下订单接口（含筛选）
	 * 下订单接口根据客户需要，可提供以下三个功能：
	 * 1) 客户系统向顺丰下发订单。
	 * 2) 为订单分配运单号。
	 * 3) 筛单（可选，具体商务沟通中双方约定，由顺丰内部为客户配置）。
	 * 此接口也用于路由推送注册。客户的顺丰运单号不是通过此下订单接口获取，但却需要获取BSP的路由推送时，
	 * 需要通过此接口对相应的顺丰运单进行注册以使用BSP的路由推送 接口。
	 *[
	"Order" => [
	'@attributes' => [
	'orderid'            => 'TEST20142034000041',
	'express_type'       => 'A1',
	'j_company'          => '寄件方公司名',
	'j_contact'          => '寄件方联系人',
	'j_tel'              => '123456',
	'j_mobile'           => '123456',
	'j_address'          => 'sddssddsds',
	'd_company'          => 'ddsssd',
	'd_contact'          => '测试',
	'd_tel'              => '121121221',
	'd_mobile'           => '2121212',
	'd_address'          => '速度速度速度速度',
	'parcel_quantity'    => '1',
	'j_province'         => '都深深地',
	'j_city'             => '四十四',
	'd_province'         => '丁丹丹',
	'd_city'             => '深圳',
	'j_country'          => 'GB',
	'j_post_code'        => '1212',
	'd_country'          => 'GB',
	'd_post_code'        => '122112',
	'cargo_total_weight' => '2',
	'returnsign'         => 'Y',
	'd_email'            => '123@qq.com',
	'operate_flag'       => '0'
	],
	'Cargo'       => [
	'@attributes' => [
	'name'   => '货物中文品名',
	'hscode' => '123',
	'ename'  => 'EEE',
	'unit'   => 'PCE',
	'count'  => '12',
	'amount' => '121',
	'weight' => '11'
	]]
	]
	 * ]
	 * @return string
	 */
	public function Order(array $params)
	{
		$this->xmlArray['Body']                                     = $params;
		$this->xmlArray['@attributes']['service']                   = 'OrderService';
		$this->xmlArray['Body']['Order']['@attributes']['pit_code'] = 'TOPH';

		return $this->getResponse('OrderResponse');
	}

	/**
	 * 顺丰BSP查单接口
	 */
	public function OrderSearch($orderid)
	{
		$this->xmlArray['@attributes']['service'] = 'OrderSearchService';
		$this->xmlArray['Body']                   = [
			'OrderSearch' => [
				'@attributes' => [
					'orderid' => $orderid
				]
			]
		];

		return $this->getResponse('OrderSearchResponse');
	}

	/**
	 * 顺丰BSP查单接口,根据运单号或者订单号【1.运单号,2.订单号】

	 */
	public function RouteSearch($orderid, $type = 1, $lang = "")
	{
		if (!empty($lang)) {
			$this->xmlArray['@attributes']['lang'] = $lang;
		}
		$this->xmlArray['@attributes']['service'] = 'RouteService';
		$this->xmlArray['Body']                   = [
			'RouteRequest' => [
				'@attributes' => [
					'method_type'     => 1,
					'tracking_type'   => $type,
					'tracking_number' => $orderid
				]
			]
		];

		return $this->getResponse('RouteResponse');
	}

	/**
	 * 确认订单
	 * @param $orderid
	 * @param $mailno
	 * @return array|bool
	 */
	public function OrderConfirm($orderid, $mailno)
	{
		return $this->OrderConfirmRequest($orderid, $mailno, 1);
	}

	/**
	 * 取消订单
	 * @param $orderid
	 * @param string $mailno
	 * @return array|bool
	 */
	public function OrderCancel($orderid, $mailno = '')
	{
		return $this->OrderConfirmRequest($orderid, $mailno, 2);
	}

	/**
	 * 订单确认与取消发送
	 * @param $orderid 客户订单号
	 * @param $mailno  运单号
	 * @param $dealtype  类型【1：确认；2：取消】
	 * @return array
	 */
	public function OrderConfirmRequest($orderid, $mailno, $dealtype)
	{
		$this->xmlArray['@attributes']['service'] = 'OrderConfirmService';
		$this->xmlArray['Body']                   = [
			'OrderConfirm' => [
				'@attributes' => [
					'dealtype' => $dealtype,
					'orderid'  => $orderid,
					'mailno'   => $mailno
				]
			]
		];

		return $this->getResponse('OrderConfirmResponse');
	}

	/**
	 * 转换顺丰返回XML
	 * @param $data
	 * @param $name
	 * @return array
	 */
	public function getResponse($name)
	{
		$this->EncryptionData();
		if (!$this->result) {
			return $this->ret;
		}

		$ret       = [];
		$xmlResult = @simplexml_load_string($this->result, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
		$xml       = $this->xmlToArray($xmlResult);
		foreach ($xml as $key => $node) {
			switch ($key) {
				case 'head':
					$ret['head'] = 'OK' == strtoupper($node['head']);
					break;
				case 'body':
					$ret['data'] = $node[strtolower($name)] ?? '';
					break;
				case 'error':
					$ret = array_merge($ret, is_array($node) ? $node : []);
					break;

				default:
					$this->logger->info(__METHOD__ . ' not key', ['key' => $key]);
					break;
			}
		}

		if ($ret['head'] && !isset($ret['data'])) {
			$ret['head'] = false;
		}

		$this->logger->info(__METHOD__ . ' data', [$ret]);

		return $ret;
	}

	/**
	 * 转换XML属性为数组
	 * @param SimpleXMLElement $xml
	 * @return array
	 */
	public function xmlToArray(\SimpleXMLElement $xml, $collection = [])
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
		} else {
			foreach ($nodes as $nodeName => $nodeValue) {
				if (count($nodeValue->xpath('../' . $nodeName)) < 2) {
					$collection[strtolower($nodeName)] = $this->xmlToArray($nodeValue);
					continue;
				}

				$collection[strtolower($nodeName)][] = $this->xmlToArray($nodeValue);
			}
		}

		return $collection;
	}

	/**
	 * 数组转为xML
	 * @param $var 数组
	 * @param $type xml的根节点
	 * @param $tag
	 * 返回xml格式
	 */
	private function arrayToXml($var, $type = 'root', $tag = '')
	{
		$ret = '';
		if (!is_int($type)) {
			if ($tag) {
				return $this->arrayToXml([$tag => $var], 0, $type);
			} else {
				$tag .= $type;
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
			} elseif (!preg_match('/[^0-9a-zA-Z@\._:\/-]/', $var)) {
				$ret .= '>' . $var . '</' . $tag . '>';
			} else {
				$ret .= "><![CDATA[{$var}]]></{$tag}>";
			}
			$ret .= "\n";
		} elseif (!(is_array($var) && count($var) && (array_keys($var) !== range(0, sizeof($var) - 1))) && !empty($var)) {
			foreach ($var as $tmp) {
				$ret .= $this->arrayToXml($tmp, $level, $tag);
			}

		} else {
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
				$ret .= $this->arrayToXml($val, $level + 1, $key);
			}
			$ret .= "{$indent}</{$tag}>\n";
		}
		return $ret;
	}

	/**
	 * 调用webserver
	 * @param $xml XML字符串
	 * @param $verifyCode 加密后的字符串
	 * 返回xml格式
	 */
	private function callWebServer($xml, $verifyCode)
	{
		try {
			$this->logger->info(__METHOD__ . ' before', ["xml" => $xml, "verifyCode" => $verifyCode]);

			libxml_disable_entity_loader(false);
			$opts = [
				'ssl'   => [
					'verify_peer' => false
				],
				'https' => [
					'curl_verify_ssl_peer' => false,
					'curl_verify_ssl_host' => false
				]
			];
			$streamContext = stream_context_create($opts);

			$client = new \SoapClient($this->wsdlnl, ['stream_context' => $streamContext]);
			$result = $client->__soapCall('sfexpressService', ["xml" => $xml, "verifyCode" => $verifyCode]);

			$this->logger->info(__METHOD__ . ' after', [$result]);

			return $result;
		} catch (\SoapFault $e) {
			$this->logger->error(__METHOD__ . ' SoapFault', [$e]);
			return false;
		}
	}
	// base64转码
	private function base64($str)
	{
		return base64_encode($str);
	}

	// md5加密并转大写
	private function _md5($str)
	{
		return strtoupper(md5($str));
	}

	/**
	 * 加密方法
	 * @param $xml XML字符串
	 * @param $checkword 密钥
	 */
	public function EncryptionData()
	{
		$xml        = $this->arrayToXml($this->xmlArray, "Request"); // 调用生成XML方法
		$md5Data    = $this->_md5($xml . $this->checkword);
		$verifyCode = $this->base64($md5Data);

		$this->result = $this->callWebServer($xml, $verifyCode); // 调用webserver
	}
}
