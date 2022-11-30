<?php

    class NFeDest
    {
        public $CPF;
        public $CNPJ;
        public $xNome;
        public $indIEDest;
        public $enderEmit;

        public function __construct($data)
        {
            $this->CPF = $this->_CPF($data);
            $this->CNPJ = $this->_CNPJ($data);
            $this->xNome = $this->_xNome($data);
            $this->indIEDest = $this->_indIEDest($data->address);
            $this->enderDest = new NFeDestEnder($data->address);
        }

        public function _indIEDest($data)
        {
            // 1: Contribuinte ICMS (informar a IE do destinatário)
            // 2: Contribuinte isento de Inscrição no cadastro de Contribuintes do ICMS
            // 9: Não Contribuinte, que pode ou não possuir Inscrição Estadual no Cadastro de Contribuintes do ICMS

            return $data->TpContribuicaoICMS;
        }

        public function _CPF($data)
        {
            return $data->TpPessoa == "F" ? str_replace([".","-"], ["",""], $data->CdCPF_CGC) : NULL;
        }

        public function _CNPJ($data)
        {
            return $data->TpPessoa == "J" ? str_replace([".","-"], ["",""], $data->CdCPF_CGC) : NULL;
        }

        public function _xNome($data)
        {
            return TP_AMBIENT == 2 ? "NF-E EMITIDA EM AMBIENTE DE HOMOLOGACAO - SEM VALOR FISCAL" : strtoupper(removeSpecialChar($data->NmPessoa));
        }

        public function xml()
        {
            $xml = new DOMDocument();
            $xml->preserveWhiteSpace = FALSE;
            $xml->load(PATH_MODEL . "nfe/xml/nfe-dest.xml");

            if(@$this->CPF) {
                $xml->getElementsByTagName("CPF")[0]->nodeValue = $this->CPF;
            } else {
                $node = $xml->getElementsByTagName("CPF")[0];
                $node->parentNode->removeChild($node);
            }

            if(@$this->CNPJ){
                $xml->getElementsByTagName("CNPJ")[0]->nodeValue = $this->CNPJ;
            } else {
                $node = $xml->getElementsByTagName("CNPJ")[0];
                $node->parentNode->removeChild($node);
            }

            $xml->getElementsByTagName("xNome")[0]->nodeValue = $this->xNome;

            $enderDest = $this->enderDest->xml();
            $xml->getElementsByTagName("dest")[0]->insertBefore(
                $xml->importNode($enderDest->getElementsByTagName("enderDest")[0],TRUE),
                $xml->getElementsByTagName("indIEDest")[0]
            );

            if(@$this->indIEDest){
                $xml->getElementsByTagName("indIEDest")[0]->nodeValue = $this->indIEDest;
            } else {
                $node = $xml->getElementsByTagName("indIEDest")[0];
                $node->parentNode->removeChild($node);
            }

            if(@$this->IE && $this->IE != "ISENTO"){
                $xml->getElementsByTagName("IE")[0]->nodeValue = $this->IE;
            } else {
                $node = $xml->getElementsByTagName("IE")[0];
                $node->parentNode->removeChild($node);
            }

            if(@$this->email) {
                $xml->getElementsByTagName("email")[0]->nodeValue = $this->email;
            } else {
                $node = $xml->getElementsByTagName("email")[0];
                $node->parentNode->removeChild($node);
            }

            return $xml;
        }
    }

?>