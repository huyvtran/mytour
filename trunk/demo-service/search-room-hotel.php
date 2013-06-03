
<?php
include 'config.php';
//start call service
ini_set('soap.wsdl_cacheecho nabled', 0);
$client = new SoapClient(URL_SERVICE);
$hotel_id = $_REQUEST['hotel_id'];
$hotel = json_decode($client->getInfo($hotel_id), true);
(empty($hotel['lat'])) ? $lat = 21.012226 : $lat = $hotel['lat'];
(empty($hotel['lng'])) ? $lng = 105.847861 : $lng = $hotel['lng'];

$date_start = $_POST['date_start'];
$date_end   = $_POST['date_end'];

if (isset($_POST['submit'])) {
    $list_rooms = json_decode($client->findRoomTypeHotel($hotel_id, $date_start, $date_end), true);
}
if (isset($list_rooms['status']) && $list_rooms['status'] == '203') {
    die($list_rooms['message']);
} else {
    ?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml">
        <head>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                <title>Kết quả tìm phòng</title>
                <link rel="stylesheet" type="text/css" href="css/reset.css" />
                <link rel="stylesheet" type="text/css" href="css/style.css" />
                <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
                <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>
                <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/themes/ui-lightness/jquery-ui.css" type="text/css" media="all" />
                <script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=AIzaSyDsduDYKlBFr7ZxGMdOqDvJuvC47vHsB3Y"></script>
                <script type="text/javascript">
                    $(document).ready(function(){
                        $(function() {
                            $( ".datepicker" ).datepicker({ dateFormat: 'dd-mm-yy' });
                        });
                        $('input[name="submit"]').click(function(){
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
                        })
                           
                        //Chuyển chuỗi kí tự (string) sang đối tượng Date()
                        function parseDate(str) {
                            var mdy = str.split('-');
                            return new Date(mdy[2], mdy[1], mdy[0]);
                        }
                            
                        function load_google_map_view(lat,lng) {
                            if (GBrowserIsCompatible()) {
                                var map = new GMap2(document.getElementById("map"));
                                map.addControl(new GSmallMapControl());
                                map.addControl(new GMapTypeControl());
                                var center = new GLatLng(lat,lng);
                                map.setCenter(center, 16);
                                geocoder = new GClientGeocoder();
                                var marker = new GMarker(center, {
                                    draggable: false
                                });  
                                map.addOverlay(marker);
                                //                jQuery("#lat").html( center.lat().toFixed(5));
                                //                jQuery("#lng").html( center.lng().toFixed(5));
                            }
                        }
                        var lat = "<?php echo($lat) ?>";
                        var lng = "<?php echo($lng) ?>";
                        load_google_map_view(lat,lng);
                    });
                </script>
            </head>
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
                        <div class="h-mainbottom">
                            <div class="d-search">
                                <span id="trang-ck"><a href="index.php" title="">Trang chủ</a></span>
                                <span id="trang-ck"><a href="#" title="">Catalogue</a></span>
                                <span id="trang-ck">
                                    <a href="hotel-info.php?hotel_id=<?php echo $hotel['ID'] ?>"><?php echo $hotel['title'] ?></a>
                                </span>
                                <div class="d-searchmain">
                                    <div class="d-imgtitle"><img src="<?php echo $hotel['img']; ?>" width="100" height="100"/></div>
                                    <div class="d-title"><h2><a href="#" title=""><?php echo $hotel['title']; ?></a></h2></div>
                                    <div class="d-bodytitle">
                                        <div class="d-star"> 
                                            <?php echo str_repeat('&#10030;', (int) $hotel['star']); ?>
                                            <?php echo str_repeat('&#10025;', 5 - (int) $hotel['star']); ?>
                                        </div>
                                        <div class="d-khdetail">
                                            <p>Địa chỉ : <?php echo $hotel['address']; ?></p>
                                            <span><?php echo $hotel['desc']; ?></span>
                                        </div>
                                        <div class="d-price">
                                            <div class="d-bouncontact">
                                                <div id="d-contact1"><a href="#" title="">Khuyễn mãi</a></div>
                                                <div id="d-contact2"><a href="#" title="">46 đánh giá</a></div>
                                                <div id="d-contact3"><a href="#" title="">Giá tốt nhất</a></div>
                                                <div id="d-contact4"><a href="#" title="">Xem bản đồ</a></div>
                                            </div><!--End d-bouncontact-->
                                        </div><!--End d-price-->
                                        <div class="d-danhgia"><p>4/5</p><a href="#" title="">46 đánh giá</a></div>
                                    </div><!--End d-bodytitle-->
                                </div><!--End c-searchmain-->
                            </div><!--End c-search-->
                            <div class="d-giaphong">
                                <div class="d-giaphongleft"><span></span></div>
                                <div class="d-giaphongcenter">
                                    <div class="d-phonggia"><span>Phòng và giá</span></div>
                                    <div class="d-pen"><span></span></div>
                                    <div class="d-phongdetail">
                                        <form name="find_room_type" method="post" action="">
                                            <span>Ngày nhận phòng</span><input type="text" name="date_start" value="<?php echo $date_start ?>" class="datepicker"/>&nbsp;
                                            <span>Ngày trả phòng</span><input type="text" name="date_end" value="<?php echo $date_end ?>" class="datepicker"/>
                                            <input type="hidden" name="hotel_id" value="<?php echo $hotel_id ?>"/>
                                            <input type="submit" value="Xem giá phòng"  name="submit" class="submit-find-room"/>
                                        </form>
                                    </div><!--End d-phongdetail-->
                                </div><!--End d-giaphongcenter-->
                                <div class="d-giaphongright"><span></span></div>
                            </div><!--End d-giaphong-->
                            <span id="titlelisttp">
                                <span>
                                    Danh sách phòng tại <?php echo $hotel['title'] ?>
                                </span>

                            </span>
                            <div class="h-bodyleft">
                                <div class="d-hotelleft"><span></span></div>
                                <div class="d-hotelcenter">

                                </div>
                                <div class="d-hotelright"><span></span></div>
                                        <div class="k-chitiet">
                                              <?php if (isset($list_rooms) && count($list_rooms) > 0) : ?>
                                    <?php foreach ($list_rooms as $room): ?>
                                            <form method="post" action ="book-room-search.php" name="book_room">
                                                <div class="k-hotelproduc">
                                                    <div class="k-imgproduc">
                                                        <a href="room-type-info?hotel_id=<?php echo $hotel['ID'] ?>&room_type_id=<?php echo $room['ID']; ?>" >
                                                            <img src="<?php echo $room['img']; ?>" width="120" height="120"/>
                                                        </a>
                                                    </div>
                                                    <div class="k-detailproduc">
                                                        <div class="k-titleproduc">
                                                            <a href="room-type-info?hotel_id=<?php echo $hotel['ID'] ?>&room_type_id=<?php echo $room['ID']; ?>" >
                                                                <?php echo $room['title']; ?>
                                                            </a>
                                                            <span> <?php echo number_format($room['price_avg'], 2, '.', ',') . ' ' . $room['currency_title'] ?></span><p>1 Đêm</p>
                                                            <img src="images/start3.png"  title="" alt=""/>
                                                            <?php if ($room['is_apply_campaign'] == 'yes') { ?>
                                                                <a id="btn1" href="#" title="" >Khuyến mãi</a>
                                                            <?php } else { ?>
                                                                <a id="btn4" href="#" title="" >Không KM</a>
                                                            <?php } ?>
                                                            <a id="btn2" href="#" title ="" >Đánh giá</a>
                                                            <input type="submit" name="book" value="Ðặt phòng" id="btn3"/>
                                                        </div>
                                                        <div class="addphong">
                                                            <table width="562" height="80" border="1">
                                                                <tr>
                                                                    <td><span>Số người tối đa</span></td>
                                                                    <td><span>Kiểu phòng</span></td>
                                                                    <td><span>Số phòng trống</span></td>
                                                                    <td><span>Số phòng</span></td>
                                                                </tr>
                                                                <tr>
                                                                    <td><span><?php echo $room['size_people'] . ' người' ?></span></td>
                                                                    <td><span>
                                                                            <?php
                                                                            if ($room['is_apply_campaign'] == 'yes') {
                                                                                echo 'Áp Dụng KM';
                                                                            } else {
                                                                                echo 'Không áp dụng KM';
                                                                            }
                                                                            ?></span></td>
                                                                    <td><span><?php echo $room['number_free'] ?></span></td>
                                                                    <td>
                                                                        <select name="number_book">
                                                                            <?php
                                                                            for ($i = 1; $i <= $room['number_free']; $i++):
                                                                                ?>
                                                                                <option value="<?php echo $i ?>"><?php echo $i ?></option>
                                                                            <?php endfor; ?>
                                                                        </select>
                                                                    </td>
                                                                    <div>
                                                                        <input type="hidden" name="room_type_id" value="<?php echo $room['ID'] ?>" />
                                                                        <input type="hidden" name="hotel_id" value="<?php echo $hotel_id ?>" />
                                                                        <input type="hidden" name="date_start" value="<?php echo $date_start ?>" />
                                                                        <input type="hidden" name="date_end" value="<?php echo $date_end ?>" />
                                                                        <input type="hidden" name="is_apply_campaign" value="<?php echo $room['is_apply_campaign'] ?>" />
                                                                        <input type="hidden" name="price_avg" value="<?php echo $room['price_avg'] ?>" />
                                                                    </div>
                                                                </tr>
                                                            </table>
                                                        </div><!--End addphong-->
                                                        <div class="k-xemchitiet">
                                                            <p>Đặt phòng trực tuyến hoặc Gọi <span>(04) 39 310 270 / (04) 66 759 717</span></p>
                                                        </div>
                                                    </div><!--End k-detailproduc-->
                                                </div><!--End k-hotelproduc-->
                                            </form>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div><!--End k-chitiet-->
                            </div><!--End h-bodyleft-->
                            <div class="c-bodytag">
                                <div class="c-titletag"><span></span></div>
                                <a href="#" title="">Sunrise Nha Trang Beach Hotel & Spa 5 sao ,</a>
                                <a href="#" title="">khach san Nha Trang bon sao, </a>
                                <a href="#" title="">khach san Nha Trang 5 sao, </a>
                                <a href="#" title="">khach san Nha Trang bon sao, </a>
                                <a href="#" title="">Long Beach Hotel 3 sao, </a>
                                <a href="#" title="">Sunrise Nha Trang Beach Hotel & Spa 5 sao ,</a>
                                <a href="#" title="">khach san Nha Trang bon sao, </a>
                                <a href="#" title="">khach san Nha Trang 5 sao, </a>
                                <a href="#" title="">khach san Nha Trang bon sao, </a>
                                <a href="#" title="">Long Beach Hotel 3 sao, </a>
                            </div><!--End c-bodytag-->
                        </div><!--End h-mainbottom-->
                        <div class="h-mainright">
                            <div class="d-quangcao1">
                                <div class="d-showprice">
                                    <div class="d-priceone"><a href="#" title="">1.739.000 / đêm</a></div>
                                    <div class="d-pricetow">
                                        <span>Giá bao gồm thuế &phí dịch vụ</span>
                                        <a href="#" title="">1.739.000 / đêm</a>
                                    </div>
                                </div>
                                <div class="d-pricedetail"><span>Làm sao để có giá này?
                                        Hãy gọi (04) 39 310 270 để có giá đặc biệt </span></div>
                            </div><!--End d-quangcao1-->
                            <div  id="map" class="d-map">
                            </div>
                            <div class="d-tksearch">
                                <div class="h-searchleft"><span></span></div>
                                <div class="h-searchcenter">
                                    <div class="h-namecity">
                                        <ul>
                                            <li><span id="h-textspan1">Thành phố</span></li>
                                            <li>	
                                                <select class="h-nameselect">
                                                    <option>--------Thành phố--------</option>
                                                    <option>Hà Nội</option>
                                                    <option>Hà Nội</option>
                                                    <option>Hà Nội</option>
                                                    <option>Hà Nội</option>
                                                </select>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="h-namecity">
                                        <ul>
                                            <li><span  id="h-textspan2">Tên khách sạn</span></li>
                                            <li>	
                                                <input class="h-namehotel" type="text" />
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="h-namecity">
                                        <ul>
                                            <li><span>Ngày nhận phòng</span></li>
                                            <li>	
                                                <select class="h-calender">
                                                    <option>10</option>
                                                    <option>10</option>
                                                    <option>10</option>
                                                    <option>10</option>
                                                </select>
                                                <select class="h-calender">
                                                    <option>10-2012</option>
                                                    <option>10-2012</option>
                                                    <option>10-2012</option>
                                                    <option>10-2012</option>
                                                </select>
                                                <a href="#" title=""><img src="images/calender.png" title="" alt="" /></a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="h-namecity">
                                        <ul>
                                            <li><span>Ngày trả phòng</span></li>
                                            <li>	
                                                <select class="h-calender">
                                                    <option>10</option>
                                                    <option>10</option>
                                                    <option>10</option>
                                                    <option>10</option>
                                                </select>
                                                <select class="h-calender">
                                                    <option>10-2012</option>
                                                    <option>10-2012</option>
                                                    <option>10-2012</option>
                                                    <option>10-2012</option>
                                                </select>
                                                <a href="#" title=""><img src="images/calender.png" title="" alt="" /></a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="h-namecity">
                                        <input class="h-seasubmit" type="submit" value="Tìm Khách Sạn" />
                                    </div>
                                </div><!--End h-searchcenter-->
                                <div class="h-searchright"><span></span></div>
                            </div><!--End h-search-->
                            <div class="h-bodyright">
                                <div class="h-hoteltop">
                                    <div class="h-hotelleft"><span></span></div>
                                    <div class="h-hotelcenter"><span>GIÁ TỐT HIỆN TẠI</span></div>
                                    <div class="h-hotelright"><span></span></div>
                                </div><!--End h-hoteltop-->
                                <div class="h-hoteltopmain">
                                    <span>Giá tốt & nhiều lợi ích hơn khi mua theo 
                                        trọn gói / khuyến mãi</span>
                                    <div class="d-quangcao2">
                                        <div class="d-dongq1"><a href="#" title="">Moupon Ana Mandara Huế Resort & Spa</a></div>
                                        <div class="d-dongq2">
                                            <div class="d-gialine"><span>Giá Gốc</span><p>5.630.000</p></div>
                                            <div class="d-gialine"><span>Tiết kiệm</span><p>3.891.000</p></div>
                                        </div>
                                        <div class="d-qdonq2">
                                            <div class="d-gialine2"><span>Cho 1 đêm</span><p>1.739.000</p></div>
                                            <div class="d-gialine2"><a href="#" title="">Mua ngay</a></div>
                                        </div>
                                    </div><!--End d-quangcao2-->
                                    <div class="d-quangcao2">
                                        <div class="d-dongq1"><a href="#" title="">Khuyến mãi Đón mùa Thu cùng gói 
                                                khuyến mãi tại Ana Mandara Huế</a></div>
                                        <div class="d-dongq2">
                                            <div class="d-giaone"><span>Giá Gốc</span><p>5.630.000</p></div>
                                            <div class="d-giaone"><span>Giảm giá</span><p>60%</p></div>
                                            <div class="d-giaone"><span>Tiết kiệm</span><p>3.891.000</p></div>
                                        </div>
                                        <div class="d-qdong2">
                                            <div class="d-gialine2"><span>Cho 1 đêm</span><p>1.739.000</p></div>
                                            <div class="d-gialine2"><a href="#" title="">Mua ngay</a></div>
                                        </div>
                                    </div><!--End d-quangcao2-->
                                </div><!--End h-hoteltopmain-->
                            </div><!--End h-bodyright -->
                        </div><!--End h-mainright-->
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
    <?php } ?>