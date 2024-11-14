<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class NavLink extends Component
{
    public string $routeName;
    public string $title;

    public function __construct($routeName, $title)
    {
        $this->routeName = $routeName;
        $this->title = $title;
    }

    public function render(): View|Closure|string
    {
        return view('components.nav-link');
    }
}
