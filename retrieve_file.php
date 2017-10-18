<?php
/**
 * Created by PhpStorm.
 * User: lluismontabes
 * Date: 18/10/17
 * Time: 18:29
 */

require_once ("backend/connection.php");
require_once ("libraries/aws/aws-autoloader.php");

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

$file = $_GET['file'];

$client = S3Client::factory(array(
    'profile' => 'files-app-user',
    'region' => 'eu-west-2',
    'version' => 'latest'
));

$bucket = 'files-app';

try {

    // Get the object
    $result = $client->getObject(array(
        'Bucket' => $bucket,
        'Key'    => $file
    ));

    // Display the object in the browser
    header("Content-Type: {$result['ContentType']}");
    echo $result['Body'];

} catch (S3Exception $e) {
    //echo $e->getMessage() . "\n";
}

?>