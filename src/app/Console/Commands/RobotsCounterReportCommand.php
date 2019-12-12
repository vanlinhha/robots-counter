<?php

namespace LinhHa\RobotsCounter\Console\Commands;

use LinhHa\RobotsCounter\Models\RobotsCounterReport;
use Carbon\Carbon;
use Illuminate\Console\Command;
use DateTime;

class RobotsCounterReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'robot:report
    {--date=daily : range time needs to report [today, yesterday, week, month, range]}
    {--start= : range time needs to report [YYYY-MM-DD]}
    {--end= : range time needs to report [YYYY-MM-DD]}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Count robots report';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $date = $this->option('date');
        list($start, $end) = $this->parseDate($date);
        $dates  = $this->getDatesFromRange($start, $end);
        $robots = [];
        foreach ($dates as $date) {
            $file = storage_path('logs/robots-' . $date . '.log');
            if (!file_exists($file)) {
                $this->warn($date . ' no data ');
                continue;
            }
            $this->info($date . ' importing ');
            $bot_list = [];
            $this->processFile($file, $date, $robots, $bot_list);
            $list_all_bot_info[] = $robots;
        }

        if(empty($list_all_bot_info))
            return;
    
        for ($i = 0; $i < count($list_all_bot_info); $i++) {
            foreach ($list_all_bot_info[$i] as $day => $robot_info) {
                foreach ($robot_info as $robot_name => $info) {
                    RobotsCounterReport::updateOrCreate(
                        [
                            'bot'         => $robot_name,
                            'report_date' => $day],
                        [
                            'visited_times'          => $info['visited_times'],
                            'average_execution_time' => $info['average_execution_time'],
                            'max_execution_time'     => $info['max_execution_time'],
                            'min_execution_time'     => $info['min_execution_time'],
                            'by_hour'                => json_encode($info['by_hour'])
                        ]);
                }
            }
        }
    }

    public function processFile($file, $date, &$robots, $bot_list)
    {
        $handle = fopen($file, "r");
        while (!feof($handle)) {
            $line = fgets($handle);
            if (strlen($line)) {
                $bot_request_time = explode(']', $line)[0];
                $bot_request_time = str_replace('[', '', $bot_request_time);
                $bot_request_time = (int)date('H', strtotime($bot_request_time));

                $bot_info           = explode('EMERGENCY: ', $line)[1];
                $bot_name           = explode(' ', $bot_info)[0];
                $bot_execution_time = explode(' ', $bot_info)[1];

                if (!in_array($bot_name, $bot_list)) {
                    $bot_list[] = $bot_name;
                    for ($i = 0; $i < 24; $i++) {
                        $time = (int)date("H", strtotime($date . '00:00:00') + 3600 * $i);
                        $robots[$date][$bot_name]['by_hour'][$time]['visited_times']          = 0;
                        $robots[$date][$bot_name]['by_hour'][$time]['average_execution_time'] = 0;
                    }
                }
                $robots[$date][$bot_name]['by_hour'][$bot_request_time]['average_execution_time'] = (int)(
                    ((int)$bot_execution_time + $robots[$date][$bot_name]['by_hour'][$bot_request_time]['average_execution_time'] * $robots[$date][$bot_name]['by_hour'][$bot_request_time]['visited_times'])
                    / ($robots[$date][$bot_name]['by_hour'][$bot_request_time]['visited_times'] + 1));
                $robots[$date][$bot_name]['by_hour'][$bot_request_time]['visited_times']++;

                if (array_key_exists('visited_times', $robots[$date][$bot_name])) {
                    $robots[$date][$bot_name]['visited_times']++;
                    $robots[$date][$bot_name]['total_time'] += (int)$bot_execution_time;

                    $robots[$date][$bot_name]['average_execution_time'] =
                        (int)($robots[$date][$bot_name]['total_time'] / $robots[$date][$bot_name]['visited_times']);
                    $robots[$date][$bot_name]['max_execution_time']     = (int)$bot_execution_time > $robots[$date][$bot_name]['max_execution_time'] ?
                        (int)$bot_execution_time : $robots[$date][$bot_name]['max_execution_time'];
                    $robots[$date][$bot_name]['min_execution_time']     = (int)$bot_execution_time < $robots[$date][$bot_name]['min_execution_time'] ?
                        (int)$bot_execution_time : $robots[$date][$bot_name]['min_execution_time'];
                } else {
                    $robots[$date][$bot_name]['visited_times']          = 1;
                    $robots[$date][$bot_name]['total_time']             = (int)$bot_execution_time;
                    $robots[$date][$bot_name]['average_execution_time'] = (int)$bot_execution_time;
                    $robots[$date][$bot_name]['max_execution_time']     = (int)$bot_execution_time;
                    $robots[$date][$bot_name]['min_execution_time']     = (int)$bot_execution_time;
                }
            }
        }
        fclose($handle);
    }

    /**
     * @param $date
     *
     * @return array [start, end]
     */
    private function parseDate($date)
    {
        $now = new Carbon();
        switch ($date) {
            case "today":// chi count cho hom nay
                $end   = $now->setTime(23, 59, 59)->format('Y-m-d');
                $start = $now->setTime(0, 0, 0)->format('Y-m-d');
                break;
            case "yesterday":// count cho hom qua
                $now   = new Carbon();
                $end   = $now->setTime(23, 59, 59)->format('Y-m-d');
                $start = $now->sub(new \DateInterval('P1D'))->setTime(0, 0, 0)->format('Y-m-d');
                break;
            case "week":// count cho tuan nay
                $now   = new Carbon();
                $end   = $now->setTime(23, 59, 59)->format('Y-m-d');
                $start = $now->sub(new \DateInterval('P1W'))->setTime(0, 0, 0)->format('Y-m-d');
                break;
            case "month":// count cho thang nay
                $now   = new Carbon();
                $end   = $now->setTime(23, 59, 59)->format('Y-m-d');
                $start = $now->sub(new \DateInterval('P1M'))->setTime(0, 0, 0)->format('Y-m-d');
                break;
            case "range":// count lai tat ca
                $start = new Carbon($this->option('start'));
                $start = $start->setTime(00, 00, 00)->format('Y-m-d');
                $end   = new Carbon($this->option('end'));
                $end   = $end->setTime(23, 59, 59)->format('Y-m-d');
                break;
            case "daily":// count cho hom nay va hom qua
                $now   = new Carbon();
                $end   = $now->setTime(23, 59, 59)->format('Y-m-d');
                $start = $now->sub(new \DateInterval('P1D'))->setTime(0, 0, 0)->format('Y-m-d');
                break;
            default:
                $now   = new Carbon($date);
                $end   = $now->setTime(23, 59, 59)->format('Y-m-d');
                $start = $now->setTime(0, 0, 0)->format('Y-m-d');
                break;
        }
        return [$start, $end];
    }

    function getDatesFromRange($start, $end, $format = 'Y-m-d')
    {
        $array    = array();
        $interval = new \DateInterval('P1D');
        $realEnd  = new DateTime($end);
        $realEnd->add($interval);
        $period = new \DatePeriod(new DateTime($start), $interval, $realEnd);
        foreach ($period as $date) {
            $array[] = $date->format($format);
        }
        return $array;
    }
}
