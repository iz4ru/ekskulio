<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UnauthorizedUserController extends Controller
{
    public function noExtracurricular()
    {
        return view('role.unauthorized.no-extracurricular');
    }
}
