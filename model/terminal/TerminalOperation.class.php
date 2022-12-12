<?php

    class TerminalOperation
    {
        public $terminal_operation_id;
        public $terminal_id;
        public $user_id;
        public $user_name;
        public $terminal_operation_type;
        public $terminal_operation_value;
        public $terminal_operation_date;

        public function __construct($data)
        {
            $this->terminal_operation_id = (int)$data->terminal_operation_id;
            $this->terminal_id = (int)$data->terminal_id;
            $this->user_id = (int)$data->user_id;
            $this->user_name = $data->user_name;
            $this->terminal_operation_type = $data->terminal_operation_type;
            $this->terminal_operation_value = @$data->terminal_operation_value ? (float)$data->terminal_operation_value : 0;
            $this->terminal_operation_date = $data->terminal_operation_date;
        }

        public static function add($params)
        {
            GLOBAL $commercial, $login;

            return (int)Model::insert($commercial, (Object)[
                "table" => "TerminalOperation",
                "fields" => [
                    ["terminal_id", "i", $params->terminal_id],
                    ["user_id", "i", $login->user_id],
                    ["terminal_operation_type", "s", $params->terminal_operation_type],
                    ["terminal_operation_value", "d", $params->terminal_operation_value],
                    ["terminal_operation_date", "s", date("Y-m-d H:i:s")]
                ]
            ]);
        }

        public static function get($params)
        {
            GLOBAL $commercial;

            $data = Model::get($commercial, (Object)[
                "join" => 1,
                "tables" => [
                    "TerminalOperation T",
                    "INNER JOIN [User] U ON U.user_id = T.user_id"
                ],
                "fields" => [
                    "T.terminal_operation_id",
                    "T.terminal_id",
                    "T.user_id",
                    "U.user_name",
                    "T.terminal_operation_type",
                    "terminal_operation_value=CAST(T.terminal_operation_value AS FLOAT)",
                    "terminal_operation_date=FORMAT(T.terminal_operation_date, 'yyyy-MM-dd HH:mm:ss')"
                ],
                "order" => "T.terminal_operation_id DESC",
                "filters" => [["T.terminal_id", "i", "=", $params->terminal_id]]
            ]);

            if(@$data){
                return new TerminalOperation($data);
            }

            return NULL;
        }
    }

?>