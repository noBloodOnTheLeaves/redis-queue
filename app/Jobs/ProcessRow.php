<?php

namespace App\Jobs;

use App\Models\Row;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessRow implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $data;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->data = array_map(function ($value) {
            return [
                'id' => $value['A'],
                'name' => $value['B'],
                'date' => $value['C'],
            ];
        }, $data);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->batch()->cancelled()) {
            $this->batch()->recordFailedJob($this->batch()->id, new \Exception());
            return;
        }

        Row::query()->insert($this->data);
    }

}
