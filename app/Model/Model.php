<?php

namespace App\Model;

class Model
{
    /**
     * @var array
     */
    protected $fillable = [];

    /**
     * @var \Illuminate\Support\Collection
     */
    protected $data;

    public function __construct()
    {
        $this->data = collect([]);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set(string $name, $value)
    {
        $this->data->put($name, $value);
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
     * @param array $data
     * @return \App\Model\Model
     */
    public function setData(array $data)
    {
        collect($data)->each(function ($value, $name) {
            if (in_array($name, $this->fillable)) {
                $this->{$name} = $value;
            }
        });

        return $this;
    }

    /**
     * @return \Illuminate\Support\Collection|\Tightenco\Collect\Support\Collection
     */
    public function toCollection()
    {
        return $this->data->map(function ($var) {
            return is_object($var) ? $var->toCollection() : $var;
        });
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this
            ->toCollection()
            ->toArray();
    }
}
