<?php

namespace App\Http\Controllers\Api\FileParse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FileParseController extends Controller
{
    public function getData(string $path, $config = [
        'readDataOnly' => true,
        'removeHeader' => true,
    ]): array
    {
        $getReaderName = IOFactory::identify($path);
        $reader = IOFactory::createReader($getReaderName);
        $reader->setReadDataOnly($config['readDataOnly']);
        $spreadsheetData = $reader->load($path);
        $data = $spreadsheetData->getActiveSheet()->toArray(null, true, true, true);
        if($config['readDataOnly']){
            array_shift($data);
        }
        return $data;
    }
}
