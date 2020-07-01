<?php

function autoloader($dir)
{
	spl_autoload_register(
		function($class) use ($dir) {
			if (class_exists($class)) {
				return true;
			}

			$class = str_replace('Express\\', '', $class);
			if (is_integer(strpos($class, '\\'))) {
				$pathPsr4 = $dir . '/src/' . strtr($class, '\\', DIRECTORY_SEPARATOR) . ".php";
			}
			else {
				$pathPsr4 = $dir . '/src/' . $class . ".php";
			}
			if (file_exists($pathPsr4)) {
				include_once $pathPsr4;
			}

			return true;

		});
}

define('BOOT_ROOT', __DIR__);
autoloader(BOOT_ROOT);