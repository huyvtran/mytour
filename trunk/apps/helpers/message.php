<?php

/**
 * @name message
 * @author Nguyen Duc Minh
 * @date Sep 23, 2012
 */
function send_message( $subject, $body, $users = null, $sign = "<br/><div class='signature'><i><small><u>Chú ý:</u> Đây là tin nhắn tự động từ hệ thống, bạn không thể trả lời tin nhắn này</small></i></div>" ) {
    if ( func_num_args() == 2 ) {
        $users = array(get_user());
    }

    if ( !is_array($users) || count($users) == 0 )
        return false;

    $data = array(
        is_draft => 0,
        subject => $subject,
        body => $body . $sign,
        date_created => date('Y-m-d H:i:s'),
        date => date('Y-m-d H:i:s'),
        user_id => 0
    );

    $bool = Zone_Base::$Model->insert('user_messages', $data);
    if ( !$bool ) {
        return false;
    }

    $message_id = Zone_Base::$Model->lastId();

    $informs = array();
    $maps = array();
    $last_date = date('Y-m-d H:i:s');
    $last_subject = strip_tags($data['subject']);
    $last_body = strip_tags($data['body']);

    foreach ( $users as $u ) {
        $maps[] = array(
            user_id => $u['ID'],
            message_id => $message_id,
            root_id => $message_id,
            is_read => 0
        );

        $inform = array(
            type => 'app',
            user_id => $u['ID'],
            root_id => $message_id,
            last_message_id => $message_id,
            is_receiver => 1,
            is_read => 0,
            last_id => 0,
            last_date => $last_date,
            last_subject => $last_subject,
            last_body => $last_body,
            num => 1
        );
        $informs[] = $inform;
    }

    Zone_Base::$Model->insertMany('user_message_maps', $maps);
    Zone_Base::$Model->insertMany('user_message_informs', $informs);
    return true;

}