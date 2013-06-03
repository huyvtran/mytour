<?php

class HotelIndexController extends Zone_Action {

    const LOG_TYPE = 'HOTEL';

    protected function fields() {
        $data = array(
            title => array(
                type => 'CHAR',
                no_empty => true,
                label => translate('default.hotel.field.title'),
            ),
            star => array(
                type => 'ENUM',
                value => array('1', '2', '3', '4', '5'),
                default_value => '1',
                label => translate('default.hotel.field.star'),
            ),
            phone => array(
                type => 'PHONE',
                no_empty => true,
                label => translate('default.hotel.field.phone'),
            ),
            phone1 => array(
                type => 'PHONE',
                label => translate('default.hotel.field.phone1'),
            ),
            phone2 => array(
                type => 'PHONE',
                label => translate('default.hotel.field.phone2'),
            ),
            email => array(
                type => 'EMAIL',
                no_empty => true,
                label => translate('default.hotel.field.email'),
            ),
            floor => array(
                type => 'INT',
                label => 'Số tầng'
            ),  
           
            country_id => array(
                type => 'INT',
                no_empty => true,
                label => translate('default.hotel.field.country_id'),
                error_format_message=>'Bạn phải chọn Quốc Gia'
            ),
            state_id => array(
                type => 'INT',
                no_empty => true,
                 error_format_message=>'Bạn phải chọn Tỉnh / Thành Phố'
            ),
             district_id => array(
                type => 'INT',
                no_empty => true,
                label => 'Quận huyện',
                 error_format_message => 'Bạn phải chọn Quận huyện'
            ),
            address => array(
                type => 'CHAR',
                no_empty => true,
                label => translate('default.hotel.field.address'),
            ),
            website => array(
                type => 'CHAR',
                label => translate('default.hotel.field.website'),
            ),
            fax => array(
                type => 'CHAR',
                label => translate('default.hotel.field.fax'),
            ),
            checkin_time => array(
                type => 'TIME',
                no_empty => true,
                label => translate('default.hotel.field.checkintime')
            ),
            checkout_time => array(
                type => 'TIME',
                no_empty => true,
                label => translate('default.hotel.field.checkouttime')
            ),
            type_id => array(
                type => 'INT',
                no_empty => true,
                label => translate('default.hotel.field.type_id')
            ),
            lat => array(
                type => 'CHAR',
                label => 'Kinh độ'
            ),
            lng => array(
                type => 'CHAR',
                label => 'Vĩ độ'
            ),
            desc => array(
                type => 'CHAR',
                label => translate('default.desc')
            ),
//            img_more => array(
//                type => 'CHAR',
//                path => 'files/hotel'
//            ),
            /* img => array(
              type => 'CHAR',
              no_empty => true,
              label => translate('default.hotel.field.img')
              ), */
            airport_transfer => array(
                type => 'ENUM',
                value => array('0', '1'),
                default_value => '0',
                label => 'Có đưa đón sân bay'
            ),
            airport_transfer_fee => array(
                type => 'INT',
                label => 'Phí đưa đón sân bay'
            ),
            airport_transfer_fee_currency_id => array(
                type => 'INT',
                label => 'Loại tiền tệ của phí đưa đón sân bay'
            ),
            breakfast_charge => array(
                type => 'INT',
                label => 'Phí ăn sáng'
            ),
            breakfast_charge_currency_id => array(
                type => 'INT',
                label => 'Loại tiền tệ của phí ăn sáng'
            ),
            check_out => array(
                type => 'TIME',
                label => 'Trả phòng'
            ),
            distance_from_city_center => array(
                type => 'INT',
                label => 'Khoảng cách tới trung tâm thành phố'
            ),
            distance_to_airport => array(
                type => 'INT',
                label => 'Khoảng cách tới sân bay'
            ),
            earliest_check_in => array(
                type => 'TIME',
                label => 'Thời gian nhận phòng sớm nhất'
            ),
            elevator => array(
                type => 'ENUM',
                value => array('0', '1'),
                default_value => '0',
                label => 'Thang máy'
            ),
            internet_usage_fee => array(
                type => 'INT',
                label => 'Phí sử dụng Internet'
            ),
            internet_usage_currency_id => array(
                type => 'INT',
                label => 'Loại tiền tệ của phí sử dụng internet'
            ),
            non_smoking => array(
                type => 'ENUM',
                value => array('0', '1'),
                default_value => '0',
                label => 'Phòng/Tầng không hút thuốc'
            ),
            number_of_bars => array(
                type => 'INT',
                label => 'Số quầy bar'
            ),
            number_of_restaurants => array(
                type => 'INT',
                label => 'Số nhà hàng'
            ),
            number_of_rooms => array(
                type => 'INT',
                label => 'Số lượng phòng'
            ),
            parking => array(
                type => 'ENUM',
                value => array('0', '1'),
                default_value => '0',
                label => 'Có chỗ đậu xe'
            ),
            parking_fee => array(
                type => 'INT',
                label => 'Phí gửi xe'
            ),
            parking_fee_currency_id => array(
                type => 'INT',
                label => 'Loại tiền tệ của phí gửi xe'
            ),
            reception_open_until => array(
                type => 'TIME',
                label => 'Tiếp tân mở cửa đến'
            ),
            room_service => array(
                type => 'ENUM',
                value => array('0', '1', '2'),
                default_value => '0',
                label => 'Dịch vụ phòng'
            ),
            room_voltage => array(
                type => 'INT',
                label => 'Điện thế trong phòng'
            ),
//            time_airport => array(
//                type => 'INT',
//                label => 'Thời gian tới sân bay'
//            ),
            year_hotel_built => array(
                type => 'DATE',
                max => date('Y-m-d'),
                label => 'Năm xây dựng khách sạn'
            ),
            year_hotel_last_renovated => array(
                type => 'DATE',
                max => date('Y-m-d'),
                label => 'Năm nâng cấp khách sạn'
            )
        );
        return $data;
    }

