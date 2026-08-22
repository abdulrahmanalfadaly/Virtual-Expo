<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class SchoolLayout extends Component
{
    public function render(): View
    {
        return view('layouts.school');
    }
}
