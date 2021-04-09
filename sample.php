<?php

require(__DIR__ . "\\vendor\\autoload.php");
            
use App\{Code39, BarCode};

$text = "123456789";
$type = BarCode::GIF;

$codeBar = (new Code39)->encode($text);

$content = (new BarCode)->draw($codeBar, $type, false);

file_put_contents(__DIR__ . "\\sample.gif", $content);