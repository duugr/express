<?php
//require dirname(__DIR__).'/bootstrap.php';
require dirname(__DIR__).'/vendor/autoload.php';

use Express\Sf;


$exp = new Sf('erptest', 'F738470712F5F54681FB5E5A9779272D', null);

$arg1 = 'abc';
$arg2 = '123';

$exp->resetXmlEmpty();
for ($i = 0; $i < 3; $i++) {
	$exp->setRouteRequestRoute('sfWaybillNo', $arg1, $i);
	$exp->setRouteRequestRoute('orderId', $arg2, $i);

}
for ($i = 4; $i < 7; $i++) {
	$exp->setRouteRequestRoute('sfWaybillNo', $arg1, $i);
	$exp->setRouteRequestRoute('orderId', $arg2, $i);
}

$exp->getXml();
die;
$array = [
	'Good guys' => [
		'Guy' => [
			['name' => 'Luke Skywalker', 'weapon' => 'Lightsaber'],
			['name' => 'Captain America', 'weapon' => 'Shield'],
		],
	],
	'Bad guys' => [
		'Guy' => [
			['name' => 'Sauron', 'weapon' => 'Evil Eye'],
			['name' => 'Darth Vader', 'weapon' => 'Lightsaber'],
		],
	],
];
print \Spatie\ArrayToXml\ArrayToXml::convert($array, "request");
$array = [
	'routes' => [
		'route' =>[
			['sfWaybillNo' => 'abc', 'orderId'     => '123'],
			['sfWaybillNo' => 'abc', 'orderId'     => '123'],
			['sfWaybillNo' => 'abc', 'orderId'     => '123']
		],
	]
];
print \Spatie\ArrayToXml\ArrayToXml::convert($array, "request");
die;
//$data['route'][$arguments[0]] = $arguments[1];
//array_push($xmlArray['routes'][$arguments[2]], $data);
//$xmlArray['routes'][$arguments[2]]['route'][$arguments[0]] = $arguments[1];


$xml = file_get_contents('tests/response.xml');
$obj = simplexml_load_string($xml, "SimpleXMLElement", LIBXML_NOCDATA);

print_r(
	json_decode(json_encode($obj), true)
);

die;
require dirname(__DIR__).'/bootstrap.php';

//use Express\Sf;

$exp = new Sf('erptest', 'F738470712F5F54681FB5E5A9779272D');

//platFormCode=erptest,sfWaybillNo=994491604995,orderId=test2018001

$result = $exp->RouteSearch('test2018001', '994491604995', 'en', 'erptest');
print_r($result);
$result = $exp->RouteSearch('test2018001', '994491604995', 'cn', 'erptest');
print_r($result);
