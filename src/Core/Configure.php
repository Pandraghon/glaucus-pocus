<?php

namespace Glaucus\Core;

class Configure {

    protected static $_values = [
        'debug' => 0
    ];

    protected static $_engines = [];

    protected static $_hasInSet = null;

    public static function write($config, $value = null) {
        if(!is_array($config))  $config = [$config => $value];

        foreach ($config as $name => $value) {
            static::$_values[$name] = $value;
        }

        if(isset($config['debug'])) {
            if(static::$_hasInSet === null) {
                static::$_hasInSet = function_exists('ini_set');
            }
            if(static::$_hasInSet) {
                ini_set('display_errors', $config['debug'] ? 1 : 0);
            }
        }
        return true;
    }

    public static function read($var = null) {
        if($var === null)   return static::$_values;

        if(strpos($var, '.') === false)
            return static::$_values[$var] ?? null;

        $tmp = static::$_values;

        foreach (explode('.', $var) as $key) {
            if(!is_array($tmp) || !isset($tmp[$key]))
                return null;
            $tmp = $tmp[$key];
        }
        return $tmp;
    }

    public static function check($var) {
        if(empty($var))     return false;
        return static::read($var) !== null;
    }

    public static function delete($var) {
        unset(static::$_values[$var]);
    }

    public static function loadPHP($basename) {
        return include(CONFIG . $basename);
    }

    public static function loadJSON($basename) {
        return json_decode(file_get_contents(CONFIG . $basename), true);
    }

    public static function loadByExt($basename) {
        $extension = pathinfo($basename)['extension'];

        switch(strtolower($extension)) {
            case 'php':
                return static::loadPHP($basename);
                break;
            case 'json':
                return static::loadJSON($basename);
                break;
        }
    }

    public static function loadConfig($basename = 'ini.json') {
        $config = static::loadByExt($basename);
        foreach ($config['configfiles'] as $file) {
            static::load($file, false);
        }
    }

    public static function load($basename = 'app.php', $merge = true) {
        $values = static::loadByExt($basename);
        if($merge) {
            $values = static::$_values + $values;
        }

        return static::write($values);
    }

    public static function clear() {
        static::$_values = [];
        return true;
    }

}
