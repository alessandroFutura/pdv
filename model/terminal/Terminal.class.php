<?php

    class Terminal
    {
        public $terminal_id;
        public $user_id;
        public $terminal_active;
        public $terminal_nfe;
        public $terminal_nfce;
        public $terminal_oe;
        public $terminal_name;
        public $terminal_token;
        public $terminal_nickname;
        public $terminal_date;

        public function __construct($data)
        {
            $this->terminal_id = (int)$data->terminal_id;
            $this->user_id = (int)$data->user_id;
            $this->terminal_active = $data->terminal_active;
            $this->terminal_nfe = $data->terminal_nfe;
            $this->terminal_nfce = $data->terminal_nfce;
            $this->terminal_oe = $data->terminal_oe;
            $this->terminal_name = $data->terminal_name;
            $this->terminal_token = $data->terminal_token;
            $this->terminal_nickname = $data->terminal_nickname;
            $this->terminal_date = $data->terminal_date;
        }

        public static function add($params)
        {
            GLOBAL $commercial;

            return (int)Model::insert($commercial, (Object)[
                "table" => "Terminal",
                "fields" => [
                    ["user_id", "i", $params->user_id],
                    ["terminal_active", "s", "Y"],
                    ["terminal_oe", "s", "Y"],
                    ["terminal_nfe", "s", "N"],
                    ["terminal_nfce", "s", "Y"],
                    ["terminal_name", "s", $params->terminal_name],
                    ["terminal_token", "s", $params->terminal_token],
                    ["terminal_nickname", "s", $params->terminal_nickname],
                    ["terminal_origin", "s", "P"],
                    ["terminal_date", "s", date("Y-m-d H:i:s")]
                ]
            ]);
        }

        public static function get($params)
        {
            GLOBAL $commercial;

            $data = Model::get($commercial, (Object)[
                "tables" => ["Terminal"],
                "fields" => [
                    "terminal_id",
                    "user_id",
                    "terminal_active",
                    "terminal_nfe",
                    "terminal_nfce",
                    "terminal_oe",
                    "terminal_name",
                    "terminal_token",
                    "terminal_nickname",
                    "terminal_date=FORMAT(terminal_date, 'yyyy-MM-dd HH:mm:ss')"
                ],
                "filters" => [["terminal_token", "s", "=", $params->token]]
            ]);

            if(@$data){
                return new Terminal($data);
            }

            return NULL;
        }

        public static function getCurrent()
        {
            GLOBAL $post;

            return (Object)[
                "token" => $post->token,
                "appVersion" => $post->appVersion,
                "hostIP" => $post->hostIP,
                "hostName" => $post->hostName,
                "platform" => $post->platform,
                "osType" => $post->osType,
                "userName" => $post->userName
            ];
        }

        public static function editSerie($params)
        {
            GLOBAL $commercial, $terminal;

            Model::update($commercial, (Object)[
                "table" => "TerminalCompany",
                "fields" => [
                    ["terminal_company_number", "i", $params->terminal_company_number],
                    ["terminal_company_changed", "s", date("Y-m-d H:i:s")]
                ],
                "filters" => [
                    ["terminal_id", "i", "=", $terminal->terminal_id],
                    ["terminal_company_id", "i", "=", $params->terminal_company_id]
                ]
            ]);
        }

        public static function getSerie($params)
        {
            GLOBAL $commercial, $terminal;

            $data = Model::get($commercial, (Object)[
                "join" => 1,
                "tables" => ["TerminalCompany"],
                "fields" => [
                    "terminal_company_id",
                    "model_id",
                    "serie_id",
                    "terminal_company_number",
                ],
                "filters" => [
                    ["terminal_id", "i", "=", $terminal->terminal_id],
                    ["company_id", "i", "=", $params->company_id],
                    ["ambient_id", "i", "=", TP_AMBIENT],
                    ["model_id", "i", "=", $params->model_id],
                    ["terminal_company_active = 'Y'"]
                ]
            ]);

            if(@$data){
                $data->model_id = (int)$data->model_id;
                $data->serie_id = (int)$data->serie_id;
                $data->terminal_company_id = (int)$data->terminal_company_id;
                $data->terminal_company_number = (int)$data->terminal_company_number;
            }

            return $data;
        }

        public static function validate($params)
        {
            GLOBAL $commercial, $terminal;

            $data = Model::get($commercial, (Object)[
                "tables" => ["TerminalUser"],
                "fields" => ["terminal_user_id"],
                "filters" => [
                    ["terminal_user_active = 'Y'"],
                    ["user_id", "i", "=", $params->user_id],
                    ["terminal_id", "i", "=", $terminal->terminal_id],
                ]
            ]);

            if(!@$data){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Usuário sem acesso ao terminal."
                ]);
            }

            $data = Model::get($commercial, (Object)[
                "join" => 1,
                "tables" => ["TerminalCompany"],
                "fields" => ["terminal_company_id"],
                "filters" => [
                    ["terminal_company_active = 'Y'"],
                    ["terminal_id", "i", "=", $terminal->terminal_id]
                ]
            ]);

            if(!@$data){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Nenhuma empresa ativa vinculada ao terminal."
                ]);
            }
        }
    }

?>