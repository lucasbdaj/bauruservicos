<?php

function generateCSRFToken() {
    if (empty($_SESSION["csrf_token"])) {
        $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
    }
    return $_SESSION["csrf_token"];
}

function validateCSRFToken($token) {
    if (isset($_SESSION["csrf_token"]) && hash_equals($_SESSION["csrf_token"], $token)) {
        unset($_SESSION["csrf_token"]); // Token should be used only once
        return true;
    }
    return false;
}

?>

