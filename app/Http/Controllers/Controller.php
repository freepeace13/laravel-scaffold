<?php

namespace App\Http\Controllers;

use App\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Create new json response instance.
     *
     * @param  mixed  $data
     * @param  int  $status
     * @param  array  $headers
     * @param  int  $options
     * @return App\Http\JsonResponse
     */
    protected function responseJson($data = null, $status = 200, $headers = [], $options = 0)
    {
        return new JsonResponse($data, $status, $headers, $options);
    }
}
