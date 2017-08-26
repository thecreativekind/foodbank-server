<?php

namespace App\Jobs;

use App\SkillRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class LogSkillRequest implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $id;

    protected $request;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id = null, array $request)
    {
        $this->id = $id;
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        SkillRequest::create([
            'bank_id' => $this->id,
            'data' => json_encode($this->request),
        ]);
    }
}
