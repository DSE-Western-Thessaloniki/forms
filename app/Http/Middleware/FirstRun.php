<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Option;

class FirstRun
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
        $check = Option::where('name', 'first_run')->first();
        $first_run = 0;
        if ($check) {
            $first_run = intval($check->value);
        }

        // If setup didn't run go to /setup
        if ($first_run) {
            if ($request->path() != 'setup')
                return redirect('/setup');
        }
        else {
            // Site already setup. Redirect to /
            if ($request->path() == 'setup')
                return redirect('/');
        }
        return $next($request);
    }
}
