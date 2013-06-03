<?php
include 'config.php';
//start call service
ini_set('soap.wsdl_cache_enabled', 0);
$client = new SoapClient(URL_SERVICE);
//,array("trace" => 1, "exceptions" => 0));
$hotel_id = $_REQUEST['hotel_id'];
$room_type_id = $_REQUEST['room_type_id'];
$customer_id = get_customer_id();

    $time = date('Y-m-d H:i:s');
    $sql = "
        INSERT INTO 
         `view_stats`(hotel_id, room_type_id, customer_id, time) 
        VALUES ('$hotel_id', '$room_type_id', '$customer_id', '$time')
           ";    
    mysql_query($sql);


$r = $client->getInfoRoomType($hotel_id, $room_type_id);
$r1 = $client->getInfo($hotel_id);

$room_type = json_decode($r, true);

$hotel = json_decode($r1, true);

(empty($hotel['lat'])) ? $lat = 21.012226 : $lat = $hotel['lat'];
(empty($hotel['lng'])) ? $lng = 105.847861 : $lng = $hotel['lng'];

$rules_all = $room_type['rules_all'];


$rules_checked = $room_type['rules_checked'];
$room_services = $room_type['services'];
$column = 4;
?>
<?php require_once 'blocks/header.php'; ?>
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

<div class="boun">
    <div class="background">
        <div class="h-bounmain">
            <div class="h-mainbottom">
                

                <span id="titlelisttp"><span>Khách sạn <a href="hotel-info.php?hotel_id=<?php echo $hotel['ID'] ?>"><?php echo $hotel['title'] ?></a></span></span>
                <div class="backphong">
                    <div class="titlephong"><span><?php echo $room_type['title'] ?></span></div>
                    <div class="backphong2">
                        <div class="imgphong"><img src="<?php echo $room_type['img']; ?>" width="312" height="235"/></div>
                        <div class="contacphong">
                            <ul>
                                <li><span>Thuộc khách sạn: </span>
                                    <i>
                                        <?php echo $hotel['title'] ?>

                                    </i>
                                    <?php echo str_repeat('&#10030;', (int) $hotel['star']); ?>
                                    <?php echo str_repeat('&#10025;', 5 - (int) $hotel['star']); ?>
                                </li>
                                <li><p>Mô tả</p></li>
                                <li><span><?php echo $room_type['desc'] ?></span></li>
                            </ul>
                        </div><!--End contacphong-->  
                        <div class="thongtinchung">
                            <div class="thongtintitle"><span>Thông tin chung</span></div>
                            <div class="thongtinnoidung">
                                <ul>
                                    <li><span>Giá phòng : <?php echo $room_type['price'] . ' ' . $room_type['currency_title'] ?></span></li>
                                    <li><span>Giá thêm giường : <?php echo $room_type['extrabed_price'] . ' ' . $room_type['currency_title'] ?></span></li>
                                    <li><span>Số lượng phòng : <?php echo $room_type['number'] ?></span></li>
                                    <li><span>Số người / phòng : <?php echo $room_type['size_people'] ?></span></li>
                                    <li><span>Diện tích phòng: <?php echo $room_type['area'] . 'm&sup2;' ?></span></li>
                                    <li><span>Số phòng cho Mytour : <?php echo $room_type['number_mytour'] ?></span></li>		
                                    <li><span>Số phòng trống: <?php echo $room_type['number_free'] ?> </span></li>
                                </ul>
                            </div><!--End thongtinnoidung-->
                        </div><!--End thongtinchung-->  

                        <div class="thongtinchung">
                            <div class="thongtintitle"><span><?php echo('Dịch vụ phòng :') ?></span></div>
                            <div class="thongtinnoidung">
                                <ul>                                        

                                    <?php foreach ($room_services as $key => $r) : ?>                                            
                                        <li id="<?php echo (trim($r['checked']) == 'checked' ? 'yesone' : 'noone') ?>"><span><?php echo $r['title'] ?></span></li>
                                    <?php endforeach; ?>                                            
                                </ul>
                            </div><!--End thongtinnoidung-->
                        </div><!--End thongtinchung-->                                
                    </div><!--End backphong2-->
                </div><!--End backphong-->
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
                <div  id="map" class="d-map"></div>
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
<div class="i-social">
    <!-- Twitter -->
    <div class="twitter">        
        <a href="https://twitter.com/share" class="twitter-share-button" data-url="http://sub.mytour.vn/demo-service/room-type-info?hotel_id=14&amp;room_type_id=21" data-text="Mytour" data-via="mytour3">Tweet</a>
        <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>        
    </div>

    <!-- Facebook -->
    <div class="facebook">
        <a title="Share this post/page" 
           href="http://www.facebook.com/sharer.php?s=100&amp;p[url]=http://sub.mytour.vn/demo-service/room-type-info?hotel_id=<?php echo $hotel_id ?>&room_type_id=<?php echo $room_type_id ?>&amp;p[images][0]=http://icons.iconarchive.com/icons/aha-soft/standard-city/128/school-icon.png&amp;p[title]=<?php echo $hotel['title'] ?>&amp;p[summary]=<?php echo $room_type['desc'] ?>" target="_blank" rel="nofollow">
            <img src="images/Facebook-icon.png" />
        </a> 
    </div>

    <!-- Google+ -->
    <div class="googleplus">
        <g:plusone></g:plusone>
    </div>

</div>
</body>
</html>