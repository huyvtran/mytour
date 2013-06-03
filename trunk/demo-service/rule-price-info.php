<?php
include 'config.php';
//start call service
ini_set('soap.wsdl_cacheecho nabled', 0);
$client = new SoapClient(URL_SERVICE);
//,array("trace" => 1, "exceptions" => 0));
$hotel_id = $_REQUEST['hotel_id'];
$rule_id = $_REQUEST['rule_id'];

$r = $client->getRulePriceInfo($hotel_id, $rule_id);
$rule_price = json_decode($r, true);
$room_types = $rule_price['room_types'];

$r1 = $client->getInfo($hotel_id);
$hotel = json_decode($r1, true);
$column = 4;
function find_thu($i) {
    switch ($i) {
        case 1 : $thu = 'Thứ hai';
            break;
        case 2 : $thu = 'Thứ ba';
            break;
        case 3 : $thu = 'Thứ tư';
            break;
        case 4 : $thu = 'Thứ năm';
            break;
        case 5 : $thu = 'Thứ sáu';
            break;
        case 6 : $thu = 'Thứ bảy';
            break;
        case 0 : $thu = 'Chủ nhật';
            break;
    }
    return $thu;
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
      
        <title></title>
    </head>
    <body>
       <div style="padding:50px 100px">
            <table border="1" cellpadding="10" width="800px">
                <tr>
                    <td valign="top"  colspan="4">
                        <h3 style="margin:0px">
                            <?php echo $rule_price['title']; ?>
                        </h3>
                        <div>
                            Thuộc khách sạn :  <a href="hotel-info.php?hotel_id=<?php echo $hotel['ID'] ?>"><?php echo $hotel['title'] ?></a>
                        </div>
                        <div>
                            <?php echo $rule_price['desc'] ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        Ngày bắt đầu : <?php echo date('d-m-Y', strtotime($rule_price['date_start'])) ?>
                    </td>
                    <td colspan="2">
                        Ngày kết thúc : <?php echo date('d-m-Y', strtotime($rule_price['date_end'])) ?>
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        Kiểu áp dụng : <?php echo ($rule_price['sign'] . $rule_price['value'] . ' ' . $rule_price['title_currency']) ?>
                    </td>
                    <td colspan="2">
                        Mức ưu tiên : <?php echo $rule_price['priority'] ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="4">
                        Thứ áp dụng:
                        <?php
                        $n = explode(',', $rule_price['days']);
                        ?>
                        <?php for ($i = 0; $i < 7; $i++): ?>
                            <input name="days[]" type="checkbox" value="<?php echo($i % 7) ?>"<?php echo(in_array($i, $n) ? ' checked' : '' ) ?> disabled="disabled"/>
                            <?php echo find_thu($i) ?>&nbsp;&nbsp;
                        <?php endfor; ?>
                    </td>
                </tr>
                <tr>
                    <td  colspan="4"> Loại phòng áp dụng</td>
                </tr>

                <?php
                $row_activities = ceil(count($room_types) / $column);
                for ($i = 0; $i < $row_activities; $i++):
                    ?>
                    <tr width="100%">
                        <?php
                        for ($j = 0; $j < $column; $j++):
                            $a = $room_types[$i * $column + $j];
                            ?>
                            <td valign="top" width="<?php echo (ceil(100 / $column)) ?>%"  >
                                <?php if (isset($a['ID'])): ?>
                                    <input type="checkbox" name="activities[]" value="<?php echo ($a['ID']) ?>"<?php echo ($a['checked']); ?> disabled="disabled" />
                                    <?php echo ($a['title']); ?>
                                <?php endif; ?>
                            </td>
                        <?php endfor; ?>
                    </tr>
                <?php endfor; ?>
            </table>
        </div>
    </body>
</html>