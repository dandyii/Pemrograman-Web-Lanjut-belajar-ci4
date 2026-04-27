<?php

namespace App\Controllers;

class ProfileController extends BaseController
{
    public function index(): string
    {
        return view('v_profile');
    }
}
