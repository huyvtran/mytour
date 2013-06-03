<?php

class RegisterIndexController extends Zone_Action {

    public function fields() {
        $data = array(
            username => array(
                type => 'CHAR',
                no_empty => true,
                min_length => 4,
                max_length => 30,
                label => 'Tên tài khoản',
            ),
            password => array(
                type => 'PASSWORD',
                no_empty => true,
                min_length => 4,
                max_length => 32,
                label => 'Mật khẩu',
            ),
            firstname => array(
                type => 'CHAR',
                no_empty => true,
                label => 'Họ',
            ),
            lastname => array(
                type => 'CHAR',
                no_empty => true,
                label => 'Tên',
            ),
            email => array(
                type => 'EMAIL',
                no_empty => true,
                label => 'Email',
            ),
            location_id => array(
                type => 'INT',
                no_empty => true,
                label => 'Quốc gia'
            ),
            city => array(
                type => 'INT',
                no_empty => true,
                label => 'Tỉnh/Thành Phố'
            ),
        );
        return $data;
    }

    public function indexAction() {
        if (is_login()) {
            redirect('/User', true);
        } else {
            if (isPost()) {
                self::set('posts', $_POST);
                loadClass('ZData');
                $f = new ZData();
                $f->addField(self::fields());
                $data = $f->getData();

                $str_check = '';
                $username = $_POST['username'];
                $user_check_username = self::$Model->fetchRow("SELECT * FROM `users`  WHERE (`username`) = UPPER('$username') LIMIT 1");
                if ($user_check_username) {
                    $str_check .= "<div>+) Tên tài khoản đã tồn tại!</div>";
                }
                /* $email       = $_POST['email'];
                  $user_check_email    = self::$Model->fetchRow("SELECT * FROM `users`  WHERE (`email`) = UPPER('$email') LIMIT 1");
                  if($user_check_email){
                  $str_check .= "<div>+) Email đã tồn tại!</div>";
                  } */
                if (!is_array($data)) {
                    $data .= $str_check;
                    self::set('errors', $data);
                } else {
                    if ($str_check != '') {
                        $data = $str_check;
                        self::set('errors', $data);
                    } else {
                        $arrParams = array(
                            username => $data['username'],
                            password => $data['password'],
                            fullname => $data['firstname'] . ' ' . $data['lastname'],
                            email => $data['email'],
                            city => (int) $data['city'],
                            location_id => (int) $data['location_id'],
                            date_created => new Model_Expr('NOW()'),
                        );
                        self::$Model->insert("users", $arrParams);
                        self::removeLayout();
                        self::setContent(success(
                                'Bạn đã đăng ký thành công, xin đợi được xác nhận !'
                        ));
                    }
                }
            }
        }
    }
}
