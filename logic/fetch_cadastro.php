<?php
require_once __DIR__ . "/../config/db_connection.php";

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Coletar dados do formulário
    $nome_profissional = $_POST['nome_profissional'];
    $id_profissao = $_POST['id_profissao'];
    $data_nascimento = $_POST['data_nascimento'];
    $tempo_profissao = $_POST['tempo_profissao'];
    $descricao = $_POST['descricao'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $rede_social = $_POST['rede_social'];
    $endereco = $_POST['endereco'];
    $presta_servico_endereco = $_POST['servicos_endereco'];
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // Verificar se a senha e a confirmação da senha são iguais
    if ($senha !== $confirmar_senha) {
        echo "<script>alert('As senhas não coincidem!');</script>";
        return;
    }

    // Gerar hash da senha antes de salvar no banco de dados
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    // Inserir dados na tabela profissional (incluindo novos campos)
    $stmt = $conn->prepare("INSERT INTO profissional 
    (nome_profissional, id_profissao, data_nascimento, tempo_profissao, descricao, telefone, email, rede_social, endereco, presta_servico_endereco, senha, ativo, nota_profissional) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'S', 5.0)");

    $stmt->bind_param(
        "sssssssssss",
        $nome_profissional,
        $id_profissao,
        $data_nascimento,
        $tempo_profissao,
        $descricao,
        $telefone,
        $email,
        $rede_social,
        $endereco,
        $presta_servico_endereco,
        $senha_hash
    );

    // Verificar se a inserção foi bem-sucedida	
	if ($stmt->execute()) {
		// Mensagem de sucesso com redirecionamento
		echo "<script>
			alert('Prestador de serviço cadastrado com sucesso!');
			window.location.href = 'index.php';
		</script>";
		session_destroy(); // Encerrar a sessão
	} else {
		echo "<script>alert('Erro ao cadastrar. Tente novamente.');</script>";
	}

	$stmt_update->close();
}
?>