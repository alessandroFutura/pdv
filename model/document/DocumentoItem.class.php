<?php

    class DocumentoItem
    {
        public $IdDocumentoItem;
        public $IdDocumento;
        public $IdCFOP;
        public $IdPreco;
        public $IdProduto;
        public $VlUnitario;
        public $IdClassificacaoFiscal;
        public $AlDescontoItem;
        public $QtItem;
        public $VlItem;
        public $VlBaseICMS;
        public $AlICMS;
        public $VlDescontoItem;
        public $VlICMS;
        public $CdSituacaoTributaria;
        public $IdSetorSaida;
        public $IdEntidadeOrigem;
        public $NmEntidadeOrigem;
        public $StEstoqueUnidadeDeEstoqueSaida;
        public $StDesoneraICMS;
        public $IdUnidade;
        public $StEstoqueSetorEntradaTransEmp;
        public $TpEntrega;
        public $AlFCP;
        public $VlFCP;

        public function __construct($data)
        {
            $this->IdDocumentoItem = @$data->IdDocumentoItem ? $data->IdDocumentoItem : NULL;
            $this->IdDocumento = @$data->IdDocumento ? $data->IdDocumento : NULL;
            $this->IdCFOP = @$data->IdCFOP ? $data->IdCFOP : NULL;
            $this->IdPreco = @$data->IdPreco ? $data->IdPreco : NULL;
            $this->IdProduto = @$data->IdProduto ? $data->IdProduto : NULL;
            $this->VlUnitario = @$data->VlUnitario ? $data->VlUnitario : NULL;
            $this->IdClassificacaoFiscal = @$data->IdClassificacaoFiscal ? $data->IdClassificacaoFiscal : NULL;
            $this->AlDescontoItem = @$data->AlDescontoItem ? $data->AlDescontoItem : NULL;
            $this->QtItem = @$data->QtItem ? $data->QtItem : NULL;
            $this->VlItem = @$data->VlItem ? $data->VlItem : NULL;
            $this->VlBaseICMS = @$data->VlBaseICMS ? $data->VlBaseICMS : NULL;
            $this->AlICMS = @$data->AlICMS ? $data->AlICMS : NULL;
            $this->VlDescontoItem = @$data->VlDescontoItem ? $data->VlDescontoItem : NULL;
            $this->VlICMS = @$data->VlICMS ? $data->VlICMS : NULL;
            $this->CdSituacaoTributaria = @$data->CdSituacaoTributaria ? $data->CdSituacaoTributaria : NULL;
            $this->IdSetorSaida = @$data->IdSetorSaida ? $data->IdSetorSaida : NULL;
            $this->IdEntidadeOrigem = @$data->IdEntidadeOrigem ? $data->IdEntidadeOrigem : NULL;
            $this->NmEntidadeOrigem = @$data->NmEntidadeOrigem ? $data->NmEntidadeOrigem : NULL;
            $this->StEstoqueUnidadeDeEstoqueSaida = @$data->StEstoqueUnidadeDeEstoqueSaida ? $data->StEstoqueUnidadeDeEstoqueSaida : NULL;
            $this->StDesoneraICMS = @$data->StDesoneraICMS ? $data->StDesoneraICMS : NULL;
            $this->IdUnidade = @$data->IdUnidade ? $data->IdUnidade : NULL;
            $this->StEstoqueSetorEntradaTransEmp = @$data->StEstoqueSetorEntradaTransEmp ? $data->StEstoqueSetorEntradaTransEmp : NULL;
            $this->TpEntrega = @$data->TpEntrega ? $data->TpEntrega : NULL;
            $this->AlFCP = @$data->AlFCP ? $data->AlFCP : NULL;
            $this->VlFCP = @$data->VlFCP ? $data->VlFCP : NULL;
            $this->IdPessoa = @$data->IdPessoa ? $data->IdPessoa : NULL;
            $this->VlBaseRepasse = @$data->VlBaseRepasse ? $data->VlBaseRepasse : NULL;
            $this->AlRepasseDuplicata = @$data->AlRepasseDuplicata ? $data->AlRepasseDuplicata : NULL;
        }

        public static function getList($params)
        {
            GLOBAL $dafel;

            $items = Model::getList($dafel, (Object)[
                "join" => 1,
                "tables" => [
                    "DocumentoItem DI (NoLock)",
                    "INNER JOIN DocumentoItemValores DIV ON DIV.IdDocumentoItem = DI.IdDocumentoItem",
                    "INNER JOIN DocumentoItemRepasse DIR ON DIR.IdDocumentoItem = DI.IdDocumentoItem"
                ],
                "fields" => [
                    "DI.IdDocumentoItem",
                    "DI.IdDocumento",
                    "DIV.IdCFOP",
                    "DI.IdPreco",
                    "DI.IdProduto",
                    "VlUnitario=CAST(DI.VlUnitario AS FLOAT)",
                    "DIV.IdClassificacaoFiscal",
                    "AlDescontoItem=CAST(DIV.AlDescontoItem AS FLOAT)",
                    "QtItem=CAST(DI.QtItem AS FLOAT)",
                    "VlItem=CAST(DI.VlItem AS FLOAT)",
                    "VlBaseICMS=CAST(DIV.VlBaseICMS AS FLOAT)",
                    "AlICMS=CAST(DIV.AlICMS AS FLOAT)",
                    "VlDescontoItem=CAST(DIV.VlDescontoItem AS FLOAT)",
                    "VlICMS=CAST(DIV.VlICMS AS FLOAT)",
                    "DIV.CdSituacaoTributaria",
                    "DI.IdSetorSaida",
                    "DI.IdEntidadeOrigem",
                    "DI.NmEntidadeOrigem",
                    "DI.StEstoqueUnidadeDeEstoqueSaida",
                    "DIV.StDesoneraICMS",
                    "DI.IdUnidade",
                    "DI.StEstoqueSetorEntradaTransEmp",
                    "DI.TpEntrega",
                    "AlFCP=CAST(DIV.AlFCP AS FLOAT)",
                    "VlFCP=CAST(DIV.VlFCP AS FLOAT)",
                    "DIR.IdPessoa",
                    "VlBaseRepasse=CAST(DIR.VlBaseRepasse AS FLOAT)",
                    "AlRepasseDuplicata=CAST(DIR.AlRepasseDuplicata AS FLOAT)",
                ],
                "filters" => [["DI.IdDocumento", "s", "=", $params->IdDocumento]]
            ]);

            $ret = [];
            foreach($items as $item){
                $ret[] = new DocumentoItem($item);
            }

            return $ret;
        }
    }

?>