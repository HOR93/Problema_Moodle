<?php

$token = '087c2f709515144fbcab093b73d23193';
$domainname = 'http://localhost/moodle';
$defaultMoodleUrl = $domainname . '/webservice/rest/server.php';
define('APP_URL', '/secretaria');

#$config_path = "../../../../moodle/config.php";
#require_once $config_path; // Certifica que o Moodle está carregado
#require_once($GLOBALS['CFG']->dirroot . '/course/externallib.php');


// Defina as configurações do banco de dados em um array global
$GLOBALS['DB_CONFIG'] = [
    'type'     => 'pgsql',
    'library'  => 'native',
    'host'     => 'localhost',
    'name'     => 'secretaria',
    'user'     => 'postgres',
    'pass'     => 'admin',
    'prefix'   => 'mdl_',
    'options'  => [
        'dbpersist' => 0,
        'dbport'    => 5432,
        'dbsocket'  =>'',
        ],
];

$GLOBALS['DB_MOODLE'] = [
    'type'     => 'pgsql',
    'library'  => 'native',
    'host'     => 'localhost',
    'name'     => 'moodledb',
    'user'     => 'admin',
    'pass'     => 'admin',
    'prefix'   => 'mdl_',
    'options'  => [
        'dbpersist' => 0,
        'dbport'    => 5432,
        'dbsocket'  =>'',
        ],
];
