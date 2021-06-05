<?php

interface Transport {
    public static function getContent(string $url): string;
}

class FileGetContents implements Transport {
    public static function getContent(string $url): string {
        $options = [
            'http' => [
                'timeout' => 10,
                'ignore_errors' => false,
                'header'  => "Content-type: application/json\r\n".
                             "Accepts: application/json; application/xml; text/csv; application/vnd.PHE-COVID19.v1+json; application/vnd.PHE-COVID19.v1+xml"."\r\n".
                             "Accept-Encoding: gzip"."\r\n",
                'method'  => 'GET',
            ],
        ];
        $context = stream_context_create($options);
        set_error_handler(function ($errno, $errstr, $errfile, $errline, array $errcontext) {
                throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
            }
        );
        try {
            $result = file_get_contents($url, false, $context);
        } finally {
            restore_error_handler();
        }
        return gzdecode($result);
    }
}


class ApiJSONFormated {

    private const URL = 'https://api.coronavirus.data.gov.uk/v1/data?filters=areaType=nation;areaName=england&structure={"date":"date","newCases":"newCasesByPublishDate"}&format=xml';

    private static function getRemoteXMLData(Transport $transport): SimpleXMLElement {
        $result = $transport::getContent(self::URL);
        $xml = simplexml_load_string($result);
        return $xml;
    }
    
    public static function getData(Transport $transport): string {
        $xml = self::getRemoteXMLData($transport);
        $output = [];
        foreach($xml->data as $row) {
            $output[] = $row;
        }
        return json_encode($output);
    }

}


class ApiCSVFormated extends ApiJSONFormated {

    public static function getData(Transport $transport): string {
        $json = parent::getData($transport);
        $json = json_decode($json);
        $csv = fopen('php://temp', 'r+');
        foreach($json as $row) {
            fputcsv($csv, [$row->date,$row->newCases]);
        }
        rewind($csv);
        $output = stream_get_contents($csv);
        fclose($csv);
        return $output;
    }

}

$transport = new FileGetContents;

try {
    $data = ApiJSONFormated::getData($transport);
} catch(ErrorException | TypeError $e) {
    echo $e->getMessage();
}

var_dump($data);

try {
    $data = ApiCSVFormated::getData($transport);
} catch(ErrorException | TypeError $e) {
    echo $e->getMessage();
}

var_dump($data);
