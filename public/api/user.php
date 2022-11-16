<?php

    include "../../config/start.php";

    GLOBAL $login, $headers, $get;

    switch($get->action){

        case "get":

//            if(!@$headers["x-user-id"] || !@$headers["x-user-session-value"]){
//                headerResponse((Object)[
//                    "code" => 417,
//                    "message" => "Parâmetro POST não encontrado."
//                ]);
//            }
//
//            $session = UserSession::get((Object)[
//                "user_id" => $headers["x-user-id"],
//                "user_session_value" => $headers["x-user-session-value"]
//            ]);
//
//            $user = User::get((Object)[
//                "user_id" => $session->user_id
//            ]);
//
//            if(!@$user || $user->user_active == "N"){
//                headerResponse((Object)[
//                    "code" => 417,
//                    "message" => "Usuário não encontrado."
//                ]);
//            }

            Json::get($login);

        break;
    }

?>