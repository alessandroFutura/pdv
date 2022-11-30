<?php

    class NFeInfCpl
    {
        public $mod;
        public $CRT;
        public $TpOperacao;
        public $DsObservacao;
        public $vFedTrib;
        public $vEstTrib;
        public $vMunTrib;
        public $vTotTrib;
        public $vCredICMSSN;
        public $AlCreditoICMSSN;
        public $AlFCP;
        public $VlFCP;

        public function __construct($data)
        {
            $this->mod = $data->mod;
            $this->CRT = $data->CRT;
            $this->TpOperacao = $data->TpOperacao;
            $this->DsObservacao = $data->DsObservacao;
            $this->vFedTrib = $data->vFedTrib;
            $this->vEstTrib = $data->vEstTrib;
            $this->vMunTrib = $data->vMunTrib;
            $this->vTotTrib = $data->vTotTrib;
            $this->vCredICMSSN = $data->vCredICMSSN;
            $this->AlCreditoICMSSN = $data->AlCreditoICMSSN;
            $this->AlFCP = $data->AlFCP;
            $this->VlFCP = $data->VlFCP;
        }
        
        public function xml()
        {
            $xml = new DOMDocument();
            $xml->preserveWhiteSpace = FALSE;
            $xml->load(PATH_MODEL . "nfe/xml/nfe-infAdic.xml");

            if($this->mod == 65){
                $xml->getElementsByTagName("infAdFisco")[0]->nodeValue = $this->_infAdFisco();
            } else {

            }

            $xml->getElementsByTagName("infCpl")[0]->nodeValue = implode(" ", $this->_infCpl());

            return $xml;
        }

        public function _infAdFisco()
        {
            if($this->CRT == 1){
                return "NAO INCIDE FECP";
            } else {
                return "AL. FCP: " . number_format($this->AlFCP,2,".","") . "% VL. FCP: " . number_format($this->VlFCP,2,".","");
            }
        }

        public function _infCpl()
        {
            $messages = [];

            if(@$this->DsObservacao){
                $messages[] = "{$this->DsObservacao}.";
            }

            if($this->mod == 55) {
                if($this->CRT == 1){
                    $messages[] = "DOCUMENTO EMITIDO POR ME OU EPP OPTANTE PELO SIMPLES NACIONAL.";
                    $messages[] = "NAO GERA DIREITO A CREDITO FISCAL DE IPI.";
                }
            } else {
                $messages[] = "INFORMACOES ADICIONAIS DO INTERESSE DO CONTRIBUINTE PROCON AV. RIO BRANCO,25 CENTRO RJ.TEL:151 ALERJ RUA DA ALFANDEGA,8 TEL. 0800 2827060";
            }
            if($this->vCredICMSSN > 0){
                $messages[] = "PERMITE O APROVEITAMENTO DO CREDITO DE ICMS NO VALOR DE R$ " . number_format($this->vCredICMSSN, 2, ",", ".") . ", CORRESPONDENTE A ALIQUOTA DE " . number_format($this->AlCreditoICMSSN, 2, ",", ".") . "%, NOS TERMOS DO ART. 23 DA LC 123/2006.";
            }
            if($this->TpOperacao == "V"){
                $messages[] = "VOCE PAGOU APROXIMADAMENTE R$ " . number_format($this->vFedTrib, 2, ",", ".") . " DE TRIBUTOS FEDERAIS, R$ " . number_format($this->vEstTrib, 2, ",", ".") . " DE TRIBUTOS ESTADUAIS E R$ " . number_format($this->vMunTrib, 2, ",", ".") . " DE TRIBUTOS MUNICIPAIS. FONTE : IBPT";
            }

            return $messages;
        }

    }    

?>