<?php

namespace Glaucus\MVC;

use Glaucus\Core\Configure;

class Layout {

    private static $loader = null;
    private static $twig = null;
    private static $params = [];

    private static function init() {
        static::$loader = new \Twig_Loader_Filesystem(TEMPLATE_DIR);
        static::$twig = new \Twig_Environment(Layout::$loader, [
            'cache' => false//TMP
        ]);
        static::$twig->addFunction(new \Twig_SimpleFunction('Configure', function($method, $parameters) {
            return Configure::$method($parameters);
        }));
        static::$twig->addFunction(new \Twig_SimpleFunction('css', function($file, $media) {
            return '<link href="' . CSS_PATH . $file . '" media="' . $media . '" rel="stylesheet" type="text/css" />';
        }));
    }

    public static function render($template) {
        if(static::$twig === null) static::init();
        echo static::$twig->render($template, Layout::$params);
    }

    public static function addParam($params) {
        if(static::$twig === null) static::init();
        foreach($params as $key => $value) {
            static::$params[$key] = $value;
        }
    }

}