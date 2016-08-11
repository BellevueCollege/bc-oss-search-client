<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit67524c7246a6fa981db3ec42b85567fb
{
    public static $prefixesPsr0 = array (
        'O' => 
        array (
            'OpenSearchServer' => 
            array (
                0 => __DIR__ . '/..' . '/opensearchserver/opensearchserver/src',
            ),
        ),
        'B' => 
        array (
            'Buzz' => 
            array (
                0 => __DIR__ . '/..' . '/kriswallsmith/buzz/lib',
            ),
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInit67524c7246a6fa981db3ec42b85567fb::$prefixesPsr0;

        }, null, ClassLoader::class);
    }
}
