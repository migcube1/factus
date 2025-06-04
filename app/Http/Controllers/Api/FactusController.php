<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerTax;
use App\Models\TypeIdentityDocument;
use App\Traits\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class FactusController extends Controller
{

    use Token;

    public function index()
    {
        $this->resolveAuthorization();

        $url = config('api.url');

        //Obtener access token
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->user->accessToken->access_token
        ])->get($url . "/v1/bills");

        if (!$response->successful()) {
            return response()->json([
                'error' => 'Ocurrió un error.',
                'message' => $response->body(),
            ], 400);
        }

        $data = $response->json();


        return view('factus.index', [
            'invoices' => $data['data']['data'],
        ]);
    }


    public function create()
    {

        return view('factus.index', [
            'type_identity_documents' => TypeIdentityDocument::pluck('code', 'name'),
            'customer_taxes' => CustomerTax::pluck('code', 'name'),
        ]);
    }
}