    public function indexAction() {
        $user_id = get_user_id();
        if ( isPost() && getInt('ID')!=0 ) {
            $hotel_id = getInt('ID');
            $hotel = Zone_Base::$Model->fetchRow("SELECT
                * FROM `hotels`
                    WHERE `user_id`='{$user_id}'
                        AND `ID`='{$hotel_id}' AND `is_active`='1'");

            if ( !$hotel ) {
                self::setJSON(array(
                    alert => error('Khách sạn đã chọn chưa được xác nhận')
                ));
            }

            set_hotel($hotel_id);
            self::setJSON(array(
                redirect => "#Hotel/Home",
            ));
        }
        loadClass('ZList');
        $list = new ZList();

        $list->setPageLink('#Hotel');
        $list->setSqlCount("SELECT COUNT(*) FROM `hotels` as `a`");
        $list->setSqlSelect("SELECT
            `a`.*
            FROM `hotels` as `a`");
        $list->setWhere("`a`.`user_id` = '{$user_id}' ");

        $list->setOrder("`a`.`title` DESC");

        $list->addFieldOrder(array(
            '`a`.`title`' => 'title',
            '`a`.`is_active`' => 'is_active',
        ));

        $list->addFieldEqual(array(
            '`a`.`is_active`' => 'is_active',
        ));
        $list->addFieldText(array(
            '`a`.`title`' => 's',
        ));

        $list->run();

        self::set(array(
            posts => $list->getPosts(),
            page => $list->getPage(),
            vars => $list->getVars()
        ));
    }

    public function viewAction() {
        $hotel_id = getInt('ID', 0);
        $user_id = get_user_id();

        $post = self::$Model->fetchRow(
                "SELECT `a`.*,`b`.`title` AS `type_title`,
                                `c`.`title` AS `country_title`,`d`.`title` AS `state_title`,
                                `e`.`title` AS `district_title`,
                                `c1`.`username` as `updated_by_name`,
				`c2`.`username` as `created_by_name`
                         FROM `hotels` AS `a`
                         LEFT JOIN `hotel_types` AS `b`
                            ON `a`.`type_id` = `b`.`ID`
                         LEFT JOIN `locations` AS `c`
                            ON `a`.`country_id` = `c`.`ID`
                         LEFT JOIN `locations` AS `d`
                            ON `a`.`state_id` = `d`.`ID`
                         LEFT JOIN `locations` AS `e`
                            ON `a`.`district_id` = `e`.`ID`

                         LEFT JOIN `users` as `c1`
                            ON `c1`.`ID`=`a`.`updated_by_id`
			LEFT JOIN `users` as `c2`
                            ON `c2`.`ID`=`a`.`created_by_id`

                         WHERE `a`.`ID`='$hotel_id' AND `a`.`user_id` = '{$user_id}'");

        if (!$post) {
            self::setJSON(array(
                content => error(translate('default.edit.post_not_found'))
            ));
        }

        $type_id = self::$Model->fetchAll('SELECT * FROM `hotel_types`');

        $activity_types = self::$Model->fetchAll("SELECT
                `a`.*,
                IF(`b`.`hotel_id`,' checked','') as `checked`
             FROM `hotel_activity_types` as `a`
                LEFT JOIN `hotel_activities` as `b`
                    ON `a`.`ID`=`b`.`service_id` AND `b`.`hotel_id`='$hotel_id'");

        $facility_types = self::$Model->fetchAll("SELECT
                `a`.*,
                IF(`b`.`hotel_id`,' checked','') as `checked`
             FROM `hotel_facility_types` as `a`
                LEFT JOIN `hotel_facilities` as `b`
                    ON `a`.`ID`=`b`.`service_id` AND `b`.`hotel_id`='$hotel_id'");

        $service_types = self::$Model->fetchAll("SELECT
                `a`.*,
                IF(`b`.`hotel_id`,' checked','') as `checked`
             FROM `hotel_service_types` as `a`
                LEFT JOIN `hotel_services` as `b`
                    ON `a`.`ID`=`b`.`service_id` AND `b`.`hotel_id`='$hotel_id'");
        
        $files = self::$Model->fetchAll("SELECT * FROM `hotel_images` WHERE `hotel_id`='$hotel_id'");

        self::set(array(
            airport_transfer_fee_currency_id => self::$Model->fetchOne("
            SELECT `a`.`title` 
            FROM `currencies` as `a`
            LEFT JOIN `hotels` as `b`
            ON `a`.`ID` = `b`.`airport_transfer_fee_currency_id`
            WHERE `b`.`ID`='{$hotel_id}'"),
            breakfast_charge_currency_id => self::$Model->fetchOne("
            SELECT `a`.`title` 
            FROM `currencies` as `a`
            LEFT JOIN `hotels` as `b`
            ON `a`.`ID` = `b`.`breakfast_charge_currency_id`
            WHERE `b`.`ID`='{$hotel_id}'"),
            internet_usage_currency_id => self::$Model->fetchOne("
                    SELECT `a`.`title` 
                        FROM `currencies` as `a`
                        LEFT JOIN `hotels` as `b`
                            ON `a`.`ID` = `b`.`internet_usage_currency_id`
                         WHERE `b`.`ID`='{$hotel_id}'"),
            parking_fee_currency_id => self::$Model->fetchOne("
            SELECT `a`.`title` 
            FROM `currencies` as `a`
            LEFT JOIN `hotels` as `b`
            ON `a`.`ID` = `b`.`parking_fee_currency_id`
            WHERE `b`.`ID`='{$hotel_id}'")
        ));

        self::set(array(
            post => $post,
            type_id => $type_id,
            activity_types => $activity_types,
            facility_types => $facility_types,
            service_types => $service_types,
            files => $files
        ));
    }
    
    public function fileAction(){        
        $file_id = get('ID', 0);
        $user_id = get_user_id();
        
        $file = self::$Model->fetchRow("SELECT * FROM `hotel_images` as `a` WHERE `a`.`ID`='$file_id' LIMIT 1");

        if ( !$file ) {
            return self::setContent(error('File đã bị xóa hoặc không tồn tại'));
        }
        
        $hotel_id = $file['hotel_id'];
        
        $post = self::$Model
                ->fetchRow("SELECT * FROM `hotels` WHERE `ID`='{$hotel_id}'");

        if ( !$post ) {
            return self::setContent(error('File đã bị xóa hoặc không tồn tại'));
        }
        
        if ( $post['created_by_id'] != $user_id) {
            return self::setContent(error('Bạn không có quyền xem file này'));
        }
        
        $file_path = "files/hotel/{$file['name']}";    
        
        if ( !file_exists($file_path) ) {
            return self::setError('404');
        }
        if ( get('type') == 'icon' ) {
            $img_types = array('image/gif', 'image/png', 'image/x-png', 'image/jpeg', 'image/x-jpeg', 'image/jpg');
            if ( in_array($file['type'], $img_types) ) {
                loadClass('PHPThumb');
                $thumb = PhpThumbFactory::create($file_path);
                $thumb->resize(60, 60);
                $thumb->show();
            } else {
                @readfile("files/static/icon.gif");
            }
        } else
        if ( get('type') == 'view' ) {
            $img_types = array('image/gif', 'image/png', 'image/x-png', 'image/jpeg', 'image/x-jpeg', 'image/jpg');
            if ( in_array($file['type'], $img_types) ) {
                loadClass('PHPThumb');
                $thumb = PhpThumbFactory::create($file_path);
                $thumb->show();
            } else {
                @readfile("files/static/icon.gif");
            }
        } else {
            @header("Content-Disposition: attachment; filename={$file['name']}");
            @readfile($file_path);
        }        
        
        
    }

    public function addAction() {
        self::set(array(
            ID => self::$Model->fetchOne("SELECT MAX(ID)+1 FROM `hotels` LIMIT 1")
        ));
        self::getOptions();

        if (isPost()) {
            loadClass('ZData');
            $f = new ZData();
            $f->addfields(self::fields());
            $data = self::checkData($f->getData(), null);

            if (!is_array($data)) {
                self::setJSON(array(
                    alert => error($data)
                ));
            }            
            
            if ($data['year_hotel_built'] > $data['year_hotel_last_renovated']) {
                self::setJSON(array(
                    alert => error('Năm nâng cấp phải lớn hơn năm xây dựng')
                ));
            }

            //files
            $file_id = getInt('img');
            if (isset($file_id)) {
                $files = get_file_upload($file_id, 'hotel');
                $fids[] = $files['ID'];
                remove_file_upload($fids);
            }


            $data['img'] = $files['filename'];
            $data['date_created'] = new Model_Expr('NOW()');
            $data['created_by_id'] = get_user_id();
            $data['user_id'] = get_user_id();
            $data['is_active'] = 0;

            self::$Model->insert('hotels', $data);

            $hotel_id = self::$Model->lastId();            
            $desc = 'Thêm mới khách sạn '.$hotel_id;
            Plugins::logActions(self::LOG_TYPE, $desc);

            $arr_activities = $_POST['activities'];
            $arr_facilities = $_POST['facilities'];
            $arr_services = $_POST['services'];
            if (isset($arr_activities) && count($arr_activities) > 0) {
                foreach ($arr_activities as $value) {
                    $data = array('hotel_id' => $hotel_id, 'service_id' => $value);
                    self::$Model->insert('hotel_activities', $data);
                }
            }
            if (isset($arr_facilities) && count($arr_facilities) > 0) {
                foreach ($arr_facilities as $value) {
                    $data = array('hotel_id' => $hotel_id, 'service_id' => $value);
                    self::$Model->insert('hotel_facilities', $data);
                }
            }
            if (isset($arr_services) && count($arr_services) > 0) {
                foreach ($arr_services as $value) {
                    $data = array('hotel_id' => $hotel_id, 'service_id' => $value);
                    self::$Model->insert('hotel_services', $data);
                }
            }
            
            $files_selected = get('files', array(), true);
            $files = self::$Model->fetchAll("SELECT * FROM `hotel_images`
				WHERE `hotel_id`='$hotel_id'");
            foreach ( $files as $f ) {
                if ( !in_array($f['ID'], $files_selected) ) {
                    self::$Model->delete('hotel_images', "`ID`='{$f['ID']}'");
                    @unlink("files/hotel/{$f['name']}");
                }
            }            

            self::setJSON(array(
                redirect => "#Hotel/Index/View?ID=$hotel_id"
            ));
        }
    }

    public function uploadAction() {
        self::removeLayout();
        $hotel_id = getInt('ID', 0);
        $user_id = get_user_id();

        if (isPost()) {
            loadClass('ZData');
            $f = new ZData();
            $f->addField(array(
                file => array(
                    type => 'IMAGE',
                    path => 'files/hotel'
                )
            ));

            $data = $f->getData();
            if (!is_array($data)) {
                self::setJSON(array(
                    alert => error($data)
                ));
            }
            $file = $data['file'];       
            $file_path = "files/hotel/{$file['0']}"; 
            self::$Model->insert('hotel_images', array(
                hotel_id => $hotel_id,
                name => $file[0],
                type => $file[1],
                size => $file[2],
                file_name => $file[3]                
            ));           

            $file_id = self::$Model->lastId();
            self::setJSON(array(
                content => "
                    <div class='x-file-info'>					
			<a href='" . ctrUrl($this) . "/File?ID=$file_id' target='_blank'>
                               <img src='".BASE_URL."/".$file_path."' style='padding:1px;border:1px solid #bbb;max-width:260px'>
                               <br>
                        <input checked type='checkbox' name='files[]' value='$file_id'/> {$file[3]} {$file[2]} KB	</a>
		    </div>"
            ));
        }
    }
    
    public function uploadmanyAction(){
        self::removeLayout();        
        self::set(array(
            type => self::$Model->fetchAll("SELECT * FROM `hotel_image_types`")
        ));
    }
    
    private function getOptions() {
        self::set(array(
            type_id => self::$Model->fetchAll('SELECT * FROM `hotel_types`'),
            activity_types => self::$Model->fetchAll('SELECT * FROM `hotel_activity_types`'),
            facility_types => self::$Model->fetchAll('SELECT * FROM `hotel_facility_types`'),
            service_types => self::$Model->fetchAll('SELECT * FROM `hotel_service_types`'),
            currencies => self::$Model->fetchAll('SELECT * FROM `currencies`')
        ));
    }

    public function editAction() {
        $hotel_id = getInt('ID', 0);
        $user_id = get_user_id();

        $post = self::$Model->fetchRow("SELECT * FROM `hotels` WHERE `ID`='$hotel_id' AND `user_id` = '{$user_id}'");
        if (!$post) {
            self::setJSON(array(
                content => error(translate('default.edit.post_not_found'))
            ));
        }
        
        self::getOptions();

        $type_id = self::$Model->fetchAll('SELECT * FROM `hotel_types`');

        $activity_types = self::$Model->fetchAll("SELECT
                `a`.*,
                IF(`b`.`hotel_id`,' checked','') as `checked`
             FROM `hotel_activity_types` as `a`
                LEFT JOIN `hotel_activities` as `b`
                    ON `a`.`ID`=`b`.`service_id` AND `b`.`hotel_id`='$hotel_id'");

        $facility_types = self::$Model->fetchAll("SELECT
                `a`.*,
                IF(`b`.`hotel_id`,' checked','') as `checked`
             FROM `hotel_facility_types` as `a`
                LEFT JOIN `hotel_facilities` as `b`
                    ON `a`.`ID`=`b`.`service_id` AND `b`.`hotel_id`='$hotel_id'");

        $service_types = self::$Model->fetchAll("SELECT
                `a`.*,
                IF(`b`.`hotel_id`,' checked','') as `checked`
             FROM `hotel_service_types` as `a`
                LEFT JOIN `hotel_services` as `b`
                    ON `a`.`ID`=`b`.`service_id` AND `b`.`hotel_id`='$hotel_id'");
        
        $files = self::$Model->fetchAll("SELECT * FROM `hotel_images` WHERE `hotel_id`='$hotel_id'");
        
        if ($post['country_id']) {
            $states = self::$Model->fetchAll("SELECT * FROM `locations` 
					WHERE `type`='2' AND `parent_id`='{$post['country_id']}' ORDER BY `title`");
            self::set('states', $states);
        }


        if ($post['state_id']) {
            $state = self::$Model->fetchAll("SELECT * FROM `locations` 
					WHERE `type`='2' AND `ID`='{$post['state_id']}'");
            if ($state) {
                $districts = self::$Model->fetchAll("SELECT * FROM `locations` 
						WHERE `type`='3' AND `parent_id`='{$post['state_id']}' ORDER BY `title`");
                self::set('districts', $districts);
            }
        }
        
        if (isPost()) {
            loadClass('ZData');
            $form = new ZData();
            $form->addField(self::fields($post));
            $data = self::checkData($form->getData(), $hotel_id);
            if (!is_array($data)) {
                self::setJSON(array(
                    alert => error($data)
                ));
            }
            
            if ($data['year_hotel_built'] > $data['year_hotel_last_renovated']) {
                self::setJSON(array(
                    alert => error('Năm nâng cấp phải lớn hơn năm xây dựng')
                ));
            }            

            $file_id = getInt('img', 0);
            if (isset($file_id)) {
                $file = get_file_upload($file_id);
                if ($file) {
                    remove_file_upload($file_id, false);
                    @unlink("files/hotel/{$post['img']}");
                    $data['img'] = $file['name'];
                }
            }
            $data['date_updated'] = new Model_Expr('NOW()');
            $data['updated_by_id'] = get_user_id();
            $data['is_active'] = $post['is_active'];

            self::$Model->update('hotels', $data, "`ID`='$hotel_id'");
            
            // Add to log_actions
            $desc = 'Sửa khách sạn '.$hotel_id;
            Plugins::logActions(self::LOG_TYPE, $desc);

            $arr_activities = $_POST['activities'];
            $arr_facilities = $_POST['facilities'];
            $arr_services = $_POST['services'];

            self::$Model->delete('hotel_activities', "`hotel_id`= '$hotel_id'");
            if (isset($arr_activities) && count($arr_activities) > 0) {
                foreach ($arr_activities as $value) {
                    $data = array('hotel_id' => $hotel_id, 'service_id' => $value);
                    self::$Model->insert('hotel_activities', $data);
                }
            }

            self::$Model->delete('hotel_facilities', "`hotel_id`= '$hotel_id'");
            if (isset($arr_facilities) && count($arr_facilities) > 0) {
                foreach ($arr_facilities as $value) {
                    $data = array('hotel_id' => $hotel_id, 'service_id' => $value);
                    self::$Model->insert('hotel_facilities', $data);
                }
            }

            self::$Model->delete('hotel_services', "`hotel_id`= '$hotel_id'");
            if (isset($arr_services) && count($arr_services) > 0) {
                foreach ($arr_services as $value) {
                    $data = array('hotel_id' => $hotel_id, 'service_id' => $value);
                    self::$Model->insert('hotel_services', $data);
                }
            }
            
            $files_selected = get('files', array(), true);
            $files = self::$Model->fetchAll("SELECT * FROM `hotel_images`
				WHERE `hotel_id`='$hotel_id'");
            foreach ( $files as $f ) {
                if ( !in_array($f['ID'], $files_selected) ) {
                    self::$Model->delete('hotel_images', "`ID`='{$f['ID']}'");
                    @unlink("files/hotel/{$f['name']}");
                }
            }            

            self::setJSON(array(
                redirect => "#Hotel/Index/View?ID=$hotel_id"
            ));
        }


        self::set(array(
            post => $post,
            type_id => $type_id,
            facility_types => $facility_types,
            service_types => $service_types,
            activity_types => $activity_types,
            files => $files
        ));
    }

    public function deleteAction() {
        $ids = getInt('ID', array(), true);

        if (count($ids) > 0) {
            //xoa khach san
            $cond = implode(',', $ids);

            $imgs = self::$Model->fetchAll("SELECT `img`
                FROM `hotels` WHERE `ID` IN($cond)");
            if (isset($imgs) && count($imgs) > 0) {
                foreach ($imgs as $img) {
                    @unlink("files/hotel/{$img['img']}");
                }
            }
            self::$Model->delete('hotels', "`ID` IN ($cond)");            
            foreach ($ids as $id) {
                self::$Model->delete('hotel_services', "`hotel_id` = '{$id}'");
                self::$Model->delete('hotel_activities', "`hotel_id` = '{$id}'");
                self::$Model->delete('hotel_facilities', "`hotel_id` = '{$id}'");
            }

            //xoa loai phong
            $room_types = self::$Model->fetchAll("SELECT *
                 FROM `hotel_room_types` WHERE `hotel_id` IN($cond)");

            if ($room_types && count($room_types) > 0) {
                $img_rooms = array();
                $ids_rooms = array();
                foreach ($room_types as $value) {
                    $img_rooms[] = $value['img'];
                    $ids_rooms[] = $value['ID'];
                }

                if (isset($img_rooms) && count($img_rooms) > 0) {
                    foreach ($img_rooms as $value) {
                        @unlink("files/rooms/{$value['img']}");
                    }
                }
                self::$Model->delete('hotel_room_types', "`hotel_id` IN ($cond)");

                //xoa rang buoc
                if (count($ids_rooms) > 0) {
                    $cond_room_types = implode(',', $ids_rooms);
                    $rule_ids = self::$Model->fetchAll("
                        SELECT `rule_id`
                        FROM `hotel_room_rules`
                        WHERE `room_type_id` IN ($cond_room_types)
                    ");

                    if ($rule_ids && count($rule_ids) > 0) {
                        $arr_rule_ids = array();
                        foreach ($rule_ids as $value) {
                            $arr_rule_ids[] = $value['rule_id'];
                        }
                        $arr_rule_ids = array_unique($arr_rule_ids);
                        $cond_rule_ids = implode(',', $arr_rule_ids);
                        self::$Model->delete('hotel_rules', "`ID` IN ($cond_rule_ids)");
                        self::$Model->delete('hotel_room_rules', "`rule_id` IN ($cond_rule_ids)");
                    }
                }
            }
            //xoa hoa don
            self::$Model->delete('hotel_orders', "`hotel_id` IN ($cond) ");
        }
        
        // Add to log_actions
        $desc = 'Xóa khách sạn '.$cond;        
        Plugins::logActions(self::LOG_TYPE, $desc);
        
        self::setJSON(array(
            redirect => "#Hotel"
        ));
    }

    protected function checkData($data, $hotel_id = NULL) {
        if (!is_array($data)) {
            return $data;
        }
        if ($hotel_id) {
            $hotel_info = self::$Model->fetchAll("SELECT `ID`,`email`,`phone` FROM `hotels` WHERE `ID` != '{$hotel_id}'");
        } else {
            $hotel_info = self::$Model->fetchAll("SELECT `ID`,`email`,`phone` FROM `hotels`");
        }
        $hotel_emails = array();
        $hotel_phones = array();
        if (count($hotel_info) > 0) {
            foreach ($hotel_info as $info) {
                $hotel_emails[] = $info['email'];
                $hotel_phones[] = $info['phone'];
            }
        }
        if (in_array($data['email'], $hotel_emails)) {
            return 'Email khách sạn đã được sử dụng ';
        }
        if (in_array($data['phone'], $hotel_phones)) {
            return 'Số điện thoại khách sạn đã được sử dụng ';
        }
        //check google maps
        if (!empty($data['lat']) && empty($data['lng'])) {
            return 'Vĩ độ không được trống !';
        }
        if (!empty($data['lng']) && empty($data['lat'])) {
            return 'Kinh độ không được trống !';
        }

        return $data;
    }

    /**
     * Select current hotel
     */
    public function chooseAction() {
        
    }

}
