<?php

/********************************************************* IA */

function auth()
{
    $ip = $_SERVER['REMOTE_ADDR'];
    if (substr($ip, 0, 6) == '143.54') {
        return True;
    }
    if (trim($ip) == '54.233.226.131') {
        return True;
    }
    if (substr($ip, 0, 4) == '127.') {
        return True;
    }
    $dt['erro'] = '500';
    $dt['description'] = 'Access from this IP ' . $ip . ' is not authorized';
    echo json_encode($dt);
    exit;
}

function getFile($id)
{
    auth();
    $filename = 'lattes' . $id . '.zip';
    $dir = '_lattes';

    $file = $dir . '/' . $filename;

    $client = new SoapClient("http://servicosweb.cnpq.br/srvcurriculo/WSCurriculo?wsdl");
    $param = array('id' => $id);
    $response = $client->__call('getCurriculoCompactado', $param);
    print_r($response);
    #$response = base64_decode($response);
    file_put_contents($file, $response);
    sleep(0.5);
    return true;
}


$id_lattes = "0016615895456187";
$arquivo = getFile($id_lattes);
