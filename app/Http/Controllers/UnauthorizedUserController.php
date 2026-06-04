<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnauthorizedUserController extends Controller
{
    public function noExtracurricular()
    {
        $user = Auth::user();
        $hasExtracurricular = $user->extracurricularList()->exists();

        if ($user->role !== 'pembina' || $hasExtracurricular) {
            return redirect('/');
        }
        
        return view('role.unauthorized.no-extracurricular');
    }
}
