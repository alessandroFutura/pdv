<?php

    include "../../config/start.php";

    GLOBAL $get, $post, $login;

    switch($get->action){

        case "cancel":

            if(!@$post->budget_id || !@$post->external_id || !@$post->budget_credit){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado"
                ]);
            }

            $data = TerminalDocument::get((Object)[
                "budget_id" => $post->budget_id
            ]);

            if(!@$data){
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Documento não encontrado"
                ]);
            }

            if($data->StCancelado == "S"){
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "O documento já foi cancelado!"
                ]);
            }

            $lot = LoteEstoque::get((Object)[
                "IdLoteEstoque" => $data->IdLoteEstoque
            ]);

            if(!@$lot){
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Lote não encontrado"
                ]);
            }

            if($lot->StLoteEstoque != "L"){
                headerResponse((Object)[
                    "code" => 404,
                    "message" => (
                        "O lote do documento não está liberado." .
                        (@$lot->DsMensagemErro ? "<br/><br/>Mais informações:<br/>{$lot->DsMensagemErro}" : "")
                    )
                ]);
            }

            if($data->CdStatus < 903){

                TerminalDocument::editStatus((Object)[
                    "terminal_document_id" => $data->terminal_document_id,
                    "CdStatus" => 900
                ]);

                if($data->modelo == "65"){

                    $certificate = Certificate::get((Object)[
                        "company_id" => $post->company_id
                    ]);
                    if(!@$certificate){
                        headerResponse((Object)[
                            "code" => 417,
                            "message" => "Certificado não encontrado"
                        ]);
                    }

                    $token = Token::get((Object)[
                        "company_id" => $post->company_id
                    ]);
                    if(!@$token){
                        headerResponse((Object)[
                            "code" => 417,
                            "message" => "Token não encontrado"
                        ]);
                    }

                    $versao = "1.00";
                    $type = $data->modelo == 55 ? "nfe" : "nfce";

                    NFe::xmlCancel((Object)[
                        "chNFe" => $data->chNFe,
                        "nProt" => $data->nProt,
                        "tpAmb" => TP_AMBIENT,
                        "path" => PATH_XML . "{$post->company_id}/",
                        "CNPJ" => str_replace([".","/","-"],["","",""], $post->company_cnpj),
                    ]);

                    TerminalDocument::editStatus((Object)[
                        "terminal_document_id" => $data->terminal_document_id,
                        "CdStatus" => 901
                    ]);

                    NFe::sign((Object)[
                        "type" => $type,
                        "tpAmb" => TP_AMBIENT,
                        "node" => "infEvento",
                        "appendNode" => "evento",
                        "operation_type_code" => "C",
                        "mod" => $data->modelo,
                        "token_code" => $token->token_code,
                        "token_value" => $token->token_value,
                        "company_id" => $post->company_id,
                        "chNFe" => "110111{$data->chNFe}01",
                        "URI" => "#ID110111{$data->chNFe}01",
                        "path" => PATH_XML . "{$post->company_id}/",
                        "certificate_id" => $certificate->certificate_id,
                        "file" => "110111{$data->chNFe}01-can.xml",
                        "output" => "110111{$data->chNFe}01-sign.xml",
                    ]);

                    TerminalDocument::editStatus((Object)[
                        "terminal_document_id" => $data->terminal_document_id,
                        "CdStatus" => 902
                    ]);

                    NFe::authorize((Object)[
                        "event" => "Evento",
                        "versao" => $versao,
                        "chNFe" => $data->chNFe,
                        "urlMethod" => "nfeRecepcaoEvento",
                        "urlOperation" => "NFeRecepcaoEvento4",
                        "path" => PATH_XML . "{$post->company_id}/",
                        "certificate_id" => $certificate->certificate_id,
                        "urlPortal" => "http://www.portalfiscal.inf.br/nfe",
                        "file" => "110111{$data->chNFe}01-sign.xml",
                        "output" => "110111{$data->chNFe}01-ret.xml",
                        "CNPJ" => str_replace([".","/","-"],["","",""], $post->company_cnpj),
                        "idLote" => substr(str_replace(',', '', number_format(microtime(true)*1000000, 0)), 0, 15),
                        "urlService" => "https://{$type}" . (TP_AMBIENT == 2 ? "-homologacao" : "") . ".svrs.rs.gov.br/ws/recepcaoevento/recepcaoevento4.asmx",
                    ]);

                    TerminalDocument::editStatus((Object)[
                        "terminal_document_id" => $data->terminal_document_id,
                        "CdStatus" => 903
                    ]);

                    $ret = NFe::event((Object)[
                        "path" => PATH_XML . "{$post->company_id}/",
                        "file" => "110111{$data->chNFe}01-ret.xml"
                    ]);

                    if($ret->cStat != 128 || (@$ret->retEvento && $ret->retEvento->cStat != 135)){
                        TerminalDocument::editStatus((Object)[
                            "terminal_document_id" => $data->terminal_document_id,
                            "CdStatus" => 9
                        ]);
                    }

                    if($ret->cStat != 128){
                        headerResponse((Object)[
                            "code" => 417,
                            "message" => $ret->xMotivo
                        ]);
                    }

                    if($ret->retEvento->cStat != 135){
                        headerResponse((Object)[
                            "code" => 417,
                            "title" => "Rejeicao {$ret->retEvento->cStat}",
                            "message" => $ret->retEvento->xMotivo
                        ]);
                    }
                }
            }

            $document = Documento::get((Object)[
                "IdDocumento" => $data->IdDocumento
            ]);

            if(!@$document){
                headerResponse((Object)[
                    "code" => 404,
                    "message" => "Documento não encontrado"
                ]);
            }

            LoteDoc::del((Object)[
                "IdDocumento" => $data->IdDocumento
            ]);

            LoteDoc::add((Object)[
                "IdDocumento" => $document->IdDocumento,
                "IdLoteEstoque" => $document->IdLoteEstoque,
                "IdOperacao" => $document->IdOperacao,
                "IdPessoa" => $document->IdPessoa,
                "CdEndereco" => $document->CdEndereco,
                "CdEspecie" => $document->CdEspecie,
                "CdSerieSubSerie" => $document->CdSerieSubSerie,
                "NrDocumento" => $document->NrDocumento,
                "StDocumentoCancelado" => "S",
                "VlDesconto" => $document->VlDesconto,
                "TpEdicao" => "A",
                "DsObservacao" => $document->DsObservacao,
                "AlDesconto" => $document->AlDesconto,
                "IdEntidadeOrigem" => $document->IdEntidadeOrigem,
                "NmEntidadeOrigem" => $document->NmEntidadeOrigem,
                "StGeraControleFiscal" => $document->StGeraControleFiscal,
                "TpFretePorConta" => $data->modelo == "OE" ? "E" : NULL,
                "DsVolumes" => $data->modelo == "OE" ? "0" : NULL,
                "IdPrazo" => $document->IdPrazo,
                "DsPesoLiquido" => $data->modelo == "OE" ? "0" : NULL,
                "StDocumentoImpresso" => $document->StDocumentoImpresso,
                "StCupomFiscalImpresso" => $document->StCupomFiscalImpresso,
                "IdCategoria" => $document->IdCategoria,
                "IdMensagem1" => $document->IdMensagem1,
                "IdMensagem2" => $document->IdMensagem2,
                "IdMensagem3" => $document->IdMensagem3,
                "IdMensagem4" => $document->IdMensagem4,
                "IdSistema" => $document->IdSistema,
                "DtCancelamento" => date("Y-m-d"),
                "DtReferenciaPagamento" => $document->DtReferenciaPagamento,
                "DtCadastro" => $document->DtCadastro,
                "StNFEletronica" => $document->StNFEletronica,
                "IdUsuarioAlteracao" => $document->IdUsuarioAlteracao,
                "TpDocumento" => $document->TpDocumento,
                "TpPagamento" => $document->TpPagamento,
                "VlDocumento" => $document->VlDocumento,
            ]);

            foreach($document->items as $item){

                LoteDocItem::add((Object)[
                    "IdDocumentoItem" => $item->IdDocumentoItem,
                    "IdDocumento" => $data->IdDocumento,
                    "IdCFOP" => $item->IdCFOP,
                    "IdPreco" => $item->IdPreco,
                    "IdProduto" => $item->IdProduto,
                    "VlUnitario" => $item->VlUnitario,
                    "IdClassificacaoFiscal" => $item->IdClassificacaoFiscal,
                    "AlDescontoItem" => $item->AlDescontoItem,
                    "QtItem" => $item->QtItem,
                    "VlItem" => $item->VlItem,
                    "VlBaseICMS" => $item->VlBaseICMS,
                    "AlICMS" => $item->AlICMS,
                    "VlDescontoItem" => $item->VlDescontoItem,
                    "VlICMS" => $item->VlICMS,
                    "TpEdicao" => "A",
                    "CdSituacaoTributaria" => $item->CdSituacaoTributaria,
                    "IdSetorSaida" => $item->IdSetorSaida,
                    "IdEntidadeOrigem" => $item->IdEntidadeOrigem,
                    "NmEntidadeOrigem" => $item->NmEntidadeOrigem,
                    "StEstoqueUnidadeDeEstoqueSaida" => $item->StEstoqueUnidadeDeEstoqueSaida,
                    "StDesoneraICMS" => $item->StDesoneraICMS,
                    "IdOperacao" => $document->IdOperacao,
                    "IdUnidade" => $item->IdUnidade,
                    "StEstoqueSetorEntradaTransEmp" => $item->StEstoqueSetorEntradaTransEmp,
                    "TpEntrega" => $item->TpEntrega,
                    "AlFCP" => $item->AlFCP,
                    "VlFCP" => $item->VlFCP
                ]);

                LoteDocItemRepasse::add((Object)[
                    "TpEdicao" => "",
                    "IdDocumentoItem" => $item->IdDocumentoItem,
                    "IdPessoa" => $item->IdPessoa,
                    "VlBaseRepasse" => $item->VlBaseRepasse,
                    "AlRepasseDuplicata" => $item->AlRepasseDuplicata
                ]);
            }

            foreach($document->payments as $payment){

                LoteDocPagamento::add((Object)[
                    "IdDocumentoPagamento" => $payment->IdDocumentoPagamento,
                    "IdDocumento" => $payment->IdDocumento,
                    "IdTipoBaixa" => $payment->IdTipoBaixa,
                    "NrDias" => $payment->NrDias,
                    "NrTitulo" => $payment->NrTitulo,
                    "DtVencimento" => $payment->DtVencimento,
                    "IdNaturezaLancamento" => $payment->IdNaturezaLancamento,
                    "VlTitulo" => $payment->VlTitulo,
                    "IdFormaPagamento" => $payment->IdFormaPagamento,
                    "TpEdicao" => "",
                    "StEntrada" => $payment->StEntrada,
                    "IdPessoaConvenio" => $payment->IdPessoaConvenio,
                    "AlConvenio" => $payment->AlConvenio,
                    "IdContaBancaria" => NULL,
                    "NrParcelas" => $payment->NrParcelas,
                    "NrParcelasRecebimento" => $payment->NrParcelasRecebimento,
                    "NrDiasRecebimento" => $payment->NrDiasRecebimento,
                    "NrDiasIntervalo" => $payment->NrDiasIntervalo,
                    "NrDiasPrimeiraParcelaVenda" => $payment->NrDiasPrimeiraParcelaVenda,
                    "AlTACConvenio" => $payment->AlTACConvenio,
                    "AlTACEmpresa" => $payment->AlTACEmpresa,
                    "StAtualizaFinanceiro" => $payment->StAtualizaFinanceiro,
                    "StCartaCredito" => "N"
                ]);
                DocumentoComplemento::add((Object)[
                    "IdDocumento" => $payment->IdDocumento,
                    "IdMotivoExclusaoTitulo" => "00A00000RA"
                ]);
            }

            LoteEstoque::reopen((Object)[
                "IdLoteEstoque" => $data->IdLoteEstoque
            ]);

            TerminalDocument::cancel((Object)[
                "budget_id" => $post->budget_id,
                "IdDocumento" => $data->IdDocumento
            ]);

            if($post->budget_credit == "Y"){
                Budget::creditReopen((Object)[
                    "budget_id" => $post->budget_id,
                    "addPayment" => 1
                ]);
            }

            Budget::recover((Object)[
                "budget_id" => $post->budget_id
            ]);

            postLog((Object)[
                "parent_id" => $data->IdDocumento
            ]);

            Json::get($data);

        break;

        case "submitNFCe":

            if(
                !@$post->person ||
                !@$post->seller ||
                !@$post->company ||
                !@$post->operation ||
                !@$post->budget_id ||
                !@$post->client_id ||
                !@$post->company_id
            ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado"
                ]);
            }

            $post->change = (Object)$post->change;
            $post->person = (Object)$post->person;
            $post->seller = (Object)$post->seller;
            $post->company = (Object)$post->company;
            $post->operation = (Object)$post->operation;
            $post->company->external = (Object)$post->company->external;
            $post->operation->document = (Object)$post->operation->document;

            $post->nfe = TerminalDocument::get((Object)[
                "budget_id" => $post->budget_id
            ]);

            $certificate = Certificate::get((Object)[
                "company_id" => $post->company_id
            ]);
            if (!@$certificate) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Certificado não encontrado"
                ]);
            }

            $token = Token::get((Object)[
                "company_id" => $post->company_id
            ]);
            if (!@$token) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Token não encontrado"
                ]);
            }

            $serie = Terminal::getSerie((Object)[
                "model_id" => 65,
                "company_id" => $post->company_id
            ]);
            if (!@$serie) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Nenhuma série ativa foi localizada para a empresa"
                ]);
            }

            $post->serie = $serie;
            $post->TpOperacao = "V";
            $post->certificate = $certificate;

            $nfe = new NFe($post);

            if(!@$post->nfe || $post->nfe->cStat != 100){

                $nfe->validate();
                $nfe->xml();

                if(!@$post->nfe){
                    Terminal::editSerie((Object)[
                        "terminal_company_id" => $serie->terminal_company_id,
                        "terminal_company_number" => $serie->terminal_company_number + 1
                    ]);
                    TerminalDocument::add((Object)[
                        "budget_id" => $post->budget_id,
                        "tpAmb" => $nfe->tpAmb,
                        "serie" => $nfe->serie,
                        "versao" => $nfe->versao,
                        "modelo" => $nfe->mod,
                        "idLote" => $nfe->idLote,
                        "cNF" => $nfe->cNF,
                        "nNF" => $nfe->nNF,
                        "chNFe" => $nfe->chNFe
                    ]);
                    $post->nfe = TerminalDocument::get((Object)[
                        "budget_id" => $post->budget_id
                    ]);
                }


                $nfe->digVal = NFe::sign((Object)[
                    "node" => "infNFe",
                    "appendNode" => "NFe",
                    "URI" => "#NFe{$nfe->chNFe}",
                    "tpAmb" => TP_AMBIENT,
                    "mod" => $nfe->mod,
                    "path" => $nfe->path,
                    "type" => $nfe->type,
                    "chNFe" => $nfe->chNFe,
                    "token_code" => $token->token_code,
                    "token_value" => $token->token_value,
                    "company_id" => $post->company_id,
                    "file" => "{$nfe->chNFe}-{$nfe->type}.xml",
                    "certificate_id" => $certificate->certificate_id,
                    "output" => "{$nfe->chNFe}-{$nfe->type}-sign.xml",
                    "operation_type_code" => $post->TpOperacao
                ]);

                TerminalDocument::editStatus((Object)[
                    "terminal_document_id" => $post->nfe->terminal_document_id,
                    "CdStatus" => 2
                ]);

                try {
                    NFe::authorize((Object)[
                        "event" => "NFe",
                        "path" => $nfe->path,
                        "versao" => $nfe->versao,
                        "idLote" => $nfe->idLote,
                        "chNFe" => $nfe->chNFe,
                        "urlMethod" => "nfeAutorizacaoLote",
                        "urlOperation" => "NFeAutorizacao4",
                        "file" => "{$nfe->chNFe}-{$nfe->type}-sign.xml",
                        "output" => "{$nfe->chNFe}-{$nfe->type}-ret.xml",
                        "urlPortal" => "http://www.portalfiscal.inf.br/nfe",
                        "certificate_id" => $certificate->certificate_id,
                        "urlService" => "https://{$nfe->type}" . (TP_AMBIENT == 2 ? "-homologacao" : "") . ".svrs.rs.gov.br/ws/NfeAutorizacao/NFeAutorizacao4.asmx"
                    ]);
                    TerminalDocument::editStatus((Object)[
                        "terminal_document_id" => $post->nfe->terminal_document_id,
                        "CdStatus" => 3
                    ]);
                } catch (Exception $e) {
                    headerResponse((Object)[
                        "code" => 417,
                        "message" => "Não houve uma resposta da Sefaz."
                    ]);
                }
            }

            $ret = NFe::ret((Object)[
                "path" => $nfe->path,
                "type" => $nfe->type,
                "chNFe" => $nfe->chNFe
            ]);

            if (!@$ret) {
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Não foi possivel recuperar o retorno da Sefaz."
                ]);
            }

            TerminalDocument::edit((Object)[
                "terminal_document_id" => $post->nfe->terminal_document_id,
                "dhRecbto" => @$ret->protNFe->dhRecbto ? $ret->protNFe->dhRecbto : $ret->dhRecbto,
                "nProt" => @$ret->protNFe->nProt ? $ret->protNFe->nProt : NULL,
                "cStat" => @$ret->protNFe->cStat ? $ret->protNFe->cStat : $ret->cStat,
                "xMotivo" => @$ret->protNFe->xMotivo ? $ret->protNFe->xMotivo : $ret->xMotivo,
                "digVal" => $nfe->digVal,
                "vFedTrib" => $nfe->total->vFedTrib,
                "vEstTrib" => $nfe->total->vEstTrib,
                "vMunTrib" => $nfe->total->vMunTrib,
                "vlPago" => $post->change->paidValue,
                "vlTroco" => $post->change->changeValue,
                "vlCobrado" => $post->change->chargedValue,
                "CdStatus" => 4
            ]);

            if($ret->cStat != 104){
                headerResponse((Object)[
                    "code" => 417,
                    "title" => "Ops!",
                    "message" => $ret->xMotivo
                ]);
            }
            if($ret->protNFe->cStat != 100){
                headerResponse((Object)[
                    "code" => 417,
                    "title" => "Rejeição {$ret->protNFe->cStat}",
                    "message" => $ret->protNFe->xMotivo
                ]);
            }

            if(@!$post->nfe->IdLoteEstoque){
                $IdLoteEstoque = LoteEstoque::add((Object)[
                    "CdEmpresa" => $post->company_id,
                    "DsLoteEstoque" => "Lote gerado pelo Checkout da Futura."
                ]);
                TerminalDocument::editLote((Object)[
                    "terminal_document_id" => $post->nfe->terminal_document_id,
                    "IdLoteEstoque" => $IdLoteEstoque,
                    "CdStatus" => 5
                ]);
                $post->nfe->IdLoteEstoque = $IdLoteEstoque;
            }

            if(!@$post->nfe->IdDocumento){
                $IdDocumento = LoteDoc::add((Object)[
                    "IdLoteEstoque" => $post->nfe->IdLoteEstoque,
                    "IdOperacao" => $post->operation->IdOperacao,
                    "IdPessoa" => $post->client_id,
                    "CdEndereco" => $post->address_code,
                    "CdEspecie" => $post->operation->document->CdEspecie,
                    "CdSerieSubSerie" => $serie->serie_id,
                    "NrDocumento" => substr("00000000{$nfe->nNF}",-9),
                    "VlDesconto" => $post->budget_value_discount,
                    "AlDesconto" => $post->budget_aliquot_discount,
                    "IdEntidadeOrigem" => $post->external_id,
                    "NmEntidadeOrigem" => "DocumentoAuxVenda",
                    "StGeraControleFiscal" => "N",
                    "IdPrazo" => $post->term_id ? $post->term_id : NULL,
                    "IdCategoria" => "0000000005",
                    "IdSistema" => "0000000126",
                    "StNFEletronica" => "I",
                    "StNFEletronicaOriginal" => "I",
                    "CdChaveAcessoNFEletronica" => $nfe->chNFe,
                    "IdUsuarioAlteracao" => "00A0000001",
                    "TpDocumento" => "C",
                    "NrProtocoloNFEletronica" => $ret->protNFe->nProt,
                    "VlDocumento" => $post->budget_value_total
                ]);
                TerminalDocument::editDocument((Object)[
                    "terminal_document_id" => $post->nfe->terminal_document_id,
                    "IdDocumento" => $IdDocumento,
                    "CdStatus" => 6
                ]);
                $post->nfe->IdDocumento = $IdDocumento;
            } else {
                LoteDocItem::del((Object)[
                    "top" => sizeof($post->items),
                    "IdDocumento" => $post->nfe->IdDocumento
                ]);
                LoteDocPagamento::del((Object)[
                    "top" => sizeof($post->payments),
                    "IdDocumento" => $post->nfe->IdDocumento
                ]);
                LoteDocItemRepasse::del((Object)[
                    "top" => sizeof($post->items),
                    "IdDocumento" => $post->nfe->IdDocumento
                ]);
            }

            foreach($post->items as $item){

                $item = (Object)$item;
                $item->product = (Object)$item->product;

                $IdDocumentoItem = LoteDocItem::add((Object)[
                    "IdDocumento" => $post->nfe->IdDocumento,
                    "IdCFOP" => $item->product->IdCFOP,
                    "IdIdPreco" => $item->price_id,
                    "IdProduto" => $item->product->IdProduto,
                    "VlUnitario" => $item->budget_item_value_unitary,
                    "IdClassificacaoFiscal" => $item->product->IdClassificacaoFiscal,
                    "AlDescontoItem" => $item->budget_item_aliquot_discount,
                    "QtItem" => $item->budget_item_quantity,
                    "VlItem" => $item->budget_item_value_total,
                    "VlBaseICMS" => $item->product->VlBaseICMS,
                    "AlICMS" => $item->product->AlICMS,
                    "VlDescontoItem" => $item->budget_item_value_discount,
                    "VlICMS" => $item->product->VlICMS,
                    "CdSituacaoTributaria" => $item->product->CST,
                    "IdSetorSaida" => @$post->comapny->company_sector_id ? $post->comapny->company_sector_id : NULL,
                    "IdEntidadeOrigem" => $item->external_id,
                    "NmEntidadeOrigem" => "DocumentoAuxVendaItem",
                    "StEstoqueUnidadeDeEstoqueSaida" => "S",
                    "StDesoneraICMS" => "N",
                    "IdUnidade" => $item->product->IdUnidade,
                    "StEstoqueSetorEntradaTransEmp" => NULL,
                    "TpEntrega" => "N",
                    "AlFCP" => $item->product->AlFCP,
                    "VlFCP" => $item->product->VlFCP,
                ]);

                LoteDocItemRepasse::add((Object)[
                    "IdDocumentoItem" => $IdDocumentoItem,
                    "IdPessoa" => $post->seller_id,
                    "VlBaseRepasse" => $item->budget_item_value_total,
                    "AlRepasseDuplicata" => $item->product->AlRepasseDuplicataDAV
                ]);
            }

            TerminalDocument::editStatus((Object)[
                "terminal_document_id" => $post->nfe->terminal_document_id,
                "CdStatus" => 7
            ]);

            foreach($post->payments as $key => $payment){

                $payment = (Object)$payment;
                $payment->external = (Object)$payment->external;

                LoteDocPagamento::add((Object)[
                    "IdDocumento" => $post->nfe->IdDocumento,
                    "IdTipoBaixa" => $payment->external->IdTipoBaixa,
                    "NrDias" => $payment->external->NrDias,
                    "NrTitulo" => substr("00000000{$nfe->nNF}", -9) . "-" . ($key+1),
                    "DtVencimento" => $payment->budget_payment_deadline,
                    "IdNaturezaLancamento" => $payment->external->IdNaturezaLancamento,
                    "VlTitulo" => $payment->budget_payment_value,
                    "IdFormaPagamento" => $payment->modality_id,
                    "StEntrada" => $payment->budget_payment_entry == "Y" ? "S" : "N",
                    "IdPessoaConvenio" => $payment->external->IdPessoaConvenio,
                    "AlConvenio" => $payment->external->AlConvenio,
                    "IdContaBancaria" => $post->company->external->IdContaBancariaCaixa,
                    "NrParcelas" => $payment->budget_payment_installment,
                    "NrParcelasRecebimento" => $payment->external->NrParcelasRecebimento,
                    "NrDiasRecebimento" => $payment->external->NrDiasRecebimento,
                    "NrDiasIntervalo" => $payment->external->NrDiasIntervalo,
                    "NrDiasPrimeiraParcelaVenda" => $payment->external->NrDiasPrimeiraParcelaVenda,
                    "AlTACConvenio" => $payment->external->AlTACConvenio,
                    "AlTACEmpresa" => $payment->external->AlTACEmpresa,
                    "StAtualizaFinanceiro" => $post->operation->StAtualizaFinanceiro,
                    "StCartaCredito" => "N"
                ]);
            }

            TerminalDocument::editStatus((Object)[
                "terminal_document_id" => $post->nfe->terminal_document_id,
                "CdStatus" => 8
            ]);

            LoteEstoque::release((Object)[
                "IdLoteEstoque" => $post->nfe->IdLoteEstoque
            ]);

            TerminalDocument::editStatus((Object)[
                "terminal_document_id" => $post->nfe->terminal_document_id,
                "CdStatus" => 9
            ]);

            Dav::edit((Object)[
                "StDocumentoAuxVenda" => "E",
                "NrCupomFiscal" => $post->nfe->nNF,
                "IdDocumentoAuxVenda" =>  $post->external_id
            ]);

            postLog((Object)[
                "parent_id" => $post->budget_id
            ]);

            Json::get((Object)[
                "budget_id" => $post->budget_id,
                "DsModelo" =>  $post->nfe->modelo,
                "NrDocumento" => substr("00000000{$nfe->nNF}",-9),
                "IdDocumento" => $post->nfe->IdDocumento,
                "IdLoteEstoque" => $post->nfe->IdLoteEstoque
            ]);

        break;

        case "submitOE":

            if(
                !@$post->person ||
                !@$post->seller ||
                !@$post->company ||
                !@$post->operation ||
                !@$post->budget_id ||
                !@$post->client_id ||
                !@$post->company_id
            ){
                headerResponse((Object)[
                    "code" => 417,
                    "message" => "Parâmetro POST não encontrado"
                ]);
            }

            $post->person = (Object)$post->person;
            $post->seller = (Object)$post->seller;
            $post->company = (Object)$post->company;
            $post->operation = (Object)$post->operation;
            $post->company->external = (Object)$post->company->external;
            $post->operation->document = (Object)$post->operation->document;

            $post->nfe = TerminalDocument::get((Object)[
                "budget_id" => $post->budget_id
            ]);

            if(@$post->nfe && $post->nfe->CdStatus == 9){
                $date = new DateTime($post->nfe->terminal_document_date);
                headerResponse((Object)[
                    "code" => 417,
                    "message" => (
                        "<b>Documento já faturado!</b><br/><br/>" .
                        "Data de Faturamento: {$date->format("d/m/Y H:i:s")}<br/>"
                    )
                ]);
            }

            if(!@$post->nfe){
                $NrSequencial = TerminalDocument::getNrSequencial((Object)[
                    "CdEmpresa" => $post->company_id,
                    "IdTipoDocumento" => "00A0000002"
                ]);
                if(!@$NrSequencial){
                    headerResponse((Object)[
                        "code" => 417,
                        "message" => (
                            "<p>Número sequencial para o documento não localizado!<p/>" .
                            "<p>A empresa já foi cadastrada no tipo de documento?<p/>"
                        )
                    ]);
                }
                TerminalDocument::add((Object)[
                    "budget_id" => $post->budget_id,
                    "tpAmb" => NULL,
                    "serie" => NULL,
                    "versao" => NULL,
                    "modelo" => "OE",
                    "idLote" => NULL,
                    "cNF" => NULL,
                    "nNF" => $NrSequencial,
                    "chNFe" => NULL,
                    "verAplic" => VERSION,
                    "CdStatus" => 1
                ]);
                $post->nfe = TerminalDocument::get((Object)[
                    "budget_id" => $post->budget_id
                ]);
            }

            if(@!$post->nfe->IdLoteEstoque){
                $IdLoteEstoque = LoteEstoque::add((Object)[
                    "CdEmpresa" => $post->company_id,
                    "DsLoteEstoque" => "Pedido -> Ordem de Entrega"
                ]);
                TerminalDocument::editLote((Object)[
                    "terminal_document_id" => $post->nfe->terminal_document_id,
                    "IdLoteEstoque" => $IdLoteEstoque,
                    "CdStatus" => 5
                ]);
                $post->nfe->IdLoteEstoque = $IdLoteEstoque;
            }

            if(!@$post->nfe->IdDocumento){
                $IdDocumento = LoteDoc::add((Object)[
                    "IdLoteEstoque" => $post->nfe->IdLoteEstoque,
                    "IdOperacao" => $post->operation->IdOperacao,
                    "IdPessoa" => $post->client_id,
                    "CdEndereco" => $post->address_code,
                    "CdEspecie" => $post->operation->document->CdEspecie,
                    "CdSerieSubSerie" => $post->operation->document->CdSerieSubSerie,
                    "NrDocumento" => $post->nfe->nNF,
                    "VlDesconto" => $post->budget_value_discount,
                    "DsObservacao" => @$post->budget_note_document ? strtoupper(removeSpecialChar($post->budget_note_document)) : NULL,
                    "AlDesconto" => $post->budget_aliquot_discount,
                    "NmEntidadeOrigem" => "PedidoDeVenda",
                    "StGeraControleFiscal" => "S",
                    "TpFretePorConta" => "E",
                    "DsVolumes" => "0",
                    "IdPrazo" => $post->term_id ? $post->term_id : NULL,
                    "DsPesoLiquido" => "0",
                    "StDocumentoImpresso" => "S",
                    "StCupomFiscalImpresso" => "N",
                    "IdMensagem1" => $post->operation->IdMensagem1 ? $post->operation->IdMensagem1 : NULL,
                    "IdMensagem2" => $post->operation->IdMensagem2 ? $post->operation->IdMensagem2 : NULL,
                    "IdMensagem3" => $post->operation->IdMensagem3 ? $post->operation->IdMensagem3 : NULL,
                    "IdMensagem4" => $post->operation->IdMensagem4 ? $post->operation->IdMensagem4 : NULL,
                    "IdSistema" => "0000000024",
                    "DtReferenciaPagamento" => date("Y-m-d"),
                    "DtCadastro" => date("Y-m-d"),
                    "StNFEletronica" => "N",
                    "IdUsuarioAlteracao" => $login->external_id,
                    "TpDocumento" => "O",
                    "TpPagamento" => "0",
                    "VlDocumento" => $post->budget_value_total
                ]);
                TerminalDocument::editDocument((Object)[
                    "terminal_document_id" => $post->nfe->terminal_document_id,
                    "IdDocumento" => $IdDocumento,
                    "CdStatus" => 6
                ]);
                $post->nfe->IdDocumento = $IdDocumento;
            } else {
                LoteDocItem::del((Object)[
                    "top" => sizeof($post->items),
                    "IdDocumento" => $post->nfe->IdDocumento
                ]);
                LoteDocPagamento::del((Object)[
                    "top" => sizeof($post->payments),
                    "IdDocumento" => $post->nfe->IdDocumento
                ]);
                PedidoDeVendaItemDocumentoItem::del((Object)[
                    "top" => sizeof($post->items),
                    "IdPedidoDeVenda" => $post->external_id
                ]);
                LoteDocItemRepasse::del((Object)[
                    "top" => sizeof($post->items),
                    "IdDocumento" => $post->nfe->IdDocumento
                ]);
            }

            foreach($post->items as $item){

                $item = (Object)$item;
                $item->product = (Object)$item->product;

                $IdDocumentoItem = LoteDocItem::add((Object)[
                    "IdDocumento" => $post->nfe->IdDocumento,
                    "IdCFOP" => $item->product->IdCFOP,
                    "IdIdPreco" => $item->price_id,
                    "IdProduto" => $item->product->IdProduto,
                    "VlUnitario" => $item->budget_item_value_unitary,
                    "IdClassificacaoFiscal" => $item->product->IdClassificacaoFiscal,
                    "AlDescontoItem" => $item->budget_item_aliquot_discount,
                    "QtItem" => $item->budget_item_quantity,
                    "VlItem" => $item->budget_item_value_total,
                    "VlBaseICMS" => 0,
                    "AlICMS" => 0,
                    "VlDescontoItem" => $item->budget_item_value_discount,
                    "VlICMS" => 0,
                    "CdSituacaoTributaria" => NULL,
                    "IdSetorSaida" => @$post->comapny->company_sector_id ? $post->comapny->company_sector_id : NULL,
                    "IdEntidadeOrigem" => NULL,
                    "NmEntidadeOrigem" => NULL,
                    "StEstoqueUnidadeDeEstoqueSaida" => "N",
                    "StDesoneraICMS" => NULL,
                    "IdUnidade" => $item->product->IdUnidade,
                    "StEstoqueSetorEntradaTransEmp" => "N",
                    "TpEntrega" => "I",
                    "AlFCP" => NULL,
                    "VlFCP" => NULL,
                ]);

                PedidoDeVendaItemDocumentoItem::add((Object)[
                    "IdPedidoDeVendaItem" => $item->external_id,
                    "IdDocumentoItem" => $IdDocumentoItem,
                    "IdPedidoDeVenda" => $post->external_id,
                    "QtAtendida" => $item->budget_item_quantity
                ]);

                LoteDocItemRepasse::add((Object)[
                    "IdDocumentoItem" => $IdDocumentoItem,
                    "IdPessoa" => $post->seller_id,
                    "VlBaseRepasse" => $item->budget_item_value_total,
                    "AlRepasseDuplicata" => $item->product->AlRepasseDuplicataPedido
                ]);
            }

            TerminalDocument::editStatus((Object)[
                "terminal_document_id" => $post->nfe->terminal_document_id,
                "CdStatus" => 7
            ]);

            if(@$post->payments){
                foreach($post->payments as $key => $payment){

                    $payment = (Object)$payment;
                    $payment->external = (Object)$payment->external;

                    if($payment->budget_payment_credit == "N"){

                        LoteDocPagamento::add((Object)[
                            "IdDocumento" => $post->nfe->IdDocumento,
                            "IdTipoBaixa" => $payment->external->IdTipoBaixa,
                            "NrDias" => $payment->external->NrDias,
                            "NrTitulo" => "{$post->nfe->nNF}-" . ($key+1),
                            "DtVencimento" => $payment->budget_payment_deadline,
                            "IdNaturezaLancamento" => $payment->external->IdNaturezaLancamento,
                            "VlTitulo" => $payment->budget_payment_value,
                            "IdFormaPagamento" => $payment->modality_id,
                            "StEntrada" => $payment->budget_payment_entry == "Y" ? "S" : "N",
                            "IdPessoaConvenio" => $payment->external->IdPessoaConvenio,
                            "AlConvenio" => $payment->external->AlConvenio,
                            "IdContaBancaria" => $post->company->external->IdContaBancariaCaixa,
                            "NrParcelas" => $payment->budget_payment_installment,
                            "NrParcelasRecebimento" => $payment->external->NrParcelasRecebimento,
                            "NrDiasRecebimento" => $payment->external->NrDiasRecebimento,
                            "NrDiasIntervalo" => $payment->external->NrDiasIntervalo,
                            "NrDiasPrimeiraParcelaVenda" => $payment->external->NrDiasPrimeiraParcelaVenda,
                            "AlTACConvenio" => $payment->external->AlTACConvenio,
                            "AlTACEmpresa" => $payment->external->AlTACEmpresa,
                            "StAtualizaFinanceiro" => $post->operation->StAtualizaFinanceiro,
                            "StCartaCredito" => "N"
                        ]);
                    }
                }
            }

            TerminalDocument::editStatus((Object)[
                "terminal_document_id" => $post->nfe->terminal_document_id,
                "CdStatus" => 8
            ]);

            LoteEstoque::release((Object)[
                "IdLoteEstoque" => $post->nfe->IdLoteEstoque
            ]);

            TerminalDocument::editStatus((Object)[
                "terminal_document_id" => $post->nfe->terminal_document_id,
                "CdStatus" => 9
            ]);

            postLog((Object)[
                "parent_id" => $post->budget_id
            ]);

            Json::get((Object)[
                "budget_id" => $post->budget_id,
                "DsModelo" =>  $post->nfe->modelo,
                "NrDocumento" => $post->nfe->nNF,
                "IdDocumento" => $post->nfe->IdDocumento,
                "IdLoteEstoque" => $post->nfe->IdLoteEstoque
            ]);

        break;

    }

?>