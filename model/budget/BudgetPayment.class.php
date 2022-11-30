<?php

    class BudgetPayment
    {
        public $external;
        public $modality_id;
        public $budget_payment_value;
        public $budget_payment_deadline;

        public function __construct($data)
        {
            $this->image = getImage((Object)[
                "image_id" => $data->modality_id,
                "image_dir" => "modality"
            ]);

            $this->modality_id = $data->modality_id;
            $this->budget_payment_entry = $data->budget_payment_entry;
            $this->budget_payment_deadline = $data->budget_payment_deadline;
            $this->budget_payment_value = (float)$data->budget_payment_value;
            $this->budget_payment_installment = (int)$data->budget_payment_installment;
            $this->external = (Object)[
                "DsFormaPagamento" => $data->DsFormaPagamento,
                "TpFormaPagamento" => $data->TpFormaPagamento,
                "NrDias" => (int)$data->NrDias >= 0 ? (int)$data->NrDias : 0,
                "IdTipoBaixa" => @$data->IdTipoBaixa ? $data->IdTipoBaixa : NULL,
                "AlConvenio" => @$data->AlConvenio ? (float)$data->AlConvenio : NULL,
                "AlTACEmpresa" => @$data->AlTACEmpresa ? $data->AlTACEmpresa : NULL,
                "AlTACConvenio" => @$data->AlTACConvenio ? $data->AlTACConvenio : NULL,
                "NrDiasIntervalo" => @$data->NrDiasIntervalo ? $data->NrDiasIntervalo : NULL,
                "IdPessoaConvenio" => @$data->IdPessoaConvenio ? $data->IdPessoaConvenio : NULL,
                "NrDiasRecebimento" => @$data->NrDiasRecebimento ? $data->NrDiasRecebimento : NULL,
                "IdNaturezaLancamento" => @$data->IdNaturezaLancamento ? $data->IdNaturezaLancamento : NULL,
                "NrParcelasRecebimento" => @$data->NrParcelasRecebimento ? (int)$data->NrParcelasRecebimento : NULL,
                "NrDiasPrimeiraParcelaVenda" => @$data->NrDiasPrimeiraParcelaVenda ? $data->NrDiasPrimeiraParcelaVenda : NULL,
            ];
        }

        public static function getList($params)
        {
            GLOBAL $conn, $commercial;

            $payments = Model::getList($commercial, (Object)[
                "join" => 1,
                "tables" => [
                    "{$conn->commercial->table}.dbo.BudgetPayment BP",
                    "INNER JOIN {$conn->dafel->table}.dbo.FormaPagamento FP ON FP.IdFormaPagamento = BP.modality_id",
                    "LEFT JOIN {$conn->dafel->table}.dbo.NaturezaLancamento NL ON NL.IdNaturezaLancamento = FP.IdNaturezaLancamento",
                    "LEFT JOIN {$conn->dafel->table}.dbo.FormaPagamentoItem FPI ON FPI.IdFormaPagamento = FP.IdFormaPagamento AND FPI.CdEmpresa = {$params->CdEmpresa} AND FPI.NrParcelas = BP.budget_payment_installment"
                ],
                "fields" => [
                    "modality_id",
                    "NL.IdTipoBaixa",
                    "FPI.AlTACEmpresa",
                    "FPI.AlTACConvenio",
                    "FP.IdPessoaConvenio",
                    "FP.DsFormaPagamento",
                    "FP.TpFormaPagamento",
                    "FPI.NrDiasIntervalo",
                    "FPI.NrDiasRecebimento",
                    "FP.IdNaturezaLancamento",
                    "BP.budget_payment_entry",
                    "FPI.NrParcelasRecebimento",
                    "FPI.NrDiasPrimeiraParcelaVenda",
                    "BP.budget_payment_installment",
                    "AlConvenio=CAST(FPI.AlConvenio AS FLOAT)",
                    "budget_payment_value=CAST(BP.budget_payment_value AS FLOAT)",
                    "budget_payment_deadline=FORMAT(BP.budget_payment_deadline, 'yyyy-MM-dd')",
                    "NrDias=DATEDIFF(DAY, '" . date("Y-m-d") . "', BP.budget_payment_deadline)"
                ],
                "filters" => [["BP.budget_id", "i", "=", $params->budget_id]]
            ]);

            $ret = [];
            foreach($payments as $payment){
                $ret[] = new BudgetPayment($payment);
            }

            return $ret;
        }
    }

?>