<?php

namespace App\Http\Controllers\Api\Upload;

use App\Http\Controllers\Api\FileParse\FileParseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\UploadXlsRequest;
use App\Jobs\ProcessRow;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Throwable;

class XlsController extends Controller
{
    /**
     * @throws \Throwable
     */
    public function upload(UploadXlsRequest $request): Batch
    {
        $rowsFromXlsx = $request->file('file');
        $path = $rowsFromXlsx->path();
        $data = (new FileParseController())->getData($path);

        $chunks = array_chunk($data, 1000);

        $batch = array_map(function ($chunk) {
            return new ProcessRow($chunk);
        }, $chunks);

        return Bus::batch($batch)->then(function (Batch $batch) {
            // All jobs completed successfully...
        })->catch(function (Batch $batch, Throwable $e) {
            // First batch job failure detected...
        })->finally(function (Batch $batch) {
            // The batch has finished executing...
        })->onConnection('redis')->dispatch();
    }

    public function progress($id): ?Batch
    {
        return Bus::findBatch($id);
    }
}
