<?php

$xml = file_get_contents('tests/response.xml');
$obj = simplexml_load_string($xml, "SimpleXMLElement", LIBXML_NOCDATA);

print_r(
    json_decode(json_encode($obj), true)
);

die;
require dirname(__DIR__).'/bootstrap.php';

use Express\Sf;

$exp = new Sf('erptest', 'F738470712F5F54681FB5E5A9779272D');

//platFormCode=erptest,sfWaybillNo=994491604995,orderId=test2018001

$result = $exp->RouteSearch('test2018001','994491604995', 'en', 'erptest');
print_r($result);
$result = $exp->RouteSearch('test2018001','994491604995', 'cn', 'erptest');
print_r($result);
