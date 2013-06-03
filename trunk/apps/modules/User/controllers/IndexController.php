<?php
class UserIndexController extends Zone_Action {

    public function init() {
        $configs = self::$Model->fetchRow("SELECT * FROM `configs` LIMIT 1");

        $user_id = get_user_id();
        $user = self::$Model->fetchRow("SELECT * FROM `users` WHERE `ID`='$user_id'");

        self::set(array(
            configs => $configs,
            user => $user
        ));

        $warning = NULL;

        foreach (array('123', '1234', '12345', '123456', 'abcdef', '1234567', '12345678', '123456789', 'iloveyou', '123abc') as $s) {
            if (md5($s) == $user['password']) {
                $warning = "<b>!!!Cảnh báo:</b> mật khẩu của bạn quá yếu, hãy thay đổi ngay";
                break;
            }
        }

        self::set(array(
            warning => $warning
        ));
    }

    public function indexAction() {}

}

?>