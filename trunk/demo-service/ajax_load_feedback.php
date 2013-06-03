<?php
include 'config.php';
//start call service
ini_set('soap.wsdl_cacheecho nabled', 0);
$client = new SoapClient(URL_SERVICE);
$customer = get_customer();
if (isset($_POST)) {
    $feedback = $_POST['feedback'];
    $hotel_id = $_POST['hotel_id'];
    $customer_id = $_POST['customer_id'];   
    
    $feedback = strip_tags($feedback);

    if (isset($feedback)) {
        $get_feedback = json_decode($client->getFeedback($hotel_id, $customer_id, $feedback), true);
    }
}
?>

<div class='comment'>
    <img src='images/represent-img.png' alt='' width='50px' style="margin-right: 10px">
    <div class="desc" style="display: inline-block">
        <span style="color: #0066cc; font-size: 14px;"><?php echo $get_feedback['fullname'] ?></span>
        <span style="color: #ABADB3; font-size: 11px; font-style: italic"><?php echo $get_feedback['time'] ?></span>
        <div class='desc2'><?php echo $get_feedback['comment'] ?></div>
    </div>    
</div>
