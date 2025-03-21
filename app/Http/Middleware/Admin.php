<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Admin
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && (Auth::user()->hasRole('administrator') || Auth::user()->hasRole('owner'))) {
            return $next($request);
        }
        
        return redirect('dashboard')->with('flash_message_warning', 'Access denied. Admin only.');
    }
}