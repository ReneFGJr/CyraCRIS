<?php

/********************************************************* IA */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
function auth()
{
    $ip = $_SERVER['REMOTE_ADDR'];

    if (substr($ip, 0, 6) == '143.54') { return true; }
    if (trim($ip) == '54.233.226.131') { return true; }
    if (substr($ip, 0, 4) == '127.') { return true; }

    $dt['erro'] = '500';
    $dt['description'] = 'Access from this IP ' . $ip . ' is not authorized';
    header("Content-Type: application/json");
    echo json_encode($dt, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function getFile($id)
{
    auth();

    $filename = 'lattes' . $id . '.zip';
    $dir = '_lattes';
    $file = $dir . '/' . $filename;

    try {
        // Verifica diretório
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // Inicia SOAP
        $client = new SoapClient(
            "http://servicosweb.cnpq.br/srvcurriculo/WSCurriculo?wsdl",
            array('trace' => true, 'exceptions' => true)
        );

        $param = array('id' => $id);

        // Tenta obter o currículo
        $response = $client->__call('getCurriculoCompactado', $param);

        // Resposta vazia
        if (!$response) {
            throw new Exception("Resposta vazia do servidor CNPq.");
        }

        // Debug opcional
        // echo "<pre>RESPONSE:\n" . htmlspecialchars($response) . "</pre>";

        // Salva ZIP
        file_put_contents($file, $response);

        return array(
            "status" => "ok",
            "arquivo" => $file,
            "id" => $id
        );

    } catch (SoapFault $e) {
        // Erro SOAP
        $erro = array(
            "erro" => "SOAP_ERROR",
            "mensagem" => $e->getMessage(),
            "codigo" => $e->getCode(),
            "id" => $id
        );

        header("Content-Type: application/json");
        echo json_encode($erro, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        exit;
    } catch (Exception $e) {
        // Outros erros
        $erro = array(
            "erro" => "GENERAL_ERROR",
            "mensagem" => $e->getMessage(),
            "id" => $id
        );

        header("Content-Type: application/json");
        echo json_encode($erro, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        exit;
    }
}

/**************************************************/

$id_lattes = "0016615895456187";
$result = getFile($id_lattes);

header("Content-Type: application/json");
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
