<?php
require_once __DIR__ . "/config/db_connection.php";
require_once __DIR__ . "/logic/helpers.php"; // Contém renderSocialLink()
require_once __DIR__ . "/logic/fetch_profissionais.php"; // Lógica para buscar profissionais/categorias

session_start(); // Inicia a sessão para mensagens de feedback
$message_type = $_SESSION['message_type'] ?? '';
$message_content = $_SESSION['message_content'] ?? '';
unset($_SESSION['message_type'], $_SESSION['message_content']); // Limpa a sessão

// Sanitização e validação das entradas
$search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);
$selectedProfissao = filter_input(INPUT_GET, 'profissao', FILTER_SANITIZE_NUMBER_INT);

// --- CORREÇÃO: Lógica para contar profissionais ativos ---
$total_profissionais = 0;
if (isset($conn)) {
    // CORRIGIDO: O nome da tabela é 'profissional' e a coluna de status é 'ativo' com valor 'S'.
    $sql_total = "SELECT COUNT(id_profissional) as total FROM profissional WHERE ativo = 'S'";
    $result_total = $conn->query($sql_total);
    if ($result_total && $result_total->num_rows > 0) {
        $total_profissionais = $result_total->fetch_assoc()['total'];
    }
}
// --- FIM DA CORREÇÃO ---

