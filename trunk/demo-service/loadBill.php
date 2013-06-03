<?php
include 'config.php';
//start call service
ini_set('soap.wsdl_cache_enabled', 0);
$client = new SoapClient(URL_SERVICE);
//,array("trace" => 1, "exceptions" => 0));

$colum = 2;

if (isset($_POST)) {
    $date_start = $_POST['date_start'];
    $date_end = $_POST['date_end'];
    $room_type_id = $_POST['room_type_id'];
    $hotel_id = $_POST['hotel_id'];
    $is_apply_campaign = $_POST['is_apply_campaign'];

    if (empty($date_start) || empty($date_end) || empty($room_type_id) || empty($hotel_id) || empty($is_apply_campaign)) {
        die;
    }
    if (strtotime($date_start) > strtotime($date_end)) {
        die('Ngày bắt đầu không được nhỏ hơn ngày kết thúc');
    }
    if ((int) (strtotime($date_end) - strtotime($date_start)) / 86400 > 30) {
        die('Không được đặt phòng quá 30 ngày');
    }

    $room_type = json_decode($client->getInfoRoomType($hotel_id, $room_type_id), true);
    $arrResult = array();
    $j = 0;
    for ($i = strtotime($date_start); $i <= strtotime($date_end); $i = $i + 86400) {
        $arrayPriceDates = json_decode($client->priceOfDate(date('Y-m-d', $i), $room_type_id, $hotel_id, $is_apply_campaign), true);
        $arrResult[$j] = $arrayPriceDates;
        $arrResult[$j]['date'] = date('d-m-Y', $i);
        $j++;
    }

    if (!empty($arrResult) && count($arrResult) > 0):
        ?>

        <table border="1" width="948" cellspacing="0" cellpadding="5" style="border-collapse:collapse" bordercolor="#ccc">
                <tr>
                    <td align="center" valign="middle" width="25%"><b>Ngày</b></td>
                    <td align="center" valign="middle" width="25%"><b>Giá từng ngày</b></td>
                    <td align="center" valign="middle" width="25%"><b>Ngày</b></td>
                    <td align="center" valign="middle" width="25%"><b>Giá từng ngày</b></td>
                </tr>
                <?php
                $rows = ceil(count($arrResult) / $colum);
                for ($i = 0; $i < $rows; $i++) {
                    ?>
                    <tr width="100%">
                        <?php
                        for ($j = 0; $j < $colum; $j++):
                            $a = $arrResult[$i * $colum + $j];
                            if(!empty($a['date'])):
                            ?>
                            <td valign="top" width="25%"  >
                                <?php echo $a['date'] ?>
                            </td>
                            <td valign="top" width="25%">
                                <?php echo($a['price_end'] . '  ' . $room_type['currency_title'] . '/ 1 phòng '); ?>
                            </td>
                            <?php endif;?>
                        <?php endfor; ?>
                    </tr>
                <?php } ?>
        </table>

    <?php
    endif;
}
?>
