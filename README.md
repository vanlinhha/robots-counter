# Robots counter
This package allow you to track how many bots visit your website, their frequency and time execution for each request.


### Installation
In your project folder, run

<code>composer require vanlinhha/robots-counter</code>

Aftef finish, publish vendor by this command:

<code>php artisan vendor:publish --provider="LinhHa\RobotsCounter\RobotsCounterServiceProvider"</code>

and <code>php artisan migrate</code> to run migration file

### Usage Instructions
This package works by using a middleware, logging every request performed by bots in a log file, you can rename the middleware in <code>config/robots_counter.php</code> file.

If you want the middleware works for every request, just put its class <code>\LinhHa\RobotsCounter\Middleware\RobotsCounterMiddleware::class</code> in array <code>$middleware </code> in <code>app/Http/Kernel.php</code>
But the best practise is using this middleware for routes need reporting for better performance.
Also, you can config your accepted request methods you want to be in your log.

Logs are saved to database by command <code>robot:report</code>, this command is scheduled to run everyday. You can use it to make report for specific day, use <code>php artisan robot:report --help</code> to see usage. 


We provide a route named <code>api.robots.counter</code> so you can easily make a report from it. 
This route uses GET method and accepts 3 parameters:<br>
<code>bot</code> : bot name you want to make report<br>
<code>range</code>: Range time you want to make report, <code>m</code> for last 30 days and <code>w</code> for last 7 days, the default value is <code>w</code>. <br>
<code>type</code>: Report by day or by hour, <code>day</code> for by day and <code>hour</code> for by hour, default is <code>hour</code>.


