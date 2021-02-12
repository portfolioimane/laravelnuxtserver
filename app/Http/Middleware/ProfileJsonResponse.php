<?php

namespace App\Http\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Closure;

class ProfileJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response=$next($request);
        if(! app()->bound('debugbar') || ! app('debugbar')->isEnabled() ){
            return $response;
        }
       // if($response instanceof JsonResponse && $request->has('_debug')){
       //    $response->setData(array_merge($response->getData(true), [
       //        '_debugbar'=>app('debugbar')->getData(true)
       //    ]));
           if($response instanceof JsonResponse && $request->has('_debug')){
             $response->setData(array_merge([
              '_debugbar'=>Arr::only(app('debugbar')->getData(true), 'queries')
            ], $response->getData(true)));
        }
        return $response;
    }
}
