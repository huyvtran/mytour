<?php
include 'config.php';
//start call service
ini_set('soap.wsdl_cacheecho nabled', 0);
$client = new SoapClient(URL_SERVICE);
//,array("trace" => 1, "exceptions" => 0));
$hotel_id = $_REQUEST['hotel_id'];
$r = $client->getInfo($hotel_id);
$r1 = $client->getRoomType($hotel_id);

//hotel
$hotel = json_decode($r, true);
$activities = $hotel['activities'];
$facilities = $hotel['facilities'];
$services = $hotel['services'];
(empty($hotel['lat'])) ? $lat = 21.012226 : $lat = $hotel['lat'];
(empty($hotel['lng'])) ? $lng = 105.847861 : $lng = $hotel['lng'];

//room

$room = json_decode($r1, true);

$room_types = $room['room_types'];
$room_type_total_page = $room['total_page'];

//states
$states = json_decode($client->getDefaultStates(), true);

// get customer
$customer = get_customer();
$customer_id = $customer['ID'];

//feedback
$allFeeds = json_decode($client->getAllFeedback($hotel_id), true);

$feed_ans = array();
$feed_rep = array();

if (!empty($allFeeds)) {
    foreach ($allFeeds as $feed) {
        if ($feed['user_id'] == 0) {
            array_push($feed_ans, $feed);
        } else {
            array_push($feed_rep, $feed);
        }
    }
}


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
        });
        
        $('input[name="feed_submit"]').click(function(){
            var feedback = $('textarea[name="feedback"]').val();
            if (feedback === '') 
            {
                alert('Nội dung feedback ko được rỗng');
                return false;
            }
            var datas =new Object();
            datas.feedback = feedback;
            datas.hotel_id = '<?php echo $hotel_id; ?>';
            datas.customer_id = '<?php echo $customer_id ?>';
            $.ajax({
                type: "POST",
                url: "ajax_load_feedback.php",
                data: datas
            }).done(function( msg ) {
                $('.comment-box').prepend(msg);
                $('textarea[name="feedback"]').val('');
            })
            window.location = '#anchor-comment';
            return false;
        });
               
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
                <div class="d-search">
                    <span id="trang-ck"><a href="index.php" title="">Trang chủ</a></span>
                    <span id="trang-ck"><a href="#" title="">Khách sạn</a></span>
                    <span id="trang-ck"><a href="#" title=""><?php echo $hotel['title'] ?></a></span>
                    <div class="d-searchmain">
                        <div class="d-imgtitle">
                            <a href="javascript:void(0)">
                                <img src="<?php echo $hotel['img']; ?>" width="120px" height="120px"/>
                            </a>
                        </div>
                        <div class="d-title">
                            <h2>
                                <a href="hotel-info.php?hotel_id=<?php echo $hotel['ID'] ?>"><?php echo $hotel['title'] ?></a>
                            </h2>
                        </div>
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
                                    <div id="d-contact1"><a href="#" title="">Khuyến mãi</a></div>
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
                            <form name="find_room_type" method="post" action="search-room-hotel.php">
                                <span>Ngày nhận phòng</span><input type="text" name="date_start" value="" class="datepicker"/>&nbsp;
                                <span>Ngày trả phòng</span><input type="text" name="date_end" value="" class="datepicker"/>
                                <input type="hidden" name="hotel_id" value="<?php echo $hotel_id ?>"/>
                                <input type="submit" value="Xem giá phòng"  name="submit" class="submit-find-room"/>
                            </form>
                        </div><!--End d-phongdetail-->
                    </div><!--End d-giaphongcenter-->
                    <div class="d-giaphongright"><span></span></div>
                </div><!--End d-giaphong-->
                <div class="h-bodyleft">
                    <div class="d-hotelleft"><span></span></div>
                    <div class="d-hotelcenter">
                        <ul>
                            <li><a href="#" title="">Chi tiết</a></li>
                            <li><a href="#" title="">Khuyến Mãi</a></li>
                            <li><a href="#" title="">Đặt Phòng</a></li>
                            <li><a href="#" title="">Đánh Giá</a></li>
                            <li><a href="#" title="">Hình Ảnh & Video</a></li>
                        </ul>
                    </div>
                    <div class="d-hotelright"><span></span></div>
                    <div class="d-chitiet">
                        <ul>
                            <li>
                                <div class="k-titleshow2"><a href="javascript:void(0)" title="">Loại khách sạn:</a></div>
                                <div class="k-giashow2"><span><?php echo $hotel['type_title'] ?></span></div>
                            </li>
                            <li>
                                <div class="k-titleshow2"><a href="javascript:void(0)" title="">Số điện thoại:</a></div>
                                <div class="k-giashow2"><span><?php echo $hotel['phone'] ?></span></div>
                            </li>
                            <li>
                                <div class="k-titleshow2"><a href="javascript:void(0)" title="">Số điện thoại 1:</a></div>
                                <div class="k-giashow2"><span><?php echo $hotel['phone1'] ?></span></div>
                            </li>
                            <li>
                                <div class="k-titleshow2"><a href="javascript:void(0)" title="">Số điện thoại 2:</a></div>
                                <div class="k-giashow2"><span><?php echo $hotel['phone2'] ?></span></div>
                            </li>
                            <li>
                                <div class="k-titleshow2"><a href="javascript:void(0)" title="">Email:</a></div>
                                <div class="k-giashow2"><span><?php echo $hotel['email'] ?></span></div>
                            </li>
                            <li>
                                <div class="k-titleshow2"><a href="javascript:void(0)" title="">Fax :</a></div>
                                <div class="k-giashow2"><span><?php echo $hotel['fax'] ?></span></div>
                            </li>
                            <li>
                                <div class="k-titleshow2"><a href="javascript:void(0)" title="">Quốc Gia:</a></div>
                                <div class="k-giashow2"><span><?php echo $hotel['country_title'] ?></span></div>
                            </li>
                            <li>
                                <div class="k-titleshow2"><a href="javascript:void(0)" title="">Tỉnh / Thành Phố :</a></div>
                                <div class="k-giashow2"><span><?php echo $hotel['state_title'] ?></span></div>
                            </li>
                            <li>
                                <div class="k-titleshow2"><a href="javascript:void(0)" title=""> Website:</a></div>
                                <div class="k-giashow2"><span><?php echo $hotel['website'] ?></span></div>
                            </li>
                            <li>
                                <div class="k-titleshow2"><a href="javascript:void(0)" title=""> Giờ mở cửa:</a></div>
                                <div class="k-giashow2"><span> <?php echo(date('H:i', strtotime($hotel['checkin_time']))); ?></span></div>
                            </li>
                            <li>
                                <div class="k-titleshow2"><a href="javascript:void(0)" title=""> Giờ đóng cửa:</a></div>
                                <div class="k-giashow2"><span> <?php echo(date('H:i', strtotime($hotel['checkout_time']))); ?></span></div>
                            </li>
                            <li>
                                <div class="k-titleshow2"><a href="javascript:void(0)" title=""> Số tầng:</a></div>
                                <div class="k-giashow2"><span><?php echo($hotel['floor']) ?></span></div>
                            </li>
                        </ul>
                    </div><!--End d-chitiet-->
                </div><!--End h-bodyleft-->

                <div class="h-bodybottom1">
                    <div class="h-boxleft"><span></span></div>
                    <div class="h-boxcenter">
                        <span>Danh sách phòng của <?php echo $hotel['title'] ?>
                        </span>
                    </div>
                    <div class="h-boxright"><span></span></div>

                    <div class="c-boxbottom1">
                        <?php foreach ($room_types as $a): ?>
                            <div class="k-hotelproduc1">
                                <div class="k-imgproduc1">
                                    <a href="room-type-info?hotel_id=<?php echo $hotel['ID'] ?>&room_type_id=<?php echo $a['ID']; ?>" >
                                        <img src="<?php echo $a['img']; ?>" width="120" height="120"/>
                                    </a>
                                </div>
                                <div class="k-detailproduc1">
                                    <div class="k-titleproduc1">
                                        <a href="room-type-info?hotel_id=<?php echo $hotel['ID'] ?>&room_type_id=<?php echo $a['ID']; ?>" >
                                            <?php echo $a['title']; ?>
                                        </a>                                       
                                        <img src="images/start3.png"  title="" alt=""/>                                       
                                    </div>
                                    <div class="k-contact">
                                        <p>Diện tích: <?php echo $a['area'] ?></p>

                                        <p>Số người : <?php echo $a['size_people'] ?></p>
                                        <p>Mô tả: <?php echo $a['desc'] ?></p>
                                    </div>

                                </div><!--End k-detailproduc-->
                            </div><!--End k-hotelproduc-->
                        <?php endforeach; ?>

                        <div class="k-phantrang-1">
                            <ul>

                                <li><a id="phantrang1" href="#" title=""> </a></li>
                                <?php for ($i = 1; $i <= $room_type_total_page; $i++): ?>
                                    <li><a href="hotel-info.php?hotel_id=<?php echo $hotel['ID'] ?>&p=<?php echo ($i) ?>"><?php echo ($i) ?></a></li>
                                <?php endfor; ?>

                                <li><a id="phantrang2" href="#" title=""> </a></li>
                            </ul>
                        </div>
                    </div><!--End h-boxbottom1-->
                </div><!--End h-bodybottom1-->



                <div class="h-bodybottom1">
                    <div class="h-boxleft"><span></span></div>
                    <div class="h-boxcenter">
                        <span>Giải trí tại <?php echo $hotel['title'] ?>
                        </span>
                    </div>
                    <div class="h-boxright"><span></span></div>

                    <div class="c-boxbottom1">
                        <?php if (count($activities) > 0) : ?>
                            <div class="d-ul1">
                                <ul>
                                    <?php for ($i = 0; $i <= ceil(count($activities) / 2); $i++) : ?>
                                        <?php if (trim($activities[$i]['checked']) == 'checked'): ?>
                                            <li><a id="yes" href="javascript:void(0)" title=""><?php echo $activities[$i]['title'] ?></a></li>
                                        <?php else: ?>
                                            <li><a id="no" href="javascript:void(0)" title=""><?php echo $activities[$i]['title'] ?></a></li>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </ul>
                            </div><!--End c-ul1-->
                            <div class="d-ul2">
                                <ul>
                                    <?php for ($i = ceil(count($activities) / 2) + 1; $i <= (count($activities)); $i++) : ?>
                                        <?php if (trim($activities[$i]['checked']) == 'checked'): ?>
                                            <li><a id="yes" href="javascript:void(0)" title=""><?php echo $activities[$i]['title'] ?></a></li>
                                        <?php else: ?>
                                            <li><a id="no" href="javascript:void(0)" title=""><?php echo $activities[$i]['title'] ?></a></li>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </ul>
                            </div><!---End c-ul2-->
                        <?php endif; ?>
                    </div><!--End h-boxbottom1-->
                </div><!--End h-bodybottom1-->



                <div class="h-bodybottom1">
                    <div class="h-boxleft"><span></span></div>
                    <div class="h-boxcenter">
                        <span>Tiện ích tại <?php echo $hotel['title'] ?>
                        </span>
                    </div>
                    <div class="h-boxright"><span></span></div>

                    <div class="c-boxbottom1">
                        <?php if (count($facilities) > 0) : ?>
                            <div class="d-ul1">
                                <ul>
                                    <?php for ($i = 0; $i <= ceil(count($facilities) / 2); $i++) : ?>
                                        <?php if (trim($facilities[$i]['checked']) == 'checked'): ?>
                                            <li><a id="yes" href="javascript:void(0)" title=""><?php echo $facilities[$i]['title'] ?></a></li>
                                        <?php else: ?>
                                            <li><a id="no" href="javascript:void(0)" title=""><?php echo $facilities[$i]['title'] ?></a></li>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </ul>
                            </div><!--End c-ul1-->
                            <div class="d-ul2">
                                <ul>
                                    <?php for ($i = ceil(count($facilities) / 2) + 1; $i <= (count($facilities)); $i++) : ?>
                                        <?php if (trim($facilities[$i]['checked']) == 'checked'): ?>
                                            <li><a id="yes" href="javascript:void(0)" title=""><?php echo $facilities[$i]['title'] ?></a></li>
                                        <?php else: ?>
                                            <li><a id="no" href="javascript:void(0)" title=""><?php echo $facilities[$i]['title'] ?></a></li>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </ul>
                            </div><!---End c-ul2-->
                        <?php endif; ?>
                    </div><!--End h-boxbottom1-->
                </div><!--End h-bodybottom1-->


                <div class="h-bodybottom1">
                    <div class="h-boxleft"><span></span></div>
                    <div class="h-boxcenter">
                        <span>Dịch vụ tại <?php echo $hotel['title'] ?>
                        </span>
                    </div>
                    <div class="h-boxright"><span></span></div>

                    <div class="c-boxbottom1">
                        <?php if (count($services) > 0) : ?>
                            <div class="d-ul1">
                                <ul>
                                    <?php for ($i = 0; $i <= ceil(count($services) / 2); $i++) : ?>
                                        <?php if (trim($services[$i]['checked']) == 'checked'): ?>
                                            <li><a id="yes" href="javascript:void(0)" title=""><?php echo $facilities[$i]['title'] ?></a></li>
                                        <?php else: ?>
                                            <li><a id="no" href="javascript:void(0)" title=""><?php echo $facilities[$i]['title'] ?></a></li>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </ul>
                            </div><!--End c-ul1-->
                            <div class="d-ul2">
                                <ul>
                                    <?php for ($i = ceil(count($services) / 2) + 1; $i <= (count($services)); $i++) : ?>
                                        <?php if (trim($services[$i]['checked']) == 'checked'): ?>
                                            <li><a id="yes" href="javascript:void(0)" title=""><?php echo $services[$i]['title'] ?></a></li>
                                        <?php else: ?>
                                            <li><a id="no" href="javascript:void(0)" title=""><?php echo $services[$i]['title'] ?></a></li>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </ul>
                            </div><!---End c-ul2-->
                        <?php endif; ?>
                    </div><!--End h-boxbottom1-->
                </div><!--End h-bodybottom1-->

                <div class="h-bodybottom2">
                    <div class="h-boxleft"><span></span></div>
                    <div class="h-boxcenter">
                        <span>Feedback</span>
                        <a href="#" title="">xem tất cả »</a>
                    </div>
                    <div class="h-boxright"><span></span></div>

                    <div id="anchor-comment" class="comment-box" style="margin: 5px; padding-top: 40px">
                        <?php foreach ($feed_ans as $f1): ?>
                            <div class='comment'>
                                <img src='images/represent-img.png' alt='' width='50px' style="margin-right: 10px">
                                <div class='desc' style="display: inline-block">
                                    <span style="color: #0066cc; font-size: 14px;"><?php echo $f1['customer_name'] ?></span>
                                    <span style="color: #ABADB3; font-size: 11px; font-style: italic"><?php echo date('d/m/Y H:i:s', strtotime($f1['time'])) ?></span>
                                    <div class='desc2'><?php echo $f1['comment'] ?></div>
                                    <?php foreach ($feed_rep as $f2): ?>
                                        <?php if ($f2['root'] == $f1['root']) : ?>                                            

                                            <div class='sub-comment' style="width:100%; margin-top: 30px">
                                                <img src='images/represent-img2.png' alt='' width='50px' style="margin-right: 10px">
                                                <div class='desc' style="display: inline-block">
                                                    <span style="color: #0066cc; font-size: 14px;"><?php echo $f2['user_fullname'] ?></span>
                                                    <span style="color: #ABADB3; font-size: 11px; font-style: italic"><?php echo $f2['time'] ?></span>
                                                    <div class='desc2'><?php echo $f2['comment'] ?></div>
                                                </div>
                                            </div>                                        


                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="comment-form">
                        <form action="" method="post" name="feed_form">
                            <img src="images/represent-img.png" alt="" width="50px"/>
                            <div class="info-comment">
                                <span><?php echo $customer['fullname'] ?></span>                            
                                <textarea name="feedback" id="" cols="60" rows="5"></textarea>
                                <input name="feed_submit" type="submit" value="Gửi" />
                            </div>

                        </form>
                    </div>
                </div><!--End h-boxbottom2-->
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
                                        <?php foreach ($states as $state): ?>
                                            <option value="<?php echo $state['ID'] ?>"><?php echo $state['title'] ?></option>
                                        <?php endforeach; ?>
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