// A lógica de fetch_profissionais.php deve popular $showCategories, $profissoesResult, $result
// Certifique-se que fetch_profissionais.php está usando $conn para suas queries e fechando statements.
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="Encontre prestadores de serviço qualificados em Bauru. Eletricistas, encanadores, pintores e muito mais. Cadastre-se e encontre o profissional ideal para suas necessidades.">
    <meta name="author" content="Lucas Borges">
    <meta name="keywords" content="bauru, serviços, eletricista, encanador, pintor, pedreiro, jardineiro, carpinteiro, mecânico, cabeleireiro, esteticista, nutricionista, designer, desenvolvedor, fotógrafo, professor, advogado, contador, arquiteto, fisioterapeuta, massagista, marido de aluguel, garçom, motorista, faxineiro, consultor">
    <meta name="email" content="contato@servicosbauru.com.br">
    <meta name="robots" content="index, follow">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:title" content="Bauru Serviços - Encontre Profissionais em Bauru">
    <meta property="og:description" content="Conectando você aos melhores prestadores de serviço em Bauru e região. Rápido, fácil e confiável.">
    <meta property="og:image" content="https://www.bauruservicos.com.br/imagem/bauru_servico.png"> <meta property="og:url" content="https://www.bauruservicos.com.br/index.php">
    <meta property="og:type" content="website">
    <title>Bauru Serviços - Encontre Profissionais em Bauru</title>
    <link rel="stylesheet" href="styles/styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo-container">
                <img src="imagem/bauru_servico.png" alt="Bauru Serviços" class="logo">
                <h1>Bauru Serviços</h1>
            </div>
            <nav class="header-links">
                <a href="index.php" class="cab-link" aria-current="page">Início</a> 
                <a href="cadastro.php" class="cab-link">Cadastro</a>
                <a href="contato.php" class="cab-link">Contato</a>
                <a href="sobre.php" class="cab-link">Sobre</a>
                <a href="login.php" class="cab-link">Entrar</a>
            </nav>
        </div>
    </header>

    <div class="container">        
        <main>
            <?php if ($message_content): ?>
                <p class="message <?php echo htmlspecialchars($message_type); ?>">
                    <?php echo htmlspecialchars($message_content); ?>
                </p>
            <?php endif; ?>
            <form method="get" action="index.php" id="searchForm">
                <input type="text" name="search" id="searchInput" placeholder="Busque por um nome ou profissão" value="<?php echo htmlspecialchars($search); ?>" class="search-field">
                <button type="submit" class="search-button" aria-label="Buscar prestadores de serviço">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </form>

            <?php if ($showCategories): ?>
                <div class="categories-section">
                    <h2>Categorias de Serviços</h2>
                    <p>Escolha uma categoria para ver os profissionais disponíveis:</p>
                    
                    <div class="categories-grid">
                        <?php
                        // A variável $profissoesResult deve vir de fetch_profissionais.php
                        if (isset($profissoesResult) && $profissoesResult->num_rows > 0) {
                            while ($row = $profissoesResult->fetch_assoc()) {
                                echo "<a href='index.php?profissao=" . htmlspecialchars($row['id_profissao']) . "' class='category-card'>
                                        <div class='category-icon'>
                                            <i class='fas fa-tools'></i>
                                        </div>
                                        <h3>" . htmlspecialchars($row['nome_profissao']) . "</h3>
                                        <p>" . htmlspecialchars($row['total_profissionais']) . " profissional" . ($row['total_profissionais'] > 1 ? 'is' : '') . " disponível" . ($row['total_profissionais'] > 1 ? 'is' : '') . "</p>
                                      </a>";
                            }
                        } else {
                            echo "<p>Nenhuma categoria encontrada ou não há profissionais ativos.</p>";
                        }
                        ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="professionals-section">
                    <?php if (!empty($selectedProfissao)): ?>
                        <div class="section-header">
                            <a href="index.php" class="back-button">
                                <i class="fas fa-arrow-left"></i> Voltar às Categorias
                            </a>
                            <h2>Profissionais da Categoria</h2>
                        </div>
                    <?php else: ?>
                        <div class="section-header">
                            <h2>Resultados da Busca</h2>
                            <?php if (!empty($search)): ?>
                                <p>Resultados para: "<strong><?php echo htmlspecialchars($search); ?></strong>"</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <ul class="service-list">
                        <?php
                        // A variável $result deve vir de fetch_profissionais.php
                        if (isset($result) && $result->num_rows > 0) {
                            $contador = 1; // Inicia o contador em 1 para numeração crescente
                            while ($row = $result->fetch_assoc()) {
                                $telefone_limpo = preg_replace('/\D/', '', $row['telefone']);
                                echo "<li class='service-card'>
                                        <div class='card-header'>
                                            <div class='professional-number'>#" . $contador . "</div>
                                            <div class='card-content'>
                                                <h2>" . htmlspecialchars($row['nome_profissional']) . "</h2>
                                                <h3 class='highlight'><strong>Profissão:</strong> " . htmlspecialchars($row['nome_profissao']) . "</h3>
                                            </div>
                                        </div>";

                                if (!empty($row['descricao'])) {
                                    echo "<p><strong>Descrição:</strong> " . nl2br(htmlspecialchars($row['descricao'])) . "</p>"; // nl2br para quebras de linha
                                }

                                echo "<p><strong>Tempo de Profissão:</strong> " . htmlspecialchars($row['tempo_profissao']) . " anos</p>";

                                if ($row['presta_servico_endereco'] === 'S' && !empty($row['endereco'])) {
                                    echo "<p><strong>Endereço:</strong> " . htmlspecialchars($row['endereco']) . "</p>";
                                }

                                echo "<div class='contact-info'>
                                        <p><strong>
                                            <a href='https://wa.me/55" . $telefone_limpo . "' target='_blank' class='whatsapp-button' title='Conversar no WhatsApp'>
                                                <i class='fab fa-whatsapp'></i> " . htmlspecialchars($row['telefone']) . "
                                            </a>
                                        </strong></p>";

                                if (!empty($row['email'])) {
                                    echo "<p><strong>Email:</strong> 
                                            <a href='mailto:" . htmlspecialchars($row['email']) . "'>" . htmlspecialchars($row['email']) . "</a></p>";
                                }

                                if (!empty($row['rede_social'])) {
                                    echo renderSocialLink($row['rede_social']);
                                }

                                if (!empty($row['link_google'])) {
                                    echo "<p><strong>Google:</strong> 
                                            <a href='" . htmlspecialchars($row['link_google']) . "' target='_blank' rel='noopener noreferrer'>Ver no Google</a></p>";
                                }

                                if (!empty($row['site_prestador'])) {
                                    echo "<p><strong>Site:</strong> 
                                            <a href='" . htmlspecialchars($row['site_prestador']) . "' target='_blank' rel='noopener noreferrer'>Visitar Site</a></p>";
                                }

                                echo "</div></li>";
                                $contador++; // Incrementa para a próxima exibição
                            }
                        } else {
                            echo "<div class='no-results'>
                                    <i class='fas fa-search'></i>
                                    <h3>Nenhum profissional encontrado</h3>
                                    <p>Tente buscar por outro termo ou <a href='index.php'>volte às categorias</a>.</p>
                                  </div>";
                        }
                        ?>
                    </ul>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <footer>
        <div class="footer-grid">
            <div class="footer-column">
                <h4>Bauru Serviços</h4>
                <p>Conectando clientes a prestadores de serviço qualificados em Bauru e região desde 2025.</p>
            </div>
            <div class="footer-column">
                <h4>Navegação</h4>
                <ul>
                    <li><a href="index.php">Início</a></li>
                    <li><a href="cadastro.php">Cadastro de Profissional</a></li>
                    <li><a href="contato.php">Contato</a></li>
                    <li><a href="sobre.php">Sobre Nós</a></li>
                    <li><a href="login.php">Entrar</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4>Contato & Legal</h4>
                <ul>
                    <li><a href="mailto:contato@servicosbauru.com.br">contato@servicosbauru.com.br</a></li>
                    <li><a href="politica_privacidade.php">Política de Privacidade</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h4>Redes Sociais</h4>
                <div class="footer-social-icons">
                    <a href="https://www.instagram.com/bauruservicos" target="_blank" aria-label="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://www.facebook.com/bauruservicosoficial" target="_blank" aria-label="Facebook">
                        <i class="fab fa-facebook"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2025 - <strong>Bauru Serviços</strong>. Todos os direitos reservados.</p>
        </div>
    </footer>
    <?php
    // Fechar conexão e liberar recursos
    if (isset($stmt_profissionais) && $stmt_profissionais instanceof mysqli_stmt) {
        $stmt_profissionais->close();
    }
    if (isset($stmt_categorias) && $stmt_categorias instanceof mysqli_stmt) {
        $stmt_categorias->close();
    }
    if (isset($conn) && $conn->ping()) { // Verifica se a conexão ainda está aberta antes de fechar
        $conn->close();
    }
    ?>

    <script type="application/ld+json">
    {
      "@context": "http://schema.org",
      "@type": "Organization",
      "name": "Bauru Serviços",
      "url": "https://www.bauruservicos.com.br",
      "logo": "https://www.bauruservicos.com.br/imagem/bauru_servico.png",
      "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "+55-14991079668", "contactType": "Customer Service",
        "email": "contato@servicosbauru.com.br"
      },
      "sameAs": [
        "https://www.instagram.com/bauruservicos",
        "https://www.facebook.com/bauruservicosoficial"
      ],
      "address": {
        "@type": "PostalAddress",
        "addressLocality": "Bauru",
        "addressRegion": "SP",
        "addressCountry": "BR"
      }
    }
    </script>
</body>
</html>