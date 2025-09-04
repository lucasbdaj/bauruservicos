<header>
    <div class="header-container">
        <a href="index.php" class="logo-container">
            <img src="imagem/bauru_servico.png" alt="Logo Bauru Serviços" class="logo">
            <h1>Bauru Serviços</h1>
        </a>
        <button class="hamburger" id="hamburger-button" aria-label="Abrir menu">
            <i class="fas fa-bars"></i>
        </button>
        <nav class="header-links" id="main-nav">
            <a href="index.php" class="cab-link">Início</a>
            <a href="sobre.php" class="cab-link">Sobre</a>
            <a href="contato.php" class="cab-link">Contato</a>

            <?php if (($is_logged_in) && isset ($user_info)): ?>
                <div class="user-menu-container">
                    <a href="javascript:void(0);" onclick="toggleDropdown()" class="user-name">
                        Olá, <?php echo htmlspecialchars($user_info['primeiro_nome']); ?> &#9662;
                    </a>
                    <div class="dropdown-content" id="userDropdown">
                        <a href="gerenciar.php">Gerenciar</a>
                        <a href="editar_cadastro.php">Editar cadastro</a>
                        <a href="alterar_email.php">Alterar e-mail</a>
                        <a href="alterar_senha.php">Alterar senha</a>
                        <a href="logout.php">Sair</a>
                        </div>
                </div>
            <?php else: ?>
                <a href="cadastro.php" class="cab-link">Cadastro</a>
                <a href="login.php" class="cab-link">Entrar</a>
            <?php endif; ?>
        </nav>
    </div>
</header>