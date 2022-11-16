<?php

    class Person
    {
        public $image;
        public $IdPessoa;
        public $CdChamada;
        public $NmPessoa;
        public $CdCPF_CGC;

        public function __construct($data)
        {
           $this->IdPessoa = $data->IdPessoa;
           $this->CdChamada = $data->CdChamada;
           $this->NmPessoa = $data->NmPessoa;
           $this->CdCPF_CGC = @$data->CdCPF_CGC ? $data->CdCPF_CGC : NULL;

            $this->image = getImage((Object)[
                "image_id" => $data->IdPessoa,
                "image_dir" => "person"
            ]);
        }

        public static function get($params)
        {
            GLOBAL $dafel;

            $data = Model::get($dafel, (Object)[
                "join" => 1,
                "tables" => ["Pessoa (NoLock)"],
                "fields" => [
                    "IdPessoa",
                    "CdChamada",
                    "NmPessoa",
                    "CdCPF_CGC"
                ],
                "filters" => [["IdPessoa", "s", "=", $params->IdPessoa]]
            ]);

            if(!@$data){
                return NULL;
            }

            return new Person($data);
        }
    }

?>