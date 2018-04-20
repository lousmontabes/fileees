<?php
/**
 * Created by PhpStorm.
 * User: lluismontabes
 * Date: 31/3/18
 * Time: 20:34
 */

require_once ("../libraries/random_compat/random.php");
require_once ("connection.php");

$proceed = true;
$errors = [];

// Get (and sanitize) user form data
$name = mysqli_real_escape_string($con, filter_var($_POST["name"]));
$email = mysqli_real_escape_string($con, filter_var($_POST["email"]));
$password = mysqli_real_escape_string($con, filter_var($_POST["password"]));

// Generate bcrypt salted hash of user password
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Generate random salt for this specific user
$salt = random_bytes(128);

// Check if a user with the same email address already exists
$result = mysqli_query($con, "SELECT id FROM users WHERE email='{$email}' LIMIT 1");
$emailExists = (mysqli_num_rows($result) > 0);

if ($emailExists) {
    $proceed = false;
    array_push($errors, "Email address already in use");
}

if (!nameMeetsCriteria($name)) {
    $proceed = false;
    array_push($errors, "Name not set");
}

if (!emailMeetsCriteria($email)) {
    $proceed = false;
    array_push($errors, "Email not set");
}

if (!passwordMeetsCriteria($password)) {
    $proceed = false;
    array_push($errors, "Password must be at least 8 characters long");
}

if ($proceed) {

    // Add row to database
    mysqli_query($con, "INSERT INTO `users`(`name`, `email`, `password`, `salt`) 
                    VALUES ('{$name}', '{$email}', '{$passwordHash}', '{$salt}')");

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

/**
 * @param $name: User name
 * @return bool
 */
function nameMeetsCriteria($name) {
    return strlen($name) > 0;
}

/**
 * @param $email: User email
 * @return bool
 */
function emailMeetsCriteria($email) {
    return strlen($email) > 0;
}

/**
 * @param $password
 * @return bool
 */
function passwordMeetsCriteria($password) {
    return strlen($password) > 8;
}