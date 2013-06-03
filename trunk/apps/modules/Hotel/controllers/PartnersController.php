<?php

class HotelPartnersController extends Zone_Action {

    public function indexAction() {

        $hotel_id = get_hotel_id();
        $user_id = get_user_id();

        loadClass('ZList');
        $list = new ZList();

        $list->setVar(array(
        ));
        $list->setPageLink("#Hotel/Partners");
        $list->setSqlCount("
            SELECT COUNT(*) 
            FROM `partners` as `a`
              LEFT JOIN `hotels` as `b`
                ON `a`.`hotel_id` = `b`.`ID`
            ");
        $list->setSqlSelect("
            SELECT `a`.*, `b`.`title` as `hotel_name`
            FROM `partners` as `a`
              LEFT JOIN `hotels` as `b`
                ON `a`.`hotel_id` = `b`.`ID`
            ");

        $list->setWhere("`a`.`hotel_id`='$hotel_id'");

        $list->setOrder('`a`.`title`');

        $list->addFieldText(array(
            '`a`.`title`' => 's'
        ));

        $list->addFieldOrder(array(
            '`a`.`title`' => 'title',
            '`hotel_name`' => 'hotel',
            '`a`.`desc`' => 'desc'
        ));

        $list->run();

        self::set(array(
            posts => $list->getPosts(),
            page => $list->getPage(),
            vars => $list->getVars()
        ));
    }

    public function viewAction() {
        $id = getInt('ID');
        $hotel_id = get_hotel_id();
        
        self::commissionView($id, $hotel_id);        
        self::allotmentView($id, $hotel_id);
        
    }

    private function commissionView($id, $hotel_id) {
        loadClass('ZList');
        $comm = new ZList();

        $comm->setPageLink('#Hotel/Partners/View?ID='.$id);
        $comm->setSqlCount("
        SELECT COUNT(*)    
        FROM `commission` as `a`
          LEFT JOIN `hotels` as `b`
            ON `a`.`hotel_id` = `b`.`ID`
          LEFT JOIN `partners` as `c`
            ON `a`.`partner_id` = `c`.`ID`      
        ");

        $comm->setSqlSelect("
        SELECT 
          `a`.*, 
          `b`.`title` as `hotel_name`,
          `c`.`title` as `partner_name`
        FROM `commission` as `a`
          LEFT JOIN `hotels` as `b`
            ON `a`.`hotel_id` = `b`.`ID`
          LEFT JOIN `partners` as `c`
            ON `a`.`partner_id` = `c`.`ID`            
        ");

        $comm->setWhere("`a`.`partner_id` = '$id'");
        $comm->setWhere("`a`.`hotel_id` = '$hotel_id'");

        $comm->run();

        self::set(array(
            post_comm => $comm->getPosts(),
            page_comm => $comm->getPage(),
            vars_comm => $comm->getVars()
        ));
    }

    private function allotmentView($id, $hotel_id){
        loadClass('ZList');
        $allot = new ZList();

        $allot->setPageLink('#Hotel/Partners/View?ID='.$id);
        $allot->setSqlCount("
        SELECT COUNT(*)    
        FROM `allotments` as `a`   
          LEFT JOIN `hotels` as `b`
            ON `a`.`hotel_id` = `b`.`ID`
          LEFT JOIN `partners` as `c`
            ON `a`.`partner_id` = `c`.`ID`
          LEFT JOIN `hotel_room_types` as `d`
            ON `a`.`room_type_id` = `d`.`ID`
        ");

        $allot->setSqlSelect("
        SELECT 
          `a`.*, 
          `b`.`title` as `hotel_name`,
          `c`.`title` as `partner_name`,
          `d`.`title` as `room_type`
        FROM `allotments` as `a`   
          LEFT JOIN `hotels` as `b`
            ON `a`.`hotel_id` = `b`.`ID`
          LEFT JOIN `partners` as `c`
            ON `a`.`partner_id` = `c`.`ID`
          LEFT JOIN `hotel_room_types` as `d`
            ON `a`.`room_type_id` = `d`.`ID`           
        ");

        $allot->setWhere("`a`.`partner_id` = '$id'");
        $allot->setWhere("`a`.`hotel_id` = '$hotel_id'");

        $allot->run();

        self::set(array(
            post_allot => $allot->getPosts(),
            page_allot => $allot->getPage(),
            vars_allot => $allot->getVars()
        ));        
    }
    
}
