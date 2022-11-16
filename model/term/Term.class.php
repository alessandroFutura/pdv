<?php

    class Term
    {
        public $IdPrazo;
        public $CdChamada;
        public $DsPrazo;

        public function __construct($data)
        {
            $this->IdPrazo = $data->IdPrazo;
            $this->CdChamada = $data->CdChamada;
            $this->DsPrazo = $data->DsPrazo;
        }

        public static function get($params)
        {
            GLOBAL $dafel;

            $data = Model::get($dafel, (Object)[
                "tables" => ["Prazo"],
                "fields" => [
                    "IdPrazo",
                    "CdChamada",
                    "DsPrazo"
                ],
                "filters" => [["IdPrazo", "s", "=", $params->IdPrazo]]
            ]);

            if(@$data){
                return new Term($data);
            }

            return NULL;
        }
    }

?>