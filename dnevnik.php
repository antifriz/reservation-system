<?php
/**
 * Created by PhpStorm.
 * User: Ivan
 * Date: 12.1.2015.
 * Time: 2:58
 */
$dnevnik = implode("<br>", array_reverse(explode("\n",file_get_contents('podaci/administratorstvo/dnevnik.txt'))));
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="utf-8">
    <!-- Bootstrap -->
    <link href="/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="col-xs-12 " style="padding: 10px;background-color: #262626;color:#262626">
        <a href="/" class="btn btn-default"><span class="glyphicon glyphicon-arrow-left"></span> Početna stranica</a>
    </div>
    <div class="col-xs-12" style="font-family: monospace">
        <?= $dnevnik ?>
    </div>
</body>
</html>