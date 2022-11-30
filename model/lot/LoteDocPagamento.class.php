<?php

    class LoteDocPagamento
    {
        public static function del($params)
        {
            GLOBAL $dafel;

            Model::delete($dafel, (Object)[
                "top" => $params->top,
                "table" => "LoteDocPagamento",
                "filters" => [["IdDocumento", "s", "=", $params->IdDocumento]]
            ]);
        }

        public static function add($params)
        {
            GLOBAL $dafel;

            if(!@$params->IdDocumentoPagamento){
                $params->IdDocumentoPagamento = Model::nextCode($dafel,(Object)[
                    "table" => "DocumentoPagamento",
                    "field" => "IdDocumentoPagamento",
                    "increment" => "S",
                    "base36encode" => 1
                ]);
            }

            Model::insert($dafel, (Object)[
                "table" => "LoteDocPagamento",
                "fields" => [
                    ["IdDocumentoPagamento", "s", $params->IdDocumentoPagamento],
                    ["IdDocumento", "s", $params->IdDocumento],
                    ["IdTipoBaixa", "s", $params->IdTipoBaixa],
                    ["NrDias", "s", $params->NrDias],
                    ["NrTitulo", "s", $params->NrTitulo],
                    ["AlParcela", "s", 0],
                    ["DtVencimento", "s", $params->DtVencimento],
                    ["IdNaturezaLancamento", "s", $params->IdNaturezaLancamento],
                    ["VlTitulo", "s", $params->VlTitulo],
                    ["IdFormaPagamento", "s", $params->IdFormaPagamento],
                    ["TpEdicao", "s", isset($params->TpEdicao) ? $params->TpEdicao : "I"],
                    ["StEntrada", "s", $params->StEntrada],
                    ["IdPessoaConvenio", "s", $params->IdPessoaConvenio],
                    ["AlConvenio", "s", $params->AlConvenio],
                    ["VlCredito", "s", 0],
                    ["IdContaBancaria", "s", $params->IdContaBancaria],
                    ["StAglutinaTituloEmCheque", "s", "N"],
                    ["VlTACEmpresa", "s", 0],
                    ["VlTACConvenio", "s", 0],
                    ["NrParcelas", "s", $params->NrParcelas],
                    ["NrParcelasRecebimento", "s", $params->NrParcelasRecebimento],
                    ["NrDiasRecebimento", "s", $params->NrDiasRecebimento],
                    ["NrDiasIntervalo", "s", $params->NrDiasIntervalo],
                    ["NrDiasPrimeiraParcelaVenda", "s", $params->NrDiasPrimeiraParcelaVenda],
                    ["AlTACConvenio", "s", $params->AlTACConvenio],
                    ["AlTACEmpresa", "s", $params->AlTACEmpresa],
                    ["StAtualizaFinanceiro", "s", $params->StAtualizaFinanceiro],
                    ["StCartaCredito", "s", $params->StCartaCredito],
                    ["VlJurosPrazo", "s", 0],
                ]
            ]);

            return $params->IdDocumentoPagamento;
        }
    }

?>
