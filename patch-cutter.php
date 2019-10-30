#!/usr/bin/env php
<?php

$patchFile = $argv[1] | "";
$patchFile_pathinfo = pathinfo($patchFile);

$validPatchFile = 
  $patchFile &&
  file_exists($patchFile) &&
  $patchFile_pathinfo["extension"] === "patch";

if (!$validPatchFile) {
    echo "No patch file found.\n";
    exit(1);
}

$patchNum = 0;
$patchPath = "";
$patchContent = [];

$handle = fopen($patchFile, "r");
if ($handle) {
    while (($line = fgets($handle)) !== false) {
        // process the line read.
        if (strpos($line, "diff --git") === 0) {
            if ($patchNum) {
                echo "Creating patch ${patchNum} (${patchPath})\n";
                file_put_contents($patchPath, implode("\n", $patchContent) . "\n");
                $patchContent = [];
            }
            $patchNum++;
            $pathMatcher = "/diff --git a\/(.+) b\/(.+)/";
            preg_match($pathMatcher, $line, $pathMatches);
            $dir = dirname($pathMatches[1]);
            if(!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $patchPath = "${pathMatches[1]}.patch";
            array_push($patchContent, trim($line, "\n"));
        } else {
            if ($patchNum) {
                array_push($patchContent, trim($line, "\n"));
            }
        }
    }
    if ($patchNum) {
        echo "Creating patch ${patchNum} (${patchPath})\n";
        file_put_contents($patchPath, implode("\n", $patchContent));
        $patchContent = [];
    }
    fclose($handle);
}