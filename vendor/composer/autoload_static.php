<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit0db0dcabce9a074590ea373a4b8e3b23
{
    public static $prefixesPsr0 = array (
        'L' => 
        array (
            'LogonLabs' => 
            array (
                0 => __DIR__ . '/../..' . '/src',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit0db0dcabce9a074590ea373a4b8e3b23::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
