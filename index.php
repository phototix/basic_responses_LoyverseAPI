<?php
require 'vendor/autoload.php';
require 'controller/functions.php';

$webhookSales = '{"merchant_id":"9c7feacc-0fec-43eb-8533-899fb513174b","type":"inventory_levels.update","created_at":"2022-06-14T10:55:41.041Z","inventory_levels":[{"variant_id":"d50b40e2-76b5-487f-b9f1-be60d48cd459","store_id":"7a1837bb-c87a-4b93-92d6-397575a32f80","in_stock":26,"updated_at":"2022-06-14T10:55:41.041Z"}]}';
$webhookSales = json_decode($webhookSales);
$inventoryData = $webhookSales->inventory_levels;
foreach($inventoryData as $value){
    $variant_id = $value->variant_id;
    $store_id = $value->store_id;
    $in_stock = $value->in_stock;
    $msgBody = "VarID: ".$variant_id."\nStoreID: ".$store_id."\nNew Stock: ".$in_stock."\n";
}

echo nl2br($msgBody);
?>