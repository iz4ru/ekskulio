<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Log::with('user');

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $query->whereHas('user', function ($q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(username) LIKE ?', ["%{$search}%"]);
            })->orWhereRaw('LOWER(activity) LIKE ?', ["%{$search}%"])
            ->orWhereRaw('LOWER(detail) LIKE ?', ["%{$search}%"]);
        }

        $x['logs'] = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();
        
        return view('role.kesiswaan.contents.logs.index', $x);
    }
}
