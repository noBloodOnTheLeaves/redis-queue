<?php

namespace App\Http\Controllers\Api\FileParse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FileParseController extends Controller
{
    public function getData(string $path, $config = [
        'readDataOnly' => false,
        'removeHeader' => true,
    ]): array
    {
        $getReaderName = IOFactory::identify($path);
        $reader = IOFactory::createReader($getReaderName);
        $reader->setReadDataOnly($config['readDataOnly']);
        $reader->setReadEmptyCells(false);
        $spreadsheetData = $reader->load($path);
        $data = $spreadsheetData->getActiveSheet()->toArray(null, true, true, true);
        if($config['removeHeader']){
            array_shift($data);
        }
        return $data;
    }
}
