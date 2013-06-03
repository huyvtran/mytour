<?php
require_once ('../config.php');
//start call service
ini_set('soap.wsdl_cacheecho nabled', 0);
$client = new SoapClient(URL_SERVICE);

//,array("trace" => 1, "exceptions" => 0));
$customer = get_customer();
$order_id = $_REQUEST['order_id'];
$order = json_decode($client->getInfoOrder($order_id), true);


if (empty($order) || $order['status'] == 2 || $order['status'] == 3) {
    die('Hóa đơn không tồn tại hoặc bạn không có quyền chỉnh sửa hóa đơn này !!!');
}

$hotel = json_decode($client->getInfo($order['hotel_id']), true);
$room_type = json_decode($client->getInfoRoomType($order['hotel_id'], $order['room_type_id']), true);

$countries = json_decode($client->getCountries(), true);
$states = json_decode($client->getDefaultStates(), true);

if(isset($_POST) && !empty($_POST)){
    $hotel_id = $_POST['hotel_id'];
    $room_type_id = $_POST['room_type_id'];
   
    $date_start = $_POST['date_start'];
    $date_start_old = $order['date_star'];
    
    $date_end = $_POST['date_end'];
    $date_end_old = $order['date_end'];
    
    $amount = $_POST['amount'];
    $amount_old = $order['amount'];
    
    if(isset($_POST['has_extrabed'])){
        $has_extrabed   = 1;
        $price_extrabed = $room_type['extrabed_price'];
    }else{
        $has_extrabed = 0;
        $price_extrabed = 0;
    }
    
    if((strtotime($date_end) - strtotime($date_start))/86400 > 30){
        $errors[] = 'Không được đặt phòng quá 30 ngày !';
    }
   
    //Số phòng trống hiện có
    $number_free = json_decode($client->getRoomNumberFree($date_start, $date_end, $room_type_id, $hotel_id, false), true);
  
    //1. Nếu sửa số lượng phòng mà không sửa ngày đến đi: Số phòng trống = số phòng trống hiện có + số phòng của hóa đơn cũ
    if(($amount != $amount_old)   && (strtotime($date_start_old) == strtotime($date_start)) && (strtotime($date_end_old) == strtotime($date_end))){
        $number_free_end = $number_free + $amount_old;
    }
    
    //2. Nếu sửa số lượng phòng đặt và sửa ngày đến ngày đi
    elseif(($amount != $amount_old)   && ((strtotime($date_start_old) != strtotime($date_start)) || (strtotime($date_end_old) != strtotime($date_end)))){
        //Ngày đến,đi trong khoảng ngày đến đi cũ :  Số phòng trống = số phòng trống hiện có + số phòng của hóa đơn cũ
        if( (strtotime($date_start)>= strtotime($date_start_old)) && (strtotime($date_end)<= strtotime($date_end_old))    ) {  
            $number_free_end = $number_free + $amount_old;
        }else{
            $number_free_end = $number_free;
        }
    }
    
    //3. Nếu không sửa số lượng phòng và không sửa ngày đến, ngày đi
    elseif(($amount == $amount_old)   && (strtotime($date_start_old) == strtotime($date_start)) && (strtotime($date_end_old) == strtotime($date_end))){
        $number_free_end = $number_free;
    }
    //4. Nếu không sửa số lượng phòng và sửa ngày đến, ngày đi
    elseif(($amount == $amount_old)   && ((strtotime($date_start_old) != strtotime($date_start)) || (strtotime($date_end_old) != strtotime($date_end)))){
        //Ngày đến,đi trong khoảng ngày đến đi cũ :  Số phòng trống = số phòng trống hiện có + số phòng của hóa đơn cũ
        if( (strtotime($date_start)>= strtotime($date_start_old)) && (strtotime($date_end)<= strtotime($date_end_old))    ) {  
            $number_free_end = $number_free + $amount_old;
        }else{
            $number_free_end = $number_free;
        }
    }
  
    if ($amount > $number_free_end) {
        $errors[] = 'Trong khoảng thời gian bạn thay đổi, chỉ còn '.$number_free_end . ' phòng trống !' ;
    }
    
     /*---Check trong khoảng ngày thay đổi, loại phòng đấy đã bị đóng hay chưa --*/
    $room_closes =  json_decode($client->checkRoomClose($date_start,$date_end,$room_type_id,$hotel_id), true);
    if(isset($room_closes) && !empty($room_closes)){
         $errors[] = 'Khi bạn thay đổi hóa đơn, phòng đã bị khóa !' ;
    }
    
    //Nếu không có lỗi
    if(empty($errors)){
        //Cập nhật tất cả lại giá
        $price_update = 0;
        for($i= strtotime($date_start); $i<= strtotime($date_end); $i= $i + 86400){
               $date_i = date('Y-m-d', $i);
                $arrayPriceDates = json_decode($client->priceOfDate($date_i, $room_type_id, $hotel_id, $order['is_apply_campaign']), true);
                $price_update+= $arrayPriceDates['price_end'];
        }
      $total_price = $price_update*$amount + $price_extrabed ;
      
      $title = $_POST['title'];
      $desc  = (isset($_POST['desc']))?$_POST['desc']:'';
      $customer_name = $_POST['customer_name'];
      $customer_email = $_POST['customer_email'];
      $customer_address = $_POST['customer_address'];
      $customer_phone = $_POST['customer_phone'];
      $location_id = $_POST['country_id'];
      $state_id = $_POST['state_id'];
      $order_type_id = 1;
      $is_apply_campaign = $order['is_apply_campaign'];
      $customer_id = get_customer_id();
      $date_created = date('Y-m-d H:i:s'); 
      $date_updated = date('Y-m-d H:i:s');
      
      $root_id = $order['root_id'];
      $is_last = 1;
      $payment_status = $order['payment_status'];
      $status = 3;
      $r = $client->bookRoom($title, $desc, $hotel_id, $room_type_id, $amount, 
                            $date_start, $date_end, $customer_name, $customer_email, 
                            $customer_address, $customer_phone, $location_id, $state_id, 
                            $has_extrabed, $order_type_id, $is_apply_campaign, $customer_id, 
                            $total_price, $date_created, $date_updated, $root_id,$is_last,$payment_status, $status
                            );
    
     $result = json_decode($r, true);
    
     if($result['status'] == 203 || $result['status'] == 204){
         $errors[] = $result['message'];
     }else{
         $is_last = 0;
         $r1 = $client->updateOrder($order_id,$order['title'], $order['desc'], $order['hotel_id'], $order['room_type_id'], $order['amount'], 
                            $order['date_start'], $order['date_end'], $order['customer_name'], $order['customer_email'], 
                            $order['customer_address'], $order['cSustomer_phone'], $order['location_id'], $order['state_id'], 
                            $order['has_extrabed'], $order['order_type_id'], $order['is_apply_campaign'], $order['customer_id'], 
                            $order['total_price'],$order['date_created'],$order['date_updated'],$order['root_id'],$is_last,$order['payment_status'], $order['status']);
         $result = json_decode($r1, true);
       
         header('Location: index.php');
     }
    }
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Sửa Đơn</title>
        <link rel="stylesheet" type="text/css" href="../css/reset.css"/>
        <link rel="stylesheet" type="text/css" href="../css/style.css"/>
        <link rel="stylesheet" type="text/css" href="../css/style1.css"/>        
        <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
        <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
        <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/themes/ui-lightness/jquery-ui.css" type="text/css" media="all" />
        <style>
            ul.error{
                width:80%;
                padding:10px auto;
                margin-bottom: 20px;
            }
            ul.error li{
                color:red;
                padding:5px;
                margin:5px;
            }
        </style>
        <script type="text/javascript">
            $(document).ready(function(){
                $(function() {
                    $( ".datepicker" ).datepicker({ dateFormat: 'dd-mm-yy' });
                });
                
                //Chuyển chuỗi kí tự (string) sang đối tượng Date()
                function parseDate(str) {
                    var mdy = str.split('-');
                    return new Date(mdy[2], mdy[1], mdy[0]);
                }
                
//                //TONG TIEN
//                var has_extrabed_first =  parseInt(<?php echo $order['has_extrabed'] ?>);
//                var total_price = parseInt(<?php echo $order['total_price'] ?>);
//                $('input[name="has_extrabed"]').change(function(){
//                    if($(this).is(':checked')){has_extrabed = 1;} else{has_extrabed = 0;}
//                    if(has_extrabed_first == 1){
//                         if(has_extrabed_first == has_extrabed){
//                            $('.total_price_order').html(total_price + '  ' + '<?php echo $order['currency_title'] ?>');
//                        }else{
//                            var price_end = parseInt(total_price - <?php echo $order['extrabed_price'] ?>);
//                            $('.total_price_order').html(price_end - + '  ' + '<?php echo $order['currency_title'] ?>');
//                        }
//                    }else if(has_extrabed_first == 0){
//                        if(has_extrabed_first == has_extrabed){
//                           $('.total_price_order').html(total_price + '  ' + '<?php echo $order['currency_title'] ?>');
//                        }else{
//                             var price_end = parseInt(total_price + <?php echo $order['extrabed_price'] ?>);
//                            $('.total_price_order').html(price_end - + '  ' + '<?php echo $order['currency_title'] ?>');
//                        }
//                    }
//                   
//                })
                
                $('input[name="submit"]').click(function(){
                
                    //Kiem tra ngay den ngay di
                    if($('input[name="date_start"]').val() == ''){
                        alert('Ngày bắt đầu không được rỗng !');
                        return false;
                    }
                    if($('input[name="date_end"]').val() == ''){
                        alert('Ngày kết thúc không được rỗng !');
                        return false;
                    }
                    var date_start = parseDate($('input[name="date_start"]').val()).getTime();
                    var date_end = parseDate($('input[name="date_end"]').val()).getTime();
                    if (date_start > date_end){
                        alert("Ngày bắt đầu không được lớn hơn ngày kết thúc !");
                        return false;
                    }
                });
            
            $('input[name="date_start"],input[name="date_end"],input[name="amount"],input[name="has_extrabed"] ').change(function(){
                load_ajax();
            })
            function load_ajax() {
                    var datas =new Object();
                    datas.customer_name = $('input[name="customer_name"]').val();
                    datas.customer_email = $('input[name="customer_email"]').val();
                    datas.customer_phone = $('input[name="customer_phone"]').val();
                    datas.customer_address = $('input[name="customer_address"]').val();
                    datas.country_id = $('select[name="country_id"]').val();
                    datas.state_id = $('select[name="state_id"]').val();
                    datas.title = $('input[name="title"]').val();
                    
                    datas.amount = $('input[name="amount"]').val();
                    datas.amount_old = <?php echo $order['amount'] ?>;
                    
                    datas.date_start = $('input[name="date_start"]').val();
                    datas.date_start_old = '<?php echo $order['date_start'] ?>';
                    
                    datas.date_end = $('input[name="date_end"]').val();
                    datas.date_end_old = '<?php echo $order['date_end'] ?>';
                    if( $('input[name="has_extrabed"]').is(':checked')){
                        datas.has_extrabed = 1;
                    }else{
                         datas.has_extrabed = 0;
                    }
                    datas.hotel_id = $('input[name="hotel_id"]').val();
                    datas.room_type_id = $('input[name="room_type_id"]').val();
                    datas.is_apply_campaign = $('input[name="is_apply_campaign"]').val();
                    datas.status = $('input[name="status"]').val();
                    datas.customer_id = <?php echo $customer['ID'] ?>;
                    $.ajax({
                        type: "POST",
                        url: "check-edit.php",
                        data: datas
                        }).done(function( msg ) {
                            $('.price_after_update').html(msg);
                        })
                }
  
            });
        </script>
    </head>

    <body>
        <div class="h-bounhead">
            <div class="h-header">
                <div class="h-logo">
                    <a href="#" title=""><img src="images/logo.png" title="" alt="" /></a>
                </div><!--End h-logo-->
                <div class="h-menu">
                    <ul>
                        <li><a href="#" title="">Trang chủ</a></li>
                        <li class="curent"><a href="#" title="">Khách sạn</a></li>
                        <li><a href="#" title="">Moupon</a></li>
                        <li><a href="#" title="">Theo bản đồ</a></li>
                        <li><a href="#" title="">Đánh giá</a></li>
                    </ul>
                </div><!--End h-menu-->
            </div><!--End h-header-->
        </div><!--End h-bounhead-->
        <div class="boun">
            <div class="background">
                <div class="h-bounmain">
                    <div class="smainbottom">
                        <div class="smainleft">
                            <div class="stitleleft"><span>Quản lý đặt phòng</span></div>
                            <div class="bgcolor"> </div>
                            <div class="smaincon">
                                <ul>
                                    <li><a href="index.php" title="">Danh sách đơn đặt phòng</a></li>
                                    <li><a href="#" title="">Nhận Xét Khách Sạn</a></li>
                                    <li><a href="#" title="">Giới Thiệu Bạn Bè</a></li>
                                    <li><a href="#" title="">Sửa hồ sơ</a></li>
                                    <li><a href="#" title="">Thay đổi mật khẩu</a></li>
                                    <li><a href="#" title="">Đăng xuất</a></li>
                                </ul>
                            </div><!--End smaincon-->
                        </div><!--End smainleft-->
                        <form method="post" action="" >
                            <div class="smaincenter">
                                <div class="bgcolor2"> </div>
                                <div class="stitlecenter"><span>Nội dung đơn</span></div>
                                <div class="sbouncenter">
                                       <ul class="error">
                                            <?php if(!empty($errors)):?>
                                                <?php foreach ($errors as $error):?>
                                                    <li><?php echo $error; ?></li>
                                                <?php endforeach;?>
                                            <?php endif;?>
                                        </ul>
                                    <div class="scontact">
                                        <span id="stitlwcon">Thông tin khách hàng</span>
                                        <ul>
                                            <li><span>Họ tên</span></li>
                                            <li><span>Email</span></li>
                                            <li><span>Điện thoại</span></li>
                                            <li><span>Quốc gia</span></li>
                                            <li><span>Tỉnh / Thành Phố</span></li>
                                            <li><span>Địa chỉ</span></li>
                                        </ul>
                                        <ul>
                                            <li><input type="text" value="<?php echo $order['customer_name'] ?>" name="customer_name" /></li>
                                            <li><input type="text" value="<?php echo $order['customer_email'] ?>" name="customer_email"/></li>
                                            <li><input type="text" value="<?php echo $order['customer_phone'] ?>" name="customer_phone"/></li>
                                            <li>
                                                <select name="country_id" >
                                                    <option value="0"></option>
                                                    <?php foreach ($countries as $value) { ?>
                                                        <option value="<?php echo $value['ID'] ?>" 
                                                        <?php
                                                        if ($order['location_id'] == $value['ID']) {
                                                            echo 'selected';
                                                        }
                                                        ?>
                                                                >
                                                                    <?php echo $value['title'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>     
                                            </li>
                                            <li>
                                                <select name="state_id" >
                                                    <option value="0"></option>
                                                    <?php foreach ($states as $value) : ?>
                                                        <option value="<?php echo $value['ID'] ?>" <?php
                                                    if ($customer['state_id'] == $value['ID']) {
                                                        echo 'selected';
                                                    }
                                                        ?> ><?php echo $value['title'] ?></option>
                                                            <?php endforeach; ?>
                                                </select>  
                                            </li>
                                            <li><input name="customer_address" value="<?php echo $order['customer_address'] ?>" /></li>
                                        </ul>
                                    </div><!--End scontact-->
                                </div><!--End sbouncenter-->
                                <div class="sbouncenter">
                                    <div class="scontact">
                                        <span id="stitlwcon">Chi tiết phòng</span>
                                        <ul>
                                            <li><span>Loại phòng</span></li>
                                            <li><span>Tên đơn</span></li>
                                            <li><span>Số lượng phòng</span></li>
                                            <li><span>Ngày đặt</span></li>
                                            <li><span>Áp dụng khuyến mãi</span></li>
                                            <li><span>Thêm giường</span></li>
                                            <?php if ($room_type['has_extrabed'] == 1): ?>
                                                <li><span>Giá thêm giường</span></li>
                                            <?php endif; ?>
                                            <li><span>Tổng giá</span></li>
                                            <li><span>Giá sau cập nhật : </span></li>
                                        </ul>
                                        <ul>
                                            <li>
                                                <a href="../room-type-info?hotel_id=<?php echo $hotel['ID'] ?>&room_type_id=<?php echo $room_type['ID']; ?>" >
                                                    <?php echo $room_type['title'] ?>
                                                </a>
                                            </li>
                                            <li><input name="title" value="<?php echo $order['title'] ?>" /></li>
                                            <li><input name="amount" value="<?php echo $order['amount'] ?>" />phòng</li>
                                            <li id="sronghon">
                                                <input type="text" name="date_start" value="<?php echo date('d-m-Y', strtotime($order['date_start'])) ?>" class="datepicker" style="width: 100px"/>&nbsp;
                                                <input type="text" name="date_end" value="<?php echo date('d-m-Y', strtotime($order['date_end'])) ?>" class="datepicker" style="width: 100px"/>
                                            </li>
                                            <li>
                                                <?php
                                                if ($order['is_apply_campaign'] == 'no') {
                                                    echo 'Không áp dụng KM';
                                                } elseif ($order['is_apply_campaign'] == 'yes') {
                                                    echo 'Áp dụng KM';
                                                }
                                                ?>
                                            </li>
                                            <?php if($room_type['has_extrabed'] == 1){ ?>
                                                    <?php if ($order['has_extrabed'] == 1) { ?>
                                                         <li>
                                                            <input type="checkbox" value="1" name="has_extrabed" checked="checked" />
                                                            <input type="hidden" name="has_extrabed_hidden" value="1" />
                                                        </li>
                                                        <li><?php echo ( $order['extrabed_price'] . ' '. $order['currency_title'] ) ?></li>
                                                    <?php }else{?>
                                                        <li>
                                                            <input type="checkbox" value="0" name="has_extrabed"  />
                                                            <input type="hidden" name="has_extrabed_hidden" value="1" />
                                                        </li>
                                                        <li><?php echo ( $order['extrabed_price'] . ' '. $order['currency_title'] ) ?></li>
                                                    <?php }?>
                                            <?php }else{ ?>
                                             <li>Không có dịch vụ thêm giường !</li>
                                            <?php }?>
                                            
                                            <li><span class="total_price_order"><?php echo ($order['total_price'] . ' '. $order['currency_title'] )?></span></li>
                                            
                                            <li><span class="price_after_update"> </span></li>
                                        </ul>
                                    </div><!--End scontact-->
                                </div><!--End sbouncenter-->
                                <div class="sbouncenter">
                                    <div class="scontact">
                                        <span id="stitlwcon">Chi tiết thanh toán của bạn</span>
                                        <ul>
                                            <li><span>Phương thức</span></li>
                                            <li><span>Tên chủ thẻ</span></li>
                                            <li><span>Số seri thẻ</span></li>
                                            <li><span>Mã CVC</span></li>
                                            <li><span>Ngày hết hạn</span></li>
                                        </ul>
                                        <ul>
                                            <li><input type="text" /></li>
                                            <li><input type="text" /></li>
                                            <li>
                                                <input id="sseriv" type="text" />
                                                <input id="sseriv" type="text" />
                                                <input id="sseriv" type="text" />
                                                <input id="sseriv" type="text" />
                                            </li>
                                            <li><input type="text" /></li>
                                            <li id="sronghon">
                                                <select id="sngaydat" />
                                                <option>20/1/2013</option>
                                                <option>20/1/2013</option>
                                                <option>20/1/2013</option>
                                                </select>
                                                <i>Đến</i>
                                                <select id="sngaydat" />
                                                <option>20/1/2013</option>
                                                <option>20/1/2013</option>
                                                <option>20/1/2013</option>
                                                </select>
                                            </li>
                                        </ul>
                                    </div><!--End scontact-->
                                </div><!--End sbouncenter-->
                            </div><!--End smaincenter-->
                            <div class="ssubmitconten"> 
                                <input type="hidden" name="hotel_id" value="<?php echo $order['hotel_id'] ?>" />
                                <input type="hidden" name="room_type_id" value="<?php echo $order['room_type_id'] ?>" />
                                <input type="hidden" name="is_apply_campaign" value="<?php echo $order['is_apply_campaign'] ?>" />
                                <input type="hidden" name="customer_id" value="<?php echo $order['customer_id'] ?>" />
                                <input type="hidden" name="status" value="<?php echo $order['status'] ?>" />
                                <input id="scapnhat" type="submit" value="Cập nhật" name="submit"/>
                                <input id="shuybo" type="button" value="Hủy" onclick="window.location = 'index.php'" />
                            </div>
                        </form>
                    </div><!--End h-mainbottom-->
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
                </div><!--End h-bounmain-->
            </div><!--End boun-->
        </div><!--End background-->
    </body>
</html>
