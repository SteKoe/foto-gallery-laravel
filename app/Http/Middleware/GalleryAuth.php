<?php

namespace App\Http\Middleware;

use App\Models\GalleryUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GalleryAuth
{

    private string $PUBLIC_GALLERY_TAG_ID;

    public function __construct()
    {
        $this->PUBLIC_GALLERY_TAG_ID = env('APP_PUBLIC_GALLERY_TAG_ID');
    }

    public function handle(Request $request, Closure $next): Response
    {
        $tags = [$this->PUBLIC_GALLERY_TAG_ID];
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
                    // Filter out scopes that do not match the current slot or are not acting as "global"
                    $allowedTags = array_filter($allowedTags, function ($tag) use ($slug) {
                        return $tag['scope'] === $slug || $tag['scope'] === '' || $tag['scope'] === null || $tag['scope'] === 'global';
                    });
                }

                $tags = array_map(function ($tag) {
                    return $tag['tag_id'];
                }, $allowedTags);

                in_array($this->PUBLIC_GALLERY_TAG_ID, $tags) ? $tags = ['*'] : $tags = [...$tags, $this->PUBLIC_GALLERY_TAG_ID];
            } else {
                $tags = [...$tags, $this->PUBLIC_GALLERY_TAG_ID];
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
