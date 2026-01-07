<?php

namespace App\Plugins\ActiveDirectoryAuth\Controllers;

use App\Http\Controllers\Controller;

class SettingsController extends Controller
{
    public function index()
    {
        return view('active-directory-auth::settings');
    }
}
