<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit812a000fbbefb7dd8eaa9e0be23c02a2
{
    public static $prefixLengthsPsr4 = array (
        'I' => 
        array (
            'Inc\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Inc\\' => 
        array (
            0 => __DIR__ . '/../..' . '/inc',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit812a000fbbefb7dd8eaa9e0be23c02a2::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit812a000fbbefb7dd8eaa9e0be23c02a2::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit812a000fbbefb7dd8eaa9e0be23c02a2::$classMap;

        }, null, ClassLoader::class);
    }
}