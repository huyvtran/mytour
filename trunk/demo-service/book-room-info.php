<?php
include 'config.php';
//start call service
ini_set('soap.wsdl_cacheecho nabled', 0);
$client = new SoapClient(URL_SERVICE);
//,array("trace" => 1, "exceptions" => 0));
$hotel_id = $_POST['hotel_id'];
$hotel = json_decode($client->getInfo($hotel_id), true);


$room_type_id = $_POST['room_type_id'];
$room_type = json_decode($client->getInfoRoomType($hotel_id, $room_type_id), true);
$countries = json_decode($client->getCountries(), true);

$states = json_decode($client->getDefaultStates(), true);

$title = $_POST['title'];
$date_start = $_POST['date_start'];
$date_end = $_POST['date_end'];
$is_apply_campaign = $_POST['is_apply_campaign'];
$customer_name = $_POST['customer_name'];
$customer_email = $_POST['customer_email'];
$customer_phone = $_POST['customer_phone'];
$state_id = $_POST['state_id'];
$country_id = $_POST['country_id'];
$price_avg = $_POST['price_avg'];
$amount = $_POST['amount'];
$total_price = $price_avg * $amount
        * ( ( (strtotime($date_end) - strtotime($date_start) ) / 86400) + 1 );
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" type="text/css" href="css/reset.css"/>
        <link rel="stylesheet" type="text/css" href="css/style.css"/>
        <link rel="stylesheet" type="text/css" href="css/style1.css"/>        
        <script src="http://code.jquery.com/jquery-1.8.3.js"></script>
        <script type="text/javascript">
            $(document).ready(function(){
                var date_start        = "<?php echo $date_start ?>";
                var date_end          = "<?php echo $date_end ?>";
                var room_type_id      = "<?php echo $room_type_id; ?>";
                var hotel_id          = "<?php echo $hotel_id; ?>";
                var is_apply_campaign =  "<?php echo $is_apply_campaign; ?>";
                $.ajax({
                    type: "POST",
                    url: "loadBill.php",
                    data: { date_start: date_start, date_end: date_end, room_type_id: room_type_id,hotel_id:hotel_id,is_apply_campaign:is_apply_campaign}
                }).done(function( msg ) {
                    $('.load-bill-auto').html(msg);
                });
                
                var has_extrabed = $('input[name="has_extrabed"]');
                var total_price = parseInt(<?php echo $total_price ?>);
                
                if(has_extrabed.is(':checked')){
                    $('.label_extrabed').html(' <li><span>Giá thêm giường </span></li>');
                    var price_extrabed = parseInt(<?php echo $room_type['extrabed_price'] ?>);
                    $('.value_extrabed').html(' <li><span>'+ price_extrabed  + '   ' + '<?php echo $room_type['currency_title'] ?>' +'</span></li>');
                        
                    var total_price_order = parseInt(total_price + price_extrabed);
                    $('.total_price_order').html( total_price_order + '<?php echo $room_type['currency_title'] ?>');
                }else{
                    $('.total_price_order').html( total_price + '<?php echo $room_type['currency_title'] ?>');
                }
                    
                has_extrabed.change(function(){
                    if($(this).is(':checked')){
                        $('.label_extrabed').html(' <li><span>Giá thêm giường </span></li>');
                        var price_extrabed = parseInt(<?php echo $room_type['extrabed_price'] ?>);
                        $('.value_extrabed').html(' <li><span>'+ price_extrabed  + '   ' + '<?php echo $room_type['currency_title'] ?>' +'</span></li>');
                        
                        var total_price_order = parseInt(total_price + price_extrabed);
                        $('.total_price_order').html( total_price_order + '<?php echo $room_type['currency_title'] ?>');
                    }else{
                        $('.total_price_order').html( total_price + '<?php echo $room_type['currency_title'] ?>');
                    }
                })
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
                        <div class="b-bodyform">
                            <div class="b-bodytop">
                                <div class="b-bodyleft"><span></span></div>
                                <div class="b-bodycenter"><span>Bước 2 : Xác nhận thông tin đặt phòng</span></div>
                                <div class="b-bodyright"><span></span></div>
                            </div><!--End b-bodytop-->
                        </div>           
                        <form method="post" action="result-book.php">
                            <div class="b-bodymain">
                                <div class="b-titlepro">
                                    <div class="b-koapdungkm">
                                        <ul>
                                            <li>
                                                <a href="user/index.php">
                                                    <?php echo $hotel['title'] ?>
                                                </a>
                                                <b>
                                                    <?php
                                                    if ($is_apply_campaign == 'yes') {
                                                        echo 'Áp dụng KM';
                                                    } elseif ($is_apply_campaign == 'no') {
                                                        echo 'Không áp dụng KM';
                                                    }
                                                    ?>                                               
                                                </b>
                                            </li>
                                            <li><span><?php echo $hotel['address'] ?></span></li>
                                            <li>
                                                <?php echo str_repeat('&#10030;', (int) $hotel['star']); ?>
                                                <?php echo str_repeat('&#10025;', 5 - (int) $hotel['star']); ?>                                            
                                            </li>
                                        </ul>
                                    </div>
                                </div><!--End b-titlepro-->
                                <div class="b-table">
                                    <div class="b-tabletop">
                                        <div class="b-sumprice">
                                            <div class="b-sumprice1">
                                                <ul id="ulform1">
                                                    <li><span>Loại phòng</span></li>
                                                    <li><span>Số phòng</span></li>
                                                    <li><span>Tên đơn</span></li>
                                                    <li><span>Ngày đặt</span></li>                                                
                                                    <li><span>Thêm giường</span></li>
                                                    <div class="label_extrabed">

                                                    </div>
                                                    <li><span>Họ tên</span></li>
                                                    <li><span>Email</span></li>
                                                    <li><span>Số điện thoại</span></li>
                                                    <li><span>Quốc Gia</span></li>
                                                    <li><span>Tỉnh / Thành Phố</span></li>

                                                </ul>
                                                <ul id="ulform2">
                                                    <li><span>
                                                            <a href="room-type-info?hotel_id=<?php echo $hotel['ID'] ?>&room_type_id=<?php echo $room_type['ID']; ?>"/>
                                                            <?php echo $room_type['title']; ?>
                                                            </a>
                                                        </span></li>
                                                    <li><span><?php echo $amount ?></span></li>
                                                    <li>
                                                        <?php echo $title ?> 
                                                    </li>
                                                    <li><span>                    
                                                            Từ ngày: <?php echo date('d-m-Y', strtotime($date_start)) ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;  
                                                            Đến ngày: <?php echo date('d-m-Y', strtotime($date_end)) ?></span></li>                                                

                                                    <?php if ($room_type['has_extrabed'] == 1 && isset($_POST['has_extrabed'])) { ?>
                                                        <li>
                                                            <input type="checkbox" value="1" name="has_extrabed" checked="checked" disabled="disabled">
                                                            <input type="hidden" name="has_extrabed_hidden" value="1">
                                                        </li>

                                                    <?php } elseif ($room_type['has_extrabed'] == 1 && !isset($_POST['has_extrabed'])) { ?>
                                                        <li>
                                                            Không thêm
                                                            <input type="hidden"  name="has_extrabed_hidden" value="0">
                                                        </li>
                                                    <?php } else { ?>
                                                        <li>
                                                            Không có dịch vụ thêm giường !
                                                        </li>
                                                    <?php } ?>

                                                    <div class="value_extrabed">

                                                    </div>
                                                    <li>
                                                        <span><?php echo $customer_name ?></span>
                                                        <input type="hidden" name="customer_name" value="<?php echo $customer_name ?>"
                                                    </li>
                                                    <li>
                                                        <span><?php echo $customer_email ?>
                                                            <input type="hidden" name="customer_email" value="<?php echo $customer_email ?>"
                                                                   </li>
                                                            <li>
                                                                <span><?php echo $customer_phone ?>
                                                                    <input type="hidden" name="customer_phone" value="<?php echo $customer_phone ?>"
                                                                           </li>
                                                                    <li><span>
                                                                            <?php
                                                                            foreach ($countries as $value) {
                                                                                if ($value['ID'] == $country_id) {
                                                                                    echo $value['title'];
                                                                                }
                                                                            }
                                                                            ?>
                                                                            <input type="hidden" name="country_id" value="<?php echo $country_id ?>"
                                                                        </span>
                                                                    </li>
                                                                    <li><span>
                                                                            <?php
                                                                            foreach ($states as $value) {
                                                                                if ($value['ID'] == $state_id) {
                                                                                    echo $value['title'];
                                                                                }
                                                                            }
                                                                            ?></span>
                                                                        <input type="hidden" name="state_id" value="<?php echo $state_id ?>"
                                                                    </li>

                                                                    </ul>
                                                                    </div>
                                                                    </div><!--End b-sumprice-->
                                                                    <div class="b-tableleft"><span></span></div>
                                                                    <div class="b-tablecenter"><span>Giá từng ngày</span></div>
                                                                    <div class="b-tableright"><span></span></div>
                                                                    <div class="b-tablemain load-bill-auto">

                                                                    </div>

                                                                    <div class="b-sumprice">
                                                                        <div class="b-sumprice21">
                                                                            <div id="sumtop"><span>Tổng cộng</span> <b>                    
                                                                                    <div class="total_price_order" >
                                                                                    </div>
                                                                                </b>
                                                                            </div>
                                                                        </div>
                                                                    </div><!--End b-sumprice-->
                                                                    <div class="b-thongtinlh">
                                                                        <ul>

                                                                        </ul>
                                                                    </div>
                                                                    <input type="hidden" name="hotel_id" value="<?php echo $hotel_id; ?>" />
                                                                    <input type="hidden" name="room_type_id" value="<?php echo $room_type['ID']; ?>" />
                                                                    <input type="hidden" name="date_start" value="<?php echo $date_start; ?>" />
                                                                    <input type="hidden" name="date_end" value="<?php echo $date_end; ?>" />
                                                                    <input type="hidden" name="is_apply_campaign" value="<?php echo $is_apply_campaign; ?>" />
                                                                    <input type="hidden" name="amount" value="<?php echo $amount; ?>" />                                                                        
                                                                    </div><!--End b-thongtinlh-->
                                                                    </div><!--End b-thongtinlh-->
                                                                    <div class="b-check">
                                                                        <ul>
                                                                            <li>
                                                                                <input class="submitBtn" type="submit" name ="submit" value="Đồng ý đặt phòng"/>
                                                                            </li>
                                                                        </ul>
                                                                    </div><!--End -b-check-->
                                                                    </div><!--End b-tabletop-->
                                                                    </form>
                                                                    </div><!--End b-table-->
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
