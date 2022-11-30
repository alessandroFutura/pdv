<?php

    class Seller
    {
        public $image;
        public $IdPessoa;
        public $CdChamada;
        public $NmPessoa;
        public $AlComissaoFaturamento;
        public $AlComissaoDuplicata;
        public $StComissao;
        public $TpComissao;

        public function __construct($data)
        {
            $this->IdPessoa = $data->IdPessoa;
            $this->CdChamada = $data->CdChamada;
            $this->TpPessoa = $data->TpPessoa;
            $this->NmPessoa = $data->NmPessoa;

            $this->AlComissaoFaturamento = @$data->AlComissaoFaturamento ? (float)$data->AlComissaoFaturamento : 0;
            $this->AlComissaoDuplicata = @$data->AlComissaoDuplicata ? (float)$data->AlComissaoDuplicata : 0;
            $this->StComissao = @$data->StComissao ? $data->StComissao : NULL;
            $this->TpComissao = @$data->TpComissao ? $data->TpComissao : NULL;

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
                "tables" => [
                    "Pessoa P(NoLock)",
                    "LEFT JOIN Representante R(NoLock) ON R.IdPessoaRepresentante = P.IdPessoa"
                ],
                "fields" => [
                    "P.IdPessoa",
                    "P.CdChamada",
                    "P.TpPessoa",
                    "P.NmPessoa",
                    "AlComissaoFaturamento=CAST(R.AlComissaoFaturamento AS FLOAT)",
                    "AlComissaoDuplicata=CAST(R.AlComissaoDuplicata AS FLOAT)",
                    "R.StComissao",
                    "R.TpComissao"
                ],
                "filters" => [["P.IdPessoa", "s", "=", $params->IdPessoa]]
            ]);

            if(!@$data){
                return NULL;
            }

            return new Seller($data);
        }
    }

?>