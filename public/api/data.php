<?php

    include "../../config/start.php";

    GLOBAL $terminal, $login, $headers, $get;

    switch($get->action){

        case "get":

            Json::get((Object)[
                "login" => $login,
                "terminal" => $terminal
            ]);

        break;
    }

?>