<?php

    include "../../config/start.php";

    GLOBAL $get, $post, $login;

    switch($get->action){

        case "get":

            if(!@$post->budget_id){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado"
                ]);
            }

            $budget = Budget::get((Object)[
                "budget_id" => $post->budget_id
            ]);

            if(!@$budget){
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Documento não encontrado"
                ]);
            }

            Json::get($budget);

        break;

        case "getList":

            if(!@$post->company_id || !@$post->reference){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado"
                ]);
            }

            Json::get(Budget::getList((Object)[
                "company_id" => $post->company_id,
                "reference" => $post->reference,
                "state" => @$post->states && sizeof($post->states) == 1 ? $post->states[0] : NULL
            ]));

        break;
    }

?>