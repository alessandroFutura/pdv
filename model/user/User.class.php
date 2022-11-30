<?php

    class User
    {
        public $user_id;
        public $external_id;
        public $person_id;
        public $user_name;
        public $user_active;
        public $user_current_session;
        public $terminal;

        public function __construct($data)
        {
            $this->user_id = $data->user_id;
            $this->external_id = $data->external_id;
            $this->person_id = @$data->person_id ? $data->person_id : NULL;
            $this->user_name = $data->user_name;
            $this->user_active = $data->user_active;

            if(@$_POST["get_user_access"]){
                $this->access = UserAccess::get((Object)[
                    "user_id" => $data->user_id
                ]);
            }

            if(@$_POST["get_user_companies"]){
                $this->companies = UserCompany::getList((Object)[
                    "user_id" => $data->user_id
                ]);
            }

            if(@$_POST["get_user_image"]){
                $this->image = getImage((Object)[
                    "image_id" => $data->person_id,
                    "image_dir" => "person"
                ]);
            }

            if(@$_POST["get_user_current_session"]){
                $this->user_current_session = UserSession::getCurrent();
            }

            if(@$_POST["get_terminal"]){
                $this->terminal = Terminal::getCurrent();
            }
        }

        public static function get($params)
        {
            GLOBAL $commercial;

            $data = Model::get($commercial,(Object)[
                "tables" => ["[User]"],
                "fields" => [
                    "user_id",
                    "external_id",
                    "person_id",
                    "user_name",
                    "user_active"
                ],
                "filters" => [
                    ["user_id", "i", "=", @$params->user_id ? $params->user_id : NULL],
                    ["user_user", "s", "=", @$params->user_user ? $params->user_user : NULL],
                    ["user_pass", "s", "=", @$params->user_pass ? $params->user_pass : NULL]
                ]
            ]);

            if(@$data){
                return new User($data);
            }

            return NULL;
        }

        public static function getSession($params)
        {
            $file = PATH_LOG . "session/{$params->user_id}/{$params->user_session_id}.json";

            if(file_exists($file)){
                return json_decode(file_get_contents($file));
            } else {
                return NULL;
            }
        }

        public static function saveSession($params)
        {
            $path = PATH_LOG . "session/{$params->user_id}/";
            if(!is_dir($path)){
                mkdir($path, 775);
            }

            file_put_contents("{$path}{$params->user_session_id}.json", json_encode($params->data));
        }
    }

?>