<?php

/*********************************************************
 * CONFIGURAÇÕES DE DEBUG
 *********************************************************/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


/*********************************************************
 * AUTENTICAÇÃO POR IP
 *********************************************************/
function auth()
{
    $ip = $_SERVER['REMOTE_ADDR'];

    // IPs permitidos
    if (substr($ip, 0, 6) == '143.54') {
        return true;
    }
    if (substr($ip, 0, 4) == '127.') {
        return true;
    }

    // Bloqueia acesso
    $dt = [
        'erro' => '500',
        'description' => 'Access from this IP ' . $ip . ' is not authorized'
    ];

    header("Content-Type: application/json");
    echo json_encode($dt, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}


/*********************************************************
 * BAIXA O ARQUIVO DO LATTES VIA SOAP
 *********************************************************/
function getFile($id)
{
    auth();

    $filename = 'lattes' . $id . '.zip';
    $dir = '_lattes';
    $file = $dir . '/' . $filename;

    $UM_DIA = 86400;                 // segundos em um dia
    $DIAS_30 = 30 * $UM_DIA;         // 30 dias

    // Se já existir e for recente (< 30 dias), retorna sem baixar
    if (file_exists($file)) {

        $modificado = filemtime($file);
        $agora = time();

        // diferença em segundos
        $idade = $agora - $modificado;

        if ($idade < $DIAS_30) {
            // Não baixa novamente
            return [
                "status" => "ok",
                "arquivo" => $file,
                "id" => $id,
                "cached" => true,
                "dias_passados" => floor($idade / 86400)
            ];
        }
    }

    try {
        // Cria diretório se não existir
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // SOAP Client
        $client = new SoapClient(
            "http://servicosweb.cnpq.br/srvcurriculo/WSCurriculo?wsdl",
            ['trace' => true, 'exceptions' => true]
        );

        $param = ['id' => $id];

        // Chamada ao serviço SOAP
        $response = $client->__call('getCurriculoCompactado', $param);

        if (!$response) {
            throw new Exception("Resposta vazia do servidor CNPq.");
        }

        // Salva novo ZIP
        file_put_contents($file, $response);

        return [
            "status" => "ok",
            "arquivo" => $file,
            "id" => $id,
            "cached" => false
        ];
    } catch (SoapFault $e) {

        $erro = [
            "erro" => "SOAP_ERROR",
            "mensagem" => $e->getMessage(),
            "codigo" => $e->getCode(),
            "id" => $id
        ];

        header("Content-Type: application/json");
        echo json_encode($erro, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    } catch (Exception $e) {

        $erro = [
            "erro" => "GENERAL_ERROR",
            "mensagem" => $e->getMessage(),
            "id" => $id
        ];

        header("Content-Type: application/json");
        echo json_encode($erro, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }
}



/*********************************************************
 * ENVIA O ARQUIVO PARA DOWNLOAD SE status="ok"
 *********************************************************/
function enviarArquivoSeOk($data)
{
    // Se já for array, ok. Se vier JSON, decodifica.
    if (is_string($data)) {
        $data = json_decode($data, true);
    }

    if (!$data || !isset($data['status'])) {
        http_response_code(400);
        echo "JSON inválido.";
        exit;
    }

    if ($data['status'] !== "ok") {
        http_response_code(400);
        echo "Status não autorizado: " . $data['status'];
        exit;
    }

    if (!isset($data['arquivo'])) {
        http_response_code(500);
        echo "Caminho do arquivo não encontrado no JSON.";
        exit;
    }

    $arquivo = $data['arquivo'];

    if (!file_exists($arquivo)) {
        http_response_code(404);
        echo "Arquivo não encontrado: " . $arquivo;
        exit;
    }

    // Cabeçalhos do download
    header("Content-Type: application/zip");
    header("Content-Disposition: attachment; filename=\"" . basename($arquivo) . "\"");
    header("Content-Length: " . filesize($arquivo));
    header("Pragma: public");
    header("Cache-Control: must-revalidate");

    readfile($arquivo);
    exit;
}


function get($key, $default = null)
{
    return isset($_GET[$key]) ? $_GET[$key] : $default;
}
/*********************************************************
 * EXECUÇÃO PRINCIPAL
 *********************************************************/
$id_lattes = get("q");
if (!$id_lattes) {
    http_response_code(400);
    echo "Parâmetro 'q' (ID Lattes) é obrigatório.";
    exit;
}
$result = getFile($id_lattes);

// Envia o arquivo imediatamente
enviarArquivoSeOk($result);

// NÃO continuar enviando JSON (download já foi enviado)
