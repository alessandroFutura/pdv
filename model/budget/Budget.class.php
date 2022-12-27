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
        public $budget_delivery;
        public $budget_aliquot_discount;
        public $budget_value_discount;
        public $budget_value_total;
        public $person_nickname;

        public function __construct($data)
        {
            $this->budget_id = (int)$data->budget_id;
            $this->external_id = $data->external_id;
            $this->company_id = (int)$data->company_id;
            $this->client_id = $data->client_id;
            $this->seller_id = $data->seller_id;
            $this->budget_delivery = $data->budget_delivery;
            $this->term_id = @$data->term_id ? $data->term_id : NULL;
            $this->address_code = $data->address_code;
            $this->external_type = $data->external_type;
            $this->budget_value = (float)$data->budget_value;
            $this->budget_aliquot_discount = (float)$data->budget_aliquot_discount;
            $this->budget_value_discount = (float)$data->budget_value_discount;
            $this->budget_value_total = (float)$data->budget_value_total;
            $this->person_nickname = @$data->person_nickname ? $data->person_nickname : NULL;

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

            $this->instance = BudgetInstance::get((Object)[
                "budget_id" => $data->budget_id
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

            $this->pages = self::pages((Object)[
                "items" => $this->items,
                "payments" => $this->payments
            ]);
        }

        public static function creditReopen($params)
        {
            GLOBAL $dafel, $commercial;

            $credits = Model::getList($commercial,(Object)[
                "tables" => [
                    "Budget B",
                    "BudgetPayment BP",
                    "BudgetPaymentCredit BPC"
                ],
                "fields" => [
                    "BP.budget_payment_id",
                    "IdAPagar=BPC.payable_id",
                    "IdPedidoDeVenda=B.external_id",
                    "IdPedidoDeVendaPagamento=BP.external_id",
                    "VlTitulo=CAST(BPC.payable_value AS FLOAT)",
                    "VlPagamento=CAST(BP.budget_payment_value AS FLOAT)",
                    "DtReferencia=FORMAT(BP.budget_payment_date, 'yyyy-MM-dd')"
                ],
                "filters" => [
                    ["B.budget_id = BP.budget_id"],
                    ["BP.budget_payment_credit", "s", "=", "Y"],
                    ["B.budget_id", "i", "=", $params->budget_id],
                    ["BP.budget_payment_id = BPC.budget_payment_id"],
                ]
            ]);

            $payable = [];
            $VlPagamento = 0;
            $DtReferencia = NULL;
            $IdPedidoDeVenda = NULL;
            $IdPedidoDeVendaPagamento = NULL;
            $budget_payment_id = NULL;

            foreach($credits as $credit){
                $payable[] = $credit->IdAPagar;
                $VlPagamento += $credit->VlPagamento;
                $IdPedidoDeVenda = $credit->IdPedidoDeVenda;
                $IdPedidoDeVendaPagamento = $credit->IdPedidoDeVendaPagamento;
                $budget_payment_id = $credit->budget_payment_id;
                $DtReferencia = $credit->DtReferencia;
            }

            if(!sizeof($payable) || !@$IdPedidoDeVenda || !@$IdPedidoDeVendaPagamento || !@$budget_payment_id){
                return (Object)[
                    "code" => 417,
                    "message" => "Não foi possível recuperar o orçamento. Contate o setor de TI."
                ];
            }

            $drops = Model::getList($dafel,(Object)[
                "tables" => ["APagarBaixa"],
                "fields" => [
                    "IdAPagarBaixa",
                    "IdLoteAPagar"
                ],
                "filters" => [
                    ["IdEntidadeOrigem", "s", "=", $IdPedidoDeVendaPagamento],
                    ["IdAPagar", "s", "in", $payable]
                ]
            ]);

            if(sizeof($drops) != sizeof($payable)){
                return (Object)[
                    "code" => 417,
                    "message" => "Não foi possível recuperar o orçamento. Uma ou mais Baixas do crédito não foram encontradas."
                ];
            }

            $payableDrop=[];
            $payableLot=[];
            foreach( $drops as $drop ){
                $payableDrop[] = $drop->IdAPagarBaixa;
                $payableLot[] = $drop->IdLoteAPagar;
            }

            Model::delete($dafel,(Object)[
                "table" => "LoteAPagar",
                "filters" => [["IdLoteAPagar", "s", "in", $payableLot]],
                "top" => sizeof($payableLot)
            ]);

            Model::delete($dafel,(Object)[
                "table" => "APagarBaixa",
                "filters" => [
                    ["IdEntidadeOrigem", "s", "=", $IdPedidoDeVendaPagamento],
                    ["IdAPagarBaixa", "s", "in", $payableDrop]
                ],
                "top" => sizeof($payableDrop)
            ]);

            Model::update($dafel,(Object)[
                "table" => "APagar",
                "fields" => [["DtBaixa", "s", NULL]],
                "filters" => [["IdAPagar", "s", "in", $payable]],
                "top" => sizeof($payable)
            ]);

            Model::delete($commercial,(Object)[
                "table" => "BudgetPayment",
                "filters" => [
                    ["budget_id", "i", "=", $params->budget_id],
                    ["budget_payment_id", "i", "=", $budget_payment_id]
                ]
            ]);

            Model::delete($commercial,(Object)[
                "table" => "BudgetPaymentCredit",
                "filters" => [["budget_payment_id", "i", "=", $budget_payment_id]]
            ]);

            Model::delete($dafel,(Object)[
                "table" => "PedidoDeVendaPagamento",
                "filters" => [
                    ["IdPedidoDeVenda", "s", "=", $IdPedidoDeVenda],
                    ["IdPedidoDeVendaPagamento", "s", "=", $IdPedidoDeVendaPagamento]
                ]
            ]);
            
            if(@$params->addPayment){
                BudgetPayment::add((Object)[
                    "budget_id" => $params->budget_id,
                    "budget_payment_value" => $VlPagamento,
                    "budget_payment_reference" => $DtReferencia
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
                    "budget_note",
                    "person_nickname",
                    "budget_delivery",
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
            GLOBAL $conn, $commercial, $login;

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
                    "LEFT JOIN {$conn->commercial->table}.dbo.[User] U ON U.user_id = TD.user_id",
                    "LEFT JOIN {$conn->commercial->table}.dbo.[User] UC ON UC.user_id = TD.IdUsuarioAutorizacaoCancelamento"
                ],
                "fields" => [
                    "B.budget_id",
                    "B.company_id",
                    "B.external_id",
                    "B.external_code",
                    "B.external_type",
                    "B.budget_credit",
                    "B.document_code",
                    "NmCliente=ISNULL(B.person_nickname,P.NmPessoa)",
                    "P3.DsPrazo",
                    "NmVendedor=(CASE WHEN LEN(P2.NmCurto) > 0 THEN P2.NmCurto ELSE P2.NmPessoa END)",
                    "budget_value_total=CAST(B.budget_value_total AS FLOAT)",
                    "budget_date=FORMAT(B.budget_date, 'yyyy-MM-dd')",
                    "terminal_document_date=FORMAT(TD.terminal_document_date, 'yyyy-MM-dd')",
                    "DtCancelamento=FORMAT(TD.DtCancelamento, 'yyyy-MM-dd HH:mm:ss')",
                    "TD.modelo",
                    "TD.nNF",
                    "xMotivo",
                    "StCancelado",
                    "TD.modelo",
                    "TD.CdStatus",
                    "TD.IdDocumento",
                    "TD.dhRecbto",
                    "U.user_name",
                    "company_cnpj=E.NrCGC",
                    "NmUsuarioCancelamento=UC.user_name",
                    "PV.DsObservacaoDocumento",
                    "cStat=(CASE WHEN ISNULL(TD.modelo,'XX') = 'OE' THEN 100 ELSE ISNULL(TD.cStat,NULL) END)",
                ],
                "filters" => [
                    ["B.budget_trash = 'N'"],
                    ["B.export_type IN('OE','65')"],
                    ["B.budget_status IN('L','B','C')"],
                    ["B.company_id", "i", "=", $params->company_id],
                    !@$params->show_others ? ["ISNULL(TD.user_id,{$login->user_id}) = {$login->user_id}"] : NULL,
                    ["(CASE WHEN ISNULL(TD.CdStatus,0) >= 9 THEN 'F' ELSE 'A' END)", "s", "=", @$params->state ? $params->state : NULL],
                    ["B.budget_date", "s", "between", ["{$params->reference} 00:00:00", "{$params->reference} 23:23:59"]]
                ]
            ]);

            foreach($budgets as $budget){
                $budget->DsPagamento = "--";
                $budget->DsPrazo = @$budget->DsPrazo ? $budget->DsPrazo : NULL;
                $budget->NmUsuario = @$budget->user_name ? strtoupper(explode(" ", $budget->user_name)[0]) : NULL;
                $budget->NmUsuarioCancelamento = @$budget->NmUsuarioCancelamento ? $budget->NmUsuarioCancelamento : NULL;
                $budget->DtCancelamento = @$budget->DtCancelamento ? $budget->DtCancelamento : NULL;
                $budget->document_code = @$budget->document_code ? $budget->document_code : NULL;
                $budget->budget_value_total = (float)$budget->budget_value_total;
                $budget->NrDocumento = @$budget->nNF ? ($budget->modelo == "65" ? substr("00000000{$budget->nNF}",-9) : $budget->nNF) : "--";
                $budget->modelo = @$budget->modelo ? $budget->modelo : NULL;
                $budget->cStat = @$budget->cStat ? (int)$budget->cStat : NULL;
                $budget->xMotivo = @$budget->xMotivo ? $budget->xMotivo : NULL;
                $budget->CdStatus = @$budget->CdStatus ? (int)$budget->CdStatus : 0;
                $budget->StCancelado = @$budget->StCancelado ? $budget->StCancelado : "N";
                $budget->IdDocumento = @$budget->IdDocumento ? $budget->IdDocumento : NULL;
                $budget->dhRecbto = @$budget->dhRecbto ? substr(str_replace("T"," ",$budget->dhRecbto),0,19) : NULL;
                $budget->terminal_document_date = @$budget->terminal_document_date ? $budget->terminal_document_date : NULL;
                $budget->NmVendedor = explode(" ", $budget->NmVendedor)[0];
            }

            return $budgets;
        }

        public static function pages($data)
        {
            $page = 1;
            $index = 0;
            $items = 1;
            $printedPerPage = 1;
            $limitFirstPage = 28;
            $limitOtherPages = 32;

            $payments = sizeof($data->payments) + 6;

            $pages = [(Object)[
                "items" => []
            ]];

            foreach($data->items as $item){
                $item->key = $items;
                $pages[$index]->items[] = $item;
                if($printedPerPage % ($page == 1 ? $limitFirstPage : $limitOtherPages) == 0){
                    $page++;
                    $index++;
                    $printedPerPage = 0;
                    $pages[] = (Object)[
                        "items" => []
                    ];
                }
                $items++;
                $printedPerPage++;
            }

            if(
                ($page > 1 && $printedPerPage == 0) ||
                ($printedPerPage + $payments > ($page == 1 ? $limitFirstPage : $limitOtherPages))
            ){
                $pages[] = (Object)[
                    "items" => []
                ];
            }

            return $pages;
        }

        public static function recover($params)
        {
            GLOBAL $commercial, $dafel;

            $budget = Model::get($commercial,(Object)[
                "tables" => ["Budget"],
                "fields" => [
                    "budget_id",
                    "external_id",
                    "external_type",
                    "budget_status",
                    "budget_credit"
                ],
                "filters" => [["budget_id", "i", "=", $params->budget_id]]
            ]);

            if(!@$budget){
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Orçamento ão encontrado."
                ]);
            }

            if($budget->external_type == "D"){

                $dav = Model::get($dafel,(Object)[
                    "tables" => ["DocumentoAuxVenda"],
                    "fields" => ["StDocumentoAuxVenda"],
                    "filters" => [["IdDocumentoAuxVenda", "s", "=", $budget->external_id]]
                ]);

                if(!@$dav){
                    headerResponse((Object)[
                        "code" => 417,
                        "message" => "O DAV não foi encontrado no ERP."
                    ]);
                }

                if($dav->StDocumentoAuxVenda != "O"){
                    headerResponse((Object)[
                        "code" => 417,
                        "message" => "Não será possivel recuperar o DAV, pois o mesmo não está disponivel."
                    ]);
                }

                Dav::edit((Object)[
                    "StDocumentoAuxVenda" => "I",
                    "IdDocumentoAuxVenda" =>  $budget->external_id
                ]);

            } else {

                $order = Model::get($dafel,(Object)[
                    "tables" => ["PedidoDeVenda"],
                    "fields" => ["StPedidoDeVenda"],
                    "filters" => [["IdPedidoDeVenda", "s", "=", $budget->external_id]]
                ]);

                if(!@$order){
                    return (Object)[
                        "code" => 417,
                        "message" => "O pedido não foi encontrado no ERP."
                    ];
                }

                if($order->StPedidoDeVenda == "T"){
                    return (Object)[
                        "code" => 417,
                        "message" => "Não será possivel recuperar o pedido, pois o mesmo já foi faturado."
                    ];
                }

                if($budget->budget_credit == "Y"){
                    self::creditReopen((Object)[
                        "budget_id" => $budget->budget_id
                    ]);
                }

                Model::update($dafel,(Object)[
                    "table" => "PedidoDeVenda",
                    "fields" => [["StPedidoDeVenda", "s", "X"]],
                    "filters" => [["IdPedidoDeVenda", "s", "=", $budget->external_id]]
                ]);
            }

            Model::update($commercial,(Object)[
                "table" => "Budget",
                "fields" => [
                    ["budget_status", "s", "O"],
                    ["budget_credit", "s", "N"],
                    ["document_id", "i", NULL],
                    ["document_type", "i", NULL],
                    ["document_code", "i", NULL],
                    ["document_canceled", "i", NULL],
                    ["budget_update", "s", date("Y-m-d H:i:s")]
                ],
                "filters" => [["budget_id", "s", "=", $budget->budget_id]]
            ]);

        }


    }

?>