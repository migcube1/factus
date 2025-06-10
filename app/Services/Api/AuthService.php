<?php

namespace App\Services\Api;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;



class AuthService
{

    protected $url;


    public function __construct()
    {
        $this->url = config('api.url');
    }


    public function getAccessToken($user)
    {

        //Obtener access token
        $response = Http::asForm()->post($this->url . '/oauth/token', [
            'grant_type' => 'password',
            'client_id' => config('api.client_id'),
            'client_secret' => config('api.client_secret'),
            'username' => config('api.username'),
            'password' => config('api.password'),
        ]);

        if (!$response->successful()) {

            // En caso de error, devolver el mensaje de error
            return response()->json([
                'error' => 'No se pudo obtener el token.',
                'message' => $response->body(),
            ], 401);
        }

        $data = $response->json();

        $this->createAccessToken($data, $user);

        return $data;
    }

    public function resolveAuthorization($user)
    {
        if (!$user->accessToken || $user->accessToken->expires_at <=  now()) {


            //Obtener access token
            $response = Http::asForm()
                ->post($this->url . '/oauth/token', [
                    'grant_type' => 'refresh_token',
                    'client_id' => config('api.client_id'),
                    'client_secret' => config('api.client_secret'),
                    'refresh_token' => $user->accessToken->refresh_token,
                ]);

            if (!$response->successful()) {
                $user->accessToken->delete();

                return response()->json([
                    'error' => 'No se pudo refrescar el token',
                    'message' => $response->body(),
                ], 401);
            }

            $data = $response->json();

            //Actualizamos el access_token
            $user->accessToken->update([
                'access_token' =>  $data['access_token'],
                'refresh_token' => $data['refresh_token'],
                'expires_at' => now()
                    ->addSecond($data['expires_in']),
            ]);

            return $data['access_token'];
        }

        // Si aún es válido, retorna el actual
        return $user->accessToken->access_token;
    }


    private function createAccessToken($data, $user)
    {
        return $user->accessToken()->create([
            'service_id' => Str::uuid(),
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'],
            'expires_at' => now()->addSecond($data['expires_in']),
        ]);
    }
}
