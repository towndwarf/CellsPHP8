<?php
// source & destination directories
$dir_source = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR ;
// move tmpl templates to 'tpl_blades' renaming 'htm' to 'blade.php'
$dir_dest = "D:\\WORK\\LIFE-CELLS\\LI8\\httpdocs\\";
//htm templates to blade
$replacePatterns = [
    ['type' => 0, 'from' => '{else}', 'to' =>'@else'],
    ['type' => 0, 'from' => '{/if}', 'to' =>'@endif'],
    ['type' => 0, 'from' => '{/unless}', 'to' =>'@endif'],
    ['type' => 0, 'from' => '{/loop}', 'to' =>'@endforeach'],
    ['type' => 0, 'from' => '</loop>', 'to' =>'@endforeach'],
    ['type' => 0, 'from' => "{if name='sel' op='=='value='1'} selected{/if}", 'to' =>'{!!$sel === \'1\'?\'selected\':\'\'!!}'],
    ['type' => 0, 'from' => '{if name=\'__EVEN__\'}class="rowbg1"{else}class="rowbg0"{/if}', 'to' =>'{!!"class=\'rowbg" . ($idx%2 ? "0\'": "1\'")!!}'],
   // ['type' => 0, 'from' => '{if name=\'__EVEN__\'}class="rowbg1"{else}class="rowbg0"{/if}', 'to' =>'{!!"class=\'rowbg" . ($idx%2 ? "0\'": "1\'")!!}'],
    ['type' => 0, 'from' => '{if name=\'__EVEN__\'}rowbg1{else}rowbg0{/if}', 'to' =>'{!!$idx%2 ? "rowbg0": "rowbg1"!!}'],
    ['type' => 0,
        'from' => '{if name=\'dir\' op=\'==\' value=\'rtl\'}success-rtl{else}success-msg{/if}', 'to' =>'{!!$dir?\'success-rtl\':\'success-msg\'!!}'],
    ['type' => 1,
        'from' => '{include file=\'(.*?).htm\'}',
        'to' =>'@include(\'partials.\1\')'],
    ['type' => 1,
        'from' => '{var name=\'(.*?)\'}',
        'to' =>'{!!\$\1!!}'],
    ['type' => 1,
        'from' => '{unless name=\'(.*?)\'}',
        'to' =>'@if(!isset(\$\1))'],
    ['type' => 1,
        'from' => '\$tmpl->setvar\(\'msg_ok\',(.*?)\)',
        'to' =>'TWIGTplPrepare::set(\'msg_ok\',\1)'],
    ['type' => 1,
        'from' => '\$tmpl->setvar\(\'msg_err\',(.*?)\)',
        'to' =>'TWIGTplPrepare::set(\'msg_err\',\1)'],
    ['type' => 1,
        'from' => '\$tmpl->setvar\(\'(.*?)\',(.*?)\)',
        'to' => '\$blade_vars[\'\1\'] =\2'],
    ['type' => 1,
        'from' => '{if name=\'(.*?)\'}',
        'to' =>'@if(isset(\$\1))'],
    ['type' => 1,
        'from' => '{if name=\'(.*?)\' op=\'(.*?)\' value=\'(.*?)\'}',
        'to' =>"@if(\$\1 \2= '\3')"],
     ['type' => 1,
        'from' => '#([A-Z ]+)#',
        'to' =>'{!!TWIGTplPrepare::cnst(\'\1\')!!}'],
    ['type' => 0, 'from' =>'@if(isset($__EVEN__))class="rowbg1"@elseclass="rowbg0"@endif',
        'to'=>'{!!$idx%2 ?\'class="rowbg0"\': \'class="rowbg1"\'!!}'],
    ['type' => 0, 'from' =>'@if(isset($__EVEN__))rowbg1@elserowbg0@endif',
        'to'=> '{!!$idx%2 ?\'rowbg0\': \'rowbg1\'!!}'],
    ['type' => 1,
        'from' => '[{|<]loop name=\'(.*?)\'[}|>]',
        'to' => '@foreach(\$\1 as \$idx =>\$lp)'],
];

$folders_to_copy = [
    ['src'=>'admin\tmpl','dest' =>'tpl_blades\admin', 'extSrc' => 'htm', 'extDest' => 'blade.php'],
    ['src'=>'tmpl','dest' =>'tpl_blades', 'extSrc' => 'htm', 'extDest' => 'blade.php'],
    //['src'=>'templates','dest' =>'tpl_blades', 'extSrc' => 'htm', 'extDest' => 'blade.php'],
    ['src'=>'','dest' =>'', 'extSrc' => 'php', 'extDest' => 'php']
];
    foreach($folders_to_copy as $folder) {
    $src = $dir_source . $folder['src'];
    $dest = $dir_dest . $folder['dest'];
    recurseCopy($src, $dest, '', $folder['extSrc'], $folder['extDest']);
}


