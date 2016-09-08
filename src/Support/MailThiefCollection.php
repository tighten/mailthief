<?php

namespace MailThief\Support;

use Illuminate\Support\Collection;

class MailThiefCollection extends Collection
{
    /**
     * Identical to the 5.3 implementation of contains
     */
    public function contains($key, $value = null)
    {
        if (func_num_args() == 2) {
            return $this->contains(function ($item) use ($key, $value) {
                return data_get($item, $key) == $value;
            });
        }

        if ($this->useAsCallable($key)) {
            return ! is_null($this->first($key));
        }

        return in_array($key, $this->items);
    }

    /**
     * 5.3 implementation of Arr::first()
     */
    public function first(callable $callback = null, $default = null)
    {
        $array = $this->items;

        if (is_null($callback)) {
            if (empty($array)) {
                return value($default);
            }
            foreach ($array as $item) {
                return $item;
            }
        }
        foreach ($array as $key => $value) {
            if (call_user_func($callback, $value, $key)) {
                return $value;
            }
        }
        return value($default);
    }
}
