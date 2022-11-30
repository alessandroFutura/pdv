<?php

    class Token
    {
        public $token_id;
        public $user_id;
        public $company_id;
        public $ambient_id;
        public $token_active;
        public $token_code;
        public $token_value;
        public $token_date;

        public function __construct($data)
        {
            $this->token_id = (int)$data->token_id;
            $this->user_id = (int)$data->user_id;
            $this->company_id = (int)$data->company_id;
            $this->ambient_id = (int)$data->ambient_id;
            $this->token_active = $data->token_active;
            $this->token_code = $data->token_code;
            $this->token_value = $data->token_value;
            $this->token_date = $data->token_date;
        }

        public static function get($params)
        {
            GLOBAL $commercial;

            $token = Model::get($commercial, (Object)[
                "tables" => ["Token"],
                "fields" => [
                    "token_id",
                    "user_id",
                    "company_id",
                    "ambient_id",
                    "token_active",
                    "token_code",
                    "token_value",
                    "token_date"
                ],
                "filters" => [
                    ["token_trash = 'N'"],
                    ["token_active = 'Y'"],
                    ["company_id", "i", "=", $params->company_id],
                    ["ambient_id", "i", "=", TP_AMBIENT]
                ]
            ]);

            if(@$token){
                return new Token($token);
            }

            return NULL;
        }
    }

?>