<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function welcome(): View
    {
        return view('welcome');
    }

    public function dashboard(Request $request): View
    {
        return view('home', [
            'user' => $request->user(),
            'fcmEnabled' => filled(config('larafire.vapid_key')) && filled(config('larafire.web.apiKey')),
        ]);
    }
}
