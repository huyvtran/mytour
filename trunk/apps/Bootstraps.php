<?php

class Bootstraps extends Zone_Bootstraps {

    public function init() {

        self::addJSON(array(
            title => SITE_TITLE
        ));

        //No need check with cronjob & service
        if ( in_array(self::getModuleName(), array('Cron', 'Service')) ) {
            return true;
        }

        /* Use json script */
        $is_json = isset($_REQUEST['_json']);
        if ( $is_json ) {
            //header('Cache-Control: no-cache, must-revalidate');
            //header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            //header('Content-type: application/json');
            self::setLayoutJSON();
        }

        if ( self::getModuleName() == 'Logout' ) {
            return true;
        }

        if ( self::getModuleName() == 'Register' ) {
            return true;
        }

        if ( !is_login() ) {
            if ( is_remember_login() ) {
                list($username, $password) = get_remember_login();
                $username = stripslashes($username);
                $password = stripslashes($password);

                $user = self::$Model->fetchRow("SELECT
						IF(`a`.`inherit_roles`='yes',`b`.`roles`,`a`.`roles`) as `role`,
						`a`.`ID`,
						`a`.`username`,
						`a`.`settings`
					FROM `users` as `a`
					LEFT JOIN `groups` as `b`
						ON `b`.`ID`=`a`.`group_id`
					WHERE `a`.`is_deleted`='no'
						AND UPPER(`a`.`username`) = UPPER('$username')
						AND `a`.`password`='$password'
                                                AND `a`.`is_active` = '1'
                                        " );
                if ( $user ) {
                    unset($_SESSION['log']);
                    self::$Model->update('users', array(
                        date_login => new Model_Expr('NOW()'),
                        ip => $_SERVER['REMOTE_ADDR']
                            ), "`ID`='{$user['ID']}'");

                    $this->setConfigAndRole($user);
                    save_login($user, $username, $password);
                    if ( self::getModuleName() == 'Login' ) {
                        redirect('/User', true);
                    }
                    return true;
                }
            }

            if ( get('accessed_by') == 'loveoffice' ) {
                $loveOffice = new loveoffice(array(
                            'appId' => '7d8d1f0c761ba80',
                            'secret' => '95deb3337846f6b8bb58f0d3bc614993'
                        ));
                $info = $loveOffice->getUserInfo();
                try {
                    $info = json_decode($info, true);
                } catch (Exception $e) {
                    die('Server is busy');
                    //what the hell
                }

                $email = $info['emailaddress'];

                $user = self::$Model->fetchRow("SELECT
						IF(`a`.`inherit_roles`='yes',`b`.`roles`,`a`.`roles`) as `role`,
						`a`.`ID`,
						`a`.`username`,
						`a`.`settings`
					FROM `users` as `a`
					LEFT JOIN `groups` as `b`
						ON `b`.`ID`=`a`.`group_id`
					WHERE `a`.`is_deleted`='no'
						AND `a`.`email`='$email'");

                if ( $user ) {
                    unset($_SESSION['log']);
                    self::$Model->update('users', array(
                        date_login => new Model_Expr('NOW()'),
                        ip => $_SERVER['REMOTE_ADDR']
                            ), "`ID`='{$user['ID']}'");

                    $this->setConfigAndRole($user);
                    $username = $user['username'];
                    $password = $user['password'];
                    save_login($user, $username, $password);
                    if ( self::getModuleName() == 'Login' ) {
                        redirect('/User', true);
                    }
                    return true;
                }
            }

            if ( self::getModuleName() != 'Login' ) {
                //only direct when in main action
                //if ($this->env ) {
                if ( $is_json ) {
                    self::setJSON(array(
                        error_login => true,
                        error_code => 401
                    ));
                    return $this->stop();
                } else {
                    redirect('/Login', true);
                }
                //}
            }
        } else {
            $user_id = get_user_id();

            $user = self::$Model->fetchRow("SELECT
					`a`.`ID`,
					`a`.`username`,
					`a`.`settings`,
					IF(`a`.`inherit_roles`='yes',`b`.`roles`,`a`.`roles`) as `role`
				FROM `users` as `a`
				LEFT JOIN `groups` as `b`
					ON `b`.`ID`=`a`.`group_id`
			WHERE `a`.`is_deleted`='no' AND `a`.`ID`='$user_id'
                             AND `a`.`is_active` = '1'
                    ");

            if ( !$user ) {
                clear_login();
                redirect('/Login', true);
                return false;
            }

            //log
            if ( !isset($_SESSION['log']) ) {
                //no for bot or cron
                if ( !preg_match("#Wget#is", $_SERVER['HTTP_USER_AGENT']) ) {
                    Plugins::log();
                    $_SESSION['log'] = 1;
                }
            }

            $this->setConfigAndRole($user);

            if ( self::getModuleName() == 'Login' ) {
                redirect('/User', true);
            }
        }

    }

    protected function setConfigAndRole( $user ) {
        self::setConfig(array(
            user => get_query_configs($user['settings'])
        ));

        self::setRole(array_map('trim', explode(',', $user['role'])));

    }

}
