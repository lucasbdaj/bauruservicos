<?php
require_once __DIR__ . "/config/db_connection.php";

session_start(); // Inicia a sessão para usar mensagens de feedback

// Função para exibir mensagens de erro/sucesso e redirecionar
function redirectWithMessage($type, $message, $location) {
    $_SESSION['message_type'] = $type;
    $_SESSION['message_content'] = $message;
    header("Location: " . $location);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recebendo e validando os dados do formulário
    $nome = filter_input(INPUT_POST, 'nome', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $telefone = preg_replace('/\D/', '', $_POST['telefone']); // Remove tudo que não for dígito
    $mensagem = filter_input(INPUT_POST, 'mensagem', FILTER_SANITIZE_STRING);

    // Validação básica
    if (empty($nome) || empty($email) || empty($telefone) || empty($mensagem)) {
        redirectWithMessage('error', 'Todos os campos são obrigatórios.', 'contato.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirectWithMessage('error', 'E-mail inválido.', 'contato.php');
    }
    
    if (!preg_match("/^\d{10,11}$/", $telefone)) { // Validação de 10 ou 11 dígitos numéricos
        redirectWithMessage('error', 'Formato de telefone inválido. Deve conter 10 ou 11 dígitos numéricos.', 'contato.php');
    }


    // Prepara a query SQL
    $sql = "INSERT INTO contato (nome_contato, email_contato, telefone_contato, mensagem) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Vincula os parâmetros
        $stmt->bind_param("ssss", $nome, $email, $telefone, $mensagem);

        // Executa a query
        if ($stmt->execute()) {
            redirectWithMessage('success', 'Mensagem enviada com sucesso!', 'contato.php');
        } else {
            error_log("Erro ao inserir contato: " . $stmt->error); // Loga o erro para depuração
            redirectWithMessage('error', 'Erro ao enviar a mensagem. Tente novamente.', 'contato.php');
        }
    } else {
        error_log("Erro ao preparar query de contato: " . $conn->error); // Loga o erro de preparação
        redirectWithMessage('error', 'Erro ao preparar a query.', 'contato.php');
    }
} else {
    // Redirecionar se o formulário não foi enviado via POST
    header("Location: contato.php");
    exit();
}
$conn->close();
?>