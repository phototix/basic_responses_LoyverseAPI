<?php
// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}
// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

}
if($json = file_get_contents("php://input")) {
    print_r($json);
    $data = $json;
}

$sendMessage="*[Alert!]* \nNew Normal WebHook Update from POS System \n".$data;
                                
$dataArray = array(
    'to_number' => '60169165315',
    'type' => 'text',
    'message' => $sendMessage
);

$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => "https://api.maytapi.com/api/037e4a24-af4e-4d58-9ed4-53e2b2b690c9/19734/sendMessage",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => json_encode($dataArray),
  CURLOPT_HTTPHEADER => array(
    "content-type: application/json",
    "x-maytapi-key: c12805d0-0ac9-4baa-b0a8-66668a6943e8"
  ),
));
$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);
?>