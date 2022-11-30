<?php

    class NFeEmit
    {
        public $CNPJ;
        public $xNome;
        public $xFant;
        public $IE;
        public $CRT;
        public $enderEmit;

        public function __construct($data)
        {
            $this->CNPJ = $data->external->NrCGC;
            $this->xNome = $this->_xNome($data);
            $this->xFant = $this->_xNome($data);
            $this->IE = $this->_IE($data);
            $this->CRT = $this->_CRT($data);
            $this->enderEmit = new NFeEmitEnder($data->external);
        }

        public function _IE($data)
        {
            return str_replace([".","-","/"],["","",""],$data->external->NrInscrEstadual);
        }

        public function _xNome($data)
        {
            return strtoupper(removeSpecialChar($data->external->NmEmpresa));
        }

        public function _CRT($data)
        {
            // 1: Simples Nacional
            // 2: Simples Nacional, excesso sublimite de receita bruta
            // 3: Regime Normal. (v2.0)

            // Parametro vindo do Alterdata
            // NULL: Nenhum Regime especial de tributação [3] Regime normal
            // P: ME EPP - Simples Nacional [1] Simples Nacional

            if(!@$data->external->TpRegimeEspecialTributacao){
                return 3;
            } else if($data->external->TpRegimeEspecialTributacao == "P"){
                return 1;
            } else {
                // FORÇAR REJEIÇÃO
                return 0;
            }
        }

        public function xml()
        {
            $xml = new DOMDocument();
            $xml->preserveWhiteSpace = FALSE;
            $xml->load(PATH_MODEL . "nfe/xml/nfe-emit.xml");

            $xml->getElementsByTagName("CNPJ")[0]->nodeValue = $this->CNPJ;
            $xml->getElementsByTagName("xNome")[0]->nodeValue = $this->xNome;
            $xml->getElementsByTagName("xFant")[0]->nodeValue = $this->xFant;
            $xml->getElementsByTagName("IE")[0]->nodeValue = $this->IE;
            $xml->getElementsByTagName("CRT")[0]->nodeValue = $this->CRT;

            $enderEmit = $this->enderEmit->xml();
            $xml->getElementsByTagName("emit")[0]->insertBefore(
                $xml->importNode($enderEmit->getElementsByTagName("enderEmit")[0], TRUE),
                $xml->getElementsByTagName("IE")[0]
            );

            return $xml;
        }
    }

?>