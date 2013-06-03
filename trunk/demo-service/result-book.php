<?php
include 'config.php';
//start call service
ini_set('soap.wsdl_cacheecho nabled', 0);
$client = new SoapClient(URL_SERVICE);
if (isset($_POST)) {
    $title = $_POST['title'];
    $desc = $_POST['desc'];
    $hotel_id = $_POST['hotel_id'];
    $room_type_id = $_POST['room_type_id'];
    $amount = $_POST['amount'];
    $date_start = $_POST['date_start'];
    $date_end = $_POST['date_end'];
    $customer_name = $_POST['customer_name'];
    $customer_email = $_POST['customer_email'];
    $customer_address = $_POST['customer_address'];
    $customer_phone = $_POST['customer_phone'];
    $location_id = $_POST['country_id'];
    $state_id = $_POST['state_id'];
    $price_avg = $_POST['price_avg'];

    $room_type = json_decode($client->getInfoRoomType($hotel_id, $room_type_id), true);
    if (isset($_POST['has_extrabed'])) {
        if ($room_type['has_extrabed'] == 1) {
            $extrabed_price = $room_type['extrabed_price'];
        }
        $has_extrabed = 1;
    } else {
        $has_extrabed = 0;
        $extrabed_price = 0;
    }
    $total_price = $price_avg * $amount
            * ( ( (strtotime($date_end) - strtotime($date_start) ) / 86400) + 1 ) + $extrabed_price;

    $order_type_id = 1;

    $is_apply_campaign = $_POST['is_apply_campaign'];

    $number_free = json_decode($client->getRoomNumberFree($date_start, $date_end, $room_type_id, $hotel_id, false), true);
    if ($amount > $number_free) {
        die('Số phòng đặt không được lớn hơn số phòng còn trống !');
    }

    $customer_id = get_customer_id();
    $date_created = date('Y-m-d H:i:s');
    $date_updated = null;
    $root_id = null;
    $is_last = 1;
   
    /*----Check Kiểu Thanh Toán -----*/
    
    $payment_type = $_POST['payment_type'];
    if($payment_type == 'tructiep'){
        $payment_status = 'UNPAID';
        $status = 0;
    }elseif($payment_type == 'baokim'){
        $arr_input = array(0,1);
        $rand_keys = array_rand($arr_input, 1);
        $payment_baokim = json_decode($client->sendPayment($arr_input[$rand_keys]), true);
        if($payment_baokim['status'] == 203){
            echo ($payment_baokim['message']);
            die;
        }else{
             $payment_status = 'PAID';
             $status = 1;
        }
    }
    
    $r = $client->bookRoom($title, $desc, $hotel_id, $room_type_id, $amount, 
                            $date_start, $date_end, $customer_name, $customer_email, 
                            $customer_address, $customer_phone, $location_id, $state_id, 
                            $has_extrabed, $order_type_id, $is_apply_campaign, $customer_id, 
                            $total_price, $date_created, $date_updated, $root_id,$is_last,
                            $payment_status, $status
                            );
    
    $result = json_decode($r, true);
    if (!empty($result['last_order_id']) && isset($result['last_order_id'])) {
        $last_order_id = $result['last_order_id'];
    }
    
    $client->updateOrder($last_order_id,$title, $desc, $hotel_id, $room_type_id, $amount, 
                            $date_start, $date_end, $customer_name, $customer_email, 
                            $customer_address, $customer_phone, $location_id, $state_id, 
                            $has_extrabed, $order_type_id, $is_apply_campaign, $customer_id, 
                            $total_price, $date_created, $date_updated, $last_order_id,$is_last,
                             $payment_status, $status
                            );
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" type="text/css" href="css/reset.css"/>
        <link rel="stylesheet" type="text/css" href="css/style.css"/>
        <link rel="stylesheet" type="text/css" href="css/style1.css"/>        
        <script src="http://code.jquery.com/jquery-1.8.3.js"></script>    
        <style type="text/css">
            .result{
                width: 100%;
                height: 400px;
                text-align: center;
                line-height: 400px;                
            }
            .result h2{
                color: orange;
                font-size: 35px;
                font-family: Helvetica;
                font-weight: bold;
            }

        </style>
    </head>
    <body>
        <div class="h-bounhead">
            <div class="h-header">
                <div class="h-logo">
                    <a href="#" title=""><img src="images/logo.png" title="" alt="" /></a>
                </div><!--End h-logo-->
                <div class="h-menu">
                    <ul>
                        <li><a href="index.php" title="">Trang chủ</a></li>
                        <li class="curent"><a href="#" title="">Khách sạn</a></li>
                        <li><a href="#" title="">Moupon</a></li>
                        <li id="h-khuyenmai"><a href="#" title="">Khuyến mãi</a></li>
                        <li><a href="#" title="">Theo bản đồ</a></li>
                        <li><a href="#" title="">Đánh giá</a></li>
                    </ul>
                </div><!--End h-menu-->
            </div><!--End h-header-->
        </div><!--End h-bounhead--> 

        <div class="boun">
            <div class="background">           
                <div class="h-bounmain">
                    <div class="b-duondan"></div>
                    <div class="b-main">
                        <div class="result">
                            <h2><?php echo $result['message'] ?></h2>
                        </div>

                    </div><!--End b-bodymain-->

                    <div class="h-footer">
                        <div class="h-footer2">
                            <span><p>Copyright 2012 by mytour.vn – Địa chỉ : Tầng 4 - Số 51 Lê Đại Hành - Hai Bà Trưng - Hà Nội</p>
                                <p>Tel:  (04) 39 310 270 / (04) 66 759 717 - Fax: (04) 39 310 052 - Hotline: Mr +84 943 886 517</p>
                                <p>Liên hệ đặt tour email: sales@mytour.vn - david@mytour.vn</p></span>
                        </div><!--End h-footer2-->
                        <div class="h-footer3">
                            <a href="#" title="" id="h-share1"><img src="images/share1.png" title="" alt="" /></a>
                            <a href="#" title="" id="h-share2"><img src="images/share2.png" title="" alt="" /></a>
                        </div>
                    </div><!--End h-footer-->
                </div><!--End b-main-->
            </div><!--End h-bounmain-->
        </div><!--End boun-->
    </div><!--End background-->
</body>
</html>