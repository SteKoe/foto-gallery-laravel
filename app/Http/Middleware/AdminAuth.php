<?php

namespace App\Http\Middleware;

use App\Models\GalleryUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $cookie = $request->cookie('token');
        $tokenParam = $request->get('token');
        $token = $tokenParam ?? $cookie;

        if (is_null($token)) {
            return redirect('/');
        }

        $user = GalleryUser::where('token', $token)->first();
        if(is_null($user) || $user->is_admin !== true) {
            return redirect('/');
        }

        return $next($request)->withCookie(cookie('token', $token));
    }
}
