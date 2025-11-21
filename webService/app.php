<?php

/********************************************************* IA */
function getFile($id)
{

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
