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

--
-- Inserindo dados da tabela `profissional`
--

INSERT INTO `profissional` (`id_profissional`, `nome_profissional`, `id_profissao`, `data_nascimento`, `tempo_profissao`, `descricao`, `telefone`, `email`, `rede_social`, `endereco`, `presta_servico_endereco`, `senha`, `nota_profissional`, `link_google`, `site_prestador`, `ativo`) VALUES
(1, 'Sem Hardware', 24, '1997-10-28', 12, 'Consultor especialista em Hardware e Tecnologia a mais de 12 anos no mercado, atuando com montagem de computadores e prestando consultoria de hardware.', '14997943340', 'lucasbdaj@gmail.com', 'https://www.instagram.com/semhardware/', 'Al. Alexandria 6-42', 'N', '$2y$10$aF1oNxHcasNBniIMbdj9.eUJ9RzgL6coUmLbEA3njhKFBQSvGQdYa', '5.0', NULL, NULL, 'S'),
(2, 'Matheus Henrique da Silva', 25, '2000-02-01', 4, 'Especialista em desenvolvimento de Landing Pages de alta performance para infoprodutos e empresas. PÃ¡gina de Vendas, PÃ¡gina de Captura, PÃ¡gina de Obrigado, Blog de LanÃ§amento, PÃ¡gina de Links para bio.', '14991193353', 'contato.matheushdesign@gmail.com', 'https://www.instagram.com/matheush.design', '', 'N', '$2y$10$kgL/dHBAbN.nyzctrWC6k.OEl4pV2LQbsh7SxV3La50FuxGVOurJC', '5.0', NULL, NULL, 'S'),
(3, 'HardSeven', 8, '2014-01-01', 9, 'Jaconia da Silva e Lucas Silva. Ambiente classico e familiar. Corte adulto e infantil.', '14988224257', 'conia_metal@hotmail.com', 'https://www.instagram.com/hard7even/', 'Quintino Bocauva 9-80 prox. ao SESI', 'S', '$2y$10$yo6ywU18ChkxZaS3mn98v.HskGUpuRADKiIRa.Xj4gsmsRpNHm0Q2', '5.0', NULL, NULL, 'S'),
(4, 'Barbearia Cardoso', 8, '1994-08-18', 2, 'O cabelo Ã© a moldura do rosto, cuide bem dele. Aqui sua autoestima Ã© minha prioridade. Agende seu atendimento em domicilio.', '14998436016', 'caiccardoso07@gmail.com', 'https://www.instagram.com/barbeariacardoso2024/', '', 'N', '$2y$10$xz7kypnkhtHvcNTNbYpTSu.noLnXjW/GJcckUxAKPvtks9dwl0/xG', '5.0', NULL, NULL, 'S'),
(5, 'Jonathan Willian da Silva ', 4, '1986-11-11', 20, 'FaÃ§o casas do basico ao acabamento', '14998610709', 'jonathanjhow@gmail.com', '', '', 'N', '$2y$10$KpCvrfJXGrQdNPgCosHnuubp3OxJRRY08aOVwxkbGBi.o6ujlDQ6K', '5.0', NULL, NULL, 'S'),
(6, 'Ana Claudia Unhas', 26, '1988-10-08', 5, 'Manicure e pedicure completa, oferecendo uma ampla variedade de serviÃ§os, como: alongamento, esmaltaÃ§Ã£o em gel, banho de gel, blindagem, nail art, cuidados com cutÃ­culas, hidrataÃ§Ã£o e plÃ¡stica dos pÃ©s.', '14998354601', 'anaclaudiia.unhas@gmail.com', 'https://www.instagram.com/anaclaudiia.unhas/', '', 'N', '$2y$10$/wFQdmcLz34uNrHJsD.CdObIE/dqI.6dXW3DwWcXk6fXJeKHWZ84a', '5.0', NULL, NULL, 'S'),
(7, 'Daniel Pintura', 3, '1974-01-21', 35, 'Prestando servicos com alta qualidade a mais de 35 anos no mercado.', '14997945548', '', '', '', 'N', '2866a68cf2a6efcf29616276843408a7cdc3b399e2a278e35b650fc7551aa57d', '5.0', NULL, NULL, 'S'),
(8, 'Funilaria do Beco', 28, '1979-06-13', 30, 'Funilaria do Beco a mais de 30 anos no ramo de funilaria e pintara, prestando serviÃ§os de qualidade.', '14996457061', 'oficinadobeco123@gmail.com', '', 'Alameda PlutÃ£o 2-35', 'S', '$2y$10$VXzEx97gLeENiW7LhYZ5MuOu.dFppS85Od8iuXEbpvDf71gRy72Ju', '5.0', NULL, NULL,'S'),
(9, 'Matheus Willian', 12, '1999-12-19', 7, 'Programador sÃªnior com experiÃªncia em desenvolvimento de sites e sistemas sob demanda.', '14996159183', 'mathpolato@gmail.com', '', 'Rua Olavo Moura 10-20', 'N', '$2y$10$zcIYeswGNFye8ygzJulcP.eYNC4qFEqQvZO0dG0/R8juIvxpKUZM6', '5.0', NULL, NULL, 'S'),
(10, 'AteliÃª Ana Polatto ', 9, '2003-04-11', 5, 'RealÃ§ando sua belezaâœ¨\r\nMaquiagem profissional \r\nDesign de sobrancelhas \r\nExtensao de cilios\r\nMicropigmentaÃ§Ã£o de sobrancelhas \r\nLash lifting e brow lamination\r\nEpilaÃ§Ã£o egÃ­pcia \r\nCursos vipâ€™s', '14999059719', 'dudulajulia11@gmail.com', 'https://atelieanapolatto.mtswill.com/', 'Santa Edwiges- Bauru', 'S', '$2y$10$2RthEtpX9a9g1WzUUdU8kuxkc2Rw1I8ANCTxr510UHvnwlfNbM4Jy', '5.0', NULL, NULL, 'S'),
(11, 'Luiz Gustavo', 11, '1994-05-30', 7, 'Designer GrÃ¡fico e Motion Desginer, com especializaÃ§Ã£o em branding e comunicaÃ§Ã£o em redes.', '14996884001', 'oliiveira.lg@gmail.com', 'https://www.instagram.com/oliiveira.lg', 'R. Aviador Mario Fundagem Nogueira, 7-36 - AP 44', 'S', '$2y$10$k0iVh4nZ8Afrcb/LD85Yo.Y3Gk49dtMCMPbbL.fAXGumzYnKsVdD2', '5.0', NULL, NULL, 'S');

COMMIT;