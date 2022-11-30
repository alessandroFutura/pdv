<?php

    class Certificate
    {
        public $certificate_id;
        public $client_id;
        public $certificate_cgc;
        public $certificate_name;
        public $certificate_validity;
        public $certificate_date;
        public $certificate_valid;

        public function __construct($data)
        {
            $this->certificate_id = (int)$data->certificate_id;
            $this->certificate_cgc = $data->certificate_cgc;
            $this->certificate_name = $data->certificate_name;
            $this->certificate_validity = $data->certificate_validity;
            $this->certificate_date = $data->certificate_date;
            $this->certificate_date = $data->certificate_date;

            $this->certificate_valid = (int)str_replace(["-",":"," "], ["","",""], $data->certificate_validity) > (int)date("YmdHis");
        }

        public static function get($params)
        {
            GLOBAL $conn, $commercial;

            $certificate = Model::get($commercial, (Object)[
                "join" => 1,
                "tables" => [
                    "{$conn->commercial->table}.dbo.Certificate C",
                    "INNER JOIN {$conn->dafel->table}.dbo.EmpresaERP EE ON SUBSTRING(REPLACE(EE.NrCGC, '.',''),1,8) = SUBSTRING(C.certificate_cgc,1,8)"
                ],
                "fields" => [
                    "C.certificate_id",
                    "C.user_id",
                    "C.certificate_cgc",
                    "C.certificate_name",
                    "C.certificate_validity",
                    "C.certificate_date"
                ],
                "filters" => [["EE.CdEmpresa", "i", "=", $params->company_id]]
            ]);

            if(@$certificate){
                return new Certificate($certificate);
            }

            return NULL;
        }
    }

?>