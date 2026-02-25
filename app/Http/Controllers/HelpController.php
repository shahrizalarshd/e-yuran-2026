<?php

namespace App\Http\Controllers;

class HelpController extends Controller
{
    public function admin()
    {
        return view('admin.help');
    }

    public function resident()
    {
        return view('resident.help');
    }
}
