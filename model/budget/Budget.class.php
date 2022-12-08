<?php

    class Budget
    {
        public $budget_id;
        public $external_id;
        public $company_id;
        public $client_id;
        public $seller_id;
        public $term_id;
        public $address_code;
        public $external_type;
        public $budget_value;
        public $budget_aliquot_discount;
        public $budget_value_discount;
        public $budget_value_total;

        public function __construct($data)
        {
            $this->budget_id = (int)$data->budget_id;
            $this->external_id = $data->external_id;
            $this->company_id = (int)$data->company_id;
            $this->client_id = $data->client_id;
            $this->seller_id = $data->seller_id;
            $this->term_id = @$data->term_id ? $data->term_id : NULL;
            $this->address_code = $data->address_code;
            $this->external_type = $data->external_type;
            $this->budget_value = (float)$data->budget_value;
            $this->budget_aliquot_discount = (float)$data->budget_aliquot_discount;
            $this->budget_value_discount = (float)$data->budget_value_discount;
            $this->budget_value_total = (float)$data->budget_value_total;

            $this->seller = Seller::get((Object)[
                "IdPessoa" => $data->seller_id
            ]);

            $this->person = Person::get((Object)[
                "IdPessoa" => $data->client_id,
                "CdEndereco" => $data->address_code
            ]);

            $this->items = BudgetItem::getList((Object)[
                "budget_id" => $data->budget_id,
                "CdEmpresa" => $data->company_id
            ]);

            $this->payments = BudgetPayment::getList((Object)[
                "budget_id" => $data->budget_id,
                "CdEmpresa" => $data->company_id
            ]);

            $this->document = TerminalDocument::get((Object)[
                "budget_id" => $data->budget_id
            ]);

            $this->operation = Operation::get((Object)[
                "company_id" => $data->company_id,
                "external_type" => $data->external_type
            ]);

            if($this->external_type == "D"){
                $this->external = DAV::get((Object)[
                    "IdDocumentoAuxVenda" => $data->external_id
                ]);
            }

            if($this->external_type == "P"){
                $this->external = PedidoDeVenda::get((Object)[
                    "IdPedidoDeVenda" => $data->external_id
                ]);
            }

            if(@$data->term_id){
                $this->term = Term::get((Object)[
                    "IdPrazo" => $data->term_id
                ]);
            }
        }

        public static function get($params)
        {
            GLOBAL $commercial;

            $data = Model::get($commercial, (Object)[
                "tables" => ["Budget"],
                "fields" => [
                    "budget_id",
                    "external_id",
                    "company_id",
                    "client_id",
                    "seller_id",
                    "address_code",
                    "term_id",
                    "external_type",
                    //"document_code",
                    "budget_note",
                    //"truck_size",
                    //"budget_delivery",
                    //"budget_note_document",
                    "budget_value=CAST(budget_value AS FLOAT)",
                    "budget_aliquot_discount=CAST(budget_aliquot_discount AS FLOAT)",
                    "budget_value_discount=CAST(budget_value_discount AS FLOAT)",
                    "budget_value_total=CAST(budget_value_total AS FLOAT)",
                ],
                "filters" => [
                    ["budget_trash = 'N'"],
                    ["budget_id", "i", "=", $params->budget_id]
                ]
            ]);

            if(@$data){
                return new Budget($data);
            }

            return NULL;
        }

        public static function getList($params)
        {
            GLOBAL $conn, $commercial;

            $budgets = Model::getList($commercial, (Object)[
                "join" => 1,
                "tables" => [
                    "{$conn->commercial->table}.dbo.Budget B",
                    "INNER JOIN {$conn->dafel->table}.dbo.Pessoa P ON P.IdPessoa = B.client_id",
                    "INNER JOIN {$conn->dafel->table}.dbo.Pessoa P2 ON P2.IdPessoa = B.seller_id",
                    "INNER JOIN {$conn->dafel->table}.dbo.EmpresaERP E(NoLock) ON E.CdEmpresa = B.company_id",
                    "INNER JOIN {$conn->dafel->table}.dbo.PedidoDeVenda PV ON PV.IdPedidoDeVenda = B.external_id",
                    "LEFT JOIN {$conn->dafel->table}.dbo.Prazo P3 ON P3.IdPrazo = B.term_id",
                    "LEFT JOIN {$conn->commercial->table}.dbo.TerminalDocument TD ON TD.budget_id = B.budget_id",
                    "LEFT JOIN {$conn->commercial->table}.dbo.[User] UC ON UC.user_id = TD.IdUsuarioAutorizacaoCancelamento"
                ],
                "fields" => [
                    "B.budget_id",
                    "B.company_id",
                    "B.external_id",
                    "B.external_code",
                    "B.external_type",
                    "B.document_code",
                    "NmCliente=P.NmPessoa",
                    "P3.DsPrazo",
                    "NmVendedor=(CASE WHEN LEN(P2.NmCurto) > 0 THEN P2.NmCurto ELSE P2.NmPessoa END)",
                    "budget_value_total=CAST(B.budget_value_total AS FLOAT)",
                    "budget_date=FORMAT(B.budget_date, 'yyyy-MM-dd')",
                    "DtCancelamento=FORMAT(TD.DtCancelamento, 'yyyy-MM-dd HH:mm:ss')",
                    "TD.nNF",
                    "xMotivo",
                    "StCancelado",
                    "TD.modelo",
                    "TD.CdStatus",
                    "TD.IdDocumento",
                    "company_cnpj=E.NrCGC",
                    "NmUsuarioCancelamento=UC.user_name",
                    "PV.DsObservacaoDocumento",
                    "cStat=(CASE WHEN ISNULL(TD.modelo,'XX') = 'OE' THEN 100 ELSE ISNULL(TD.cStat,NULL) END)",
                ],
                "filters" => [
                    ["B.budget_trash = 'N'"],
                    //["B.budget_date >= '2022-12-01'"],
                    ["B.budget_status IN('L','B')"],
                    ["B.company_id", "i", "=", $params->company_id],
                    ["(CASE WHEN ISNULL(TD.CdStatus,0) >= 9 THEN 'F' ELSE 'A' END)", "s", "=", @$params->state ? $params->state : NULL],
                    ["B.budget_date", "s", "between", ["{$params->reference} 00:00:00", "{$params->reference} 23:23:59"]]
                ]
            ]);

            foreach($budgets as $budget){
                $budget->DsPagamento = "--";
                $budget->DsPrazo = @$budget->DsPrazo ? $budget->DsPrazo : NULL;
                $budget->NmUsuarioCancelamento = @$budget->NmUsuarioCancelamento ? $budget->NmUsuarioCancelamento : NULL;
                $budget->DtCancelamento = @$budget->DtCancelamento ? $budget->DtCancelamento : NULL;
                $budget->document_code = @$budget->document_code ? $budget->document_code : NULL;
                $budget->budget_value_total = (float)$budget->budget_value_total;
                $budget->nNF = @$budget->nNF ? $budget->nNF : "--";
                $budget->modelo = @$budget->modelo ? $budget->modelo : NULL;
                $budget->cStat = @$budget->cStat ? (int)$budget->cStat : NULL;
                $budget->xMotivo = @$budget->xMotivo ? $budget->xMotivo : NULL;
                $budget->CdStatus = @$budget->CdStatus ? (int)$budget->CdStatus : 0;
                $budget->StCancelado = @$budget->StCancelado ? $budget->StCancelado : "N";
                $budget->IdDocumento = @$budget->IdDocumento ? $budget->IdDocumento : NULL;
                $budget->NmVendedor = explode(" ", $budget->NmVendedor)[0];
            }

            return $budgets;
        }
    }

?>