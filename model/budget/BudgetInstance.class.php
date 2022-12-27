<?php

    class BudgetInstance
    {
        public $host_ip;
        public $host_name;
        public $user_name;
        public $budget_instance_date;

        public function __construct($data)
        {
            $this->instance_id = $data->instance_id;
            $this->user_name = $data->user_name;
            $this->host_ip = $data->host_ip;
            $this->host_name = $data->host_name;
            $this->budget_instance_date = $data->budget_instance_date;
        }

        public static function add($params)
        {
            GLOBAL $commercial, $login;

            Model::insert($commercial, (Object)[
                "table" => "BudgetInstance",
                "fields" => [
                    ["budget_id", "i", $params->budget_id],
                    ["instance_id", "s", $params->instance_id],
                    ["user_id", "i", $login->user_id],
                    ["host_ip", "s", $login->terminal->hostIP],
                    ["host_name", "s", $login->terminal->hostName],
                    ["budget_instance_date", "s", date("Y-m-d H:i:s")]
                ]
            ]);
        }

        public static function del($params)
        {
            GLOBAL $commercial;

            Model::delete($commercial, (Object)[
                "table" => "BudgetInstance",
                "filters" => [
                    ["budget_id", "i", "=", $params->budget_id],
                    ["instance_id", "s", "=", $params->instance_id],
                ]
            ]);
        }

        public static function get($params)
        {
            GLOBAL $commercial;

            $data = Model::get($commercial, (Object)[
                "join" => 1,
                "tables" => [
                    "BudgetInstance BI",
                    "INNER JOIN [User] U ON U.user_id = BI.user_id",
                ],
                "fields" => [
                    "BI.instance_id",
                    "BI.host_ip",
                    "BI.host_name",
                    "U.user_name",
                    "budget_instance_date=FORMAT(BI.budget_instance_date,'dd/MM/yyyy HH:mm:ss')",
                ],
                "filters" => [["BI.budget_id", "i", "=", $params->budget_id]]
            ]);

            if(@$data){
                return new BudgetInstance($data);
            }

            return NULL;
        }
    }

?>