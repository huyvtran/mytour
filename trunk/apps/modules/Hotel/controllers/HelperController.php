<?php

class HotelHelperController extends Zone_Action {

    public function autolocalAction() {
        $id = getInt('parent_id');
        $locals = self::$Model->fetchAll("SELECT * 
			FROM `locations` WHERE `parent_id`='$id'");
        die(json_encode($locals));
    }

}

