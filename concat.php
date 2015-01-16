<?php

/**
 * Command line script which takes files in a folder concats all or a selection
 * 
 * It is used essentially to assemble a large number of MPEGTS chunks
 * in order to pipe the output in FFMPEG and then convert this input 
 * to MP4 or WEBM for example
 * 
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3
 * @package raspistream
 * @see http://www.tmplab.org/wiki/index.php/Streaming_Video_With_RaspberryPi
 * @author Alban Crommer
 * @copyright (c) 2015
 * @version 1.0
 */

// --------------------------------------------------------------
// User Tweakable parameters
// --------------------------------------------------------------

// Defines how many digits your FFMPEG segments have 
// Ex: "/tmp/capture/out-0001.ts" => 4
$digits                         = 9;
// Defines the path to your segments to merge
// Ex: "/tmp/capture/out-0001.ts" => "/tmp/capture"
$path                           = "/tmp/capture/";
// Defines a prefix for your segments ex "out-0001.ts"
// Ex: "/tmp/capture/out-0001.ts" => "out-"
$prefix                         = "";
// Defines the extension of your segments ex "0001.ts"
// Ex: "/tmp/capture/out-0001.ts" => "ts"
$extension                      = "ts";
// Defines a log file
$log_file                       = "/tmp/concat.log";


/**
 * Return the path of a file given an integer id
 * 
 * @global int $digits
 * @global string $prefix
 * @global string $extension
 * @global string $path
 * @param type $id
 * @param string $path
 * @return type
 */
function getChunkName($id){

    global $digits;
    global $prefix;
    global $extension;
    return $prefix.str_pad($id, $digits, "0", STR_PAD_LEFT).".".$extension;

}

/**
 * Writes logs
 * 
 * @global string $log_file
 * @param type $data
 */
function writeLog( $data ){
    global $log_file;
    file_put_contents( $log_file, $data."\n", FILE_APPEND);
}

/**
 * Exits the script
 * 
 * @global string $log_file
 * @param string $msg
 * @param type $code
 */
function scriptExit( $msg, $code = 1){
    global $log_file;
    $msg                        .= "\n";
    writeLog( "EXIT with code $code : $msg");
    echo $msg;
    exit ($code);
}

/**
 * Attempts to read a file to OUT
 * 
 * @param type $file_path
 */
function displayChunk( $file_path ){
    global $path;
    $file_path                  = $path.DIRECTORY_SEPARATOR.$file_path;
    writeLog($file_path);
    if( is_file ( $file_path ) ){
        readfile( $file_path );
    }else{
        writeLog( "ERROR ! Invalid path ".$file_path );
    }
}

// Parameters handling
$min                            = 0;
$max                            = null;
foreach( $argv as $key => $value ){
    switch ($key) {
        case 1:
            $min            = (int) $value;
            break;
        case 2:
            $max            = (int) $value;
            break;
    }
    
}

$first_file                     = getChunkName($min);
$last_file                      = is_null($max) ? null : getChunkName($max); 

// Script start

// Attempt to read the directory content
$fileList                       = array();

// Builds the file list based on min / max
$is_first_found                 = false;
$dirList                        = scandir($path);
sort($dirList);
foreach( $dirList as $current_file) {
    if( $first_file == $current_file ){
        $is_first_found         = true;
    }
    if( $is_first_found ){
        $fileList[]             = $current_file;
    }
    if( $last_file  == $current_file ){
        break;
    }
}

if( ! count($fileList)){
    scriptExit("No file to concat");
}

writeLog("---- ".date("y-m-d H:i:s")." ----");
writeLog("Starting new concat");

// Read the files 
foreach($fileList as $current_file) {
    displayChunk( $current_file, $path );
}
 