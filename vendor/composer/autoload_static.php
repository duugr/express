<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInita698fa259bcecad7686d09d90ca06002
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Spatie\\ArrayToXml\\' => 18,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
        'E' => 
        array (
            'Express\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Spatie\\ArrayToXml\\' => 
        array (
            0 => __DIR__ . '/..' . '/spatie/array-to-xml/src',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'Express\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInita698fa259bcecad7686d09d90ca06002::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInita698fa259bcecad7686d09d90ca06002::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
