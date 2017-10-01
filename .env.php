<?php
/* What kind of environment is this: development, test, or live (ie, production)? */
define('SS_ENVIRONMENT_TYPE', 'dev');

/* Database connection */
define('SS_DATABASE_SERVER', '127.0.0.1');
define('SS_DATABASE_USERNAME', 'root');
define('SS_DATABASE_PASSWORD', 'ubuntu');
define('SS_DATABASE_NAME', 'circle_test');

/* Configure a default username and password to access the CMS on all sites in this environment. */
define('SS_DEFAULT_ADMIN_USERNAME', 'admin');
define('SS_DEFAULT_ADMIN_PASSWORD', 'password');

global $_FILE_TO_URL_MAPPING;
$_FILE_TO_URL_MAPPING[__DIR__] = 'http://localhost:8080';