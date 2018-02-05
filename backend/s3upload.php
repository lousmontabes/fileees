<?php
/**
 * Created by PhpStorm.
 * User: lluismontabes
 * Date: 18/10/17
 * Time: 17:35
 */

require_once ("connection.php");
require_once ("../libraries/aws/aws-autoloader.php");

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

$folderId = $_POST['folderId'];

$result = mysqli_query($con, "SELECT `public_key` FROM `folders` WHERE id = '{$folderId}'");
$row = mysqli_fetch_array($result);

$publickey = $row['public_key'];
echo $publickey;

$client = S3Client::factory(array(
    'profile' => 'files-app-user',
    'region' => 'eu-west-2',
    'version' => 'latest'
));

$bucket = 'files-app';

try {

    // Undefined | Multiple Files | $_FILES Corruption Attack
    // If this request falls under any of them, treat it invalid.
    if (
        !isset($_FILES['file']['error']) ||
        is_array($_FILES['file']['error'])
    ) {
        throw new RuntimeException('Invalid parameters.');
    }

    // Check $_FILES['file']['error'] value.
    switch ($_FILES['file']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit.');
        default:
            throw new RuntimeException('Unknown errors.');
    }

    // You should also check filesize here.
    if ($_FILES['file']['size'] > 32000000) {
        throw new RuntimeException('Exceeded filesize limit.');
    }

    // DO NOT TRUST $_FILES['file']['mime'] VALUE !!
    // Check MIME Type by yourself.
    /*$finfo = new finfo(FILEINFO_MIME_TYPE);
    if (false === $ext = array_search(
            $finfo->file($_FILES['file']['tmp_name']),
            array(
                'txt' => 'text/plain',
                'css' => 'text/css',
                'pdf' => 'application/pdf',
                'rar' => 'application/x-rar-compressed',
                'zip' => 'application/zip',
                'json' => 'application/json',
                'js' => 'application/javascript',
                'jpg' => 'image/jpeg',
                'png' => 'image/png',
                'gif' => 'image/gif',
                'svg' => 'image/svg+xml',
                'wav' => 'audio/x-wav',
                'psd' => 'application/photoshop',
            ),
            true
        )) {
        throw new RuntimeException('Invalid file format.');
    }*/

    $name = $_FILES['file']['name'];
    $extension = pathinfo($name, PATHINFO_EXTENSION);
    $size = filesize($_FILES['file']['tmp_name']);

    $hash = sha1_file($_FILES['file']['tmp_name']);

    // You should name it uniquely.
    // DO NOT USE $_FILES['file']['name'] WITHOUT ANY VALIDATION !!
    // On this example, obtain safe unique name from its binary data.
    $keyname = $hash;
    $filepath = $_FILES['file']['tmp_name'];

    // Get file byte data
    $encryptedfile = "";
    $filebytes = file_get_contents($filepath);

    /** ENCRYPTION **/

    // Initialization vector for symmetric key
    $ivlen = openssl_cipher_iv_length("AES-128-CBC");
    $iv = openssl_random_pseudo_bytes($ivlen);

    // Generate symmetric key from random values
    $skey = sha1(microtime(true).mt_rand(10000, 90000));

    // TODO: Asymmetric encryption
    //$keypairpath = "";
    //$publicKey = file_get_contents($keypairpath . "/public");

    // Encrypt file byte data with symmetric key
    $algorithm = "AES-128-CBC";
    $encryptedfile = openssl_encrypt($filebytes, $algorithm, $skey, $raw_output = false, $iv);

    // Encrypt symmetric key with public key
    $encryptedkey = "";
    openssl_public_encrypt($skey, $encryptedkey, $publickey);

    try {
        // Upload data.
        $result = $client->putObject(array(
            'Bucket' => $bucket,
            'Key'    => $keyname,
            'Body'   => $encryptedfile,
            'ACL'    => 'public-read'
        ));

        // Print the URL to the object.
        echo $result['ObjectURL'] . "\n";

        // Add row to database
        mysqli_query($con, "INSERT INTO `files`(`name`, `type`, `format`, `extension`, `size`, `uploader`, `folder`, `hash`, `skey`, `iv`) 
                    VALUES ('{$name}',0,0,'{$extension}', {$size},0, {$folderId}, '{$hash}', '{$encryptedkey}', '{$iv}')");

        $id = mysqli_insert_id($con);

    } catch (S3Exception $e) {
        echo $e->getMessage() . "\n";
    }

    echo 'File is uploaded successfully.';

} catch (RuntimeException $e) {

    echo $e->getMessage();

}

?>