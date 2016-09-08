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
}
