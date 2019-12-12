<?php

namespace LinhHa\RobotsCounter\Models;

use Illuminate\Database\Eloquent\Model;

class RobotsCounterReport extends Model
{
    protected $table = 'robots_counter_reports';

    protected $fillable = [
        'bot', 'report_date', 'visited_times', 'min_execution_time', 'max_execution_time', 'average_execution_time', 'by_hour'
    ];

    protected $hidden = ['created_at', 'updated_at'];


}
