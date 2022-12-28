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

            if(!@$budget->instance && !@$budget->document){
                BudgetInstance::add((Object)[
                    "budget_id" => $post->budget_id,
                    "instance_id" => $post->instance_id
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

            $states = [];
            if(@$post->show_opened && $post->show_opened == "Y"){
                $states[] = "A";
            }
            if(@$post->show_billed && $post->show_billed == "Y"){
                $states[] = "F";
            }

            Json::get(Budget::getList((Object)[
                "company_id" => $post->company_id,
                "reference" => $post->reference,
                "show_others" => @$post->show_others && $post->show_others == "Y",
                "state" => sizeof($states) && sizeof($states) == 1 ? $states[0] : NULL
            ]));

        break;

        case "recover":

            if(!@$post->budget_id){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado."
                ]);
            }

            Budget::recover((Object)[
                "budget_id" => $post->budget_id
            ]);

            postLog((Object)[
                "parent_id" => $post->budget_id
            ]);

            Json::get([]);

        break;

        case "removeInstance":

            if(@$post->instance_id){
                BudgetInstance::del((Object)[
                    "budget_id" => @$post->budget_id ? $post->budget_id : NULL,
                    "instance_id" => $post->instance_id
                ]);
            }

            Json::get([]);

        break;
    }

?>