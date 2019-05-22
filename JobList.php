<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;

class JobList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'job:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List pending jobs';


    /**
     * Headers for display in table.
     *
     * @var array
     */
    private $headers = [
        'id',
        'queue',
        'name',
        'attempts',
        'reserved_at',
        'available_at',
        'created_at',
    ];

    /**
     * Table data.
     *
     * @var array
     */
    private $data = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->loadJobs();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (count($this->data)) {
            $this->table($this->headers, $this->data);
        } else {
            $this->info('No jobs available to display.');
        }
    }

    /**
     * Decode job.
     *
     * @param $job
     * @return mixed
     */
    private function decodeJob($job)
    {
        return json_decode($job->payload);
    }

    /**
     * Format timestamps.
     *
     * @param $timestamp
     * @return false|string
     */
    private function parseDate($timestamp)
    {
        return $timestamp ? date('Y-m-d H:i:s', $timestamp) : 'NULL';
    }

    /**
     * Load all available jobs from database.
     *
     * @return void
     */
    private function loadJobs()
    {
        $jobs = DB::select(DB::raw('SELECT * FROM jobs'));

        if (count($jobs)) {

            foreach ($jobs as $job) {
                array_push($this->data, [
                    'id' => $job->id,
                    'queue' => $job->queue,
                    'name' => $this->decodeJob($job)->displayName,
                    'attempts' => $job->attempts,
                    'reserved_at' => $this->parseDate($job->reserved_at),
                    'available_at' => $this->parseDate($job->available_at),
                    'created_at' => $this->parseDate($job->created_at),
                ]);
            }
        }
    }
}

