<?php

    include "../../config/start.php";

    GLOBAL $config, $terminal, $login, $headers, $get;

    switch($get->action){

        case "get":

            Json::get((Object)[
                "login" => $login,
                "config" => $config,
                "terminal" => $terminal
            ]);

        break;
    }

?>