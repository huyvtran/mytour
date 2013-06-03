<?php
include 'config.php';
//start call service
ini_set('soap.wsdl_cacheecho nabled', 0);
$client = new SoapClient(URL_SERVICE);
//,array("trace" => 1, "exceptions" => 0));
$hotel_id = $_REQUEST['hotel_id'];
$hotel = json_decode($client->getInfo($hotel_id), true);


$room_type_id = $_REQUEST['room_type_id'];
$room_type = json_decode($client->getInfoRoomType($hotel_id, $room_type_id), true);

$countries = json_decode($client->getCountries(), true);

$states = json_decode($client->getDefaultStates(), true);

$customer_name = $_REQUEST['customer_name '];
$amount = $_REQUEST['amount'];
$date_start = $_REQUEST['date_start'];
$date_end = $_REQUEST['date_end'];
if (isset($_REQUEST['amount'])) {
    $r = $client->bookRoom($hotel_id, $room_type_id, $date_start, $date_end, $amount, $customer_name);
    $r = json_decode($r, true);
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <script src="http://code.jquery.com/jquery-1.8.3.js"></script>
        <script type="text/javascript">
            $(document).ready(function(){
                function loadbill(date_start,date_end,room_type_id,hotel_id,is_apply_campaign) {
                    $.ajax({
                        type: "POST",
                        url: "loadBill.php",
                        data: { date_start: date_start, date_end: date_end, room_type_id: room_type_id,hotel_id:hotel_id,is_apply_campaign:is_apply_campaign}
                    }).done(function( msg ) {
                        $('.load-bill-auto').html(msg);
                    });
                }
                var date_start        = $('input[name="date_start"]');
                var date_end          = $('input[name="date_end"]');
                var room_type_id      = "<?php echo $room_type_id; ?>";
                var hotel_id          = "<?php echo $hotel_id; ?>"
                var is_apply_campaign =  $('select[name="is_apply_campaign"]');
                
                $('input[type="date"]').change(function(){
                    loadbill( date_start.val(), date_end.val(), room_type_id,hotel_id,is_apply_campaign.val());
                })
                is_apply_campaign.change(function(){
                   loadbill( date_start.val(), date_end.val(), room_type_id,hotel_id,is_apply_campaign.val());
                })
            });
        </script>
    </head>
    <body>
        <div style="padding:50px 100px">
            <?php echo "<h1>$r</h1>"; ?>
            <form method="post">
                <input type="hidden" name="hotel_id" value="<?php echo $_REQUEST['hotel_id'] ?>"/>
                <input type="hidden" name="room_type_id" value="<?php echo $_REQUEST['room_type_id'] ?>"/>
                <table cellpadding="10" border="1" width="600px">
                    <tr>
                        <td valign="top">Tên đơn : </td>
                        <td>
                            <input type="text" name="title" value=""  style="width:300px"/>
                        </td>
                        
                    </tr>
                    <tr>
                        <td valign="top">Khách sạn : </td>
                        <td>
                            <a href="hotel-info.php?hotel_id=<?php echo $hotel['ID'] ?>"><?php echo $hotel['title'] ?></a>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">Loại phòng : </td>
                        <td>
                            <a href="room-type-info?hotel_id=<?php echo $hotel['ID'] ?>&room_type_id=<?php echo $room_type['ID']; ?>"/><?php echo $room_type['title']; ?></a>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">Ngày</td>
                        <td>
                            <input type="date" name="date_start" value=""/> -
                            <input type="date" name="date_end" value=""/>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">Áp dụng KM </td>
                        <td>
                            <select name="is_apply_campaign" style="width: 258px;" >
                                <option></option>
                                <option value="yes">Áp dụng KM</option>
                                <option value="no">Không áp dụng KM</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">Họ tên</td>
                        <td>
                            <input type="text" name="customer_name" value=""  style="width:300px"/>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">Email</td>
                        <td>
                            <input type="text" name="customer_email" value=""  style="width:300px"/>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">Quốc gia</td>
                        <td>
                            <select name="country_id" style="width: 258px;">
                                <option value="0"></option>
                                <?php foreach ($countries as $value) : ?>
                                    <option value="<?php echo $value['ID'] ?>"><?php echo $value['title'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">Tỉnh/ Thành Phố</td>
                        <td>
                            <select name="state_id" style="width: 258px;">
                                <option value="0"></option>
                                <?php foreach ($states as $value) : ?>
                                    <option value="<?php echo $value['ID'] ?>"><?php echo $value['title'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">Địa chỉ</td>
                        <td>
                            <input type="text" name="customer_address" value=""  style="width:300px"/>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">Số điện thoại</td>
                        <td>
                            <input type="text" name="customer_phone" value=""/>
                        </td>
                    </tr>

                    <tr>
                        <td valign="top">Số phòng</td>
                        <td>
                            <input type="text" name="amount" value=""/>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top"> Thêm giường: </td>
                        <td>
                            <input type="checkbox" value="" name="has_extrabed">
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">Mô tả : </td>
                        <td>
                            <textarea name="desc" cols="50" rows="7"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td valign="top">Giá từng ngày : </td>
                        <td class="load-bill-auto">

                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <input type="submit" value="book"/>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
    </body>
</html>
