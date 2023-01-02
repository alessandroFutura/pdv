<?php

    class BudgetPayment
    {
        public $external;
        public $modality_id;
        public $budget_payment_id;
        public $budget_payment_value;
        public $budget_payment_deadline;
        public $modality_group_id;

        public function __construct($data)
        {
            $this->image = getImage((Object)[
                "image_id" => $data->modality_id,
                "image_dir" => "modality"
            ]);

            $this->modality_id = $data->modality_id;
            $this->budget_payment_id = (int)$data->budget_payment_id;
            $this->budget_payment_entry = $data->budget_payment_entry;
            $this->budget_payment_deadline = $data->budget_payment_deadline;
            $this->budget_payment_value = (float)$data->budget_payment_value;
            $this->budget_payment_installment = (int)$data->budget_payment_installment;

            $this->modality_group_id = @$data->modality_group_id ? (int)$data->modality_group_id : NULL;
            $this->modality_group_description = @$data->modality_group_description ? $data->modality_group_description : NULL;

            $this->external = (Object)[
                "TpCartao" => $data->TpCartao,
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

        public static function add($params)
        {
            GLOBAL $commercial;

            Model::insert($commercial, (Object)[
                "table" => "BudgetPayment",
                "fields" => [
                    ["budget_id", "s", $params->budget_id],
                    ["modality_id", "s", "00A000000P"],
                    ["budget_payment_value", "s", $params->budget_payment_value],
                    ["budget_payment_installment", "s", 1],
                    ["budget_payment_entry", "s", "N"],
                    ["budget_payment_credit", "s", "N"],
                    ["budget_payment_deadline", "s", $params->budget_payment_reference],
                    ["budget_payment_date", "s", $params->budget_payment_reference]
                ]
            ]);
        }

        public static function edit($params)
        {
            GLOBAL  $commercial;

            Model::update($commercial,(Object)[
                "table" => "BudgetPayment",
                "fields" => [
                    ["modality_id", "s", $params->modality_id],
                    ["budget_payment_update", "s", date("Y-m-d H:i:s")]
                ],
                "filters" => [["budget_payment_id", "s", "=", $params->budget_payment_id]]
            ]);
        }

        public static function getList($params)
        {
            GLOBAL $conn, $commercial;

            $payments = Model::getList($commercial, (Object)[
                "join" => 1,
                "tables" => [
                    "{$conn->commercial->table}.dbo.BudgetPayment BP",
                    "INNER JOIN {$conn->dafel->table}.dbo.FormaPagamento FP ON FP.IdFormaPagamento = BP.modality_id",
                    "LEFT JOIN {$conn->commercial->table}.dbo.ModalityGroupModality MGM ON MGM.modality_id = FP.IdFormaPagamento",
                    "LEFT JOIN {$conn->commercial->table}.dbo.ModalityGroup MG ON MG.modality_group_id = MGM.modality_group_id",
                    "LEFT JOIN {$conn->dafel->table}.dbo.NaturezaLancamento NL ON NL.IdNaturezaLancamento = FP.IdNaturezaLancamento",
                    "LEFT JOIN {$conn->dafel->table}.dbo.FormaPagamentoItem FPI ON FPI.IdFormaPagamento = FP.IdFormaPagamento AND FPI.CdEmpresa = {$params->CdEmpresa} AND FPI.NrParcelas = BP.budget_payment_installment"
                ],
                "fields" => [
                    "BP.modality_id",
                    "FP.TpCartao",
                    "NL.IdTipoBaixa",
                    "FPI.AlTACEmpresa",
                    "FPI.AlTACConvenio",
                    "FP.IdPessoaConvenio",
                    "FP.DsFormaPagamento",
                    "FP.TpFormaPagamento",
                    "FPI.NrDiasIntervalo",
                    "BP.budget_payment_id",
                    "FPI.NrDiasRecebimento",
                    "FP.IdNaturezaLancamento",
                    "BP.budget_payment_entry",
                    "FPI.NrParcelasRecebimento",
                    "FPI.NrDiasPrimeiraParcelaVenda",
                    "BP.budget_payment_installment",
                    "AlConvenio=CAST(FPI.AlConvenio AS FLOAT)",
                    "budget_payment_value=CAST(BP.budget_payment_value AS FLOAT)",
                    "budget_payment_deadline=FORMAT(BP.budget_payment_deadline, 'yyyy-MM-dd')",
                    "NrDias=DATEDIFF(DAY, '" . date("Y-m-d") . "', BP.budget_payment_deadline)",
                    "MGM.modality_group_id",
                    "MG.modality_group_description",
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