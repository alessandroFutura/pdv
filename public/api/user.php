<?php

    include "../../config/start.php";

    GLOBAL $login, $headers, $get;

    switch($get->action){

        case "get":

            $login->companies = UserCompany::getList((Object)[
                "user_id" => $login->user_id
            ]);
            Json::get($login);

        break;
    }

?>