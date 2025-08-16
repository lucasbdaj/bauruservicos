<?php
session_start();

require_once __DIR__ . "/config/db_connection.php";
require_once __DIR__ . "/logic/csrf_token.php";

// Função para exibir mensagens de erro/sucesso e redirecionar
function redirectWithMessage($type, $message, $location) {
    $_SESSION['message_type'] = $type;
    $_SESSION['message_content'] = $message;
    header("Location: " . $location);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar CSRF token
    if (!isset($_POST["csrf_token"]) || !validateCSRFToken($_POST["csrf_token"])) {
        redirectWithMessage('error', 'Erro de segurança: Token CSRF inválido.', 'cadastro.php');
    }

    // Verificar se o checkbox de aceite de termos foi marcado
    if (!isset($_POST["aceite_termos"])) {
        redirectWithMessage('error', 'Você deve aceitar os Termos de Uso e Política de Privacidade para se cadastrar!', 'cadastro.php');
    }

    // Coletar e sanitizar dados do formulário
    $nome_profissional = filter_input(INPUT_POST, 'nome_profissional', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $id_profissao = filter_input(INPUT_POST, 'id_profissao', FILTER_SANITIZE_NUMBER_INT); // Sanitizar como INT
    $data_nascimento = filter_input(INPUT_POST, 'data_nascimento', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $tempo_profissao = filter_input(INPUT_POST, 'tempo_profissao', FILTER_SANITIZE_NUMBER_INT); // Sanitizar como INT
    $descricao = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_STRING);
    $telefone = preg_replace('/\D/', '', $_POST['telefone']); // Remove tudo que não for dígito
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $rede_social = filter_input(INPUT_POST, 'rede_social', FILTER_SANITIZE_URL);
    $link_google = filter_input(INPUT_POST, 'link_google', FILTER_SANITIZE_URL);
    $site_prestador = filter_input(INPUT_POST, 'site_prestador', FILTER_SANITIZE_URL);
    $endereco = filter_input(INPUT_POST, 'endereco', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $servicos_endereco = isset($_POST['servicos_endereco']) ? 'S' : 'N';
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // Validações
    if (empty($nome_profissional) || empty($id_profissao) || empty($data_nascimento) || empty($tempo_profissao) || empty($descricao) || empty($telefone) || empty($senha) || empty($confirmar_senha)) {
        redirectWithMessage('error', 'Por favor, preencha todos os campos obrigatórios.', 'cadastro.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($email)) { // Email é opcional, mas se preenchido, deve ser válido
        redirectWithMessage('error', 'Formato de e-mail inválido.', 'cadastro.php');
    }

    if (!preg_match("/^\d{10,11}$/", $telefone)) {
        redirectWithMessage('error', 'Formato de telefone inválido. Deve conter 10 ou 11 dígitos numéricos.', 'cadastro.php');
    }

    if ($senha !== $confirmar_senha) {
        redirectWithMessage('error', 'As senhas não coincidem!', 'cadastro.php');
    }

    // Validar a força da senha (mínimo 8 caracteres, com letras, números e caracteres especiais)
if (!preg_match("/^(?=.*[A-Za-z])(?=.*\d)(?=.*[!@#$%^&*()_+|~=`{}\[\]:'\";<>?,.\/-]).{8,}$/", $senha)) {
    redirectWithMessage('error', 'A senha é fraca. A senha deve ter pelo menos 8 caracteres, incluindo uma letra, números e um caractere especial (@, #, $, %, etc.).', 'cadastro.php');
}

    // Gerar hash da senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Inserir dados na tabela profissional
    // Corrigida a string de tipos para 's i s i s s s s s s s s s' (nome, id_profissao, data_nasc, tempo, desc, tel, email, rede, google, site, end, presta, senha_hash)
    $stmt = $conn->prepare("INSERT INTO profissional (nome_profissional, id_profissao, data_nascimento, tempo_profissao, descricao, telefone, email, rede_social, link_google, site_prestador, endereco, presta_servico_endereco, senha, ativo, nota_profissional)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'S', 5.0)");

    // Ajustado bind_param: 'i' para id_profissao e tempo_profissao
    $stmt->bind_param("sisisssssssss", $nome_profissional, $id_profissao, $data_nascimento, $tempo_profissao, $descricao, $telefone, $email, $rede_social, $link_google, $site_prestador, $endereco, $servicos_endereco, $senha_hash);

    // if ($stmt->execute()) {
    //     redirectWithMessage('success', 'Prestador de serviço cadastrado com sucesso!', 'index.php');
    // } else {
    //     // Captura o erro do banco de dados para depuração, mas não exibe para o usuário final
    //     error_log("Erro ao cadastrar profissional: " . $stmt->error);
    //     redirectWithMessage('error', 'Erro ao cadastrar. Tente novamente.', 'cadastro.php');
    // }
    try {
        if ($stmt->execute()) {
            $stmt->close();
            redirectWithMessage('success', 'Prestador de serviço cadastrado com sucesso!', 'index.php');
        } else {
            $stmt->close();
            // Este bloco raramente será alcançado, pois exceções são lançadas em caso de erro
            redirectWithMessage('error', 'Erro ao cadastrar. Tente novamente.', 'cadastro.php');
        }
    } catch (mysqli_sql_exception $e) {
        $stmt->close();
        error_log("Erro ao cadastrar profissional: " . $e->getMessage());
    
        // Verifica se a mensagem de erro indica duplicidade de telefone
        if (str_contains($e->getMessage(), 'telefone')) {
            redirectWithMessage('error', 'O telefone informado já está cadastrado para outro profissional.', 'cadastro.php');
        } elseif (str_contains($e->getMessage(), 'email')) {
            redirectWithMessage('error', 'O e-mail informado já está cadastrado para outro profissional.', 'cadastro.php');
        } else {
            redirectWithMessage('error', 'Erro ao cadastrar. Verifique os dados informados.', 'cadastro.php');
        }
    }    
} else {
    // Redireciona se a requisição não for POST
    header("Location: cadastro.php");
    exit();
}
$conn->close();
?>