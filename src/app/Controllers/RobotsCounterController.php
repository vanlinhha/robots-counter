<?php

namespace LinhHa\RobotsCounter\App\Controllers;

use App\Models\RobotsCounterReport;
use Illuminate\Http\Request;


class RobotsCounterController
{
    function index(Request $request)
    {
        $bot   = $request->bot;
        $range = $request->range == 'm' ? 'm' : 'w';
        $type  = $request->type == 'day' ? 'day' : 'hour';

        $end = date('Y-m-d', strtotime(now()));
        if ($range == 'w') {
            $start = date('Y-m-d', strtotime(now()->subRealWeek()));
        } else {
            $start = date('Y-m-d', strtotime(now()->subRealMonth()));
        }
        $builder = RobotsCounterReport::query();
        $builder->whereBetween('report_date', [$start, $end]);

        if (trim($bot)) {
            $builder->where('bot', $bot);
        }
        $robots    = $builder->get()->toArray();
        $all_items = [];
        foreach ($robots as $robot) {
            $robot['by_hour'] = json_decode($robot['by_hour'], true);
            if ($type == 'hour') {
                foreach ($robot['by_hour'] as $item => $value) {
                    $temp                           = [];
                    $temp['report_date']            = $robot['report_date'];
                    $temp['hour']                   = $item;
                    $temp['bot']                    = $robot['bot'];
                    $temp['visited_times']          = $value['visited_times'];
                    $temp['average_execution_time'] = $value['average_execution_time'];
                    $all_items[]                    = $temp;
                }
            } else {
                unset($robot['by_hour']);
                $all_items[] = $robot;
            }
        }
        $lists['start'] = $start;
        $lists['end']   = $end;
        $lists['items'] = $all_items;
        return response()->json($lists);
    }
}
