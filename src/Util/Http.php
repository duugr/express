<?php

namespace Express\Util;

class Http
{
	static function get($url)
	{
		$ch      = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		$file_contents = curl_exec($ch);
		curl_close($ch);

		return $file_contents;
	}

	static function post($url, $params)
	{
		$ch      = curl_init();
		$timeout = 5;
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"Content-Type: application/x-www-form-urlencoded; charset=utf-8",
		]);
		$file_contents = curl_exec($ch);
		curl_close($ch);

		return $file_contents;
	}
}