function recurseCopy(
     $sourceDirectory,
     $destinationDirectory,
     $childFolder = '',
     $sourceExtension = '',
     $destinationExtension = ''
)  {
    $directory = opendir($sourceDirectory);
    echo '<ol>';
    if ((is_dir($destinationDirectory) === false) && !mkdir($destinationDirectory) && !is_dir($destinationDirectory)) {
        throw new \RuntimeException(sprintf('Directory "%s" was not created', $destinationDirectory));
    }
    if ($childFolder !== '') {
        if ((is_dir("$destinationDirectory/$childFolder") === false) && !mkdir("$destinationDirectory/$childFolder") && !is_dir("$destinationDirectory/$childFolder")) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', "$destinationDirectory/$childFolder"));
        }
        echo '\r\n FROM:' . $sourceDirectory . ' TO: ' . $destinationDirectory;
        while (($file = readdir($directory)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            if (is_dir("$sourceDirectory/$file") === true) {
                recurseCopy("$sourceDirectory/$file", "$destinationDirectory/$childFolder/$file");
            } else if($sourceExtension === '') {
                copy("$sourceDirectory/$file", "$destinationDirectory/$childFolder/$file");
                replaceInFile("$destinationDirectory/$childFolder/$file");
            } else {
                $file_parts = pathinfo($file);
                if ($file_parts['extension'] === $sourceExtension) {
                    $destFilename = "$destinationDirectory/$childFolder/{$file_parts['filename']}.$destinationExtension";
                    echo "<li>FROM: $sourceDirectory/$file  TO:  $destFilename</li>";
                    copy("$sourceDirectory/$file", $destFilename);
                    replaceInFile($destFilename);
                }
            }
        }
        closedir($directory);
        echo '</ol>';
        return;
    }
    while (($file = readdir($directory)) !== false) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        if (is_dir("$sourceDirectory/$file") === true) {
            recurseCopy("$sourceDirectory/$file", "$destinationDirectory/$file");
        }
        elseif($sourceExtension === '') {
            copy("$sourceDirectory/$file", "$destinationDirectory/$file");
            replaceInFile("$destinationDirectory/$file");
        } else {
                $file_parts = pathinfo($file);
                if ($file_parts['extension'] === $sourceExtension) {
                    $destFilename = "$destinationDirectory/{$file_parts['filename']}.$destinationExtension";
                    echo "<li>FROM: $sourceDirectory/$file  TO:  $destFilename</li>";
                    copy("$sourceDirectory/$file", $destFilename);
                    replaceInFile($destFilename);
                }
        }
    }
    closedir($directory);
    echo '</ol>';
}
function replaceInFile ($sourceFile)
{
    global $replacePatterns;
    $str = file_get_contents($sourceFile);
    foreach($replacePatterns as $idx => $patternSet) {
        if ($patternSet['type'] === 0) {
            $str = str_replace($patternSet['from'], $patternSet['to'],$str);
        } else {
            if ($idx === 11) {
                $stop = 't';
            }
            $str = preg_replace('/'. $patternSet['from'] . '/', $patternSet['to'],$str);
        }
        if ($str === NULL) {
            error_log('FILE: '. $sourceFile . ' PATTERN ' . $patternSet['from']);
        }
    }
    if ($tmp = processForeachLoops($str)) {
        file_put_contents($sourceFile, $tmp);
    } else {
        file_put_contents($sourceFile, $str);
    }
}
function processForeachLoops($sourceStr) {
    if(!$pos = strpos($sourceStr,'@foreach')) {
        return false;
    }
    $length = strlen($sourceStr);
    $offset = 0;
    do{
        $foreachStart = substr($sourceStr, $offset,$pos-1);
        $foreachEndPos = strpos($sourceStr,'@endforeach');
        $foreachBlock = substr($sourceStr,$pos, $foreachEndPos) ;
        $strArr = explode("\r", $foreachBlock );
        $simpleReplacements = [
            ['{!!$sno!!}', '{!!$lp[\'sno\']!!}'],
            ['{!!$id!!}','{!!$lp[\'id\']!!}'],
            ['{!!$name!!}','{!!$lp[\'name\']!!}'],
            ['{!!$username!!}','{!!$lp[\'username\']!!}']
            ];
        foreach ($strArr as $i => $row) {
            $start = 0;
            $blockStart =
            if ($idxPos = strpos($row,)) {
                $strArr[$i] = str_replace('{!!$sno!!}',, $row);
            }
            if ($idxPos = strpos($row,)) {
                $strArr[$i] = str_replace('{!!$id!!}', , $row);
            }
            if ($idxPos = strpos($row,)) {
                $strArr[$i] = str_replace('{!!$name!!}', , $row);
            }
            if ($idxPos = strpos($row,'{!!$username!!}')) {
                $strArr[$i] = str_replace('{!!$username!!}', ', $row);
            }
            $idxPos = strpos($row,'{!!$
            $strArr[$i] = str_replace(['{!!$sno!!}','{!!$id!!}','{!!$name!!}'], ['{!!$lp["sno"]!!}','{!!$lp["id"]!!}','{!!$lp["name"]!!}'],$row);
        }
        }
}