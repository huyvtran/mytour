<?php

class LoginIndexController extends Zone_Action {

    public function indexAction() {
        if (isPost()) {
            if ((int) $_SESSION['warning_login'] > 2) {
                if (!isset($_SESSION['LOGIN']) || $_SESSION['LOGIN'] != $_POST['captcha']) {
                    unset($_SESSION['LOGIN']);
                    return self::set('post_error', "Mã bảo mật không đúng.");
                }
            }

            if (empty($_POST['username'])) {
                return self::set('post_error', "Bạn chưa nhập tên đăng nhập");
            }

            if (empty($_POST['userpwd'])) {
                return self::set('post_error', "Bạn chưa nhập mật khẩu");
            }

            $password = md5(get('userpwd', ''));
            $username = get('username', '');

            //check information from login
            $user = self::$Model
                    ->fetchRow("SELECT * FROM `users`
					WHERE
						`is_deleted`='no'
						AND `password`='$password'
						AND UPPER(`username`) = UPPER('$username') LIMIT 1
                                    ");

            if ($user['is_active'] == 0) {
                return self::set('post_error', "Tài khoản của bạn chưa được kích hoạt !");
            }
            //if success direct to user page
            if ($user) {
                unset($_SESSION['log']);
                //whether or not remmeber login with cookie
                if ($_POST['persistent'] == '1') {
                    save_login($user, $user['username'], $user['password']);
                } else {
                    save_login($user);
                }

                self::$Model->insert('notices', array(
                    user_id => 0,
                    date => new Model_Expr('NOW()'),
                    created_by_id => $user['ID'],
                    title => 'Thông báo từ hệ thống',
                    content => get_user_link($user) . ' vừa đăng nhập',
                    url => '#User/Info?ID=' . $user['ID']
                ));

                self::$Model->update('users', array(
                    date_login => new Model_Expr('NOW()'),
                    ip => $_SERVER['REMOTE_ADDR']
                        ), "`ID`='{$user['ID']}'");

                $_SESSION['warning_login'] = 0;

                if ($url = get('r', NULL)) {
                    redirect($url);
                } else {
                    redirect('/User', true);
                }

                $_SESSION['warning_login'] = 0;
                return true;
            }

            if (!is_numeric($_SESSION['warning_login'])) {
                $_SESSION['warning_login'] = 1;
            } else {
                $c = (int) $_SESSION['warning_login'];
                if ($c < 5) {
                    $_SESSION['warning_login'] = $c + 1;
                }
            }

            //check type of error
            $pass_right = self::$Model->fetchRow("SELECT *
				FROM `users`
				WHERE
					`is_deleted`='no'
					AND `password`='$password'");
            $error = $pass_right ? "Tài khoản không tồn tại hoặc đã bị khóa" : "Sai mật khẩu";
            self::set('post_error', $error);
        }
    }

    public function captchaAction() {
        self::removeLayout();
        loadClass('ZCaptcha');
        $captcha = new ZCaptcha();
        // Change configuration...
        //$captcha->wordsFile = null;           // Disable dictionary words
        //$captcha->wordsFile = 'words/es.txt'; // Enable spanish words
        $captcha->session_var = 'LOGIN'; // Change session variable
        $captcha->CreateImage();
        exit;
    }

}
