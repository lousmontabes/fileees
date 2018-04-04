<?php
/**
 * Created by PhpStorm.
 * User: lluismontabes
 * Date: 1/4/18
 * Time: 19:28
 */

require_once ("connection.php");

$proceed = true;
$errors = [];

// Get (and sanitize) user form data
$email = mysqli_real_escape_string($con, filter_var($_POST["email"]));
$password = mysqli_real_escape_string($con, filter_var($_POST["password"]));

// Check if a user with the same email address exists
$result = mysqli_query($con, "SELECT * FROM users WHERE email='{$email}' LIMIT 1");
$row = mysqli_fetch_array($result);
$emailExists = (mysqli_num_rows($result) > 0);

if (!$emailExists) {
    $proceed = false;
    array_push($errors, "User with defined credentials could not be found");
}

if (!password_verify($password, $row["password"])) {
    $proceed = false;
    array_push($errors, "Incorrect pasword");
}

if ($proceed) {

    // Generate PBKDF2 of user password with user salt
    $pbkdf2 = hash_pbkdf2("sha512", $password, $row["salt"], 10000, 128);

    // Start PHP session
    session_start();
    $_SESSION["user_id"] = $row["id"];
    $_SESSION["pbkdf2"] = $pbkdf2;

    $response = ["status" => "success", "error" => $errors];
    $json = json_encode($response);

    echo $json;
    exit;

} else {

    $response = ["status" => "failure", "error" => $errors];
    $json = json_encode($response);

    echo $json;
    exit;

}