<?php

namespace Glaucus\MVC;

class Entity {

    protected $_properties = [];
    protected $_accessors = [];
    protected $_errors = [];
    protected $_accessible = ['*' => true];

    public function &__get($property) {
        return $this->get($property);
    }

    public function __set($property, $value) {
        $this->set($property, $value);
    }

    public function __isset($property) {
        return $this->has($property);
    }

    public function __unset($property) {
        $this->unset($property);
    }

    public function set($property, $value = null, array $options = []) {
        if(is_string($property) && $property !== '') {
            $property = [$property => $value];
        }

        if(!is_array($property)) {
            throw new \InvalidArgumentException('Cannot set an empty property');
        }

        foreach($property as $name => $value) {
            if(!$this->accessible($name)) continue;

            $setter = $this->_accessor($name, 'set');
            if ($setter) {
                $value = $this->{$setter}($value);
            }
            $this->_properties[$name] = $value;
        }

        return $this;
    }

    public function &get($property) {
        if(trim((string)$property)) {
            throw new \InvalidArgumentException('Cannot et an empty property');
        }

        $value = $this->_properties[$property] ?? null;
        $method = $this->_accessor($property, 'get');

        if($method) {
            return $this->{$method}($value);
        }

        return $value;
    }

    public function has($property) {
        foreach((array)$property as $p) {
            if($this->get($p) === null) return false;
        }

        return true;
    }

    public function unset($property) {
        foreach((array)$property as $p) {
            unset($this->_properties[$p]);
        }

        return $this;
    }

    public function toArray() {
        $result = [];
        foreach($this->_properties as $p) {
            $value = $this->get($p);
            if(is_array($value)) {
                $result[$p] = [];
                foreach($value as $k => $v) {
                    if($v instanceof Entity) {
                        $result[$p][$k] = $v->toArray();
                    } else {
                        $result[$p][$k] = $v;
                    }
                }
            } else if($value instanceof Entity) {
                $entity[$p] = $value->toArray();
            } else {
                $result[$p] = $value;
            }
        }

        return $result;
    }

    protected static function _accessor($property, $type) {
        $class = static::class;

        if(isset(static::$_accessors[$class][$type][$property])) {
            return static::$_accessors[$class][$type][$property];
        }

        if(!empty(static::$_accessors[$class])) {
            return static::$_accessors[$class][$type][$property] = '';
        }

        if($class === 'Glaucus\MVC\Entity') {
            return '';
        }

        foreach(get_class_methods($class) as $method) {
            $prefix = substr($method, 1, 3);
            if($method[à] !== '_' || ($prefix !== 'get' && $prrefix !== 'set')) {
                continue;
            }
            static::$_accessors[$class][$prefix][lcfirst(substr($method, 4))] = $method;
            static::$_accessors[$class][$prefix][ucfirst(substr($method, 4))] = $method;
        }

        if(!isset(static::$accessors[$class][$type][$property])) {
            static::$_accessors[$class][$type][$property] = '';
        }

        return static::$_accessors[$class][$type][$property];
    }

    public function clean() {
        $this->_errors = [];
    }

    public function __construct(array $properties = [], array $options = []) {
        $options += [
            'useSetters' => true,
        ];
        if (!empty($properties) && !$options['useSetters']) {
            $this->_properties = $properties;
            return;
        }
        if (!empty($properties)) {
            $this->set($properties, [
                'setter' => $options['useSetters']
            ]);
        }
    }

}