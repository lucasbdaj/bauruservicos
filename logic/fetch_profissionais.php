<?php
require_once __DIR__ . "/../config/db_connection.php";

// Constantes para status
define('STATUS_ATIVO', 'S');

/**
 * Busca profissões com profissionais cadastrados e conta quantos profissionais há em cada categoria.
 *
 * @param mysqli $conn Conexão com o banco de dados.
 * @return mysqli_result|false Resultado da consulta ou false em caso de erro.
 */
function getProfissoesComContagem($conn) {
    $profissoesSql = "SELECT pr.id_profissao, pr.nome_profissao, COUNT(p.id_profissional) as total_profissionais
                      FROM profissao pr 
                      JOIN profissional p ON pr.id_profissao = p.id_profissao 
                      WHERE p.ativo = ? 
                      GROUP BY pr.id_profissao, pr.nome_profissao
                      ORDER BY pr.nome_profissao ASC";
    
    $stmt = $conn->prepare($profissoesSql);
    if ($stmt) {
        $statusAtivo = STATUS_ATIVO;
        $stmt->bind_param("s", $statusAtivo);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;;
    } else {
        error_log("Erro na preparação da consulta de profissões: " . $conn->error);
        return false;
    }
}

/**
 * Busca prestadores de serviços de uma categoria específica ordenados por data de cadastro decrescente.
 *
 * @param mysqli $conn Conexão com o banco de dados.
 * @param int $idProfissao ID da profissão selecionada.
 * @return mysqli_result|false Resultado da consulta ou false em caso de erro.
 */
function getPrestadoresPorCategoria($conn, $idProfissao) {
    $sql = "SELECT p.id_profissional, p.nome_profissional, p.descricao, p.telefone, p.email, pr.nome_profissao, 
                   p.tempo_profissao, p.rede_social, p.link_google, p.site_prestador, p.endereco, p.presta_servico_endereco
            FROM profissional p 
            JOIN profissao pr ON p.id_profissao = pr.id_profissao 
            WHERE p.ativo = ? AND pr.id_profissao = ?
            ORDER BY p.id_profissional DESC";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $statusAtivo = STATUS_ATIVO;
        $stmt->bind_param("si", $statusAtivo, $idProfissao);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;;
    } else {
        error_log("Erro na preparação da consulta de prestadores por categoria: " . $conn->error);
        return false;
    }
}

/**
 * Busca prestadores de serviços com base em filtros (para busca geral).
 *
 * @param mysqli $conn Conexão com o banco de dados.
 * @param string $search Termo de busca.
 * @return mysqli_result|false Resultado da consulta ou false em caso de erro.
 */
function getPrestadoresBusca($conn, $search) {
    $sql = "SELECT p.id_profissional, p.nome_profissional, p.descricao, p.telefone, p.email, pr.nome_profissao, 
                   p.tempo_profissao, p.rede_social, p.link_google, p.site_prestador, p.endereco, p.presta_servico_endereco
            FROM profissional p 
            JOIN profissao pr ON p.id_profissao = pr.id_profissao 
            WHERE p.ativo = ? 
            AND (p.nome_profissional LIKE ? OR pr.nome_profissao LIKE ?)
            ORDER BY p.id_profissional DESC";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $statusAtivo = STATUS_ATIVO;
        $searchTerm = "%" . $search . "%";
        $stmt->bind_param("sss", $statusAtivo, $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;;
    } else {
        error_log("Erro na preparação da consulta de prestadores: " . $conn->error);
        return false;
    }
}

// Processar dados do front-end
$search = filter_input(INPUT_GET, 'search', FILTER_DEFAULT) ?? '';
$selectedProfissao = filter_input(INPUT_GET, 'profissao', FILTER_VALIDATE_INT) ?? '';

// Determinar o modo de exibição
$showCategories = empty($search) && empty($selectedProfissao);
$showProfissionais = !empty($selectedProfissao) || !empty($search);

// Obter dados do banco
if ($showCategories) {
    // Mostrar categorias
    $profissoesResult = getProfissoesComContagem($conn);
    if (!$profissoesResult) {
        $fetch_error = "Erro ao buscar profissões.";
        $profissoesResult = null;
    }
    $result = null;
} else if (!empty($selectedProfissao)) {
    // Mostrar profissionais de uma categoria específica
    $result = getPrestadoresPorCategoria($conn, $selectedProfissao);
    if (!$result) {
        $fetch_error = "Erro ao buscar prestadores de serviço";
        $result = null;
    }
    $profissoesResult = null;
} else if (!empty($search)) {
    // Mostrar resultados da busca
    $result = getPrestadoresBusca($conn, $search);
    if (!$result) {
        die("Erro ao buscar prestadores de serviço.");
    }
    $profissoesResult = null;
}
?>