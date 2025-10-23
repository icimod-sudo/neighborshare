<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PwaController extends Controller
{
    public function manifest()
    {
        return response()->json([
            'name' => 'Gwache App - Agri Commerce',
            'short_name' => 'Gwache App',
            // ... rest of manifest
        ]);
    }

    public function offline()
    {
        return view('offline');
    }
}
