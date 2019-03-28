<?php

/**
 * 顺丰国际SDK，soapClient
 */
namespace SFEXP;

/**
 *
 */
class SfLog
{
	public static function log($msg, $method)
	{
		$data = sprintf("\n%s\t-- %s start --\n%s\n -- %s end --\n",
			date('Y-m-d H:i:s'),
			$method,
			var_export($msg, true),
			$method
		);

		file_put_contents('logs/lib_' . date('Y-m-d') . '.log', $data, FILE_APPEND);
	}
}
