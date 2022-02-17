<?php

namespace App\Model;

use Illuminate\Contracts\Support\Arrayable;

abstract class Model implements Arrayable
{
    /**
     * @var array
     */
    protected $fillable = [];

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $data;

    /**
     * Model constructor
     */
    public function __construct()
    {
        $this->data = collect([]);
    }

    /**
     * Set data property according to fillable
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set(string $name, $value)
    {
        if (in_array($name, array_keys($this->fillable))) {
            $this->data->put($name, $value);
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->data->get($name);
    }

    /**
     * Populate model data
     * @param array $data
     * @return \App\Model\Model
     */
    public function setData(array $data)
    {
        collect($data)->each(function ($value, $name) {
            $this->{$name} = $this->castAttribute($name, $value);
        });

        return $this;
    }

    /**
     * return model data as collection
     * @return \Illuminate\Support\Collection|\Tightenco\Collect\Support\Collection
     */
    public function toCollection()
    {
        return $this->data->map(function ($var) {
            return $var;
        });
    }

    /**
     * return model data as array
     * @return array
     */
    public function toArray()
    {
        return $this
            ->toCollection()
            ->toArray();
    }

    /**
     * Cast an attribute to a native PHP type.
     *
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    protected function castAttribute(string $name, $value)
    {
        switch ($this->fillable[$name]) {
            case 'int':
            case 'integer':
                return (int)$value;
            case 'real':
            case 'float':
            case 'double':
                return (float)$value;
            case 'string':
                return (string)$value;
            case 'bool':
            case 'boolean':
                return (bool)$value;
            case 'object':
                return json_decode($value, true);
            case 'array':
            case 'json':
                return json_decode($value);
            default:
                return $value;
        }
    }
}
