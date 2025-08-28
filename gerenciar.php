<?php
require_once __DIR__ . '/bootstrap.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['id_profissional'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Conta - Bauru Serviços</title>
    <link rel="stylesheet" href="styles/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>

    <?php require_once __DIR__ . "/partials/header.php"; ?>

    <main>
        <div class="form-container" style="max-width: 700px;">
            <h2>Gerenciar Conta</h2>          
            <div class="management-options">
                <a href="editar_cadastro.php" class="management-link">
                    <i class="fas fa-user-edit"></i>
                    <div>
                        <span>Editar cadastro</span>
                        <small>Atualize suas informações de contato, descrição e profissão.</small>
                    </div>
                </a>
                <a href="alterar_email.php" class="management-link">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <span>Alterar e-mail</span>
                        <small>Modifique o seu e-mail de login e contato.</small>
                    </div>
                </a>
                <a href="alterar_senha.php" class="management-link">
                    <i class="fas fa-key"></i>
                    <div>
                        <span>Alterar senha</span>
                        <small>Mantenha sua conta segura alterando sua senha de acesso.</small>
                    </div>
                </a>

                <a href="logout.php" class="management-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <div>
                        <span>Sair</span>
                        <small>Sair do sistema.</small>
                    </div>
                </a>
            </div>
        </div>
    </main>

    <?php require_once __DIR__ . "/partials/footer.php"; ?>
</body>
</html>