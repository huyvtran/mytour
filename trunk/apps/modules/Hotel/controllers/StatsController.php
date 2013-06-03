<?php

class HotelStatsController extends Zone_Action {

    public function indexAction() {
        $mode = get('mode');
        $filter = get('filter');
        $hotel_id = get_hotel_id();
        $room_type = get('room_type');

        $hotel_name = self::$Model->fetchOne("SELECT `title` FROM `hotels` WHERE `ID` = '$hotel_id'");
        self::set('room_type', self::$Model->fetchAll("SELECT `ID`, `title` FROM `hotel_room_types` WHERE `hotel_id` = '$hotel_id'"));

        $where = " WHERE `a`.`status` <> '2' 
                    AND `a`.`is_last` = '1' 
                    AND `a`.`hotel_id` = $hotel_id ";

        if ($room_type == 0) {
            $and = "AND `a`.`hotel_id` = '$hotel_id'";
            $and2 = '';
            self::set('room_title', 'tất cả loại phòng');
        } else {
            $and = "AND `a`.`hotel_id` = '$hotel_id'";
            $and2 = "AND `a`.`room_type_id` = '$room_type'";
            self::set('room_title', self::$Model->fetchOne("SELECT `title` FROM `hotel_room_types` WHERE `ID` = '$room_type'"));
        }

        // Statistic according to day
        if ($filter == 'day') {

            $date_from = change_date_format(getDateRq('date_from', date('d/m/Y')));
            $date_to = change_date_format(getDateRq('date_to'));

            if (!$date_to) {
                $date_to = $date_from;
            }

            if ($date_from > $date_to) {
                self::setJSON(array(
                    alert => error('Ngày bắt đầu lớn hơn ngày kết thúc'),
                    close => true
                ));
            }

            $diffdate = date_diff_day($date_from, $date_to);

            if ($diffdate > 31) {
                self::setJSON(array(
                    alert => error('Tổng số ngày bắt đầu và kết thúc không lớn hơn 31')
                ));
            }

            // According to form
            if ($mode == 'form') {
                $post = self::$Model->fetchAll("
                SELECT 
                  COUNT(`a`.`ID`) as `number_of_form`, 
                  DATE(`a`.`date_updated`) as `of_date` 
                FROM `hotel_orders` as `a`
                $where
                  AND (DATE(`a`.`date_updated`) BETWEEN '$date_from' AND '$date_to')
                GROUP BY DATE(`a`.`date_updated`)
                ");

                // According to gender    
            } else
            if ($mode == 'gender') {
                $post = self::$Model->fetchAll("
               SELECT 
                 `a`.*,
                 `b`.`gender` as `gender`                 
               FROM `hotel_orders` as `a`
               LEFT JOIN `customers` as `b`
               ON `a`.`customer_id` = `b`.`ID`
               $where
                 AND ( DATE(`a`.`date_updated`) BETWEEN '$date_from' AND '$date_to')               
               ");

                // According to age    
            } else
            if ($mode == 'age') {

                $post = self::$Model->fetchAll("
               SELECT 
                 `a`.*,                 
                 `b`.`age` as `age`
               FROM `hotel_orders` as `a`
               LEFT JOIN `customers` as `b`
               ON `a`.`customer_id` = `b`.`ID`
               $where
                 AND ( DATE(`a`.`date_updated`) BETWEEN '$date_from' AND '$date_to')                             
               ");

                // According to children    
            } else
            if ($mode == 'children') {

                $post = self::$Model->fetchAll("
                    SELECT 
                      COUNT(`a`.`ID`) as `total`,
                      DATE(`a`.`date_updated`) as `of_date`
                    FROM `hotel_orders` as `a`
                    $where
                        AND `a`.`has_children` = '1'
                        AND ( DATE(`a`.`date_updated`) BETWEEN '$date_from' AND '$date_to') 
                    GROUP BY DATE(`a`.`date_updated`)        
                ");

                // According to book / view    
            } else
            if ($mode == 'bpv') {

                $post = self::$Model->fetchAll("
                    SELECT 
                      `a`.`ID`
                    FROM `hotel_orders` as `a`
                    $where
                      AND ( DATE(`a`.`date_updated`) BETWEEN '$date_from' AND '$date_to')
                      $and2
                ");

                $view = self::$Model->fetchAll("
                    SELECT
                      `a`.`ID`
                    FROM `view_stats` as `a`
                    WHERE ( DATE(`a`.`time`) BETWEEN '$date_from' AND '$date_to')     
                      $and
                      $and2
                ");
            }

            self::set(array(
                date_from => getDateRq('date_from', date('d/m/Y')),
                date_to => getDateRq('date_to'),
            ));


            // Statistic according to month
        } else
        if ($filter == 'month') {

            $date_from = get('date_from');
            $date_to = get('date_to');

            if (!$date_to) {
                $date_to = $date_from;
            }

            $date_from = explode('/', $date_from);
            $date_to = explode('/', $date_to);

            if ($date_from[1] !== $date_to[1]) {
                self::setJSON(array(
                    alert => error("Chỉ thống kế những tháng trong cùng một năm"),
                ));
            }

            $date_f = $date_from[1] . '-' . $date_from[0];
            $date_t = $date_to[1] . '-' . $date_to[0];           

            // According to form
            if ($mode == 'form') {

                $post = self::$Model->fetchAll("
                SELECT 
                COUNT( `a`.`ID` ) as `number_of_form`, 
                DATE_FORMAT( `a`.`date_updated`, '%Y-%m' ) as `of_date`
                FROM `hotel_orders` AS `a`
                  $where
                  AND
                    DATE_FORMAT( `a`.`date_updated`, '%Y-%m' )
                    BETWEEN '$date_f'
                    AND '$date_t'               
                GROUP BY DATE_FORMAT( `a`.`date_updated`, '%Y-%m' )
                ");

                // According to gender    
            } else
            if ($mode == 'gender') {
                $post = self::$Model->fetchAll("
               SELECT 
                 `a`.*,
                 `b`.`gender` as `gender`,
                 `b`.`age` as `age`
               FROM `hotel_orders` as `a`
               LEFT JOIN `customers` as `b`
               ON `a`.`customer_id` = `b`.`ID`
               $where
                 AND (DATE_FORMAT( `a`.`date_updated`, '%Y-%m' ) BETWEEN '$date_f' AND '$date_t')               
               ");

                // According to age
            } else
            if ($mode == 'age') {
                $post = self::$Model->fetchAll("
               SELECT 
                 `a`.*,                 
                 `b`.`age` as `age`
               FROM `hotel_orders` as `a`
               LEFT JOIN `customers` as `b`
               ON `a`.`customer_id` = `b`.`ID`
               $where
                 AND (DATE_FORMAT( `a`.`date_updated`, '%Y-%m' ) BETWEEN '$date_f' AND '$date_t')  "
                );

                // According to children    
            } else
            if ($mode == 'children') {

                $post = self::$Model->fetchAll("
                    SELECT 
                      COUNT(`a`.`ID`) as `total`,
                      DATE_FORMAT( `a`.`date_updated`, '%Y-%m' ) as `of_date`
                    FROM `hotel_orders` as `a`
                    $where
                        AND `a`.`has_children` = '1'
                        AND (DATE_FORMAT( `a`.`date_updated`, '%Y-%m' ) BETWEEN '$date_f' AND '$date_t')
                    GROUP BY DATE_FORMAT( `a`.`date_updated`, '%Y-%m' )      
                ");

                // According to book / view    
            } else
            if ($mode == 'bpv') {

                $post = self::$Model->fetchAll("
                    SELECT 
                      `a`.`ID`
                    FROM `hotel_orders` as `a`
                    $where                                                                         
                      AND (DATE_FORMAT( `a`.`date_updated`, '%Y-%m' ) BETWEEN '$date_f' AND '$date_t') 
                      $and2    
                ");

                $view = self::$Model->fetchAll("
                    SELECT
                      `a`.`ID`
                    FROM `view_stats` as `a`
                    WHERE (DATE_FORMAT( `a`.`time`, '%Y-%m' ) BETWEEN '$date_f' AND '$date_t')                      
                      $and
                      $and2
                ");
            }
            
            self::set(array(
              date_from => $date_f,
              date_to => $date_t        
            ));            

            // Statistic according to year
        } else
        if ($filter == 'year') {

            $date_from = get('date_from');
            $date_to = get('date_to');

            if (!$date_to) {
                $date_to = $date_from;
            }

            if ($date_from > $date_to) {
                self::setJSON(array(
                    alert => error("Năm bắt đầu không được lớn hơn năm kết thúc"),
                ));
            }

            // According to form
            if ($mode == 'form') {

                $post = self::$Model->fetchAll("
                SELECT 
                COUNT( `a`.`ID` ) as `number_of_form`, 
                YEAR( `a`.`date_updated` ) as `of_date`
                FROM `hotel_orders` AS `a`
                  $where
                  AND
                    DATE_FORMAT( `a`.`date_updated`, '%Y' )
                    BETWEEN '$date_from'
                    AND '$date_to'               
                GROUP BY DATE_FORMAT( `a`.`date_updated`, '%Y' )
                ");

                // According to gender      
            } else
            if ($mode == 'gender') {
                $post = self::$Model->fetchAll("
               SELECT 
                 `a`.*,
                 `b`.`gender` as `gender`,
                 `b`.`age` as `age`
               FROM `hotel_orders` as `a`
               LEFT JOIN `customers` as `b`
               ON `a`.`customer_id` = `b`.`ID`
               $where
                 AND (DATE_FORMAT( `a`.`date_updated`, '%Y' ) BETWEEN '$date_from' AND '$date_to')               
               ");

                // According to age    
            } else
            if ($mode == 'age') {
                $post = self::$Model->fetchAll("
               SELECT 
                 `a`.*,                 
                 `b`.`age` as `age`
               FROM `hotel_orders` as `a`
               LEFT JOIN `customers` as `b`
               ON `a`.`customer_id` = `b`.`ID`
               $where
                 AND ( DATE_FORMAT(`a`.`date_updated`, '%Y') BETWEEN '$date_from' AND '$date_to')               
               ");

                // According to children    
            } else
            if ($mode == 'children') {

                $post = self::$Model->fetchAll("
                    SELECT 
                      COUNT(`a`.`ID`) as `total`,
                      DATE(`a`.`date_updated`) as `of_date`
                    FROM `hotel_orders` as `a`
                    $where
                        AND `a`.`has_children` = '1'
                        AND ( DATE_FORMAT(`a`.`date_updated`, '%Y') BETWEEN '$date_from' AND '$date_to')               
                    GROUP BY DATE_FORMAT( `a`.`date_updated`, '%Y' )      
                ");

                // According to book / view    
            } else
            if ($mode == 'bpv') {

                $post = self::$Model->fetchAll("
                    SELECT 
                      `a`.`ID`
                    FROM `hotel_orders` as `a`
                    $where                                                                         
                      AND (DATE_FORMAT( `a`.`date_updated`, '%Y' ) BETWEEN '$date_from' AND '$date_to') 
                      $and2    
                ");

                $view = self::$Model->fetchAll("
                    SELECT
                      `a`.`ID`
                    FROM `view_stats` as `a`
                    WHERE (DATE_FORMAT( `a`.`time`, '%Y' ) BETWEEN '$date_from' AND '$date_to')                      
                      $and
                      $and2
                ");
            }
            
            self::set(array(
              date_from => $date_from,
              date_to => $date_to        
            ));             

            // Statistic according to quarter    
        } else
        if ($filter == 'quarter') {
            $q_from = get('date_from');
            $q_to = get('date_to');
            $year = get('year');

            if (!$q_to) {
                $q_to = $q_from;
            }

            if ($q_from > $q_to) {
                self::setJSON(array(
                    alert => error('Quý đầu lớn hơn quý cuối')
                ));
            }

            if ($mode == 'form') {
                $post = self::$Model->fetchAll("
                SELECT 
                COUNT( `a`.`ID` ) as `number_of_form`, 
                QUARTER( `a`.`date_updated` ) as `of_date`
                FROM `hotel_orders` AS `a`
                  $where
                  AND
                    QUARTER( `a`.`date_updated` )
                    BETWEEN '$q_from'
                    AND '$q_to'
                  AND YEAR(`a`.`date_updated`) = '$year'
                GROUP BY QUARTER( `a`.`date_updated` )
                ");
            } else
            if ($mode == 'gender') {
                $post = self::$Model->fetchAll("
               SELECT 
                 `a`.*,
                 `b`.`gender` as `gender`,
                 `b`.`age` as `age`
               FROM `hotel_orders` as `a`
               LEFT JOIN `customers` as `b`
               ON `a`.`customer_id` = `b`.`ID`
               $where
                 AND (QUARTER( `a`.`date_updated` ) BETWEEN '$q_from' AND '$q_to') 
                     AND YEAR(`a`.`date_updated`) = '$year'
               ");

                // According to age    
            } else
            if ($mode == 'age') {
                $post = self::$Model->fetchAll("
               SELECT 
                 `a`.*,                 
                 `b`.`age` as `age`
               FROM `hotel_orders` as `a`
               LEFT JOIN `customers` as `b`
               ON `a`.`customer_id` = `b`.`ID`
               $where
                 AND (QUARTER( `a`.`date_updated` ) BETWEEN '$q_from' AND '$q_to') 
                     AND YEAR(`a`.`date_updated`) = '$year'
               ");

                // According to children    
            } else
            if ($mode == 'children') {

                $post = self::$Model->fetchAll("
                    SELECT 
                      COUNT(`a`.`ID`) as `total`,
                      QUARTER(`a`.`date_updated`) as `of_date`
                    FROM `hotel_orders` as `a`
                    $where
                        AND `a`.`has_children` = '1'
                        AND (QUARTER( `a`.`date_updated` ) BETWEEN '$q_from' AND '$q_to') 
                        AND YEAR(`a`.`date_updated`) = '$year'                        
                    GROUP BY QUARTER( `a`.`date_updated` )      
                ");

                // According to book / view    
            } else
            if ($mode == 'bpv') {

                $post = self::$Model->fetchAll("
                    SELECT 
                      `a`.`ID`
                    FROM `hotel_orders` as `a`
                    $where                                                                         
                      AND (QUARTER( `a`.`date_updated` ) BETWEEN '$q_from' AND '$q_to') 
                      AND YEAR(`a`.`date_updated`) = '$year'                             
                      $and2    
                ");

                $view = self::$Model->fetchAll("
                    SELECT
                      `a`.`ID`
                    FROM `view_stats` as `a`
                    WHERE (QUARTER( `a`.`time` ) BETWEEN '$q_from' AND '$q_to')
                      AND YEAR(`a`.`time`) = '$year'   
                      $and
                      $and2
                ");
            }
            
            self::set(array(
              date_from => $q_from,
              date_to => $q_to        
            ));             
            
        }

        self::set(array(
            post => $post,
            name => $hotel_name
        ));

        if ($view) {
            self::set('view', $view);
        }
    }

}
