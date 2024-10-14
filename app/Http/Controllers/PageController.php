<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController
{
    function impressum()
    {
        return view('impressum');
    }
}
