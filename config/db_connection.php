<?php
require_once __DIR__ . "/config.php";

$host = "mysql";            //nome do serviço no docker-compose
$user = "user";             //igual ao serviço do mysql
$password = "password";     //igual ao serviço do mysql
$database = "bauru2971415_servicosbauru";

//Configura o estilo de report do MySQLi antes de criar a conexão
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Criar conexão
// $conn = new mysqli(DB_SERVERNAME, DB_USERNAME, DB_PASSWORD, DB_NAME);

$conn = new mysqli($host, $user, $password, $database);

// Verificar conexão
if ($conn->connect_error) {
    die("Conexão com o banco de dados falhou: " . $conn->connect_error);
}
?>