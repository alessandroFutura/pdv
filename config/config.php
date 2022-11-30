<?php

    define("PATH_ROOT", "C:/wamp/www/pdv/");
    define("PATH_LOG", PATH_ROOT . "log/");
    define("PATH_DATA", PATH_ROOT . "data/");
    define("PATH_CLASS", PATH_ROOT . "class/");
    define("PATH_MODEL", PATH_ROOT . "model/");
    define("PATH_PUBLIC", PATH_ROOT . "public/");
    define("PATH_XML", PATH_ROOT . "xml/");

    define("PATH_PRODUCTION", "C:/wamp/www/commercial3/" );
    define("PATH_PRODUCTION_FILES", PATH_PRODUCTION . "public/files/" );
    define("PATH_CERTIFICATES", PATH_PRODUCTION . "certificates/" );

    define("URI_PUBLIC", "http://{$_SERVER["HTTP_HOST"]}/pdv/");
    define("URI_PRODUCTION", "http://{$_SERVER["HTTP_HOST"]}/commercial3/");
    define("URI_PRODUCTION_FILES", URI_PRODUCTION . "files/");

    // XML SEFAZ
    define("TP_AMBIENT", 2);
    define("TP_EMISSAO", 1);
    define("XML_VERSION", "4.00");

    define("VERSION", "1.0.0");
    define("SCRIPT_NAME", basename($_SERVER["SCRIPT_FILENAME"], '.php'));

?>