<?php
header('Content-Type: application/json');

$config = file_get_contents("config.json");
if ($config === false) {
    http_response_code(500);
    print_r(json_encode(array('status' =>'error' ,'code'=> 'config_error')));
    exit();
}
$config = json_decode($config, TRUE);
$pass = $config["pass"];
$storageZone = $config["storageZone"];
$pullZone = $config["pullZone"];
$externalPath = "https://" . $pullZone . ".b-cdn.net";
$storagePath = $config["storageServer"] . $storageZone;
$dirs = $config["dirs"];

function getBcdnDirectory($path) {
    global $pass, $storagePath, $externalPath;
    $curl = curl_init($storagePath . $path);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Accept: application/json", "AccessKey: " . $pass));
    $curlResult = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ($http_code !== 200) {
        http_response_code(500);
        print_r(json_encode(array('status' =>'error' ,'code'=> 'curl_error', 'result' => curl_error($curl))));
        curl_close($curl);
        exit();
    } else {
        $output = array();
        $fileArray = json_decode($curlResult);
        curl_close($curl);
        for ($i = 0; $i < count($fileArray); $i++) {
            $file = $fileArray[$i];
            $fileObj = array("filename" => $file->ObjectName, "directory" => $file->IsDirectory);
            if ($fileObj["directory"] === true) {
                $output = array_merge($output, json_decode(getBcdnDirectory($path . $fileObj["filename"] . "/")));
            }
            $fileObj["filename"] = $externalPath . $path . $fileObj["filename"];
            array_push($output, $fileObj);
        }
        return json_encode($output);
    }
}

if (array_key_exists("type", $_GET) && array_key_exists($_GET["type"], $dirs)) {
    print_r(getBcdnDirectory($dirs[$_GET["type"]]));
} else {
    http_response_code(500);
    print_r(json_encode(array('status' =>'error' ,'code'=> 'input_error')));
}
?>
