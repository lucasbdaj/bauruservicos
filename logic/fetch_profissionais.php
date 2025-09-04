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
 * Busca prestadores de serviços com base em filtros dinâmicos (termo de busca e/ou ID da profissão).
 *
 * @param mysqli $conn Conexão com o banco de dados.
 * @param string $search Termo de busca (opcional).
 * @param int $idProfissao ID da profissão selecionada (opcional).
 * @return mysqli_result|false Resultado da consulta ou false em caso de erro.
 */
function getPrestadoresComFiltros($conn, $search = '', $idProfissao = 0) {
    // Base da consulta SQL
    $sql = "SELECT p.id_profissional, p.nome_profissional, p.descricao, p.telefone, p.email, pr.nome_profissao, 
                   p.tempo_profissao, p.rede_social, p.link_google, p.site_prestador, p.endereco, p.presta_servico_endereco
            FROM profissional p 
            JOIN profissao pr ON p.id_profissao = pr.id_profissao 
            WHERE p.ativo = ?";

    $params = [STATUS_ATIVO];
    $types = 's';

    // Adiciona o filtro por ID da profissão, se fornecido
    if (!empty($idProfissao)) {
        $sql .= " AND pr.id_profissao = ?";
        $params[] = $idProfissao;
        $types .= 'i';
    }

    // Adiciona o filtro por termo de busca, se fornecido
    if (!empty($search)) {
        $sql .= " AND (p.nome_profissional LIKE ?)";
        $searchTerm = "%" . $search . "%";
        $params[] = $searchTerm;
        $types .= 's';
    }

    $sql .= " ORDER BY p.id_profissional DESC";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        // Usa o operador splat (...) para passar os parâmetros dinamicamente
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    } else {
        error_log("Erro na preparação da consulta de prestadores com filtros: " . $conn->error);
        return false;
    }
}

// Processar dados do front-end
$search = filter_input(INPUT_GET, 'search', FILTER_DEFAULT) ?? '';
$selectedProfissao = filter_input(INPUT_GET, 'profissao', FILTER_VALIDATE_INT) ?? 0;

// Determinar o modo de exibição
$showCategories = empty($search) && empty($selectedProfissao);

// Obter dados do banco
if ($showCategories) {
    // Modo de exibição: Categorias
    $profissoesResult = getProfissoesComContagem($conn);
    if (!$profissoesResult) {
        $fetch_error = "Erro ao buscar profissões.";
    }
    $result = null; // Garante que a variável de resultados de profissionais esteja nula

} else {
    // Modo de exibição: Lista de Profissionais (com ou sem filtros)
    $result = getPrestadoresComFiltros($conn, $search, $selectedProfissao);
    if (!$result) {
        $fetch_error = "Erro ao buscar prestadores de serviço.";
    }
    $profissoesResult = null; // Garante que a variável de profissões esteja nula
}
?>