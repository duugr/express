<?php


namespace Express\Util;


class Http
{
	static function get($url){
		return file_get_contents($url);
	}
}