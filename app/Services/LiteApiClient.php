<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;

class LiteApiClient
{
    protected function request(): PendingRequest
    {
        $http = Http::baseUrl(config('services.lite_api.base_url'))
            ->acceptJson()
            ->asJson();

        if ($token = Session::get('lite_api_token')) {
            $http = $http->withToken($token);
        }

        return $http;
    }

    public function get(string $uri, array $query = []): Response
    {
        return $this->request()->get($uri, $query);
    }

    public function post(string $uri, array $data = []): Response
    {
        return $this->request()->post($uri, $data);
    }

    public function patch(string $uri, array $data = []): Response
    {
        return $this->request()->patch($uri, $data);
    }

    public function put(string $uri, array $data = []): Response
    {
        return $this->request()->put($uri, $data);
    }

    public function delete(string $uri, array $data = []): Response
    {
        return $this->request()->delete($uri, $data);
    }
}
