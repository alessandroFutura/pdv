<?php

    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: *");

    if($_SERVER['REQUEST_METHOD'] == "OPTIONS"){
        die();
    };

    error_reporting(E_ALL);
    ini_set('memory_limit', '-1');

    session_name("PDV");
    session_start();

    //date_default_timezone_set("America/Sao_Paulo");
    date_default_timezone_set("America/Araguaina");

    include "config.php";
    include "func.php";

    loadClass();
    $conn = json_decode(file_get_contents(PATH_DATA . "conn.json"));

    $dafel = new MSSQL($conn->dafel);
    $commercial = new MSSQL($conn->commercial);

    $get = (Object)$_GET;
    $post = json_decode(file_get_contents("php://input"));
    $headers = getallheaders();

    if(SCRIPT_NAME != "terminal"){
        $terminal = Terminal::get((Object)[
            "token" => $headers["x-token"]
        ]);
        if(!@$terminal || $terminal->terminal_active == "N"){
            headerResponse((Object)[
                "code" => 401,
                "message" => "Terminal não autorizado"
            ]);
        }
    }

    if(SCRIPT_NAME != "login" && SCRIPT_NAME != "terminal"){
        $login = UserSession::restore();
        if(!@$login || $login->user_active == "N"){
            headerResponse((Object)[
                "code" => 403,
                "message" => "Usuário não autorizado"
            ]);
        }
    }

?>