<?php

include '../config.php';
//start call service
ini_set('soap.wsdl_cacheecho nabled', 0);
$client = new SoapClient(URL_SERVICE);
//,array("trace" => 1, "exceptions" => 0));

if (isset($_POST)) {
    $hotel_id = $_POST['hotel_id'];
    $room_type_id = $_POST['room_type_id'];

    $date_start = $_POST['date_start'];
    $date_start_old = $_POST['date_start_old'];

    $date_end = $_POST['date_end'];
    $date_end_old = $_POST['date_end_old'];

    $amount = $_POST['amount'];
    $amount_old = $_POST['amount_old'];

    if (strtotime($date_start) > strtotime($date_end)) {
        die('Ngày bắt đầu không được lớn hơn ngày kết thúc !');
    }
    if ((strtotime($date_end) - strtotime($date_start)) / 86400 > 30) {
        die('Không được book phòng quá 30 ngày !');
    }

    //Số phòng trống hiện có
    $number_free = json_decode($client->getRoomNumberFree($date_start, $date_end, $room_type_id, $hotel_id, false), true);

    //1. Nếu sửa số lượng phòng mà không sửa ngày đến đi: Số phòng trống = số phòng trống hiện có + số phòng của hóa đơn cũ
    if (($amount != $amount_old) && (strtotime($date_start_old) == strtotime($date_start)) && (strtotime($date_end_old) == strtotime($date_end))) {
        $number_free_end = $number_free + $amount_old;
    }

    //2. Nếu sửa số lượng phòng đặt và sửa ngày đến ngày đi
    elseif (($amount != $amount_old) && ((strtotime($date_start_old) != strtotime($date_start)) || (strtotime($date_end_old) != strtotime($date_end)))) {
        //Ngày đến,đi trong khoảng ngày đến đi cũ :  Số phòng trống = số phòng trống hiện có + số phòng của hóa đơn cũ
        if ((strtotime($date_start) >= strtotime($date_start_old)) && (strtotime($date_end) <= strtotime($date_end_old))) {
            $number_free_end = $number_free + $amount_old;
        } else {
            $number_free_end = $number_free;
        }
    }

    //3. Nếu không sửa số lượng phòng và không sửa ngày đến, ngày đi
    elseif (($amount == $amount_old) && (strtotime($date_start_old) == strtotime($date_start)) && (strtotime($date_end_old) == strtotime($date_end))) {
        $number_free_end = $number_free;
    }
    //4. Nếu không sửa số lượng phòng và sửa ngày đến, ngày đi
    elseif (($amount == $amount_old) && ((strtotime($date_start_old) != strtotime($date_start)) || (strtotime($date_end_old) != strtotime($date_end)))) {
        //Ngày đến,đi trong khoảng ngày đến đi cũ :  Số phòng trống = số phòng trống hiện có + số phòng của hóa đơn cũ
        if ((strtotime($date_start) >= strtotime($date_start_old)) && (strtotime($date_end) <= strtotime($date_end_old))) {
            $number_free_end = $number_free + $amount_old;
        } else {
            $number_free_end = $number_free;
        }
    }

    if ($amount > $number_free_end) {
        die('Số phòng book vượt quá số phòng còn trống !');
    }
    
    /*---Check trong khoảng ngày thay đổi, loại phòng đấy đã bị đóng hay chưa --*/
    $room_closes =  json_decode($client->checkRoomClose($date_start,$date_end,$room_type_id,$hotel_id), true);
    if(isset($room_closes) && !empty($room_closes)){
        die('Trong khoảng thời gian này, phòng đã bị khóa !');
    }
    
    //Cập nhật tất cả lại giá
    $room_type = json_decode($client->getInfoRoomType($hotel_id, $room_type_id), true);
    $price_update = 0;
    for ($i = strtotime($date_start); $i <= strtotime($date_end); $i = $i + 86400) {
        $date_i = date('Y-m-d', $i);
        $arrayPriceDates = json_decode($client->priceOfDate($date_i, $room_type_id, $hotel_id, $_POST['is_apply_campaign']), true);
        $price_update+= $arrayPriceDates['price_end'];
    }
    
    if ($_POST['has_extrabed'] == 1) {
       $total_price = $price_update * $amount + $room_type['extrabed_price'] ;
    } elseif($_POST['has_extrabed'] == 0){
        $total_price  =  $price_update * $amount ;
    }
    die($total_price . '  '. $room_type['currency_title']);
}