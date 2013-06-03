<?php

/**
 * Config for app
 */
return array(
    debug => true,
    adapter => 'Pdo_Mysql',
    db => array(
        host => 'localhost',
        dbname => 'mytour',
        username => 'root',
        password => '123456',
        charset => 'utf8'
    ),
    max_user => 5,
    defaultModule => 'User',
    defaultLanguage => 'vn',
    default_timezone => 'Asia/Saigon',
    define => array(
        SITE_TITLE => 'My Tour',
        USER => 'office' . strtolower(str_replace('/', '_', BASE_URL)),
        DATETIME_FORMAT => 'd/m/Y H:i:s',
        DATE_FORMAT => 'd/m/Y',
        TIME_FORMAT => 'H:i:s',
        CURRENTCY => '<sup><u>đ</u></sup>'
    )
);