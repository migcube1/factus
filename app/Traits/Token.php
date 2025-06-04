<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

trait Token
{
    public function getAccessToken($user)
    {
        $url = config('api.url');

        //Obtener access token
        $response = Http::asForm()->post($url . '/oauth/token', [
            'grant_type' => 'password',
            'client_id' => config('api.client_id'),
            'client_secret' => config('api.client_secret'),
            'username' => config('api.username'),
            'password' => config('api.password'),
        ]);

        if (!$response->successful()) {

            // En caso de error, devolver el mensaje de error
            return response()->json([
                'error' => 'No se pudo obtener el token',
                'message' => $response->body(),
            ], 400);
        }

        // Acceder a los datos del token
        return $response->json();
    }

    public function resolveAuthorization()
    {
        $user = auth()->user();

        if ($user->accessToken->expires_at <=  now()) {
            $url = config('api.url');

            //Obtener access token
            $response = Http::asForm()->post($url . '/oauth/token', [
                'grant_type' => 'refresh_token',
                'client_id' => config('api.client_id'),
                'client_secret' => config('api.client_secret'),
                'refresh_token' => $user->accessToken->refresh_token,
            ]);

            $data = $response->json();

            //Actualizamos el access_token
            $user->accessToken->update([
                'access_token' =>  $data['access_token'],
                'refresh_token' => $data['refresh_token'],
                'expires_at' => now()
                    ->addSecond($data['expires_in']),
            ]);
        }
    }


    public function createAccessToken($data, $user)
    {
        return $user->accessToken()->create([
            'service_id' => Str::uuid(),
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'expires_at' => now()->addSecond($data['expires_in']),
        ]);
    }
}
