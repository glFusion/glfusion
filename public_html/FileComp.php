<?php

$xSys =  $_GET["Sys"];

$xSys = ($xSys == ''?'language':$xSys);

$div = '=';
$com1 = '#';
$com2 = '/';

$hideFiles = array(".", "..", ".htaccess", ".htpasswd");

$Path = '/var/www/test.nwr/glfusion/'.$xSys.'/';

echo '<a href="/FileComp.php?Sys=public_html/admin/install/language/&Mod=">install</a> - <a href="/FileComp.php?Sys=private/language&Mod=">language</a> - <a href="/FileComp.php?Sys=private/plugins&Mod=">plugins</a><br />';

if (file_exists($Path)) {
  echo '<table border="1"><caption>'.$Path.'</caption><tr><th>Directorios</th><th>Archivos</th></tr><tr style="vertical-align:text-top;"><td>';
  foreach (glob($Path."*",GLOB_ONLYDIR) as $dant) {
    $name = basename($dant, "");
    echo '<a href="/FileComp.php?Sys='.$xSys.'/'.$name.'" >'.$name. '</a><br />';
  }
  echo '</td><td>';
  foreach (glob($Path."*.php") as $xFile) {
    $fName = basename($xFile, "");
    echo $fName.'<br />';
  }
  echo '</td><td rowspan="2">';
  echo '<pre style="min-height:500px;max-height:500px;min-width:700px;max-width:700px;overflow:scroll;">';
  chkFile();
  echo '</pre>';
  echo '</td></tr></table>';
  } else {
    echo '<br />NO existe: '.$Path.'<br />';
 }

function chkFile() {
  global $Path, $div;

  $MFile = 'english_utf-8.php';
  $TFile = 'spanish_colombia_utf-8.php';
  
  $Mast = fopen($Path.$MFile, "r");
  if ($Mast) {
    $Trsl = fopen($Path.'/'.$TFile, "r");
    if ($Trsl) {
      echo '<b>INICIANDO COMPARACIÓN:<br />';
      echo 'A. Maestro: </b>'.$Path.$MFile.'<br />';
      echo '<b>A. Traduc.: </b>'.$Path.$TFile.'<br />';
      $i = 1;
      while (!feof($Mast) and !feof($Trsl)) {
        $MLine = fgets($Mast);
        $TLine = fgets($Trsl);
        cmpLine($i, $MLine, $TLine);
        $i++;
      }
      echo '<br /><b>Lineas Faltantes:</b><br />';
      while (!feof($Mast)) {
        $MLine = fgets($Mast);
        shwLine($MLine);
        $i++;
      }
      echo '<br /><b>Lineas Sobrantes:</b><br />';
      while (!feof($Trsl)) {
        $TLine = fgets($Trsl);
        shwLine($TLine);
        $i++;
      }
      fclose($Trsl);
    } else {
      echo '<br /><b style="color:red">NO EXISTE:</b> '.$Path.$TFile.'<br /><br />';
    }
    echo '<br />';
    fclose($Mast);
  } else {
    echo '<br />NO existe: '.$Path.$MFile.'<br />';
  }
}

function cmpLine ($l, $MLine, $TLine) {
  global $div, $com1, $com2;
// echo '{'.substr($MLine,-2,1).' - '.substr($TLine,-2,1).'}<br />';
  if ($MLine !== $TLine) {
    if (strpos($MLine, $div) > 0) {
     list($mtag, $mdef) = split($div,$MLine);
     list($ttag, $tdef) = split($div,$TLine);
    } else if (strpos($MLine,'define') == 0) {
        list($mtag, $mdef) = split(',',str_replace('define','',$MLine));
        list($ttag, $tdef) = split(',',str_replace('define','',$TLine));
    } else {
      $mtag = $MLine;
      $ttag = $TLine;
    }
    $mtag = trim($mtag);
    $ttag = trim($ttag);
    if ($mtag !== $ttag) {
      if (substr($MLine,0,1) == $com1 or substr($MLine,0,1) == $com2)
        echo '<br />Comentario: '.$l.'<br />';
      else
        echo '<br />Lineas: '.$l.' Etiqueta: ['.$mtag.' - '.$ttag.']<br />';
      shwLine($MLine);
      shwLine($TLine);
    }
    if (substr($MLine,-2,1) !== substr($TLine,-2,1) and substr($MLine,0,1) !== $com) {
      echo '<br /><b>Linea: '.$l.' Incompleta ?</b><br />';
      echo '['.substr($MLine,-2,1).'] - ['.substr($TLine,-2,1).'] ';
      shwLine($MLine);
      shwLine($TLine);
    }
  }
}

function shwLine($line) {
//  echo '<pre>';
  if (strpos($line,'<?php') !== false) {
    echo 'Inicio Codigo PHP';
  } elseif (strpos($line,'?>') !== false) {
    echo 'Fin Codigo PHP';
  } else {
    echo '<span style="color:DarkRed;">'.$line.'</span>';
//    echo $line;
  }
// echo '</pre>';
}
echo '<br /><hr>';
echo 'Ajustar codificación de caracteres:<br />';
echo "<pre>sed -i 's/".'\r'."//g' spanish/total/*.php </pre>";

?>
