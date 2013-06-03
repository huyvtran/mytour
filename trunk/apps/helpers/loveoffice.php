<?php

if ( !function_exists('curl_init') ) {
    throw new Exception('Loveoffice needs the CURL PHP extension.');
}
if ( !function_exists('json_decode') ) {
    throw new Exception('Loveoffice needs the JSON PHP extension.');
}

/**
 *
 * @author Ngô Thành Giang <giangnt1305@gmail.com>
 * @version 1.1.0 - 2012/10/29
 *
 */
class loveoffice {

    const VERSION = '1.0.0';

    public static $DOMAIN_MAP = array(
        'dev' => array(
            //'resource'		=> 'http://dev.loveoffice.vn/apps/resource/',
            'resource' => 'http://localhost/loveoffice/apps/resource/',
            'notify' => 'http://localhost/loveoffice/apps/notification/',
        ),
        'real' => array(
            'resource' => 'http://loveoffice.vn/apps/resource/',
            'notify' => 'http://loveoffice.vn/apps/notification/',
        ),
    );
    protected $appId;
    protected $appSecret;
    protected $accessToken = null;
    protected $sessionId;
    protected $mode = 'real';

    public function __construct( $config ) {
        if ( !session_id() )
            session_start();
        $this->sessionId = session_id();
        $this->setAppId($config['appId']);
        $this->setAppSecret($config['secret']);
        if ( isset($config['mode']) )
            if ( $config['mode'] == 'dev' )
                $this->mode = 'dev';

    }

    public function setAppId( $appId ) {
        $this->appId = $appId;
        return $this;

    }

    public function getAppId() {
        return $this->appId;

    }

    public function setAppSecret( $appSecret ) {
        $this->appSecret = $appSecret;
        return $this;

    }

    public function getAppSecret() {
        return $this->appSecret;

    }

    public function setAccessToken( $access_token ) {
        $this->accessToken = $access_token;
        return $this;

    }

    public function getSessionId() {
        return $this->sessionId;

    }

    /**
     * Get Access token from authorization server
     */
    public function getAccessToken() {
        if ( isset($_GET['access_token']) )
            if ( $_GET['access_token'] ) {
                $_SESSION['lo_access_token'] = $_GET['access_token'];
            }
        return $_SESSION['lo_access_token'];

    }

    /**
     * function sendRequest
     * @param string required, request url
     * @param array optinal, request parameters
     * @return string, request result
     */
    private function sendRequest( $url, $params = null ) {
        $ch = curl_init();
        $access_token = $this->getAccessToken();
        $headers = array();
        array_push($headers, 'Authorization: Bearer ' . $access_token);
        if ( isset($params) )
            foreach ( $params as $key => $value ) {
                array_push($headers, $key . ': ' . $value);
            }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HTTPGET, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;

    }

    /**
     * function getUserInfo()
     * Get User Basic info from Resource server
     * @param none
     * @return array - user's basic info. such name, email address, birthday, friends...
     */
    public function getUserInfo() {
        $url = self::$DOMAIN_MAP[$this->mode]['resource'] . 'userinfo/';
        return $this->sendRequest($url);

    }

    /** @author minhbn <minhcoltech@gmail.com>
     * function addUser()
     * @param string $email
     * @param string $fullname fullname of user
     * @param string $avatar link to a images
     * @param string $birthday (Year-month-day or month/day/Year)
     * @param int $gender (3: unidentified, 0: male, 1: female )
     * @param string $address address of user
     * @param string $phone lenght 10->11
     * @param string $department
     * @param string $position
     */
    function addUser( $email = '', $fullname = '', $avatar = '', $birthday = '', $gender = 2, $address = '', $phone = '', $department = null, $position = null ) {
        $url = self::$DOMAIN_MAP[$this->mode]['resource'] . 'adduser/';
        return $this->sendRequest($url, array(
                    "email" => $email,
                    "fullname" => $fullname,
                    "avatar" => $avatar,
                    "birthday" => $birthday,
                    "gender" => $gender,
                    "address" => $address,
                    "phone" => $phone,
                    "department" => $department,
                    "position" => $position,
                ));

    }

    /** @author minhbn <minhcoltech@gmail.com>
     * function remove user
     * @param interger $userid userid in loveoffice
     */
    function removeUser( $userid = null ) {
        $url = self::$DOMAIN_MAP[$this->mode]['resource'] . 'removeuser/';
        return $this->sendRequest($url, array(
                    "userid" => $userid,
                ));

    }

    /**
     * function addNotification()
     * add a new notification to LoveOffice
     * @param string, notification message
     * @param integer, number of notifications
     * @param string, 'passive' OR 'active'
     * @param string, 'redirect' OR 'url'
     * @param string, fetch url for notifications
     * @param integer, height of notifications in pixel
     * @param string, update url for 'active' notify mode
     * @return string
     */
    public function addNotification( $message = '', $news_count = '0', $notify_mode = 'passive', $notify_type = 'redirect', $fetch_url = '', $redirect_url = '', $fetch_height = 300, $update_url = '' ) {
        $url = self::$DOMAIN_MAP[$this->mode]['notify'] . 'add/';
        return $this->sendRequest($url, array('Message' => $message, 'News_count' => $news_count, 'Notify_mode' => $notify_mode, 'Notify_type' => $notify_type, 'Fetch_url' => $fetch_url, 'Redirect_url' => $redirect_url, 'Fetch_height' => $fetch_height, 'Update_url' => $update_url));

    }

}

/**
 * LoveOffice Connect
 * @author GiangNT - giangnt1305@gmail.com
 * @version 1.0.0
 * @date 2012/09/27
 */
class LOconnect {

    public static $DOMAIN_MAP = array(
        'dev' => array(
            'oauth' => 'http://dev.loveoffice.vn/apps/oauth/',
        ),
        'real' => array(
            'oauth' => 'http://loveoffice.vn/apps/oauth/',
        ),
    );
    protected $mode = 'real';
    protected $appId;
    protected $appSecret;
    protected $accessToken = null;
    protected $sessionId;
    protected $style = 'style.css';

    public function __construct( $config ) {
        if ( isset($config['appId']) )
            $this->appId = $config['appId'];
        if ( isset($config['secret']) )
            $this->appSecret = $config['secret'];
        if ( isset($config['mode']) )
            if ( $config['mode'] == 'dev' )
                $this->mode = 'dev';
        if ( isset($config['style']) )
            $this->style = 'style.css';

        include($this->style);

    }

    /**
     * function loginURL()
     * @param none
     * @return string - login url for LoveOffice connect
     *
     */
    public function getLoginURL() {
        return "javascript:void window.open('" . self::$DOMAIN_MAP[$this->mode]['oauth'] . "?client_id=" . $this->appId . "&client_secret=" . $this->appSecret . "','_blank','resizable=no,scrollbars=no,menubar=no,toolbar=no,location=no,height=530,width=990');";

    }

    /**
     * function loginButton()
     * @param string - button's width in pixels, default is 'auto'
     * @param string - button's height in pixels, default is '24'
     * @param string - button's text, default is 'Đăng nhập qua LoveOffice'
     * @return string - HTML code for login button
     */
    public function getLoginButton( $width = 'auto', $height = '24', $text = 'Đăng nhập qua LoveOffice' ) {
        return '<a href="' . $this->loginURL() . '" class="lo-connect-btn" style="width=' . $width . 'px; height=' . $height . 'px;">' . $text . '</a>';

    }

}
