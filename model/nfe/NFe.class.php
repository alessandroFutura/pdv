<?php

    class NFe
    {
        public $mod=65;
        public $type;

        public $serie;
        public $versao;
        public $tpAmb;
        public $path;

        public $nNF;
        public $CNPJ;
        public $cMun;

        public $cNF;
        public $dhEmi;
        public $chNFe;
        public $dhSaiEnt;
        public $idLote;
        public $digVal;

        public $det;
        public $ide;
        public $Signature;

        public function __construct($data)
        {
            $this->mod = 65;
            $this->type = "nfce";

            $this->serie = $data->serie->serie_id;
            $this->versao = XML_VERSION;
            $this->tpAmb = TP_AMBIENT;
            $this->tpEmis = TP_EMISSAO;
            $this->path = PATH_XML . "{$data->company->company_id}/";

            $this->nNF = @$data->nfe ? $data->nfe->nNF : $data->serie->terminal_company_number;
            $this->CNPJ = $data->company->external->NrCGC;
            $this->cMunFG = $data->company->external->CdIBGE;

            $this->dhEmi = $this->_dhEmi();
            $this->dhSaiEnt = $this->_dhEmi();
            $this->cNF = @$data->nfe ? $data->nfe->cNF : $this->_cNF();
            $this->chNFe = @$data->nfe ? $data->nfe->chNFe : $this->_chNFe();
            $this->idLote = @$data->nfe ? $data->nfe->idLote : $this->_idLote();
            $this->cDV = $this->_cDV();

            $data->cNF = $this->cNF;
            $data->mod = $this->mod;
            $data->serie = $this->serie;
            $data->nNF = $this->nNF;
            $data->dhEmi = $this->dhEmi;
            $data->dhSaiEnt = $this->dhSaiEnt;
            $data->cMunFG = $this->cMun;
            $data->tpEmis = $this->tpEmis;
            $data->tpAmb = $this->tpAmb;
            $data->chNFe = $this->chNFe;
            $data->cDV = $this->cDV;

            $this->ide = new NFeIde($data);
            $this->emit = new NFeEmit($data->company);
            $this->dest = !$data->person->StConsumidor ? new NFeDest($data->person) : NULL;
            $this->total = new NFeTotal($data);

            $this->det = [];
            foreach($data->items as $key => $item){

                $item->nItem = $key+1;
                $item->mod = $this->mod;
                $item->TpOperacao = $data->TpOperacao;
                $item->CSOSNEmpresa = $data->company->external->CSOSNEmpresa;
                $item->CSOSNEmpresaPDV = $data->company->external->CSOSNEmpresaPDV;

                $det = new NFeDet($item);

                if($this->mod == 55 && in_array($det->imposto->ICMS->CSOSN, ["101","201"])){
                    $item->pCredSN = $data->company->external->AlCreditoICMSSN;
                    $item->vCredICMSSN = round($item->pCredSN * $item->budget_item_value_total / 100, 2);
                    $this->total->vCredICMSSN += $item->vCredICMSSN;
                }

                $this->total->vFedTrib += $det->imposto->vFedTrib;
                $this->total->vEstTrib += $det->imposto->vEstTrib;
                $this->total->vMunTrib += $det->imposto->vMunTrib;
                $this->total->vTotTrib += $det->imposto->vTotTrib;

                $this->det[] = $det;
            }

            $this->transp = new NFeTransp($data);
            $this->pag = new NFePag($data);
            //$this->cobr = $data->operation->operation_type_code == "V" ? new NFeCobr($data) : NULL;
            $this->infCpl = new NFeInfCpl((Object)[
                "mod" => $this->mod,
                "CRT" => $this->emit->CRT,
                "TpOperacao" => $data->TpOperacao,
                "DsObservacao" => NULL,
                "vFedTrib" => $this->total->vFedTrib,
                "vEstTrib" => $this->total->vEstTrib,
                "vMunTrib" => $this->total->vMunTrib,
                "vTotTrib" => $this->total->vTotTrib,
                "AlCreditoICMSSN" => $data->company->external->AlCreditoICMSSN,
                "vCredICMSSN" => $this->total->vCredICMSSN,
                "AlFCP" => $data->company->external->AlFCP,
                "VlFCP" => $this->total->vFCP
            ]);

        }

        public function _cDV()
        {
            return (int)substr($this->chNFe, -1);
        }

        public function _cNF()
        {
            return rand(10000000, 99999999);
        }

        public function _dhEmi()
        {
            return str_replace(' ', 'T', date('Y-m-d H:i:sP'));
        }

        public function _chNFe()
        {
            $chNFe = sprintf(
                "%02d%02d%02d%s%02d%03d%09d%01d%08d",
                substr($this->cMunFG,0,2),
                substr($this->dhEmi,2,2),
                substr($this->dhEmi,5,2),
                $this->CNPJ,
                $this->mod,
                $this->serie,
                $this->nNF,
                $this->tpEmis,
                $this->cNF
            );

            $iCount = 42;
            $somaPonderada = 0;
            $multiplicadores = array(2, 3, 4, 5, 6, 7, 8, 9);

            while($iCount >= 0){
                for($mCount = 0; $mCount < count($multiplicadores) && $iCount >= 0; $mCount++){
                    $num = (int) substr($chNFe, $iCount, 1);
                    $peso = (int) $multiplicadores[$mCount];
                    $somaPonderada += $num * $peso;
                    $iCount--;
                }
            }

            $resto = $somaPonderada % 11;
            if($resto == '0' || $resto == '1'){
                $this->cDV = 0;
            } else{
                $this->cDV = 11 - $resto;
            }

            return "{$chNFe}{$this->cDV}";
        }

        public function _idLote()
        {
            return substr(str_replace(',', '', number_format(microtime(true)*1000000, 0)), 0, 15);
        }

        public static function authorize($params)
        {
            $date = date("Y/F/d");

            $priKeyPath = PATH_CERTIFICATES . "{$params->certificate_id}/priKEY.pem";
            $certKeyPath = PATH_CERTIFICATES . "{$params->certificate_id}/certKEY.pem";

            $aXml = file_get_contents("{$params->path}assinadas/{$date}/{$params->file}");

            $sxml = $aXml;
            $sxml = preg_replace("/<\?xml.*\?>/", "", $sxml);

            $urlNamespace = sprintf("%s/wsdl/%s", $params->urlPortal, $params->urlOperation);

            $soap = new Soap();
            $soap->method = $params->urlMethod;
            $soap->urlService = $params->urlService;
            $soap->header($urlNamespace, 33, $params->versao);
            $soap->cons($params->urlPortal, $params->versao, $params->idLote, $sxml, $params->event);
            $soap->body($urlNamespace);
            $soap->prepareData();
            $soap->parametros();
            $soap->exec($certKeyPath, $priKeyPath);

            if(empty($soap->resposta)){
                $pathLog =  PATH_LOG . "soap/" . date("Y/F/d") . "/";
                if( !is_dir($pathLog) ) mkdir($pathLog, 0755, true);
                file_put_contents("{$pathLog}{$params->chNFe}-data.xml" , $soap->data);
                file_put_contents("{$pathLog}{$params->chNFe}-infoCurl.json" , $soap->infoCurl);
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Não houve uma resposta da Sefaz.<br/>Erro: {$soap->errorCurl}"
                ]);
            }

            $xPos = stripos($soap->resposta, "<");
            $txt = substr($soap->resposta, 0, $xPos);
            if($soap->infoCurl["http_code"] != "200"){
                $pathLog =  PATH_LOG . "soap/" . date("Y/F/d") . "/";
                if( !is_dir($pathLog) ) mkdir($pathLog, 0755, true);
                file_put_contents("{$pathLog}{$params->chNFe}-data.xml" , $soap->data);
                file_put_contents("{$pathLog}{$params->chNFe}-resposta.txt",$txt);
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "A nota não foi autorizada pela Sefaz."
                ]);
            }

            $lenresp = strlen($soap->resposta);
            $xPos = stripos($soap->resposta, "<");

            $xml = "";
            if($xPos !== false){
                $xml = substr($soap->resposta, $xPos, $lenresp-$xPos);
            }

            $result = simplexml_load_string($xml, 'SimpleXmlElement', LIBXML_NOERROR + LIBXML_ERR_FATAL+LIBXML_ERR_NONE);
            if($result === false){
                $xml = "";
            }
            if($xml == ""){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Não houve uma resposta válido da Sefaz. Tente novamente mais tarde."
                ]);
            }
            if($xml != '' && substr($xml, 0, 5) != '<?xml'){
                $xml = '<?xml version="1.0" encoding="utf-8"?>' . $xml;
            }

            $path = "{$params->path}/retorno/{$date}/";
            if( !is_dir($path) ) mkdir($path, 0755, true);

            file_put_contents("{$path}/{$params->output}", $xml);

        }

        public static function event($params)
        {
            $date = date("Y/F/d");

            $xml = new DOMDocument();
            $xml->load("{$params->path}retorno/{$date}/{$params->file}");

            $retEvento  = $xml->getElementsByTagName("retEvento")[0];

            return (Object)[
                "xMotivo" => str_replace("Rejeicao: ", "", $xml->getElementsByTagName("xMotivo")[0]->nodeValue),
                "cStat" => $xml->getElementsByTagName("cStat")[0]->nodeValue,
                "retEvento" => (Object)[
                    "xMotivo" => @$retEvento ? str_replace("Rejeicao: ", "", $retEvento->getElementsByTagName("xMotivo")[0]->nodeValue) : NULL,
                    "cStat" => @$retEvento ? $retEvento->getElementsByTagName("cStat")[0]->nodeValue : NULL,
                ]
            ];
        }

        public static function qrCode($params)
        {
            $ver = 2;
            $url = "http://www4.fazenda.rj.gov.br/consultaNFCe/QRCode";
            $cscId = (int)$params->token_code;
            $csc = $params->token_value;

            if(strpos($url, '?p=') === false){
                $url = $url.'?p=';
            }

            $seq = "$params->chNFe|$ver|$params->tpAmb|$cscId";
            $hash = strtoupper(sha1($seq.$csc));

            return "$url$seq|$hash";
        }

        public static function ret($params)
        {
            $date = date("Y/F/d");

            $xml = new DOMDocument();
            $xml->load("{$params->path}retorno/{$date}/{$params->chNFe}-{$params->type}-ret.xml");

            $protNFeNode = $xml->getElementsByTagName("protNFe")[0];
            $protNFe = (Object)[
                "tpAmb" => NULL,
                "verAplic" => NULL,
                "chNFe" => NULL,
                "dhRecbto" => NULL,
                "nProt" => NULL,
                "digVal" => NULL,
                "cStat" => NULL,
                "xMotivo" => NULL,
            ];

            if(@$protNFeNode){
                $tpAmbNode = $protNFeNode->getElementsByTagName("tpAmb")[0];
                $verAplicNode = $protNFeNode->getElementsByTagName("verAplic")[0];
                $chNFeNode = $protNFeNode->getElementsByTagName("chNFe")[0];
                $dhRecbtoNode = $protNFeNode->getElementsByTagName("dhRecbto")[0];
                $nProtNode = $protNFeNode->getElementsByTagName("nProt")[0];
                $digValNode = $protNFeNode->getElementsByTagName("digVal")[0];
                $cStatNode = $protNFeNode->getElementsByTagName("cStat")[0];
                $xMotivoNode = $protNFeNode->getElementsByTagName("xMotivo")[0];
                $protNFe = (Object)[
                    "tpAmb" => @$tpAmbNode ? $tpAmbNode->nodeValue : NULL,
                    "verAplic" => @$verAplicNode ? $verAplicNode->nodeValue : NULL,
                    "chNFe" => @$chNFeNode ? $chNFeNode->nodeValue : NULL,
                    "dhRecbto" => @$dhRecbtoNode ? $dhRecbtoNode->nodeValue : NULL,
                    "nProt" => @$nProtNode ? $nProtNode->nodeValue : NULL,
                    "digVal" => @$digValNode ? $digValNode->nodeValue : NULL,
                    "cStat" => @$cStatNode ? $cStatNode->nodeValue : NULL,
                    "xMotivo" => @$xMotivoNode ? str_replace("Rejeicao: ","",$xMotivoNode->nodeValue) : NULL,
                ];
                if(@$cStatNode && $cStatNode->nodeValue == "100"){

                    $ret = new DOMDocument();
                    $ret->preserveWhiteSpace = FALSE;
                    $ret->load(PATH_MODEL . "nfe/xml/nfe-proc.xml");

                    $sign = new DOMDocument();
                    $sign->preserveWhiteSpace = FALSE;
                    $sign->load("{$params->path}assinadas/{$date}/{$params->chNFe}-{$params->type}-sign.xml");

                    $SignatureNode = $sign->getElementsByTagName("Signature")[0];
                    $SignatureNode->parentNode->removeChild($SignatureNode);

                    $ret->getElementsByTagName("nfeProc")[0]->appendChild(
                        $ret->importNode($sign->getElementsByTagName("NFe")[0],TRUE)
                    );

                    $ret->getElementsByTagName("NFe")[0]->appendChild(
                        $ret->importNode($SignatureNode,TRUE)
                    );

                    $ret->getElementsByTagName("nfeProc")[0]->appendChild(
                        $ret->importNode($protNFeNode,TRUE)
                    );

                    $ret->getElementsByTagName("NFe")[0]->setAttribute("xmlns", "http://www.portalfiscal.inf.br/nfe");
                    $ret->getElementsByTagName("protNFe")[0]->setAttribute("xmlns", "http://www.portalfiscal.inf.br/nfe");

                    $path = "{$params->path}/autorizadas/{$date}/";
                    if( !is_dir($path) ) mkdir($path, 0755, true);

                    $ret->save("{$path}/{$params->chNFe}-ret.xml");
                }
            }

            return (Object)[
                "tpAmb" => $xml->getElementsByTagName("tpAmb")[0]->nodeValue,
                "verAplic" => $xml->getElementsByTagName("verAplic")[0]->nodeValue,
                "cStat" => $xml->getElementsByTagName("cStat")[0]->nodeValue,
                "xMotivo" => $xml->getElementsByTagName("xMotivo")[0]->nodeValue,
                "dhRecbto" => $xml->getElementsByTagName("dhRecbto")[0]->nodeValue,
                "protNFe" => $protNFe
            ];
        }

        public static function sign($params)
        {
            $password = file_get_contents(PATH_CERTIFICATES . "{$params->certificate_id}/pass.txt");
            $certStore = file_get_contents(PATH_CERTIFICATES . "{$params->certificate_id}/cert.pfx");
            $status = openssl_pkcs12_read($certStore, $certInfo, $password);

            if(!$status){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Não foi possível ler o certificado digital."
                ]);
            }

            $objSSLPriKey = openssl_get_privatekey($certInfo['pkey']);

            if(!$objSSLPriKey) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Não foi possível ler a chave privada do certificado digital."
                ]);
            }

            $date = date("Y/F/d");
            $xml = new DOMDocument();
            $xml->preserveWhiteSpace = FALSE;
            $xml->load("{$params->path}backup/{$date}/{$params->file}");

            $node = $xml->getElementsByTagName($params->node)[0];
            $dados = $node->C14N(true, false, null, null);

            $DigestValue = NULL;
            $SignatureValue = NULL;
            $X509Certificate = NULL;

            $hashValue = hash('sha1', $dados, true);
            $DigestValue = base64_encode($hashValue);

            $qrCode = NULL;
            if($params->mod == 65 && $params->operation_type_code == "V"){
                $qrCode = NFe::qrCode((Object)[
                    "chNFe" => $params->chNFe,
                    "tpAmb" => $params->tpAmb,
                    "token_code" => $params->token_code,
                    "token_value" => $params->token_value
                ]);
                $NFeNode = $xml->getElementsByTagName("NFe")[0];
                $infNFeSupl = $xml->createElement("infNFeSupl");
                $NFeNode->appendChild($infNFeSupl);
                $nodeqr = $infNFeSupl->appendChild($xml->createElement("qrCode"));
                $nodeqr->appendChild($xml->createCDATASection($qrCode));
                $urlChave = $xml->createElement("urlChave", "www.fazenda.rj.gov.br/nfce/consulta");
                $infNFeSupl->appendChild($urlChave);

                include PATH_CLASS . "phpqrcode/qrlib.php";
                $path =  PATH_PRODUCTION_FILES . "qrcode/{$params->company_id}/" . date("Y/F/d/");
                if(!is_dir($path)) mkdir($path, 0755, true);
                QRCode::png($qrCode,"{$path}{$params->chNFe}.png");
            }

            $signature = new DOMDocument();
            $signature->preserveWhiteSpace = FALSE;
            $signature->load(PATH_MODEL . "nfe/xml/nfe-signature.xml");

            $signature->getElementsByTagName("Reference")[0]->setAttribute("URI", $params->URI);
            $signature->getElementsByTagName("DigestValue")[0]->nodeValue = $DigestValue;

            $dataSignature = '';
            $signedInfoNode = $signature->getElementsByTagName("SignedInfo")[0];

            $cnSignedInfoNode = $signedInfoNode->C14N(true, false, null, null);
            if(!openssl_sign($cnSignedInfoNode, $dataSignature, $objSSLPriKey)){
                die("Houve erro durante a assinatura digital");
            }

            $SignatureValue = base64_encode($dataSignature);
            $signature->getElementsByTagName("SignatureValue")[0]->nodeValue = $SignatureValue;

            $data = "";
            $pubKey = file_get_contents(PATH_CERTIFICATES . "{$params->certificate_id}/pubKEY.pem");
            $arCert = explode("\n", $pubKey);
            foreach($arCert as $curData){
                if(
                    strncmp($curData, '-----BEGIN CERTIFICATE', 22) != 0 &&
                    strncmp($curData, '-----END CERTIFICATE', 20) != 0
                ){
                    $data .= trim($curData);
                }
            }

            $X509Certificate = $data;
            $signature->getElementsByTagName("X509Certificate")[0]->nodeValue = $X509Certificate;

            $xml->getElementsByTagName($params->appendNode)[0]->appendChild(
                $xml->importNode($signature->getElementsByTagName("Signature")[0],TRUE)
            );

            $path = "{$params->path}/assinadas/{$date}/";
            if(!is_dir($path)) mkdir($path, 0755, true);

            $xml->save("{$path}/{$params->output}");

            return $DigestValue;

        }

        public function validate()
        {
            if(@$this->dest && !@$this->dest->indIEDest){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "A pessoa <b>{$this->dest->xNome}</b> não possui a informação do tipo de contribuição do ICMS. Verifique!"
                ]);
            }
            if(@$this->dest && (!@$this->dest->CPF && !@$this->dest->CNPJ)){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "A pessoa <b>{$this->dest->xNome}</b> não possui o documento informado. Verifique!"
                ]);
            }
            foreach($this->det as $det){
                if(!@$det->prod->NCM){
                    headerResponse((Object)[
                        "code" => 417,
                        "message" => "O produto <b>{$det->prod->cProd} - {$det->prod->xProd}</b> não possui um NCM cadastrado. Verifique!"
                    ]);
                }
                if(is_null($det->imposto->ICMS->orig)){
                    headerResponse((Object)[
                        "code" => 417,
                        "message" => "O produto <b>{$det->prod->cProd} - {$det->prod->xProd}</b> não possui uma origem tributaria cadastrada. Verifique!"
                    ]);
                }
                if(!@$det->imposto->ICMS->CSOSN){
                    headerResponse((Object)[
                        "code" => 417,
                        "message" => "O item <b>{$det->prod->cProd} - {$det->prod->xProd}</b> não possui um CSOSN informado. Verifique!"
                    ]);
                }
                if($this->mod == 65 && !in_array($det->imposto->ICMS->CSOSN,["102","103","300","400","500","900"])){
                    headerResponse((Object)[
                        "code" => 417,
                        "message" => implode("<br/>",[
                            "O item <b>{$det->prod->cProd} - {$det->prod->xProd}</b> possui o CSOSN <b>{$det->imposto->ICMS->CSOSN}</b> não permitido para a NFC-e.<br/>",
                            "CSOSNs permitidos:",
                            "<b>102</b> - Tributada pelo Simples Nacional sem permissão de crédito",
                            "<b>103</b> - Isenção do ICMS no Simples Nacional para faixa de receita bruta",
                            "<b>300</b> - Imune",
                            "<b>400</b> - Não tributada pelo Simples Nacional",
                            "<b>500</b> - ICMS cobrado anteriormente por substituição tributária (substituído) ou por antecipação",
                            "<b>900</b> - Outros (a critério da UF)",
                        ])
                    ]);
                }
            }
        }

        public function xml()
        {
            $xml = new DOMDocument();
            $xml->preserveWhiteSpace = FALSE;
            $xml->load(PATH_MODEL . "nfe/xml/nfe.xml");

            $xml->getElementsByTagName("infNFe")[0]->setAttribute("versao", $this->versao);
            $xml->getElementsByTagName("infNFe")[0]->setAttribute("Id", "NFe{$this->chNFe}");

            $ide = $this->ide->xml();
            $xml->getElementsByTagName("infNFe")[0]->appendChild(
                $xml->importNode($ide->getElementsByTagName("ide")[0],TRUE)
            );

            $emit = $this->emit->xml();
            $xml->getElementsByTagName("infNFe")[0]->appendChild(
                $xml->importNode($emit->getElementsByTagName("emit")[0],TRUE)
            );

            if(@$this->dest){
                $dest = $this->dest->xml();
                $xml->getElementsByTagName("infNFe")[0]->appendChild(
                    $xml->importNode($dest->getElementsByTagName("dest")[0],TRUE)
                );
            }

            foreach($this->det as $det){
                $item = $det->xml();
                $xml->getElementsByTagName("infNFe")[0]->appendChild(
                    $xml->importNode($item->getElementsByTagName("det")[0],TRUE)
                );
            }

            $total = $this->total->xml();
            $xml->getElementsByTagName("infNFe")[0]->appendChild(
                $xml->importNode($total->getElementsByTagName("total")[0],TRUE)
            );

            $transp = $this->transp->xml();
            $xml->getElementsByTagName("infNFe")[0]->appendChild(
                $xml->importNode($transp->getElementsByTagName("transp")[0],TRUE)
            );

            if(@$this->cobr && @$this->cobr->cobr){
                $cobr = $this->cobr->xml();
                $xml->getElementsByTagName("infNFe")[0]->appendChild(
                    $xml->importNode($cobr->getElementsByTagName("cobr")[0],TRUE)
                );
            }

            $pag = $this->pag->xml();
            $xml->getElementsByTagName("infNFe")[0]->appendChild(
                $xml->importNode($pag->getElementsByTagName("pag")[0],TRUE)
            );

            $infCpl = $this->infCpl->xml();
            $xml->getElementsByTagName("infNFe")[0]->appendChild(
                $xml->importNode($infCpl->getElementsByTagName("infAdic")[0],TRUE)
            );

            $date = date("Y/F/d");
            $path = "{$this->path}backup/{$date}/";
            if( !is_dir($path) ) mkdir($path, 0755, true);
            $xml->save("{$path}/{$this->chNFe}-{$this->type}.xml");
        }

        public static function xmlCancel($params)
        {
            $xml = new DOMDocument();
            $xml->preserveWhiteSpace = FALSE;
            $xml->load(PATH_MODEL . "nfe/xml/nfe-cancelamento.xml");

            $dhEmi = str_replace(' ', 'T', date('Y-m-d H:i:sP'));

            $xml->getElementsByTagName("infEvento")[0]->setAttribute("Id", "ID110111{$params->chNFe}01");
            $xml->getElementsByTagName("tpAmb")[0]->nodeValue = $params->tpAmb;
            $xml->getElementsByTagName("CNPJ")[0]->nodeValue = $params->CNPJ;
            $xml->getElementsByTagName("chNFe")[0]->nodeValue = $params->chNFe;
            $xml->getElementsByTagName("dhEvento")[0]->nodeValue = $dhEmi;
            $xml->getElementsByTagName("nProt")[0]->nodeValue = $params->nProt;

            $date = date("Y/F/d");
            $path = "{$params->path}/backup/{$date}/";
            if(!is_dir($path)) mkdir($path, 0755, true);

            $xml->save("{$path}/110111{$params->chNFe}01-can.xml");
        }
    }

?>