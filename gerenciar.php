<?php
require_once __DIR__ . "/config/db_connection.php";
session_start();

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

    <style>
        .management-options { margin-top: 20px; display: flex; flex-direction: column; gap: 15px; }
        .management-link { display: flex; align-items: center; gap: 20px; padding: 20px; background-color: var(--light-bg); border-radius: 8px; text-decoration: none; color: var(--text-color); transition: background-color 0.3s, transform 0.2s; border-left: 5px solid var(--primary-color); }
        .management-link:hover { background-color: #e9ecef; transform: translateX(5px); }
        .management-link i { font-size: 2em; color: var(--primary-color); width: 40px; text-align: center; }
        .management-link div { display: flex; flex-direction: column; }
        .management-link span { font-size: 1.2em; font-weight: bold; }
        .management-link small { color: var(--light-text-color); }
    </style>
</body>
</html>