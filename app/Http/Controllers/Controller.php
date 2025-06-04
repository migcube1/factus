<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }
}
