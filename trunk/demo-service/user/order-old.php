<?php
require_once ('../config.php');
//start call service
ini_set('soap.wsdl_cacheecho nabled', 0);
$client = new SoapClient(URL_SERVICE);

//,array("trace" => 1, "exceptions" => 0));
$customer = get_customer();
$order_id = $_REQUEST['order_id'];
$order = json_decode($client->getInfoOrder($order_id), true);

if (empty($order) || $order['is_last'] != 1) {
    die('<meta charset="utf-8" />Hóa đơn không tồn tại hoặc bạn không có quyền chỉnh sửa hóa đơn này !!!');
}

$order_olds = json_decode($client->getOldOrders($order_id), true);
$hotel = json_decode($client->getInfo($order['hotel_id']), true);
$room_type = json_decode($client->getInfoRoomType($order['hotel_id'], $order['room_type_id']), true);

$countries = json_decode($client->getCountries(), true);
$states = json_decode($client->getDefaultStates(), true);


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
                        <?php
                        foreach ($order_olds as $order):
                            if ($order['status'] == 0) {
                                $status = 'Đang chờ';
                            } elseif ($order['status'] == 1) {
                                $status = 'Đã xác nhận';
                            } elseif ($order['status'] == 2) {
                                $status = 'Hủy bỏ';
                            } elseif ($order['status'] == 3) {
                                $status = 'Chờ xác nhận sửa';
                            }

                            ?>
                            <div class="smaincenter" style="float:right !important">
                                <div class="bgcolor2"> </div>
                                <div class="stitlecenter"><span>Lịch Sử Hóa Đơn (Ngày : <?php echo date('d-m-Y H:i:s',  strtotime($order['date_created'])) ?>)</span></div>
                                <div class="sbouncenter">
                                    <div class="scontact">
                                        <span id="stitlwcon">Trạng thái hóa đơn</span> 
                                        <ul>
                                            <li><span>Trạng Thái</span></li>
                                        </ul>
                                         <ul>
                                            <li><?php echo $status ?></li>
                                         </ul>
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
                                            <li><?php echo $order['customer_name'] ?></li>
                                            <li><?php echo $order['customer_email'] ?></li>
                                            <li><?php echo $order['customer_phone'] ?></li>
                                            <li>
                                                    <?php foreach ($countries as $value){
                                                        if ($order['location_id'] == $value['ID']) {
                                                            echo $value['title'];
                                                        }
                                                    }?>
                                            </li>
                                            <li>
                                                <?php foreach ($states as $value){
                                                        if ($order['state_id'] == $value['ID']) {
                                                            echo $value['title'];
                                                        }
                                                    }?>
                                            </li>
                                            <li><?php echo $order['customer_address'] ?></li>
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
                                        </ul>
                                        <ul>
                                            <li>
                                                <a href="../room-type-info?hotel_id=<?php echo $hotel['ID'] ?>&room_type_id=<?php echo $room_type['ID']; ?>" >
                                                    <?php echo $room_type['title'] ?>
                                                </a>
                                            </li>
                                            <li><?php echo $order['title'] ?></li>
                                            <li><?php echo $order['amount'] ?> phòng</li>
                                            <li id="sronghon">
                                                <?php echo date('d-m-Y', strtotime($order['date_start'])) ?>  đến
                                                <?php echo date('d-m-Y', strtotime($order['date_end'])) ?>
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
                                                            <?php echo 'Có' ?>
                                                        </li>
                                                        <li><?php echo ( $room_type['extrabed_price'] . ' '. $room_type['currency_title'] ) ?></li>
                                                    <?php }else{?>
                                                        <li>
                                                           <?php echo 'Không' ?>
                                                        </li>
                                                        <li><?php echo ( $room_type['extrabed_price'] . ' '. $room_type['currency_title'] ) ?></li>
                                                    <?php }?>
                                            <?php }else{ ?>
                                             <li>Không có dịch vụ thêm giường !</li>
                                            <?php }?>
                                            
                                            <li><span class="total_price_order"><?php echo ($order['total_price'] . ' '. $room_type['currency_title'] )?></span></li>
                                        </ul>
                                    </div><!--End scontact-->
                                </div><!--End sbouncenter-->
                                
                            </div><!--End smaincenter-->
                            <?php endforeach;?>
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
