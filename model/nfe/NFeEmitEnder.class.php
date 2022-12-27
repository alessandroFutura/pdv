<?php

    class NFeEmitEnder
    {
        public $xLgr;
        public $nro;
        public $xBairro;
        public $cMun;
        public $xMun;
        public $UF;
        public $CEP;
        public $cPais;
        public $xPais;
        public $fone;

        public function __construct($data)
        {
            $this->xLgr = $this->_xLgr($data);
            $this->nro = $this->_nro($data);
            $this->xBairro = $this->_xBairro($data);
            $this->cMun = $data->CdIBGE;
            $this->xMun = $this->_xMun($data);
            $this->UF = $data->CdUF;
            $this->CEP = $this->_CEP($data);
            $this->cPais = 1058;
            $this->xPais = "BRASIL";
            $this->fone = $this->_fone($data);
        }

        public function _CEP($data)
        {
            return str_replace("-", "", $data->NrCEP);
        }

        public function _fone($data)
        {
            return str_replace(["(",")","-"," "],["","","",""], $data->NrTelefone);
        }

        public function _nro($data)
        {
            return strtoupper(removeSpecialChar($data->NrLogradouro));
        }

        public function _xBairro($data)
        {
            return strtoupper(removeSpecialChar($data->NmBairro));
        }

        public function _xLgr($data)
        {
            return strtoupper(removeSpecialChar("{$data->TpLogradouro} {$data->DsEndereco}"));
        }

        public function _xMun($data)
        {
            return strtoupper(removeSpecialChar($data->NmCidade));
        }

        public function xml()
        {
            $xml = new DOMDocument();
            $xml->preserveWhiteSpace = FALSE;
            $xml->load(PATH_MODEL . "nfe/xml/nfe-emit-ender.xml");

            $xml->getElementsByTagName("xLgr")->item(0)->nodeValue = $this->xLgr;
            $xml->getElementsByTagName("nro")->item(0)->nodeValue = $this->nro;
            $xml->getElementsByTagName("xBairro")->item(0)->nodeValue = $this->xBairro;
            $xml->getElementsByTagName("cMun")->item(0)->nodeValue = $this->cMun;
            $xml->getElementsByTagName("xMun")->item(0)->nodeValue = $this->xMun;
            $xml->getElementsByTagName("UF")->item(0)->nodeValue = $this->UF;
            $xml->getElementsByTagName("CEP")->item(0)->nodeValue = $this->CEP;
            $xml->getElementsByTagName("cPais")->item(0)->nodeValue = $this->cPais;
            $xml->getElementsByTagName("xPais")->item(0)->nodeValue = $this->xPais;
            $xml->getElementsByTagName("fone")->item(0)->nodeValue = $this->fone;

            return $xml;
        }
    }

?>