<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class NavLink extends Component
{

    public string $routeName;

    public function __construct($routeName)
    {
        $this->routeName = $routeName;
    }

    public function render(): View|Closure|string
    {
        return view('components.nav-link');
    }
}
