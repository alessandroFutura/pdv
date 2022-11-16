<?php

    class UserSession
    {
        public $user_id;
        public $user_session_value;
        public $user_session_app_version;
        public $user_session_host_ip;
        public $user_session_host_name;
        public $user_session_platform;
        public $user_session_date;

        public function __construct( $data )
        {
            $this->user_id = (int)$data->user_id;
            $this->user_session_value = $data->user_session_value;
            $this->user_session_host_ip = $data->user_session_host_ip;
            $this->user_session_host_name = $data->user_session_host_name;
            $this->user_session_platform = $data->user_session_platform;
            $this->user_session_date = $data->user_session_date;
        }

        public static function add($params)
        {
            GLOBAL $commercial;

            Model::insert($commercial, (Object)[
                "table" => "UserSession",
                "fields" => [
                    ["user_id", "s", $params->user_id],
                    ["user_session_value", "s", $params->user_session_value],
                    ["user_session_origin", "s", "P"],
                    ["user_session_app_version", "s", $params->user_session_app_version],
                    ["user_session_host_ip", "s", $params->user_session_host_ip],
                    ["user_session_host_name", "s", $params->user_session_host_name],
                    ["user_session_platform", "s", $params->user_session_platform],
                    ["user_session_date", "s", date("Y-m-d H:i:s")]
                ]
            ]);
        }

        public static function get($params)
        {
            GLOBAL $commercial;

            $data = Model::get($commercial, (Object)[
                "tables" => ["UserSession"],
                "fields" => [
                    "user_id",
                    "user_session_value",
                    "user_session_app_version",
                    "user_session_host_ip",
                    "user_session_host_name",
                    "user_session_platform",
                    "user_session_date=FORMAT(user_session_date,'yyyy-MM-dd HH:mm:ss')"
                ],
                "filters" => [
                    ["user_session_origin = 'P'"],
                    ["user_id", "i", "=", $params->user_id],
                    ["user_session_value", "s", "=", $params->user_session_value],
                ]
            ]);

            if(@$data){
                return new UserSession($data);
            }

            return NULL;
        }

        public static function restore()
        {
            GLOBAL $headers, $commercial;

            $login = NULL;

            if(@$headers["x-user-id"] && $headers["x-user-session-value"]){
                $data = Model::get($commercial, (Object)[
                    "top" => 1,
                    "tables" => ["UserSession"],
                    "fields" => [
                        "user_id",
                        "user_session_value"
                    ],
                    "filters" => [
                        ["user_session_origin = 'P'"],
                        ["user_id", "i", "=", $headers["x-user-id"]],
                        ["user_session_value", "s", "=", $headers["x-user-session-value"]],
                        ["user_session_date", "s", ">=", date("Y-m-d")]
                    ]
                ]);

                if(@$data){
                    session_id($data->user_session_value);
                    $_SESSION["user_id"] = $data->user_id;
                    $_SESSION["user_session_id"] = $data->user_session_value;
                    $file = PATH_LOG . "session/{$data->user_id}/{$data->user_session_value}.json";
                    if(file_exists($file)){
                        $login = json_decode(file_get_contents($file));
                    }
                }
            }

            return $login;
        }
    }

?>