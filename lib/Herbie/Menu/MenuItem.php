<?php

namespace Herbie\Menu;

use Exception;

class MenuItem
{

    /**
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $route;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $date;

    /**
     * @var int
     */
    protected $depth;

    /**
     * @var string
     */
    protected $hidden;

    /**
     * @var array
     */
    public $items;

    /**
     * @var array
     */
    protected $_data_ = [];

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->setData($data);
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        if (array_key_exists('_data_', $data)) {
            throw new \LogicException("Field _data_ is not allowed.");
        }
        foreach ($data AS $key => $value) {
            $this->__set($key, $value);
        }
    }

    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @return string
     */
    public function getParentRoute()
    {
        return trim(dirname($this->route), '.');
    }

    /**
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    public function getVisible()
    {
        return false === $this->getHidden();
    }

    /**
     * @return string
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * @return string
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @return bool
     */
    public function isFile()
    {
        return $this->type == 'file';
    }

    /**
     * @return bool
     */
    public function isFolder()
    {
        return $this->type == 'folder';
    }

    /**
     * @return bool
     */
    public function isStartPage()
    {
        return (1 == $this->depth) && ('index' == $this->route);
    }

    /**
     * @return bool
     */
    public function hasItems()
    {
        return !empty($this->items);
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    public function setDate($date)
    {
        $this->date = is_numeric($date) ? date('c', $date) : $date;
    }

    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }

    public function setRoute($route)
    {
        $this->route = $route;
    }

    public function setDepth($depth)
    {
        $this->depth = $depth;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @param array $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return bool
     */
    public function hasVisibleItems()
    {
        if (!$this->hasItems()) {
            return false;
        }
        foreach ($this->getItems() AS $item) {
            if ($item->visible) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $name
     * @throws \LogicException
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } elseif (array_key_exists($name, $this->_data_)) {
            return $this->_data_[$name];
        } else {
            throw new \LogicException("Field {$name} does not exist.");
        }
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter() !== null;
        } elseif (array_key_exists($name, $this->_data_)) {
            return $this->_data_[$name] !== null;
        } else {
            return false;
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws \LogicException
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } else {
            $this->_data_[$name] = $value;
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->title;
    }

    /**
     * @param string $route
     * @return bool
     */
    public function routeEquals($route)
    {
        return $this->route == $route;
    }

    /**
     * @param string $route
     * @return bool
     */
    public function routeInRootPath($route)
    {
        $realRoute = $this->isFile() ? $this->route : substr($this->route, 0, -6);
        $pos = strpos($route, $realRoute);
        return 0 === $pos;
    }

}