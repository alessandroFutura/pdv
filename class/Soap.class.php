<?php

    class Soap
    {
        public $port;
        public $soapTimeout = 10;
        public $sslProtocol = 0;

        public $urlHeader;
        public $urlService;

        public $cons;
        public $body;
        public $data;
        public $parametros;

        public $method;
        public $lastMsg;
        public $tamanho;

        public $resposta;
        public $infoCurl;
        public $txtInfo;
        public $soapDebug;
        public $errorCurl;

        public function __construct()
        {
            $this->port = 443;
            $this->soapTimeout = 30;
            $this->sslProtocol = 0;
        }

        public function body($urlNamespace)
        {
            $this->body = "<nfeDadosMsg xmlns=\"{$urlNamespace}\">{$this->cons}</nfeDadosMsg>";
        }

        public function cons($urlPortal, $versao, $idLote, $sxml, $event)
        {
            if($event == "NFe") {
                $this->cons = "<enviNFe xmlns=\"{$urlPortal}\" versao=\"{$versao}\">"
                    . "<idLote>{$idLote}</idLote>"
                    . "<indSinc>1</indSinc>"
                    . "{$sxml}"
                . "</enviNFe>";
            }
            if($event == "Evento"){
                $this->cons = "<envEvento xmlns=\"{$urlPortal}\" versao=\"{$versao}\">"
                    . "<idLote>{$idLote}</idLote>"
                    . "{$sxml}"
                . "</envEvento>";
            }
        }

        public function header($urlNamespace, $cUF, $versaoDados)
        {
            $this->urlHeader = "<nfeCabecMsg xmlns=\"{$urlNamespace}\">"
                . "<cUF>{$cUF}</cUF>"
                . "<versaoDados>{$versaoDados}</versaoDados>"
            . "</nfeCabecMsg>";
        }

        public function parametros()
        {
            $this->parametros = [
                'Content-Type: application/soap+xml;charset=utf-8',
                'SOAPAction: "' . $this->method . '"',
                "Content-length: {$this->tamanho}"
            ];
        }

        public function prepareData()
        {
            $this->data = '<?xml version="1.0" encoding="utf-8"?>'.'<soap12:Envelope ';
            $this->data .= 'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ';
            $this->data .= 'xmlns:xsd="http://www.w3.org/2001/XMLSchema" ';
            $this->data .= 'xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">';
            $this->data .= '<soap12:Header>' . $this->urlHeader . '</soap12:Header>';
            $this->data .= '<soap12:Body>' . $this->body . '</soap12:Body>';
            $this->data .= '</soap12:Envelope>';

            $this->data = str_replace(array(' standalone="no"','default:',':default',"\n","\r","\t"), '', $this->data);
            $this->data = str_replace('> ', '>', $this->data);

            if(strpos($this->data, '> ')){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Mensagem de retorno inválida"
                ]);
            }

            $this->lastMsg = $this->data;
            $this->tamanho = strlen($this->data);
        }

        public function exec($certKeyPath,$priKeyPath)
        {
            $oCurl = curl_init();

            curl_setopt($oCurl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            curl_setopt($oCurl, CURLOPT_CONNECTTIMEOUT, $this->soapTimeout);
            curl_setopt($oCurl, CURLOPT_TIMEOUT, $this->soapTimeout * 6);
            curl_setopt($oCurl, CURLOPT_URL, $this->urlService);
            curl_setopt($oCurl, CURLOPT_VERBOSE, 1);
            curl_setopt($oCurl, CURLOPT_HEADER, 1);

            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, 0);

            curl_setopt($oCurl, CURLOPT_PORT, $this->port);
            curl_setopt($oCurl, CURLOPT_SSLCERT, $certKeyPath);
            curl_setopt($oCurl, CURLOPT_SSLKEY, $priKeyPath);

            curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
            if($this->data != ''){
                curl_setopt($oCurl, CURLOPT_POST, 1);
                curl_setopt($oCurl, CURLOPT_POSTFIELDS, $this->data);
            }
            if(!empty($this->parametros)){
                curl_setopt($oCurl, CURLOPT_HTTPHEADER, $this->parametros);
            }

            $this->resposta = curl_exec($oCurl);
            $info = curl_getinfo($oCurl);

            $this->infoCurl = [];
            $this->infoCurl["url"] = $info["url"];
            $this->infoCurl["content_type"] = $info["content_type"];
            $this->infoCurl["http_code"] = $info["http_code"];
            $this->infoCurl["header_size"] = $info["header_size"];
            $this->infoCurl["request_size"] = $info["request_size"];
            $this->infoCurl["filetime"] = $info["filetime"];
            $this->infoCurl["ssl_verify_result"] = $info["ssl_verify_result"];
            $this->infoCurl["redirect_count"] = $info["redirect_count"];
            $this->infoCurl["total_time"] = $info["total_time"];
            $this->infoCurl["namelookup_time"] = $info["namelookup_time"];
            $this->infoCurl["connect_time"] = $info["connect_time"];
            $this->infoCurl["pretransfer_time"] = $info["pretransfer_time"];
            $this->infoCurl["size_upload"] = $info["size_upload"];
            $this->infoCurl["size_download"] = $info["size_download"];
            $this->infoCurl["speed_download"] = $info["speed_download"];
            $this->infoCurl["speed_upload"] = $info["speed_upload"];
            $this->infoCurl["download_content_length"] = $info["download_content_length"];
            $this->infoCurl["upload_content_length"] = $info["upload_content_length"];
            $this->infoCurl["starttransfer_time"] = $info["starttransfer_time"];
            $this->infoCurl["redirect_time"] = $info["redirect_time"];

            $this->txtInfo = "";
            foreach($info as $key => $content){
                if(is_string($content)){
                    $this->txtInfo .= strtoupper($key) . '=' . $content."\n";
                }
            }

            $this->soapDebug = $this->data . "\n\n" . $this->txtInfo . "\n" . $this->resposta;
            $this->errorCurl = curl_error($oCurl);

            curl_close($oCurl);
        }
    }

?>