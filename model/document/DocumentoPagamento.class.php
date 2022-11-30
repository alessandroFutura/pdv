<?php

    class DocumentoPagamento
    {
        public $IdDocumentoPagamento;
        public $IdDocumento;
        public $IdTipoBaixa;
        public $NrDias;
        public $NrTitulo;
        public $DtVencimento;
        public $IdNaturezaLancamento;
        public $VlTitulo;
        public $IdFormaPagamento;
        public $StEntrada;
        public $IdPessoaConvenio;
        public $AlConvenio;
        public $IdContaBancaria;
        public $NrParcelas;
        public $NrParcelasRecebimento;
        public $NrDiasRecebimento;
        public $NrDiasIntervalo;
        public $NrDiasPrimeiraParcelaVenda;
        public $AlTACConvenio;
        public $AlTACEmpresa;
        public $StAtualizaFinanceiro;
        public $StCartaCredito;

        public function __construct($data)
        {
            $this->IdDocumentoPagamento = @$data->IdDocumentoPagamento ? $data->IdDocumentoPagamento : NULL;
            $this->IdDocumento = @$data->IdDocumento ? $data->IdDocumento : NULL;
            $this->IdTipoBaixa = @$data->IdTipoBaixa ? $data->IdTipoBaixa : NULL;
            $this->NrDias = @$data->NrDias ? $data->NrDias : 0;
            $this->NrTitulo = @$data->NrTitulo ? $data->NrTitulo : NULL;
            $this->DtVencimento = @$data->DtVencimento ? $data->DtVencimento : NULL;
            $this->IdNaturezaLancamento = @$data->IdNaturezaLancamento ? $data->IdNaturezaLancamento : NULL;
            $this->VlTitulo = @$data->VlTitulo ? $data->VlTitulo : NULL;
            $this->IdFormaPagamento = @$data->IdFormaPagamento ? $data->IdFormaPagamento : NULL;
            $this->StEntrada = @$data->StEntrada ? $data->StEntrada : NULL;
            $this->IdPessoaConvenio = @$data->IdPessoaConvenio ? $data->IdPessoaConvenio : NULL;
            $this->AlConvenio = @$data->AlConvenio ? $data->AlConvenio : NULL;
            $this->IdContaBancaria = @$data->IdContaBancaria ? $data->IdContaBancaria : NULL;
            $this->NrParcelas = @$data->NrParcelas ? $data->NrParcelas : NULL;
            $this->NrParcelasRecebimento = @$data->NrParcelasRecebimento ? $data->NrParcelasRecebimento : NULL;
            $this->NrDiasRecebimento = @$data->NrDiasRecebimento ? $data->NrDiasRecebimento : NULL;
            $this->NrDiasIntervalo = @$data->NrDiasIntervalo ? $data->NrDiasIntervalo : NULL;
            $this->NrDiasPrimeiraParcelaVenda = @$data->NrDiasPrimeiraParcelaVenda ? $data->NrDiasPrimeiraParcelaVenda : NULL;
            $this->AlTACConvenio = @$data->AlTACConvenio ? $data->AlTACConvenio : NULL;
            $this->AlTACEmpresa = @$data->AlTACEmpresa ? $data->AlTACEmpresa : NULL;
            $this->StAtualizaFinanceiro = @$data->StAtualizaFinanceiro ? $data->StAtualizaFinanceiro : NULL;
            $this->StAglutinaTituloEmCheque = @$data->StAglutinaTituloEmCheque ? $data->StAglutinaTituloEmCheque : NULL;
            $this->StCartaCredito = @$data->StCartaCredito ? $data->StCartaCredito : NULL;
        }

        public static function getList($params)
        {
            GLOBAL $dafel;

            $payments = Model::getList($dafel, (Object)[
                "join" => 1,
                "tables" => ["DocumentoPagamento (NoLock)"],
                "fields" => [
                    "IdDocumentoPagamento",
                    "IdDocumento",
                    "IdTipoBaixa",
                    "NrDias",
                    "NrTitulo",
                    "DtVencimento=FORMAT(DtVencimento, 'yyyy-MM-dd')",
                    "IdNaturezaLancamento",
                    "VlTitulo=CAST(VlTitulo AS FLOAT)",
                    "IdFormaPagamento",
                    "StEntrada",
                    "IdPessoaConvenio",
                    "AlConvenio=CAST(AlConvenio AS FLOAT)",
                    "IdContaBancaria",
                    "NrParcelas",
                    "NrParcelasRecebimento",
                    "NrDiasRecebimento",
                    "NrDiasIntervalo",
                    "NrDiasPrimeiraParcelaVenda",
                    "AlTACConvenio=CAST(AlTACConvenio AS FLOAT)",
                    "AlTACEmpresa=CAST(AlTACEmpresa AS FLOAT)",
                    "StAtualizaFinanceiro",
                    "StAglutinaTituloEmCheque",
                    "StCartaCredito",
                ],
                "filters" => [["IdDocumento", "s", "=", $params->IdDocumento]]
            ]);

            $ret = [];
            foreach($payments as $payment){
                $ret[] = new DocumentoPagamento($payment);
            }

            return $ret;
        }
    }

?>