<?php

namespace App\Http;

use Illuminate\Http\JsonResponse as BaseJsonResponse;

final class JsonResponse extends BaseJsonResponse
{
    /**
     * @var array
     */
    protected $additional = [];

    /**
     * @var mixed
     */
    public static $wrapper = 'data';

    /**
     * Include additional meta data to the response.
     *
     * @param  array  $additional
     * @return $this
     */
    public function with(array $additional = [])
    {
        $this->additional = $additional;

        $this->setData($this->getData());

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setData($data = [])
    {
        if (is_array($data)) {
            $data = [static::$wrapper => $data['data'] ?? $data];
        }

        else if (is_object($data)) {
            $data = [static::$wrapper => $data->data ?? $data];
        }

        if (is_array($data) && ! blank($this->additional)) {
            $data['meta'] = $this->additional;
        }

        return parent::setData($data);
    }
}
