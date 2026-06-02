<?php

namespace App\Http\Controllers;

class DocumentationController extends Controller
{
    public function userManual()
    {
        return view('docs.user-manual');
    }

    public function guidelines()
    {
        return view('docs.guidelines');
    }
}
