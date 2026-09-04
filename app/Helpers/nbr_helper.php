<?php

/**
 * CodeIgniter Form Helpers
 *
 * @package     CodeIgniter
 * @subpackage  NBR SisDoc
 * @category    Helpers
 * @author      Rene F. Gabriel Junior <renefgj@gmail.com>
 * @link        http://www.sisdoc.com.br/CodIgniter
 * @version     v0.21.08.26
 */
//$sx .= form($url,$dt,$this);

function nbr_title($t)
{
    if (substr($t, -1) == '.') {
        $t = substr($t, 0, -1);
    }
    $prep = array();
    $file = '../.temp/oa/prepositions-pt-BR.php';
    if (file_exists($file)) {
        eval(file_get_contents($file));
    }

    $country = array();
    $file = '../.temp/oa/country-pt-BR.php';
    if (file_exists($file)) {
        eval(file_get_contents($file));
    }

    $tl = mb_strtolower($t);
    $tu = mb_strtoupper($t);

    $wdl = explode(' ', $tl);
    $wdu = explode(' ', $tu);

    $sx = '';
    $rl = 0;
    for ($r = 0; $r < count($wdl); $r++) {
        if ($rl == 0) {
            if (in_array($wdl[$r], $prep)) {
                $sx .= $wdl[$r];
            } else {
                $sx .= substr($wdu[$r], 0, 1) . substr($wdl[$r], 1, strlen($wdl[$r]));
            }
            $rl = 1;
        } else {
            /* Excessões *****************************************/
            if (in_array($wdl[$r], $country)) {
                $sx .= substr($wdu[$r], 0, 1) . substr($wdl[$r], 1, strlen($wdl[$r]));
            } else {
                $sx .= $wdl[$r];
            }
        }
        $sx .= ' ';
    }
    $sx = trim($sx);
    return $sx;
}

function nbr_author($xa, $xp)
{
    $xa = trim($xa);
    /* Remove ponto no final */
    if (substr($xa, -1) == '.') {
        $xa = substr($xa, 0, -1);
    }

    if (trim($xa) == '') {
        return "";
    }

    // Corrigir encoding se necessário
    if (mb_detect_encoding($xa) != 'UTF-8') {
        $xa = mb_convert_encoding($xa, 'UTF-8', 'ISO-8859-1');
    }

    /******************************** PREPARA *******/

    if (strpos($xa, ';')) {
        $xa = substr($xa, 0, strpos($xa, ';'));
    }
    $xa = str_replace([' -', '- '], '-', $xa);
    while (strpos($xa, '  ')) {
        $xa = str_replace('  ', ' ', $xa);
    }
    $xa = trim($xa);

    /******************************* NOME SOBRENOME */
    if (strpos($xa, ',') > 0) {
        $xa = substr($xa, strpos($xa, ',') + 1) . ' ' . substr($xa, 0, strpos($xa, ','));
        $xa = str_replace(',', '', $xa);
    }

    /******************************* DIVIDE NOMES */
    $MN = mb_strtoupper($xa);
    $NMT = explode(' ', $MN);
    $NM = array_filter($NMT, fn($name) => $name !== '');

    /***************************************** SOBRENOMES FALSOS */
    $er1 = ['JÚNIOR', 'JUNIOR', 'NETTO', 'NETO', 'SOBRINHO', 'FILHO', 'JR.', 'JR'];
    $TOT = count($NM);
    if ($TOT > 2) {
        if (in_array($NM[$TOT - 1], $er1)) {
            if (isset($NM[$TOT - 2])) {
                $NM[$TOT - 2] .= ' ' . $NM[$TOT - 1];
                unset($NM[$TOT - 1]);
                $TOT--;
            }
        }
    }

    /****************************************** PREPOSICOES **************/
    $er2 = ['DE', 'DA', 'DO', 'DOS', 'E', 'EM', 'DAS'];
    $NM2 = array_diff($NM, $er2);

    /***************************************** MINUSCULAS E ABREVIATURAS */
    $Nm = [];
    $Ni = [];
    $Nf = [];

    if (count($NM) > 1) {
        foreach ($NM as $r => $name) {
            $Nm[$r] = mb_strtolower($name);
            $Ni[$r] = mb_strtoupper($name[0]);
            if (ord($name[0]) > 128) {
                $Nf[$r] = mb_strtoupper(substr($name, 0, 2)) . mb_strtolower(substr($name, 2));
            } else {
                $Nf[$r] = mb_strtoupper($name[0]) . mb_strtolower(substr($name, 1));
            }
            if (strpos($Nf[$r], '-') || strpos($Nf[$r], ' ')) {
                $n = $Nf[$r];
                $pos = strpos($n, '-');

                if ($pos) {
                    if (isset($n[$pos + 1])) {
                        $Nf[$r] = substr($n, 0, $pos + 1) . mb_strtoupper($n[$pos + 1]) . mb_strtolower(substr($n, $pos + 2));
                    }
                }
                $pos = strpos($n, ' ');
                if ($pos) {
                    $Nf[$r] = substr($n, 0, $pos + 1) . mb_strtoupper($n[$pos + 1]) . mb_strtolower(substr($n, $pos + 2));
                }
            }
            if (in_array($name, $er2)) {
                $Nf[$r] = $Nm[$r];
            }
        }
    } else {
        return  mb_strtoupper(substr($xa, 0, 2)) . mb_strtolower(substr($xa, 2));
    }

    $name = '';
    switch ($xp) {
        case '1': // Sobrenome e Nome
            if (isset($NM[$TOT - 1])) {
                $name = $NM[$TOT - 1] . ', ';
            } else {
                $name = 'ERROR';
            }

            for ($r = 0; $r < ($TOT - 1); $r++) {
                if (isset($Nf[$r])) {
                    $name .= $Nf[$r] . ' ';
                }
            }
            break;

        case '2': // Sobrenome e Nome CURTO
            $TOT = count($NM2);
            $nt = 1;
            $nameA = '';
            foreach ($NM2 as $xname) {
                if ($nt == $TOT) {
                    $nameA = $xname;
                } else {
                    $name .= ($nt < $TOT) ? substr($xname, 0, 1) . '. ' : $xname . ', ';
                }
                $nt++;
            }
            $name = trim($nameA . ', ' . $name);
            break;

        case '3': // Sobrenome e Nome CURTO sem ponto
            $name = implode('', array_map(fn($xname) => substr($xname, 0, 1), $NM2));
            $name = end($NM2) . ' ' . $name;
            break;

        case '7': // Nome e Sobrenome
            $name = implode(' ', $Nf);
            break;

        case '8': // Primeira letra maiúscula
            $name = $Nf[0];
            $name .= ' ' . implode(' ', $Nm);
            break;

        default:
            echo "Method $xp not implemented";
            $name = $xa;
    }

    return trim($name) ?: "";
}

if (! function_exists('nbr_autor')) {
    function nbr_autor($nome, $formato)
    {
        return nbr_author($nome, $formato);
    }
}
