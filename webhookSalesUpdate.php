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

require 'vendor/autoload.php';
require 'controller/functions.php';
require "config.php";

$webhookSales = json_decode($data);
$receiptData = $webhookSales->receipts;
foreach($receiptData as $value){

    $receipt_number = $value->receipt_number;
    $order = $value->order;
    $receipt_date = $value->receipt_date;
    $total_money = $value->total_money;
    $total_tax = $value->total_tax;

    $customer_id = $value->customer_id;

    $customer_phone="";$customerURL="";$payURL="";
    if($customer_id<>""){
        $loyverse = new Pashkevich\Loyverse\Loyverse('75088e447ec14b6396e7a6a254fdd30d');
        $getCustomerData = json_encode($loyverse->customer($customer_id));
        $getCustomerData = json_decode($getCustomerData);
        $customer_phone = $getCustomerData->phoneNumber;
        if($customer_phone[0]=="0"){
            $customer_phone = substr($customer_phone, 1);
        }
        $payURL = get_tiny_url("https://checkinpay.asia/merchant?id=".$checkINmerchant."&branch=".$checkINbranch."&phone=".$customer_phone."&amount=".$total_money."&remarks=ORDERID+".$receipt_number);
        $customerURL = "\n\nPayment Link:\n".$payURL;
    }
    
    $receipt_date = date("d-m-Y", strtotime($receipt_date));
    
    $msgBody="OrderID: ".$receipt_number."\n"."Date: ".$receipt_date."\n";

    $line_items = $value->line_items;
    foreach($line_items as $itemValue){
        $item_name = $itemValue->item_name;
        $quantity = $itemValue->quantity;
        $price = $itemValue->price;
        $gross_total_money = $itemValue->gross_total_money;

        $itemList .= "============\n*".$item_name."*"."\nQuantity: ".$quantity."\n"."Total: ".number_format($gross_total_money, 2)."\n";
    }

    $payments = $value->payments;
    foreach($payments as $payValue){
        $payment_name = $payValue->name;
        $paymentType .= "*Pay By:* ".$payment_name."\n";
    }

    $msgBody=$msgBody."\n".$itemList."============\n\n"."*Sub Total*: ".number_format($total_money-$total_tax, 2)."\n*SST (6%)*: ".number_format($total_tax, 2)."\n"."*Grand Total*: ".number_format($total_money, 2)."\n".$paymentType;
}
$msgBody = $msgBody.$customerURL;

$sendMessage="*[Alert!]* \n".$msgBody;
                                
$dataArray = array(
    'to_number' => '60169165315',
    'type' => 'text',
    'message' => $sendMessage
);

$curl = curl_init();
curl_setopt_array($curl, array(
  CURLOPT_URL => "https://api.maytapi.com/api/".$mayTAPIKey."/19734/sendMessage",
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

$dataArray = array(
    'to_number' => '60169165315',
    'type' => 'text',
    'message' => "Raw: \n".$data
);

$useThis="";if($useThis=="yes"){
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://api.maytapi.com/api/".$mayTAPIKey."/19734/sendMessage",
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
}
?>