<?php

namespace App\Jobs;

use App\Models\Row;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Redis;

class ProcessRow implements ShouldQueue
{
    use  Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $data;
    public string $key;
    public int $total;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data, string $key, int $total)
    {
        $this->data = array_map(function ($value) {
            return [
                'id' => $value['A'],
                'name' => $value['B'],
                'date' => $value['C'],
            ];
        }, $data);
        $this->key = $key;
        $this->total = $total;
    }

    /**
     * Execute the job.
     * @throws \RedisException
     */
    public function handle(): void
    {
        sleep(5);
        $redis = new Redis();
        $redis->connect('redis');

        if (Row::query()->insert($this->data)) {
            try {
                $previousValue = (int)$redis->hGet($this->key, 'previous_value') ?? 0;
                $newValue = $previousValue + count($this->data);
                $progress = $newValue / $this->total;
                $redis->hset($this->key, 'previous_value', $newValue);
                $redis->hset($this->key,'total', $this->total);
                $redis->hset($this->key,'progress', $progress);
                event(new \App\Events\RowEvent($this->key, $progress));
            } catch (\RedisException $e) {
                Log::error($e->getMessage());
            }
        }
    }

}
