<?php
require_once __DIR__ . '/bootstrap.php';

$message_type = $_SESSION['message_type'] ?? '';
$message_content = $_SESSION['message_content'] ?? '';
unset($_SESSION['message_type'], $_SESSION['message_content']); // Limpa a sessão

// Sanitização e validação das entradas
$search = $_GET['search'] ??'';
$search = strip_tags(trim($search));
$selectedProfissao = filter_input(INPUT_GET, 'profissao', FILTER_SANITIZE_NUMBER_INT);

// --- Lógica para contar profissionais ativos ---
$total_profissionais = 0;
if (isset($conn)) {
    $sql_total = "SELECT COUNT(id_profissional) as total FROM profissional WHERE ativo = 'S'";
    $result_total = $conn->query($sql_total);
    if ($result_total && $result_total->num_rows > 0) {
        $total_profissionais = $result_total->fetch_assoc()['total'];
    }
}



require_once __DIR__ . "/logic/fetch_profissionais.php";

$todas_as_profissoes = getProfissoesComContagem($conn)

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
    
    <?php require_once __DIR__ . "/partials/header.php"; ?>

    <div class="container">        
        <main>
            <?php if ($message_content): ?>
                <p class="message <?php echo htmlspecialchars($message_type); ?>">
                    <?php echo htmlspecialchars($message_content); ?>
                </p>
            <?php endif; ?>
            

            <div class="professional-counter">
                <p>Atualmente, temos <strong><?php echo $total_profissionais; ?></strong> profissionais ativos na plataforma!</p>
            </div>
            <form method="get" action="index.php" id="searchForm">
                <input type="text" name="search" id="searchInput" placeholder="Busque por um nome ou profissão" value="<?php echo htmlspecialchars($search); ?>" class="search-field">
                <select name="profissao" class="search-field" aria-label="Filtrar por profissão">
                    <option value="">Filtrar por profissão...</option>
                    <?php
                    // Verifica se a variável existe e se há linhas
                    if (isset($todas_as_profissoes) && $todas_as_profissoes->num_rows > 0) {
                        // Volta o ponteiro para o início para garantir a leitura
                        $todas_as_profissoes->data_seek(0); 
                        while ($row = $todas_as_profissoes->fetch_assoc()) {
                            // Adiciona 'selected' se esta for a profissão já filtrada
                            $selected = (isset($selectedProfissao) && $selectedProfissao == $row['id_profissao']) ? 'selected' : '';
                            echo "<option value='" . htmlspecialchars($row['id_profissao']) . "' $selected>" . htmlspecialchars($row['nome_profissao']) . "</option>";
                        }
                    }
                    ?>
                </select>
                <button type="submit" class="search-button" aria-label="Buscar prestadores de serviço">
                    <i class="fas fa-search"></i> Buscar
                </button>
                <?php
                // Só exibe o botão de limpar se um dos filtros estiver ativo
                if (!empty($search) || !empty($selectedProfissao)):
                ?>
                    <a href="index.php" class="clear-filter-btn" title="Remover todos os filtros">
                        <i class="fas fa-times"></i> Limpar
                    </a>
                <?php 
                endif;
                ?>
            </form>

            <?php if ($showCategories): ?>
                <div class="categories-section">
                    <h2>Categorias de Serviços</h2>
                    <p>Escolha uma categoria para ver os profissionais disponíveis:</p>
                    
                    <div class="categories-grid">
                        <?php
                        if (isset($profissoesResult) && $profissoesResult->num_rows > 0) {
                            while ($row = $profissoesResult->fetch_assoc()) {
                                echo "<a href='index.php?profissao=" . htmlspecialchars($row['id_profissao']) . "' class='category-card'>
                                    <div class='category-icon'>
                                        <i class='fas fa-tools'></i>
                                    </div>
                                    <h3>" . htmlspecialchars($row['nome_profissao']) . "</h3>
                                    <p>" . htmlspecialchars($row['total_profissionais']) . " profissiona" . ($row['total_profissionais'] > 1 ? 'is' : 'l') . " disponível" . ($row['total_profissionais'] > 1 ? 'is' : '') . "</p>
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
                        if (isset($result) && $result->num_rows > 0) {
                            $contador = 1; 
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
                                    echo "<p><strong>Descrição:</strong> " . nl2br(htmlspecialchars($row['descricao'])) . "</p>";
                                }

                                echo "<p><strong>Tempo de Profissão:</strong> " . htmlspecialchars($row['tempo_profissao']) . " anos</p>";

                                if ($row['presta_servico_endereco'] === 'S' && !empty($row['endereco'])) {
                                    echo "<p><strong>Endereço:</strong> " . htmlspecialchars($row['endereco']) . "</p>";
                                }

                                echo "<div class='contact-info'>
                                <div class='contact-buttons-grid'>";
                                // MODIFICAÇÃO 1: Botões para Ligar e WhatsApp (sem expor o número)
                                echo "  <a href='tel:+55" . $telefone_limpo . "' class='contact-button call-button' title='Ligar para o profissional'>
                                            <i class='fas fa-phone-alt'></i> Ligar
                                        </a>
                                        <a href='https://wa.me/55" . $telefone_limpo . "' target='_blank' class='contact-button whatsapp-button' title='Conversar no WhatsApp'>
                                            <i class='fab fa-whatsapp'></i> WhatsApp
                                        </a>";

                                // MODIFICAÇÃO 2: Botão para Enviar E-mail (sem expor o e-mail)
                                if (!empty($row['email'])) {
                                    echo "<a href='mailto:" . htmlspecialchars($row['email']) . "' class='contact-button email-button' title='Enviar um e-mail'>
                                            <i class='fas fa-envelope'></i> Enviar E-mail
                                        </a>";
                                }

                                // MODIFICAÇÃO 3: Botão para Google Maps (sem expor o endereço)
                                if ($row['presta_servico_endereco'] === 'S' && !empty($row['endereco'])) {
                                    // Codifica o endereço para ser usado em uma URL de forma segura
                                    $endereco_url = urlencode($row['endereco']);
                                    echo "<a href='https://www.google.com/maps/search/?api=1&query=" . $endereco_url . "' target='_blank' class='contact-button maps-button' title='Ver endereço no mapa'>
                                            <i class='fas fa-map-marker-alt'></i> Ver no Mapa
                                        </a>";
                                }

                                echo "  </div>"; // Fim de .contact-buttons-grid

                                // Links públicos (Rede Social, Google, Site) continuam normais
                                $social_links_html = '';
                                if (!empty($row['rede_social'])) {
                                    $social_links_html .= renderSocialLink($row['rede_social']);
                                }
                                if (!empty($row['link_google'])) {
                                    $social_links_html .= "<p><strong>Google:</strong> 
                                                            <a href='" . htmlspecialchars($row['link_google']) . "' target='_blank' rel='noopener noreferrer'>Ver no Google</a></p>";
                                }
                                if (!empty($row['site_prestador'])) {
                                    $social_links_html .= "<p><strong>Site:</strong> 
                                                            <a href='" . htmlspecialchars($row['site_prestador']) . "' target='_blank' rel='noopener noreferrer'>Visitar Site</a></p>";
                                }

                                if (!empty($social_links_html)) {
                                    echo "<div class='social-links-container'>" . $social_links_html . "</div>";
                                }

                                echo "</div></li>";
                                $contador++;
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
        <?php if (!$is_logged_in): ?>
        <div class="cta-container">
            <h3>É prestador de serviço e ainda não se cadastrou?</h3>
            <a href="cadastro.php" class="cta-button">Clique aqui!</a>
        </div>
        <?php endif; ?>
        </main>
    </div>

    
    <?php require_once __DIR__ . "/partials/footer.php"; ?>
    
    <?php
    if (isset($conn) && $conn instanceof mysqli) {
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