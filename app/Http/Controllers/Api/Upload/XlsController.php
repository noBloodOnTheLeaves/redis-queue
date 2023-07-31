<?php

namespace App\Http\Controllers\Api\Upload;

use App\Http\Controllers\Api\FileParse\FileParseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\UploadXlsRequest;
use App\Jobs\ProcessRow;
use Redis;

class XlsController extends Controller
{
    /**
     * @throws \Throwable
     */
    public function upload(UploadXlsRequest $request)
    {
        $rowsFromXlsx = $request->file('file');
        $path = $rowsFromXlsx->path();
        $fileName = $rowsFromXlsx->getFilename();
        $data = (new FileParseController())->getData($path);

        $chunks = array_chunk($data, 1000);
        $total = count($data);

        $redis = new Redis();
        $redis->connect('redis');
        $redis->DEL($fileName);

        foreach ($chunks as $chunk){
            ProcessRow::dispatch($chunk, $fileName, $total);
        }

        /*return Bus::batch($batch)->then(function (Batch $batch) {
            // All jobs completed successfully...
        })->catch(function (Batch $batch, Throwable $e) {
            // First batch job failure detected...
        })->finally(function (Batch $batch) {
            // The batch has finished executing...
        })->onConnection('redis')->dispatch();*/

        return response()->json([
            'status' => 'success',
            'total' => count($data),
            'file_name' => $fileName
        ]);
    }


}
