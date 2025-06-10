<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerTax;
use App\Models\TypeIdentityDocument;
use App\Services\Api\AuthService;
use App\Services\Api\BillService;


class FactusController extends Controller
{
    public function __construct(
        protected BillService $billService,
        protected AuthService $authService
    ) {}

    public function index()
    {
        $access_token = $this->authService
            ->resolveAuthorization($this->getAuthUser());

        $data = $this->billService->getBills($access_token);

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
