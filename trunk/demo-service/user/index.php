<?php
require_once ('../config.php');
//start call service
ini_set('soap.wsdl_cacheecho nabled', 0);
$client = new SoapClient(URL_SERVICE);

//,array("trace" => 1, "exceptions" => 0));
$customer = get_customer();
$customer_id = get_customer_id();
$orders = json_decode($client->getOrderCustomer($customer_id), true);
?>

<!DOCTYPE html>
<html>
    <title>Quản lý đơn đặt phòng</title>
    <header>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" type="text/css" href="../css/reset.css"/>
        <link rel="stylesheet" type="text/css" href="../css/style.css"/>
        <link rel="stylesheet" type="text/css" href="../css/style1.css"/>        
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
    </header>
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
                        <div class="smaincenter">
                            <div class="bgcolor2"> </div>
                            <div class="stitlecenter"><span>Danh sách đơn đặt phòng</span></div>
                            <div class="stablelist">
                                <table width="772" border="1" cellpadding="5" cellspacing="0">
                                    <tr>
                                        <td align="center" valign="middle" bgcolor="#c3d8e0">STT</td>
                                        <td align="center" valign="middle" bgcolor="#c3d8e0">Tên đơn</td>
                                        <td align="center" valign="middle" bgcolor="#c3d8e0">Khách sạn</td>
                                        <td align="center" valign="middle" bgcolor="#c3d8e0">Loại phòng</td>
                                        <td align="center" valign="middle" bgcolor="#c3d8e0">Ngày bắt đầu</td>
                                        <td align="center" valign="middle" bgcolor="#c3d8e0">Ngày kết thúc</td>
                                        <td align="center" valign="middle" bgcolor="#c3d8e0">SL</td>
                                        <td align="center" valign="middle" bgcolor="#c3d8e0">Tổng tiền</td>
                                        <td align="center" valign="middle" bgcolor="#c3d8e0">Trạng thái</td>
                                        <td align="center" valign="middle" bgcolor="#c3d8e0" width="50px">Tác vụ</td>
                                    </tr>
                                    <?php if (!empty($orders)): ?>
                                        <?php $stt = 0; ?>
                                        <?php foreach ($orders as $order): ?>
                                            <?php
                                            $stt++;
                                            $id = $order['ID'];
                                            $title = $order['title'];
                                            $hotel = json_decode($client->getInfo($order['hotel_id']), true);
                                            $room_type = json_decode($client->getInfoRoomType($order['hotel_id'], $order['room_type_id']), true);
                                            $amount = $order['amount'];
                                            $date_start = date('d-m-Y', strtotime($order['date_start']));
                                            $date_end = date('d-m-Y', strtotime($order['date_end']));
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
                                            <tr>
                                                <td align="center" valign="middle"><?php echo $stt ?></td>
                                                <td align="center" valign="middle"><?php echo $order['title'] ?></td>
                                                <td align="center" valign="middle">
                                                    <a href="../hotel-info.php?hotel_id=<?php echo $hotel['ID'] ?>">
                                                        <?php echo $hotel['title'] ?>
                                                    </a>
                                                </td>
                                                <td align="center" valign="middle">
                                                    <a href="../room-type-info?hotel_id=<?php echo $hotel['ID'] ?>&room_type_id=<?php echo $room_type['ID']; ?>"/>
                                                    <?php echo $room_type['title'] ?>
                                                    </a>
                                                </td>
                                                <td align="center" valign="middle"><?php echo $date_start ?></td>
                                                <td align="center" valign="middle"><?php echo $date_end ?></td>
                                                <td align="center" valign="middle"><?php echo $amount ?></td>
                                                <td align="center" valign="middle"><?php echo (number_format($order['total_price'], 0, '.', ',') . '  '. $order['currency_title'] )?> </td>
                                                <td align="center" valign="middle"><?php echo $status ?></td>
                                                <td align="center" valign="middle" width="50px">
                                                    <?php if ($order['status'] != 2 && $order['status'] !=3): ?>
                                                        <a href="order-edit.php?order_id=<?php echo $id ?>"><img src="../images/icon-quanly3.png" /></a></a>
                                                    <?php else: ?>
                                                        <img src="../images/icon-quanly3.png" />
                                                    <?php endif; ?>
                                                    &nbsp;&nbsp;
                                                    <?php if($order['is_last'] == 1):?>
                                                        <a href="order-old.php?order_id=<?php echo $id?>">
                                                            <img src="../images/icon-quanly2.png" />
                                                        </a>
                                                    <?php else:?>
                                                        <img src="../images/icon-quanly2.png" />
                                                    <?php endif;?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </table>
                            </div><!--End stablelist-->
                        </div><!--End smaincenter-->
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
