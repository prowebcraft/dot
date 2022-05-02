<?php

namespace Prowebcraft;

use ArrayAccess;

/**
 * Dot Notation
 *
 * This class provides dot notation access to arrays, so it's easy to handle
 * multidimensional data in a clean way.
 */
class Dot implements \ArrayAccess, \Iterator, \Countable
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
        if (is_array($key)) {
            // Iterate array of paths and values
            foreach ($key as $k => $v) {
                self::addValue($array, $k, $v);
            }
        } else {
            // Iterate path
            $keys = explode('.', (string)$key);
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
        }
    }

    /**
     * Delete path or array of paths
     *
     * @param array $array Target array with data
     * @param mixed $key Path or array of paths to delete
     */
    public static function deleteValue(&$array, $key)
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
    public function get($key, $default = null, $asObject = false)
    {
        $value = self::getValue($this->data, $key, $default);
        if ($asObject && is_array($value)) {
            return new self($value);
        }

        return $value;
    }

    /**
     * Set value or array of values to path
     *
     * @param mixed $key Path or array of paths and values
     * @param mixed|null $value Value to set if path is not an array
     * @return $this
     */
    public function set($key, $value = null)
    {
        self::setValue($this->data, $key, $value);
        return $this;
    }

    /**
     * Add value or array of values to path
     *
     * @param mixed $key Path or array of paths and values
     * @param mixed|null $value Value to set if path is not an array
     * @param boolean $pop Helper to pop out last key if value is an array
     * @return $this
     */
    public function add($key, $value = null, $pop = false)
    {
        self::addValue($this->data, $key, $value);
        return $this;
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
     * @return $this
     */
    public function delete($key)
    {
        self::deleteValue($this->data, $key);
        return $this;
    }

    /**
     * Increase numeric value
     *
     * @param string $key
     * @param float $number
     * @return float
     */
    public function plus(string $key, float $number): float
    {
        $newAmount = $this->get($key, 0) + $number;
        $this->set($key, $newAmount);

        return $newAmount;
    }

    /**
     * Reduce numeric value
     *
     * @param string $key
     * @param float $number
     * @return float
     */
    public function minus(string $key, float $number): float
    {
        $newAmount = $this->get($key, 0) - $number;
        $this->set($key, $newAmount);

        return $newAmount;
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
     * @inheritDoc
     */
    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset): void
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

    /**
     * Check for emptiness
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return !(bool)count($this->data);
    }

    /**
     * Return all data as array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Return as json string
     *
     * @return false|string
     */
    public function toJson()
    {
        return json_encode($this->data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toJson();
    }

    /**
     * @return array
     */
    public function __toArray(): array
    {
        return $this->toArray();
    }

    /**
     * Return the current element
     * @link https://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current(): mixed
    {
        return current($this->data);
    }

    /**
     * Move forward to next element
     * @link https://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next(): void
    {
        next($this->data);
    }

    /**
     * Return the key of the current element
     * @link https://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key(): mixed
    {
        return key($this->data);
    }

    /**
     * Checks if current position is valid
     * @link https://php.net/manual/en/iterator.valid.php
     * @return bool The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid(): bool
    {
        $key = key($this->data);
        return ($key !== NULL && $key !== FALSE);
    }

    /**
     * Rewind the Iterator to the first element
     * @link https://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind(): void
    {
        reset($this->data);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        return count($this->data);
    }
}
