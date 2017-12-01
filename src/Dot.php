<?php

namespace Prowebcraft;

use ArrayAccess;

/**
 * Dot Notation
 *
 * This class provides dot notation access to arrays, so it's easy to handle
 * multidimensional data in a clean way.
 */
class Dot implements ArrayAccess
{

    /** @var array Data */
    protected $data = [];

    /**
     * Constructor
     *
     * @param array|null $data Data
     */
    public function __construct(array $data = null)
    {
        if (is_array($data)) {
            $this->data = $data;
        }
    }

    /**
     * Get value of path, default value if path doesn't exist or all data
     *
     * @param  array $array Source Array
     * @param  mixed|null $key Path
     * @param  mixed|null $default Default value
     * @return mixed Value of path
     */
    public static function getValue($array, $key, $default = null)
    {
        if (is_string($key)) {
            // Iterate path
            $keys = explode('.', $key);
            foreach ($keys as $key) {
                if (!isset($array[$key])) {
                    return $default;
                }
                $array = &$array[$key];
            }
            // Get value
            return $array;
        } elseif (is_null($key)) {
            // Get all data
            return $array;
        }
        return null;
    }

    /**
     * Set value or array of values to path
     *
     * @param array $array Target array with data
     * @param mixed $key Path or array of paths and values
     * @param mixed|null $value Value to set if path is not an array
     */
    public static function setValue(&$array, $key, $value)
    {
        if (is_string($key)) {
            // Iterate path
            $keys = explode('.', $key);
            foreach ($keys as $key) {
                if (!isset($array[$key]) || !is_array($array[$key])) {
                    $array[$key] = [];
                }
                $array = &$array[$key];
            }
            // Set value to path
            $array = $value;
        } elseif (is_array($key)) {
            // Iterate array of paths and values
            foreach ($key as $k => $v) {
                self::setValue($array, $k, $v);
            }
        }
    }

    /**
     * Add value or array of values to path
     *
     * @param array $array Target array with data
     * @param mixed $key Path or array of paths and values
     * @param mixed|null $value Value to set if path is not an array
     * @param boolean $pop Helper to pop out last key if value is an array
     */
    public static function addValue(&$array, $key, $value = null, $pop = false)
    {
        if (is_string($key)) {
            // Iterate path
            $keys = explode('.', $key);
            if ($pop === true) {
                array_pop($keys);
            }
            foreach ($keys as $key) {
                if (!isset($array[$key]) || !is_array($array[$key])) {
                    $array[$key] = [];
                }
                $array = &$array[$key];
            }
            // Add value to path
            $array[] = $value;
        } elseif (is_array($key)) {
            // Iterate array of paths and values
            foreach ($key as $k => $v) {
                self::addValue($array, $k, $v);
            }
        }
    }

    /**
     * Delete path or array of paths
     *
     * @param array $array Target array with data
     * @param mixed $key Path or array of paths to delete
     */
    public static function deleteValue($array, $key)
    {
        if (is_string($key)) {
            // Iterate path
            $keys = explode('.', $key);
            $last = array_pop($keys);
            foreach ($keys as $key) {
                if (!isset($array[$key])) {
                    return;
                }
                $array = &$array[$key];
            }
            if (isset($array[$last])) {
                // Detele path
                unset($array[$last]);
            }
        } elseif (is_array($key)) {
            // Iterate array of paths
            foreach ($key as $k) {
                self::delete($k);
            }
        }
    }


    /**
     * Get value of path, default value if path doesn't exist or all data
     *
     * @param  mixed|null $key Path
     * @param  mixed|null $default Default value
     * @return mixed               Value of path
     */
    public function get($key = null, $default = null)
    {
        return self::getValue($this->data, $key, $default);
    }

    /**
     * Set value or array of values to path
     *
     * @param mixed $key Path or array of paths and values
     * @param mixed|null $value Value to set if path is not an array
     */
    public function set($key, $value = null)
    {
        return self::setValue($this->data, $key, $value);
    }

    /**
     * Add value or array of values to path
     *
     * @param mixed $key Path or array of paths and values
     * @param mixed|null $value Value to set if path is not an array
     * @param boolean $pop Helper to pop out last key if value is an array
     */
    public function add($key, $value = null, $pop = false)
    {
        return self::addValue($this->data, $key, $value, $pop);
    }

    /**
     * Check if path exists
     *
     * @param  string $key Path
     * @return boolean
     */
    public function has($key)
    {
        $keys = explode('.', (string)$key);
        $data = &$this->data;
        foreach ($keys as $key) {
            if (!isset($data[$key])) {
                return false;
            }
            $data = &$data[$key];
        }

        return true;
    }

    /**
     * Delete path or array of paths
     *
     * @param mixed $key Path or array of paths to delete
     */
    public function delete($key)
    {
        return self::deleteValue($this->data, $key);
    }

    /**
     * Delete all data, data from path or array of paths and
     * optionally format path if it doesn't exist
     *
     * @param mixed|null $key Path or array of paths to clean
     * @param boolean $format Format option
     */
    public function clear($key = null, $format = false)
    {
        if (is_string($key)) {
            // Iterate path
            $keys = explode('.', $key);
            $data = &$this->data;
            foreach ($keys as $key) {
                if (!isset($data[$key]) || !is_array($data[$key])) {
                    if ($format === true) {
                        $data[$key] = [];
                    } else {
                        return;
                    }
                }
                $data = &$data[$key];
            }
            // Clear path
            $data = [];
        } elseif (is_array($key)) {
            // Iterate array
            foreach ($key as $k) {
                $this->clear($k, $format);
            }
        } elseif (is_null($key)) {
            // Clear all data
            $this->data = [];
        }
    }

    /**
     * Set data
     *
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * Set data as a reference
     *
     * @param array $data
     */
    public function setDataAsRef(array &$data)
    {
        $this->data = &$data;
    }

    /**
     * ArrayAccess abstract methods
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetUnset($offset)
    {
        $this->delete($offset);
    }

    /**
     * Magic methods
     */
    public function __set($key, $value = null)
    {
        $this->set($key, $value);
    }

    public function __get($key)
    {
        return $this->get($key);
    }

    public function __isset($key)
    {
        return $this->has($key);
    }

    public function __unset($key)
    {
        $this->delete($key);
    }
}
