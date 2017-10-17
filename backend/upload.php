<?php
/**
 * Created by PhpStorm.
 * User: lluismontabes
 * Date: 9/10/17
 * Time: 16:14
 */

require_once ("connection.php");

$folderId = $_POST['folderId'];

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

    mysqli_query($con, "INSERT INTO `files`(`name`, `type`, `format`, `extension`, `size`, `uploader`, `folder`, `hash`) 
                    VALUES ('{$name}',0,0,'{$extension}', {$size},0, {$folderId}, '{$hash}')");

    $id = mysqli_insert_id($con);

    // You should name it uniquely.
    // DO NOT USE $_FILES['file']['name'] WITHOUT ANY VALIDATION !!
    // On this example, obtain safe unique name from its binary data.
    if (!move_uploaded_file(
        $_FILES['file']['tmp_name'],
        sprintf('../uploaded/%s.%s',
            $hash,
            $extension
        )
    )) {
        throw new RuntimeException('Failed to move uploaded file.');
    }

    echo 'File is uploaded successfully.';

} catch (RuntimeException $e) {

    echo $e->getMessage();

}

?>