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

if (isset($_POST['folderId'])) {
    $folderId = $_POST['folderId'];
} else {
    $result = mysqli_query($con, "SELECT id FROM folders WHERE token = '{$_POST['folderToken']}'");
    $row = mysqli_fetch_array($result);
    $folderId = $row['id'];
}

$name = $_POST['name'];
$size = $_POST['size'];
$filebytes = $_POST['bytes'];
$key = $_POST['key'];

// Check if a file with the same name already exists in folder
$result = mysqli_query($con, "SELECT id FROM files WHERE folder={$folderId} AND name='{$name}' LIMIT 1");
$fileExists = (mysqli_num_rows($result) > 0);

// In case it exists, retrieve its id
if ($fileExists) {
    $originalId = mysqli_fetch_row($result)[0];
    echo $originalId;
}

$client = S3Client::factory(array(
    'profile' => 'files-app-user',
    'region' => 'eu-west-2',
    'version' => 'latest'
));

$bucket = 'files-app';

try {

    // Undefined | Multiple Files | $_FILES Corruption Attack
    // If this request falls under any of them, treat it invalid.
    if (!isset($_POST['bytes']) || is_array($_POST['bytes'])) {
        throw new RuntimeException('Invalid parameters.');
    }

    $extension = pathinfo($name, PATHINFO_EXTENSION);
    $hash = sha1($filebytes);

    // You should name it uniquely.
    // DO NOT USE $_FILES['file']['name'] WITHOUT ANY VALIDATION !!
    // On this example, obtain safe unique name from its binary data.
    $keyname = $hash;

    try {
        // Upload data.
        $result = $client->putObject(array(
            'Bucket' => $bucket,
            'Key'    => $keyname,
            'Body'   => $filebytes,
            'ACL'    => 'public-read'
        ));

        // Print the URL to the object.
        echo $result['ObjectURL'] . "\n";

        // If a file with the same name already existed in this folder,
        // add uploaded item to database as a version of said file
        if ($fileExists) {

            // Add to versions table
            $result = mysqli_query($con, "INSERT INTO `versions`(`file_id`, `size`, `hash`, `skey`) 
                                VALUES ({$originalId}, {$size}, '{$hash}', '{$key}')");


        } else {

            echo "Creating new file\n";

            // Add to files table
            mysqli_query($con, "INSERT INTO `files`(`name`, `type`, `format`, `extension`, `uploader`, `folder`) 
                                VALUES ('{$name}', 0, 0, '{$extension}', 0, {$folderId})");

            $originalId = mysqli_insert_id($con);
            echo $originalId;

            // Add to versions table
            $result = mysqli_query($con, "INSERT INTO `versions`(`file_id`, `size`, `hash`, `skey`) 
                                VALUES ({$originalId}, {$size}, '{$hash}', '{$key}')");

        }

    } catch (S3Exception $e) {
        echo $e->getMessage() . "\n";
    }

    echo 'File uploaded successfully.';

} catch (RuntimeException $e) {

    echo $e->getMessage();

}

?>