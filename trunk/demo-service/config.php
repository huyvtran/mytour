<?php

define('URL_SERVICE', 'http://sub.mytour.vn/service/hotel?wsdl');
//define('URL_SERVICE', 'http://localhost/Mytour_VG/trunk/service/hotel?wsdl');
//define('URL_SERVICE', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 'https://' : 'http://') . $_SERVER['SERVER_NAME']);

$conn = mysql_connect('localhost', 'root', 'thaydoichinhminh');
mysql_select_db("mytour2", $conn);

function get_customer() {
    $customer_id = get_customer_id();
    $sql = "SELECT * 
                        FROM `customers`
                        WHERE `ID` = '{$customer_id}' and `is_active` = '1'
                        ";
    $query = mysql_query($sql);
    $user = mysql_fetch_assoc($query);
    return $user;
}

function get_customer_id() {
    return 1;
}

?>
