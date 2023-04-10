<?php
require 'vendor/autoload.php';
require 'controller/functions.php';

$webhookSales = '{"merchant_id":"9c7feacc-0fec-43eb-8533-899fb513174b","type":"receipts.update","created_at":"2022-06-14T09:25:43.288Z","receipts":[{"receipt_number":"2-1033","note":null,"receipt_type":"SALE","refund_for":null,"order":"0614-02-13","created_at":"2022-06-14T09:25:38.000Z","updated_at":"2022-06-14T09:25:38.000Z","source":"point of sale","receipt_date":"2022-06-14T09:25:36.000Z","cancelled_at":null,"total_money":15.79,"total_tax":0.89,"points_earned":1.58,"points_deducted":0,"points_balance":4.74,"customer_id":"44b2c31a-8ea2-473f-bef4-fa0271cd56ba","total_discount":0,"employee_id":"6fb31c15-b7a3-4e7c-b5f4-480b90488e3b","store_id":"7a1837bb-c87a-4b93-92d6-397575a32f80","pos_device_id":"486a9255-76eb-4b8c-b586-939186442263","dining_option":null,"total_discounts":[],"total_taxes":[{"id":"b9bb72c3-9440-451a-aeaa-06811b961cfa","type":"ADDED","name":"SST","rate":6,"money_amount":0.89}],"tip":0,"surcharge":0,"line_items":[{"id":"9553152a-9d9e-8844-f6a3-2ab7d933516c","item_id":"30a54374-d408-4eec-b670-82e54791d0da","variant_id":"d50b40e2-76b5-487f-b9f1-be60d48cd459","item_name":"C1. Gratifying Mango & Dragon Fruit Smoothie","variant_name":null,"sku":"C1","quantity":1,"price":14.9,"gross_total_money":14.9,"total_money":15.79,"cost":13.5,"cost_total":13.5,"line_note":null,"line_taxes":[{"money_amount":0.89,"id":"b9bb72c3-9440-451a-aeaa-06811b961cfa","type":"ADDED","name":"SST","rate":6}],"total_discount":0,"line_discounts":[],"line_modifiers":[]}],"payments":[{"payment_type_id":"3279c70e-c4ac-4220-b112-d0ca182687c7","name":"CheckinPay","type":"OTHER","money_amount":15.79,"paid_at":"2022-06-14T09:25:36.000Z","payment_details":null}]}]}';
$webhookSales = json_decode($webhookSales);
$receiptData = $webhookSales->receipts;
foreach($receiptData as $value){

    $checkINbranch = "6a70f137ceaadf4f5a1ec4be7f1db21a";
    $checkINmerchant = "911095aec34e381e565898b300ed7576";

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
        $payURL = get_tiny_url("https://checkinpay.asia/merchant?id=".$checkINmerchant."&branch=".$checkINbranch."&phone=".$customer_phone."&amount=".$total_money);
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

echo nl2br($msgBody);
?>