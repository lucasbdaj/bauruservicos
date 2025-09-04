-- Remove o banco de dados se ele existir para garantir uma recriação limpa
DROP DATABASE IF EXISTS `bauru2971415_servicosbauru`;

-- Cria o banco de dados com o padrão de caracteres UTF-8 recomendado
CREATE DATABASE `bauru2971415_servicosbauru` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Seleciona o banco de dados recém-criado para uso
USE `bauru2971415_servicosbauru`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Banco de dados: `bauru2971415_servicosbauru`

-- Remove as tabelas caso já existam
DROP TABLE IF EXISTS avaliacao;
DROP TABLE IF EXISTS servico;
DROP TABLE IF EXISTS profissional;
DROP TABLE IF EXISTS cliente;
DROP TABLE IF EXISTS profissao;
DROP TABLE IF EXISTS contato;

-- Estrutura da tabela `cliente`
CREATE TABLE `cliente` (
  `id_cliente` int(11) NOT NULL,
  `nome_cliente` varchar(250) NOT NULL,
  `data_nascimento` date NOT NULL,
  `telefone` varchar(11) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Estrutura da tabela `contato`
CREATE TABLE `contato` (
  `id_contato` int(11) NOT NULL,
  `nome_contato` varchar(250) NOT NULL,
  `email_contato` varchar(100) NOT NULL,
  `telefone_contato` varchar(15) NOT NULL,
  `mensagem` text NOT NULL,
  `data_envio` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Estrutura da tabela `profissao`
CREATE TABLE `profissao` (
  `id_profissao` int(11) NOT NULL,
  `nome_profissao` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Acionadores `profissao`
DELIMITER $$
CREATE TRIGGER `verificar_profissao_unica` BEFORE INSERT ON `profissao` FOR EACH ROW BEGIN
    -- Verifica se a profissão já existe
    IF EXISTS (SELECT 1 FROM profissao WHERE nome_profissao = NEW.nome_profissao) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Profissão já cadastrada.';
    END IF;
END
$$
DELIMITER ;


-- Estrutura da tabela `profissional`
CREATE TABLE `profissional` (
  `id_profissional` int(11) NOT NULL,
  `nome_profissional` varchar(250) NOT NULL,
  `id_profissao` int(11) NOT NULL,
  `data_nascimento` date NOT NULL,
  `tempo_profissao` int(11) NOT NULL,
  `descricao` varchar(500) NOT NULL,
  `telefone` varchar(11) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `rede_social` varchar(50) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `presta_servico_endereco` char(1) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `nota_profissional` decimal(2,1) DEFAULT NULL,
  `link_google` varchar(255) DEFAULT NULL COMMENT 'Link da página do Google do prestador',
  `site_prestador` varchar(255) DEFAULT NULL COMMENT 'Site próprio do prestador',
  `ativo` char(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Acionadores `profissional`
DELIMITER $$
CREATE TRIGGER `validar_profissional_unico` BEFORE INSERT ON `profissional` FOR EACH ROW BEGIN
    -- Verifica se o telefone j? est? cadastrado
    IF EXISTS (SELECT 1 FROM profissional WHERE telefone = NEW.telefone) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O telefone já está cadastrado para outro profissional.';
    END IF;

    -- Verifica se o email j? est? cadastrado
    IF EXISTS (SELECT 1 FROM profissional WHERE email = NEW.email) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O email já está cadastrado para outro profissional.';
    END IF;
END
$$
DELIMITER ;

-- Estrutura da tabela `servico`
CREATE TABLE `servico` (
  `id_servico` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_profissional` int(11) NOT NULL,
  `status` char(1) NOT NULL,
  `data_agendamento` date NOT NULL,
  `data_realizada` date DEFAULT NULL,
  `valor_servico` decimal(7,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AUTO_INCREMENT de tabelas despejadas
-- Índices para tabelas
ALTER TABLE `cliente` ADD PRIMARY KEY (`id_cliente`);
ALTER TABLE `contato` ADD PRIMARY KEY (`id_contato`);
ALTER TABLE `profissao` ADD PRIMARY KEY (`id_profissao`);
ALTER TABLE `profissional` ADD PRIMARY KEY (`id_profissional`), ADD KEY `id_profissao` (`id_profissao`);
ALTER TABLE `servico` ADD PRIMARY KEY (`id_servico`), ADD KEY `id_cliente` (`id_cliente`), ADD KEY `id_profissional` (`id_profissional`);

-- Configuração do AUTO_INCREMENT para as tabelas
ALTER TABLE `cliente` MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `contato` MODIFY `id_contato` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `profissao` MODIFY `id_profissao` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `profissional` MODIFY `id_profissional` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `servico` MODIFY `id_servico` int(11) NOT NULL AUTO_INCREMENT;

-- Definição das chaves estrangeiras (restrições)
ALTER TABLE `profissional` ADD CONSTRAINT `profissional_ibfk_1` FOREIGN KEY (`id_profissao`) REFERENCES `profissao` (`id_profissao`);
ALTER TABLE `servico` ADD CONSTRAINT `servico_ibfk_1` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`), ADD CONSTRAINT `servico_ibfk_2` FOREIGN KEY (`id_profissional`) REFERENCES `profissional` (`id_profissional`);

-- Insert de dados da tabela `profissao`
INSERT INTO `profissao` (`id_profissao`, `nome_profissao`) VALUES
(1, 'Eletricista'),
(2, 'Encanador'),
(3, 'Pintor'),
(4, 'Pedreiro'),
(5, 'Jardineiro'),
(6, 'Carpinteiro'),
(7, 'Mecanico'),
(8, 'Cabeleireiro'),
(9, 'Esteticista'),
(10, 'Nutricionista'),
(11, 'Designer Grafico'),
(12, 'Desenvolvedor de Software'),
(13, 'Fotografo'),
(14, 'Professor'),
(15, 'Fisioterapeuta'),
(16, 'Contador'),
(17, 'Arquiteto'),
(18, 'Tapeceiro'),
(19, 'Massagista'),
(20, 'Marido de Aluguel'),
(21, 'Garcom'),
(22, 'Motorista'),
(23, 'Faxineiro'),
(24, 'Consultor de TI'),
(25, 'Webdesigner'),
(26, 'Manicure'),
(27, 'Alfaiate'),
(28, 'Funilaria e Pintura'),
(29, 'Cuidador de Idosos'),
(30, 'Fretes e Carretos'),
(31, 'Montador de Moveis');
