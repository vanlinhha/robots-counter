<?php

namespace LinhHa\RobotsCounter\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;

class RobotsCounterMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $agent = new Agent();
        if ($agent->isRobot()  && !$request->ajax()) {
            if (!in_array($request->method(), config('robots_counter.accepted_methods')))
                return $response;
            Log::channel(config('robots_counter.log_channel_name'))
                ->emergency($agent->robot()
                    . ' '
                    . (int)((microtime(true) - LARAVEL_START_EXECUTION_TIME) * 1000)
                    . ' '
                    . $request->getRequestUri());
        }
        return $response;
    }
}
