<?php
include 'config.php';
//start call service
ini_set('soap.wsdl_cache_enabled', 0);
$client = new SoapClient(URL_SERVICE);
//,array("trace" => 1, "exceptions" => 0));
$page_range = 4;
$r = $client->getList($page_range, isset($_REQUEST['p']) ? $_REQUEST['p'] : 1, 'title', 'asc');
$data = json_decode($r, true);
$hotels = $data['hotels'];
$total_page = $data['total_page'];
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Index-khachsan</title>
        <link rel="stylesheet" type="text/css" href="css/reset.css">
            <link rel="stylesheet" type="text/css" href="css/style.css">
                <link rel="stylesheet" type="text/css" href="css/voupon.css"/>
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
                                <div class="h-maintop">
                                    <div class="h-lienhe">
                                        <div class="h-lienhetop">
                                            <div class="h-lienheleft"><span></span></div>
                                            <div class="h-lienhecenter"><span>Liên Hệ mytour.vn</span></div>
                                            <div class="h-lienheright"><span></span></div>
                                        </div><!--End h-lienhetop-->
                                        <div class="h-lienhebottom">
                                            <div class="h-livechat">
                                                <a href="#" title=""><img src="images/online.png" title="" alt="" /></a>
                                            </div>
                                            <div class="h-livechat">
                                                <a href="#" title=""><img src="images/online.png" title="" alt="" /></a>
                                            </div>
                                            <div class="h-mail">
                                                <span><p>Phòng Kinh doanh</p></span>
                                                <span>Hotline: <a href="#" title="">0943 886 517</a></span>
                                                <span>Email: <a href="#" title="">sales@mytour.vn</a></span>
                                            </div>
                                            <div class="h-mail">
                                                <span><p>Phòng Kinh doanh</p></span>
                                                <span>Hotline: <a href="#" title="">0943 886 517</a></span>
                                                <span>Email: <a href="#" title="">david@mytour.vn</a></span>
                                            </div>
                                            <div class="h-thukm">
                                                <span>Đăng Ký Thư Khuyễn Mãi</span>
                                                <input class="h-textkm" type="text" />
                                                <input class="h-submitkm" type="button" value="Đăng Ký" />
                                            </div><!--End h-thukm-->
                                        </div><!--End h-lienhebottom-->
                                    </div><!--End h-lienhe-->
                                    <div class="h-bounbaner">	
                                        <div class="h-baner">
                                            <div class="h-imgbaner">
                                                <img src="images/baner.jpg" title="" alt="" />
                                            </div>
                                            <div class="h-quangcao">
                                                <span><a href="#">Sales giảm giá 40% chỉ áp dụng cho phòng đôi</a></span>
                                                <p>Gái chỉ còn 2.450.000 vnđ</p>
                                            </div><!--End h-quangcao-->
                                            <div class="h-search">
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
                                        </div><!--End h-baner-->
                                    </div><!--End h-bounbaner-->
                                    <div class="v-navtool"><a href="index.php" title="">Danh sách khách sạn</a></div>
                                    <div class="v-maincenter"  style="margin-top:10px;">
                                        <?php foreach ($hotels as $a): ?>
                                            <div class="v-produccenter">

                                                <div class="v-bookmaincenter"><a href="hotel-info.php?hotel_id=<?php echo $a['ID']; ?>">Chi tiết</a></div>

                                                <div class="v-contcmaincenter">

                                                    <div class="v-contcbottom">
                                                        <a href="hotel-info.php?hotel_id=<?php echo $a['ID']; ?>"><?php echo $a['title']; ?></a>
                                                        <ul>                                    	
                                                            <li> <?php echo str_repeat('&#10030;', (int) $a['star']); ?>
                                                                <?php echo str_repeat('&#10025;', 5 - (int) $a['star']); ?></li>
                                                            <li><p><?php echo $a['address'] ?></p></li>
                                                        </ul>
                                                    </div>
                                                </div><!--End v-contcmaincenter-->

                                                <div class="v-detailmaincenter">
                                                    <a href="#" title="">
                                                        <img src="<?php echo $a['img']; ?>" width="488px" height="256px"/>
                                                    </a>
                                                </div>
                                            </div><!--End v-produccenter-->
                                        <?php endforeach; ?>

                                        <div class="k-phantrang">
                                            <ul>
                                                <li><a id="phantrang1" href="javascript:void(0)" title=""> </a></li>
                                                <?php for ($i = 1; $i <= $total_page; $i++): ?>
                                                    <li> <a href="?p=<?php echo ($i) ?>"><?php echo ($i) ?></a></li>
                                                <?php endfor; ?>
                                                <li><a id="phantrang2"href="javascript:void(0)" title=""> </a></li>
                                            </ul>
                                        </div>     

                                    </div><!--End v-maincenter-->

                                </div><!--End h-maintop-->


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
