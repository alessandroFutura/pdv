<?php

    class BudgetItem
    {
//        public $budget_item_id;
//        public $budget_id;
//        public $external_id;
//        public $product_id;
//        public $price_id;
//        public $price_date;
        public $budget_item_quantity;
//        public $budget_item_cost;
//        public $budget_item_value;
        public $budget_item_value_unitary;
//        public $budget_item_aliquot_discount;
//        public $budget_item_value_discount;
//        public $budget_item_value_st;
//        public $budget_item_value_icms;
        public $budget_item_value_total;
//        public $budget_item_update;
//        public $budget_item_date;
        public $product;

        public function __construct($data)
        {
//            $this->budget_item_id = (int)$data->budget_item_id;
//            $this->budget_id = (int)$data->budget_id;
//            $this->external_id = @$data->external_id ? $data->external_id : NULL;
//            $this->product_id = $data->product_id;
//            $this->price_id = $data->price_id;
//            $this->price_date = $data->price_date;
            $this->budget_item_quantity = (float)$data->budget_item_quantity;
//            $this->budget_item_cost = (float)$data->budget_item_cost;
//            $this->budget_item_value = (float)$data->budget_item_value;
            $this->budget_item_value_unitary = (float)$data->budget_item_value_unitary;
//            $this->budget_item_aliquot_discount = (float)$data->budget_item_aliquot_discount;
//            $this->budget_item_value_discount = (float)$data->budget_item_value_discount;
//            $this->budget_item_value_st = (float)$data->budget_item_value_st;
//            $this->budget_item_value_icms = (float)$data->budget_item_value_icms;
            $this->budget_item_value_total = (float)$data->budget_item_value_total;
//            $this->budget_item_update = @$data->budget_item_update ? $data->budget_item_update : NULL;
//            $this->budget_item_date = $data->budget_item_date;

            $this->product = (Object)[
                "IdProduto" => $data->IdProduto,
                "CdChamada" => $data->CdChamada,
                "NmProduto" => $data->NmProduto,
                "unit" => (Object)[
                    "CdSigla" => $data->CdSigla
                ]
            ];
        }

        public static function getList($params)
        {
            GLOBAL $conn, $commercial;

            $data = Model::getList($commercial, (Object)[
                "join" => 1,
                "debug" => 0,
                "tables" => [
                    "{$conn->commercial->table}.dbo.BudgetItem BI",
                    "INNER JOIN {$conn->dafel->table}.dbo.Produto P ON P.IdProduto = BI.product_id",
                    //"INNER JOIN {$conn->dafel->table}.dbo.Produto_Empresa PE ON PE.IdProduto = P.IdProduto AND PE.CdEmpresa = {$params->CdEmpresa}",
                    "INNER JOIN {$conn->dafel->table}.dbo.CodigoProduto CP ON CP.IdProduto = P.IdProduto AND CP.StCodigoPrincipal = 'S'",
                    "INNER JOIN {$conn->dafel->table}.dbo.Unidade U ON U.IdUnidade = P.IdUnidade",
                    //"INNER JOIN {$conn->dafel->table}.dbo.Preco PR ON PR.IdPreco = BI.price_id",
                    //"INNER JOIN {$conn->commercial->table}.dbo.Price PR2 ON PR2.price_id = BI.price_id",
                    //"LEFT JOIN {$conn->commercial->table}.dbo.UserPrice UP ON UP.price_id = BI.price_id",
                    //"LEFT JOIN {$conn->dafel->table}.dbo.EstoqueEmpresa EE ON EE.IdProduto = ISNULL(P.IdProdutoOrigem, P.IdProduto) AND EE.CdEmpresa = {$params->CdEmpresa} AND EE.DtReferencia = (SELECT TOP 1 EE2.DtReferencia FROM {$conn->dafel->table}.dbo.EstoqueEmpresa EE2 WHERE EE2.IdProduto = ISNULL(P.IdProdutoOrigem, P.IdProduto) AND EE2.CdEmpresa = {$params->CdEmpresa} ORDER BY EE2.DtReferencia DESC)",
                    //"LEFT JOIN {$conn->dafel->table}.dbo.HistoricoCusto HC ON HC.IdProduto = ISNULL(P.IdProdutoOrigem, P.IdProduto) AND HC.CdEmpresa = {$params->CdEmpresa} AND HC.DtReferencia = (SELECT TOP 1 HC2.DtReferencia FROM {$conn->dafel->table}.dbo.HistoricoCusto HC2 WHERE HC2.IdProduto = ISNULL(P.IdProdutoOrigem, P.IdProduto) AND HC2.CdEmpresa = {$params->CdEmpresa} ORDER BY HC2.DtReferencia DESC)",
                    //"LEFT JOIN {$conn->dafel->table}.dbo.HistoricoPreco HP ON HP.IdProduto = P.IdProduto AND HP.IdPreco = BI.price_id AND HP.CdEmpresa = {$params->CdEmpresa} AND HP.DtReferencia = (SELECT TOP 1 HP2.DtReferencia FROM {$conn->dafel->table}.dbo.HistoricoPreco HP2 WHERE HP2.IdProduto = P.IdProduto AND HP2.IdPreco = BI.price_id AND HP2.CdEmpresa = {$params->CdEmpresa} ORDER BY HP2.DtReferencia DESC)",
                    //"LEFT JOIN {$conn->commercial->table}.dbo.BudgetItemData BID (NoLock) ON(BID.budget_item_id = BI.budget_item_id)",
                    //"LEFT JOIN {$conn->commercial->table}.dbo.BudgetItemTrib BIT (NoLock) ON(BIT.budget_item_id = BI.budget_item_id)"
                ],
                "fields" => [
                    //"BI.budget_item_id",
                    //"BI.budget_id",
                    //"BI.external_id",
                    //"BI.product_id",
                    //"BI.price_id",
                    //"company_id=PE.CdEmpresa",
                    //"price_date=FORMAT(BI.price_date, 'yyyy-MM-dd')",
                    //"budget_item_cost=CAST(BI.budget_item_cost AS FLOAT)",
                    "budget_item_quantity=CAST(BI.budget_item_quantity AS FLOAT)",
//                    "budget_item_value=CAST(BI.budget_item_value AS FLOAT)",
                    "budget_item_value_unitary=CAST(BI.budget_item_value_unitary AS FLOAT)",
                    //"budget_item_aliquot_discount=CAST(BI.budget_item_aliquot_discount AS FLOAT)",
                    //"budget_item_value_discount=CAST(BI.budget_item_value_discount AS FLOAT)",
                    //"budget_item_value_icms=CAST(BI.budget_item_value_icms AS FLOAT)",
                    //"budget_item_value_st=CAST(BI.budget_item_value_st AS FLOAT)",
                    "budget_item_value_total=CAST(BI.budget_item_value_total AS FLOAT)",
                    //"budget_item_update=FORMAT(BI.budget_item_update, 'yyyy-MM-dd HH:mm:ss')",
                    //"budget_item_date=FORMAT(BI.budget_item_date, 'yyyy-MM-dd HH:mm:ss')",
                    "P.IdProduto",
                    "CP.CdChamada",
                    "P.NmProduto",
                    //"PE.StAtivoVenda",
                    //"P.CdClassificacao",
                    //"P.NrDiasReposicao",
                    //"P.IdProdutoOrigem",
                    //"P.CdCFOP",
                    //"P.IdCalculoICMS",
                    //"P.CdCFOPEntreUF",
                    //"P.IdClassificacaoFiscal",
                    //"AlDesconto=CAST(P.AlDesconto AS FLOAT)",
                    //"FtConversaoUnidade=CAST(P.FtConversaoUnidade AS FLOAT)",
                    //"AlRepasseDuplicata=CAST(P.AlRepasseDuplicata AS FLOAT)",
                    //"VlCusto=CAST((HC.VlCusto * (CASE WHEN P.IdProdutoOrigem IS NOT NULL THEN P.FtConversaoUnidade ELSE 1 END)) AS FLOAT)",
                    //"DtReferenciaCusto=FORMAT(HC.DtReferencia, 'yyyy-MM-dd')",
                    //"U.IdUnidade",
                    "U.CdSigla",
                    //"U.NmUnidade",
                    //"U.TpUnidade",
                    //"QtEstoque=CAST((EE.QtEstoque / (CASE WHEN P.IdProdutoOrigem IS NOT NULL THEN P.FtConversaoUnidade ELSE 1 END)) AS FLOAT)",
                    //"DtReferenciaEstoque=FORMAT(EE.DtReferencia, 'yyyy-MM-dd')",
                    //"PR.IdPreco",
                    //"PR.CdPreco",
                    //"PR.NmPreco",
                    //"max_discount=CAST(ISNULL(UP.max_discount, 0) AS FLOAT)",
                    //"PR2.price_discount",
                    //"PR2.price_quantity",
                    //"BID.budget_item_data_id",
                    //"VTI=CAST(BID.VTI AS FLOAT)",
                    //"CMV=CAST(BID.CMV AS FLOAT)",
                    //"LB=CAST(BID.LB AS FLOAT)",
                    //"T=CAST(BID.T AS FLOAT)",
                    //"TT=CAST(BID.TT AS FLOAT)",
                    //"FR=CAST(BID.FR AS FLOAT)",
                    //"ICMS=CAST(BID.ICMS AS FLOAT)",
                    //"ICMSST=CAST(BID.ICMSST AS FLOAT)",
                    //"PISCOFINS=CAST(BID.PISCOFINS AS FLOAT)",
                    //"VlSimplesNacional=CAST(BID.VlSimplesNacional AS FLOAT)",
                    //"VlComissao=CAST(BID.VlComissao AS FLOAT)",
                    //"AlComissao=CAST(BID.AlComissao AS FLOAT)",
                    //"VlComissaoOriginal=CAST(BID.VlComissaoOriginal AS FLOAT)",
                    //"AlComissaoOriginal=CAST(BID.AlComissaoOriginal AS FLOAT)",
                    //"BID.Corte",
                    //"BIT.budget_item_trib_id",
                    //"IdCFOP=BIT.IdCFOP",
                    //"CST=CAST(BIT.CST AS FLOAT)",
                    //"MVA=CAST(BIT.MVA AS FLOAT)",
                    //"AlFCP=CAST(BIT.AlFCP AS FLOAT)",
                    //"AlICMS=CAST(BIT.AlICMS AS FLOAT)",
                    //"VlICMS=CAST(BIT.VlICMS AS FLOAT)",
                    //"VlICMSST=CAST(BIT.VlICMSST AS FLOAT)",
                    //"VlBaseFCP=CAST(BIT.VlBaseFCP AS FLOAT)",
                    //"VlICMSFCP=CAST(BIT.VlICMSFCP AS FLOAT)",
                    //"VlICMSFCPST=CAST(BIT.VlICMSFCPST AS FLOAT)",
                    //"AlICMSFCPST=CAST(BIT.AlICMSFCPST AS FLOAT)",
                    //"VlBaseICMSST=CAST(BIT.VlBaseICMSST AS FLOAT)",
                    //"AlICMSInterna=CAST(BIT.AlICMSInterna AS FLOAT)",
                    //"VlBaseICMSFCPST=CAST(BIT.VlBaseICMSFCPST AS FLOAT)",
                ],
                "filters" => [["BI.budget_id", "i", "=", $params->budget_id]]
            ]);

            $ret = [];
            foreach($data as $item){
                $ret[] = new BudgetItem($item);
            }

            return $ret;
        }
    }

?>