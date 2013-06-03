<?php

class HotelHomeController extends Zone_Action {

    public function indexAction() {

        $hotel_id = get_hotel_id();
        
        // Show left info
        $post = self::$Model->fetchRow("SELECT * FROM `hotels` WHERE `ID` = '$hotel_id'");
        
        $district = self::$Model->fetchOne("SELECT `title` FROM `locations` WHERE `ID`='{$post['district_id']}'");
        $state = self::$Model->fetchOne("SELECT `title` FROM `locations` WHERE `ID`='{$post['state_id']}'");
        $country = self::$Model->fetchOne("SELECT `title` FROM `locations` WHERE `ID`='{$post['country_id']}'");
        
        $arrAddress = array();
        $arrPassed = array($post['address'], $district, $state, $country);
        array_push($arrAddress, $arrPassed);
        
        $strAddress = implode(',', $arrPassed);       

        // Show the right stats graph   
        
        $date_to = date('Y-m-d', time());               
        $sub_date = strtotime('-1 month', strtotime($date_to));
        $date_from = date('Y-m-d', $sub_date);
        
        $where = " WHERE `a`.`status` <> '2' 
                    AND `a`.`is_last` = '1' 
                    AND `a`.`hotel_id` = $hotel_id ";        
        
        $stats_form = self::$Model->fetchAll("
        SELECT 
          COUNT(`a`.`ID`) as `total`,
          DATE(`a`.`date_updated`) as `of_date`
        FROM `hotel_orders` as `a`
        $where 
          AND DATE(`date_updated`) BETWEEN '$date_from' AND '$date_to'
        GROUP BY DATE(`a`.`date_updated`) ASC
        ");
        
//        echo "<pre>";
//        print_r($stats_form);
//        die();        
        
        $stats_age = self::$Model->fetchAll("
        SELECT 
          `b`.`age` as `age`,
          DATE(`a`.`date_updated`) as `of_date`
        FROM `hotel_orders` as `a`
          LEFT JOIN `customers` as `b`
            ON `a`.`customer_id` = `b`.`ID`         
        $where
          AND DATE(`a`.`date_updated`) BETWEEN '$date_from' AND '$date_to'
        GROUP BY DATE(`a`.`date_updated`) ASC
        ");
        
//        echo "<pre>";
//        print_r($stats_age);
//        die();
        
        $total_book = self::$Model->fetchAll("
        SELECT 
          `a`.`ID` as `book`
        FROM `hotel_orders` as `a`
        $where 
          AND DATE(`a`.`date_updated`) BETWEEN '$date_from' AND '$date_to'
        ");
        
        $total_view = self::$Model->fetchAll("
        SELECT 
          `a`.`ID` as `view`       
        FROM `view_stats` as `a`
        WHERE DATE(`a`.`time`) BETWEEN '$date_from' AND '$date_to'
          AND `a`.`hotel_id` = '$hotel_id'
        ");
        
//        echo "<pre>";
//        print_r($total_view);
//        die();
        
        self::set(array(            
            post => $post,
            address => $strAddress,
            stats_form => $stats_form,
            stats_age => $stats_age,
            book => $total_book,
            view => $total_view
        ));
    }

}
