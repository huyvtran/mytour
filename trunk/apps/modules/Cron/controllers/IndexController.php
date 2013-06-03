<?php

/**
 * Description of IndexController
 *
 * @author Nguyen Duc Minh
 * @date created Jul 12, 2012
 */
class CronIndexController extends Zone_Action {

    public function indexAction() {
        self::removeLayout();
        $time_start = time();

        //check email
        $config = self::$Model->fetchRow("SELECT * FROM `configs` LIMIT 1");

        if ( !empty($config['email_host'])
                && !empty($config['email_account'])
                && !empty($config['email_password'])
        ) {

            //send 300 email every minute
            $emails = self::$Model->fetchAll("SELECT * FROM `cron_emails` ORDER BY `date_send` LIMIT 50");
            foreach ( $emails as $a ) {
                $send = send_mail_smpt($config['email_host'], $config['email_account'], $config['email_name'], $a['email_to'], $a['name_to'], $a['subject'], $a['body'], array(
                    password => $config['email_password']
                        ));

                //if ( $send ) {
                    self::$Model->delete("cron_emails", "`ID`='{$a['ID']}'");
               // }else{
               ///     self::$Model->update("cron_emails",array(
                //        date_send => new Model_Expr('NOW()')
                 //   ), "`ID`='{$a['ID']}'");
               // }
            }
        }
        self::setContent('finish in ' . (time() - $time_start) . ' seconds');

    }

}

?>
