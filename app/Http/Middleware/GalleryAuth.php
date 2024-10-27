<?php

namespace App\Http\Middleware;

use App\Models\GalleryUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GalleryAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $tags = [9];
        $user = null;

        $cookie = $request->cookie('token');
        $tokenParam = $request->get('token');
        $token = $tokenParam ?? $cookie;

        if (!is_null($token)) {
            $user = GalleryUser::where('token', $token)->first();

            if ($user !== null) {
                $slug = null;

                if ($request->route()->hasParameter('slug')) {
                    $parameters = $request->route()->parameters();
                    $slug = $parameters['slug'];
                }

                $allowedTags = $user->tags()->get()->toArray();

                if ($slug !== null) {
                    $allowedTags = array_filter($allowedTags, function ($tag) use ($slug) {
                        return $tag['scope'] === $slug || $tag['scope'] === '' || $tag['scope'] === null;
                    });
                }

                $tags = array_map(function ($tag) {
                    return $tag['tag_id'];
                }, $allowedTags);

                in_array(9, $tags) ? $tags = ['*'] : $tags = [...$tags, 9];
            } else {
                $tags = [...$tags, 9];
            }
        }

        $request->session()->put('allowed_tags', $tags);
        $request->session()->put('token', $token);
        $request->session()->put('user', $user);

        $response = $next($request);
        if (!is_null($token)) {
            $response->withCookie(cookie('token', $token));
        }
        return $response;
    }
}
