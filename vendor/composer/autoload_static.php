<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2ffb3d19e8810d7ac33d468b486ba9b7
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'PolyPlugins\\Maintenance_Mode_Made_Easy\\' => 39,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'PolyPlugins\\Maintenance_Mode_Made_Easy\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes/classes',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2ffb3d19e8810d7ac33d468b486ba9b7::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2ffb3d19e8810d7ac33d468b486ba9b7::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit2ffb3d19e8810d7ac33d468b486ba9b7::$classMap;

        }, null, ClassLoader::class);
    }
}
