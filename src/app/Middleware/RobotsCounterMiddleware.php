<?php

namespace LinhHa\RobotsCounter\app\Middleware;

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
        if ($agent->isRobot() && $request->isMethod('GET') && !$request->ajax()) {
            Log::channel('robot_counter_log')
                ->emergency($agent->robot()
                    . ' '
                    . (int)((microtime(true) - LARAVEL_START_EXECUTION_TIME) * 1000)
                    . ' '
                    . $request->getRequestUri());
        }

        return $response;
    }
}
