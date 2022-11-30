<?php

    class NFeIde
    {
        public $cUF;
        public $cNF;
        public $natOp;
        public $mod;
        public $serie;
        public $nNF;
        public $dhEmi;
        public $dhSaiEnt;
        public $tpNF;
        public $idDest;
        public $cMunFG;
        public $tpImp;
        public $tpEmis;
        public $cDV;
        public $tpAmb;
        public $finNFe;
        public $indFinal;
        public $indPres;
        public $procEmi;
        public $verProc;
        public $refNFe;

        public function __construct($data)
        {
            $this->cUF = $this->_cUF($data);
            $this->cNF = $data->cNF;
            $this->natOp = $this->_natOp();
            $this->mod = $data->mod;
            $this->serie = $data->serie;
            $this->nNF = $data->nNF;
            $this->dhEmi = $data->dhEmi;
            $this->dhSaiEnt = $data->dhSaiEnt;
            $this->tpNF = $this->_tpNF();
            $this->idDest = $this->_idDest();
            $this->cMunFG = $data->company->external->CdIBGE;
            $this->tpImp = $this->_tpImp($data);
            $this->tpEmis = $data->tpEmis;
            $this->cDV = $data->cDV;
            $this->tpAmb = $data->tpAmb;
            $this->finNFe = $this->_finNFe();;
            $this->indFinal = $this->_indFinal();
            $this->indPres = $this->_indPres();
            $this->procEmi = $this->_procEmi();
            $this->verProc = VERSION;
            $this->refNFe = $this->_refNFe();
        }

        public function _cUF($data)
        {
            // Abreviação de Unidade Federativa,
            // onde cada estado Brasileiro tem seu código de identificação.

            return substr($data->company->external->CdIBGE,0,2);
        }

        public function _idDest()
        {
            // 1: MODELO 65 OU UF DA ENTREGA IGUAL AO DO EMISSOR
            // 2: MODELO 55 E UF DA ENTREGA DIFERENTE AO DO EMISSOR

            return 1;
        }

        public function _indFinal()
        {
            // 0: Se a Operação ocorrer com Consumidor Normal
            // 1: Se a Operação ocorrer com Consumidor Final

            return 1;
        }

        public function _indPres()
        {
            // 0: Não se aplica (por exemplo, Nota Fiscal complementar ou de ajuste)
            // 1: Operação presencial
            // 2: Operação não presencial, pela Internet
            // 3: Operação não presencial, tele atendimento
            // 4: NFC-e em operação com entrega a domicílio
            // 5: Operação presencial, fora do estabelecimento
            // 9: Operação não presencial, outros

            return 1;
        }

        public function _finNFe()
        {
            // 1: NF-e normal
            // 2: NF-e complementar
            // 3: NF-e de ajuste
            // 4: Devolução de mercadoria

            return 1;
        }

        public function _natOp()
        {
            // Descrição da Natureza da Operação da NF

            return "VENDA";
        }

        public function _procEmi()
        {
            // 0: Emissão de NF-e com aplicativo do contribuinte.
            // 1: Emissão de NF-e avulsa pelo Fisco.
            // 2: Emissão de NF-e avulsa, pelo contribuinte com seu certificado digital, através do site do Fisco.
            // 3: Emissão NF-e pelo contribuinte com aplicativo fornecido pelo Fisco.

            return 0;
        }

        public function _refNFe()
        {
            // Chave de acesso da NF-e referenciada da NF-e.
            // Esse campo deve ser preenchido com uma chave de
            // NF-e (modelo 55) ou uma chave de NFC-e (modelo 65).

            return  NULL;
        }

        public function _tpImp($data)
        {
            // 0 = Sem geração de DANFE
            // 1 = DANFE normal, Retrato
            // 2 = DANFE normal, Paisagem
            // 3 = DANFE Simplificado
            // 4 - DANFE NFC-e
            // 5 - DANFE NFC-e em mensagem eletrônica

            return $data->mod == 55 ? 1 : 4;
        }

        public function _tpNF()
        {
            // 0: Entrada
            // 1: Saída

            return 1;
        }

        public function xml()
        {
            $xml = new DOMDocument();
            $xml->preserveWhiteSpace = FALSE;
            $xml->load(PATH_MODEL . "nfe/xml/nfe-ide.xml");

            $xml->getElementsByTagName("cUF")[0]->nodeValue = $this->cUF;
            $xml->getElementsByTagName("cNF")[0]->nodeValue = $this->cNF;
            $xml->getElementsByTagName("natOp")[0]->nodeValue = $this->natOp;
            $xml->getElementsByTagName("mod")[0]->nodeValue = $this->mod;
            $xml->getElementsByTagName("serie")[0]->nodeValue = $this->serie;
            $xml->getElementsByTagName("nNF")[0]->nodeValue = $this->nNF;
            $xml->getElementsByTagName("dhEmi")[0]->nodeValue = $this->dhEmi;
            $xml->getElementsByTagName("dhSaiEnt")[0]->nodeValue = $this->dhSaiEnt;
            $xml->getElementsByTagName("tpNF")[0]->nodeValue = $this->tpNF;
            $xml->getElementsByTagName("idDest")[0]->nodeValue = $this->idDest;
            $xml->getElementsByTagName("cMunFG")[0]->nodeValue = $this->cMunFG;
            $xml->getElementsByTagName("tpImp")[0]->nodeValue = $this->tpImp;
            $xml->getElementsByTagName("tpEmis")[0]->nodeValue = $this->tpEmis;
            $xml->getElementsByTagName("cDV")[0]->nodeValue = $this->cDV;
            $xml->getElementsByTagName("tpAmb")[0]->nodeValue = $this->tpAmb;
            $xml->getElementsByTagName("finNFe")[0]->nodeValue = $this->finNFe;
            $xml->getElementsByTagName("indFinal")[0]->nodeValue = $this->indFinal;
            $xml->getElementsByTagName("indPres")[0]->nodeValue = $this->indPres;
            $xml->getElementsByTagName("procEmi")[0]->nodeValue = $this->procEmi;
            $xml->getElementsByTagName("verProc")[0]->nodeValue = $this->verProc;

            if($this->mod == 65){
                $node = $xml->getElementsByTagName("dhSaiEnt")[0];
                $node->parentNode->removeChild($node);
            }

            $node = $xml->getElementsByTagName("NFref")[0];
            if($this->finNFe == 4 && @$this->refNFe){
                $node->getElementsByTagName("refNFe")[0]->nodeValue = $this->refNFe;
            } else {
                $node->parentNode->removeChild($node);
            }

            return $xml;
        }
    }

?>