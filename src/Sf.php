<?php

/**
 * 顺丰国际SDK，soapClient
 */

namespace Express;

use Express\Util\Xml;
use Psr\Log\LoggerInterface;

/**
 *
 */
class Sf
{
	/* 顺丰接口配置 */
	protected $accesscode = '';             //商户号码
	protected $checkword  = '';             //商户密匙
	protected $wsdlnl     = '';             // 接口地址
	const FuncName        = 'sfKtsService'; // 接口方法 String sfKtsService(String xml, String verifyCode)

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

	public function __construct($accesscode, $checkword, LoggerInterface $logger)
	{
		$this->accesscode = $accesscode;
		$this->checkword  = $checkword;
		$this->wsdlnl     = $_SERVER['EXP_SF_URI'];
		set_time_limit(0);
		$this->xmlArray['Head'] = $this->accesscode;

		$this->logger = $logger;
	}

	public function setCreateOrderAttributesOrderId(string $orderId)
	{
		$this->xmlArray['Body']['Order']['@attributes']['orderid']           = $orderId;
		$this->xmlArray['Body']['Order']['@attributes']['platform_order_id'] = $orderId;
		$this->xmlArray['Body']['Order']['@attributes']['platform_code']     = '0000';
		$this->xmlArray['Body']['Order']['@attributes']['erp_code']          = '0000';
	}

	public function setCreateOrderAttributesExpressType(string $expressType = '29', string $orderUrl = '')
	{
		$this->xmlArray['Body']['Order']['@attributes']['express_type'] = $expressType;
		if ('29' === $expressType && !empty($orderUrl)) {
			$this->xmlArray['Body']['Order']['@attributes']['order_url'] = $orderUrl;
		}
	}

	public function __call($name, $arguments)
	{
		if (method_exists($this, $name)) {
			if (count($arguments) < 2) {
				return;
			}
			// TODO: Implement __call() method.
			switch ($name) {
				case 'setCreateOrderAttributes':
					$this->xmlArray['Body']['Order']['@attributes'][$arguments[0]] = $arguments[1];
					break;
				case 'setCreateOrderCargoAttributes':
					$this->xmlArray['Body']['Order']['Cargo']['@attributes'][$arguments[0]] = $arguments[1];
					break;
				case 'setCreateOrderExtraAttributes':
					$this->xmlArray['Body']['@attributes'][$arguments[0]] = $arguments[1];
					break;
				case 'setAttributes':
					$this->xmlArray['@attributes'][$arguments[0]] = $arguments[1];
					break;
				case 'setBodyAttributes':
					$this->xmlArray['Body']['@attributes'][$arguments[0]] = $arguments[1];
					break;
				case 'setOrderSearchAttributes':
					$this->xmlArray['Body']['OrderSearch']['@attributes'][$arguments[0]] = $arguments[1];
					break;
				case 'setRouteRequestAttributes':
					$this->xmlArray['Body']['RouteRequest']['@attributes'][$arguments[0]] = $arguments[1];
					break;
				default:
					$this->logger->info(__METHOD__ . ' default ', [$name, $arguments]);
			}
		}
	}

	/**
	 * @return array
	 */
	public function Create()
	{
		$this->setBodyAttributes('service', 'OrderService');

		$this->EncryptionData();
		if (!$this->result) {
			return $this->ret;
		}

		return $this->getResponse('OrderResponse');
	}

	/**
	 * 顺丰BSP查单接口
	 */
	public function OrderSearch($orderid)
	{
		$this->setBodyAttributes('service', 'OrderSearchService');
		$this->setOrderSearchAttributes('orderid', $orderid);

		$this->EncryptionData();
		if (!$this->result) {
			return $this->ret;
		}
		return $this->getResponse('OrderSearchResponse');
	}

	/**
	 * 顺丰BSP查单接口,根据运单号或者订单号【1.运单号,2.订单号】
	 */
	public function RouteSearch($orderid, $type = 1, $lang = "")
	{
		$this->setBodyAttributes('service', 'RouteService');
		$this->setRouteRequestAttributes('tracking_number', $orderid);
		$this->setRouteRequestAttributes('tracking_type', $type);
		$this->setRouteRequestAttributes('method_type', 1);
		if (!empty($lang)) {
			$this->setAttributes('lang', $lang);
		}

		$this->EncryptionData();
		if (!$this->result) {
			return $this->ret;
		}
		return $this->getResponse('RouteResponse');
	}

	/**
	 * 确认订单
	 *
	 * @param $orderid
	 * @param $mailno
	 *
	 * @return array|bool
	 */
	public function OrderConfirm($orderid, $mailno)
	{
		return $this->OrderConfirmRequest($orderid, $mailno, 1);
	}

	/**
	 * 取消订单
	 *
	 * @param        $orderid
	 * @param string $mailno
	 *
	 * @return array|bool
	 */
	public function OrderCancel($orderid, $mailno = '')
	{
		return $this->OrderConfirmRequest($orderid, $mailno, 2);
	}

	/**
	 * 订单确认与取消发送
	 *
	 * @param $orderid   客户订单号
	 * @param $mailno    运单号
	 * @param $dealtype  类型【1：确认；2：取消】
	 *
	 * @return array
	 */
	public function OrderConfirmRequest($orderid, $mailno, $dealtype)
	{
		$this->setBodyAttributes('service', 'OrderConfirmService');
		$this->setOrderConfirmAttributes('dealtype', $dealtype);
		$this->setOrderConfirmAttributes('orderid', $orderid);
		$this->setOrderConfirmAttributes('mailno', $mailno);

		$this->EncryptionData();
		if (!$this->result) {
			return $this->ret;
		}
		return $this->getResponse('OrderConfirmResponse');
	}

	/**
	 * 转换顺丰返回XML
	 *
	 * @param $data
	 * @param $name
	 *
	 * @return array
	 */
	public function getResponse($name)
	{
		$ret       = [];
		$xmlResult = @simplexml_load_string($this->result, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
		$xml       = Xml::xmlToArray($xmlResult);
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
	 * 调用webserver
	 *
	 * @param $xml        XML字符串
	 * @param $verifyCode 加密后的字符串
	 *                    返回xml格式
	 */
	private function callWebServer($xml, $verifyCode)
	{
		try {
			$this->logger->info(__METHOD__ . ' before', ["xml" => $xml, "verifyCode" => $verifyCode]);

			$client = new \SoapClient($this->wsdlnl);
			$result = $client->__soapCall(self::FuncName, ["xml" => $xml, "verifyCode" => $verifyCode]);
			// sleep(1);
			$this->logger->info(__METHOD__ . ' after', [$result]);

			return $result;
		} catch (\SoapFault $e) {
			$this->logger->error(
				__METHOD__ . ' SoapFault', [
					$e,
					"xml"        => $xml,
					"verifyCode" => $verifyCode
				]);
			return false;
		}
	}

	/**
	 * 加密方法
	 *
	 * @param $xml       XML字符串
	 * @param $checkword 密钥
	 */
	public function EncryptionData()
	{
		$xml     = Xml::arrayToXml($this->xmlArray, "Request"); // 调用生成XML方法
		$md5Data = md5($xml . $this->checkword, true);
		// base64转码
		$verifyCode = base64_encode($md5Data);

		$this->result = $this->callWebServer($xml, $verifyCode); // 调用webserver
	}
}
