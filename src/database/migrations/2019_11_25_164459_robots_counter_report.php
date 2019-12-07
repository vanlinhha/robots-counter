<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RobotsCounterReport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('robots_counter_reports', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('report_date');
            $table->string('bot');
            $table->integer('visited_times');
            $table->integer('min_execution_time');
            $table->integer('max_execution_time');
            $table->integer('average_execution_time');
            $table->longText('by_hour');
            $table->timestamps();
            $table->unique(['report_date', 'bot']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('robots_counter_report');
    }
}
