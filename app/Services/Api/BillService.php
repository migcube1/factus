<?php

namespace App\Services\Api;

use Illuminate\Support\Facades\Http;


class BillService
{
    protected $url;

    public function __construct()
    {
        $this->url = config('api.url');
    }

    // Get the bills for show
    public function getBills($access_token)
    {
        //Obtener access token
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token
        ])->get($this->url . "/v1/bills");

        if (!$response->successful()) {

            return response()->json([
                'error' => 'Ocurrió un error al obtener las facturas.',
                'message' => $response->body(),
            ], 400);
        }

        return $response->json();
    }
}
