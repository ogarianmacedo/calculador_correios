<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use SoapClient;

class CalculadorController extends Controller
{
    /**
     * CalculadorController constructor.
     */
    public function __construct()
    {
        ini_set('max_execution_time', 1800);
        ini_set('soap.wsdl_cache_enabled',0);
        ini_set('soap.wsdl_cache_ttl',0);
    }

    /**
     * @return Application|Factory|View
     */
    public function index()
    {
        return view('calculador.index');
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     */
    public function calcPrecoPrazo(Request $request)
    {
        $this->validaFormulario($request);

        $result = $this->usingCurl($request);
        $xml = simplexml_load_string($result);

        return view('calculador.resultado', ['resultado' => $xml->cServico]);
    }

    /**
     * @return Application|Factory|View
     */
    public function resultado()
    {
        return view('calculador.resultado');
    }

    /**
     * TODO: O USO COM O SOAPCLIENT A RESPOSTA ESTÁ DEMORANDO MUITO!
     *
     * @param $request
     * @return mixed
     */
    private function usingSoapClient($request)
    {
        $params = array(
            'nCdEmpresa'          => $request->get('nCdEmpresa'),           // String
            'sDsSenha'            => $request->get('sDsSenha'),             // String
            'nCdServico'          => $request->get('nCdServico'),           // String
            'sCepOrigem'          => $request->get('sCepOrigem'),           // String
            'sCepDestino'         => $request->get('sCepDestino'),          // String
            'nVlPeso'             => $request->get('nVlPeso'),              // String
            'nCdFormato'          => (int) $request->get('nCdFormato'),     // Int
            'nVlComprimento'      => (float) $request->get('nVlComprimento'),    // Decimal
            'nVlAltura'           => (float) $request->get('nVlAltura'),         // Decimal
            'nVlLargura'          => (float) $request->get('nVlLargura'),        // Decimal
            'nVlDiametro'         => (float) $request->get('nVlDiametro'),       // Decimal
            'sCdMaoPropria'       => $request->get('sCdMaoPropria'),             // String
            'nVlValorDeclarado'   => (float) $request->get('nVlValorDeclarado'), // Decimal
            'sCdAvisoRecebimento' => $request->get('sCdAvisoRecebimento'),       // string
            'StrRetorno'          => 'xml', // string
            'nIndicaCalculo'      => 3
        );

        $client = new SoapClient(
            "http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx?wsdl"
        );

        $response = $client->__soapCall("CalcPrecoPrazo", array($params));
        return $response->CalcPrecoPrazoResult->Servicos->cServico;
    }

    /**
     * @param $request
     * @return bool|string
     */
    private function usingCurl($request)
    {
//        "nCdEmpresa=" . $request->get('nCdEmpresa') .
//        "&sDsSenha=" . $request->get('sDsSenha') .
//        "&nVlDiametro=" . (float)$request->get('nVlDiametro') .

        $stringUrl =
                    "&nCdServico=" . $request->get('nCdServico') .
                    "&sCepOrigem=" . $request->get('sCepOrigem') .
                    "&sCepDestino=" . $request->get('sCepDestino') .
                    "&nVlPeso=" . $request->get('nVlPeso') .
                    "&nCdFormato=" . (int)$request->get('nCdFormato') .
                    "&nVlComprimento=" . (float)$request->get('nVlComprimento') .
                    "&nVlAltura=" . (float)$request->get('nVlAltura') .
                    "&nVlLargura=" . (float)$request->get('nVlLargura') .
                    "&sCdMaoPropria=" . $request->get('sCdMaoPropria') .
                    "&nVlValorDeclarado=" . (float)$request->get('nVlValorDeclarado') .
                    "&sCdAvisoRecebimento=" . $request->get('sCdAvisoRecebimento') .
                    "&StrRetorno=xml" .
                    "&nIndicaCalculo=3";

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://ws.correios.com.br/calculador/CalcPrecoPrazo.aspx?' . $stringUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    private function validaFormulario(Request $request)
    {
        $request->validate(
            [
                //'nCdEmpresa'          => 'required',
                //'sDsSenha'            => 'required',
                'nCdServico'          => 'required',
                'sCepOrigem'          => 'required',
                'sCepDestino'         => 'required',
                'nVlPeso'             => 'required',
                'nCdFormato'          => 'required',
                'nVlComprimento'      => 'required',
                'nVlAltura'           => 'required',
                'nVlLargura'          => 'required',
                //'nVlDiametro'         => 'required',
                'sCdMaoPropria'       => 'required',
                'nVlValorDeclarado'   => 'required',
                'sCdAvisoRecebimento' => 'required',
            ],
            [
                'required' => 'O campo :attribute é obrigatório',
            ],
            [
                //'nCdEmpresa'          => 'Código Empresa',
                //'sDsSenha'            => 'Senha',
                'nCdServico'          => 'Código Serviço',
                'sCepOrigem'          => 'Cep Origem',
                'sCepDestino'         => 'Cep Destino',
                'nVlPeso'             => 'Peso da Encomenda',
                'nCdFormato'          => 'Formado da Encomenda',
                'nVlComprimento'      => 'Comprimento',
                'nVlAltura'           => 'Altura',
                'nVlLargura'          => 'Largura',
                //'nVlDiametro'         => 'required',
                'sCdMaoPropria'       => 'Mão Própria',
                'nVlValorDeclarado'   => 'Valor Declarado (Se não optar pelo serviço informar ZERO)',
                'sCdAvisoRecebimento' => 'Aviso Recebimento',
            ]
        );
    }
}
