-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Dec 02, 2025 at 09:39 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cinelive`
--

-- --------------------------------------------------------

--
-- Table structure for table `aluguer_sala`
--

DROP TABLE IF EXISTS `aluguer_sala`;
CREATE TABLE IF NOT EXISTS `aluguer_sala` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cliente_id` int DEFAULT NULL,
  `cinema_id` int NOT NULL,
  `sala_id` int DEFAULT NULL,
  `data` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fim` time NOT NULL,
  `estado` enum('pendente','confirmado','cancelado') NOT NULL,
  `tipo_evento` varchar(100) NOT NULL,
  `observacoes` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx-aluguer_sala-cliente_id` (`cliente_id`),
  KEY `idx-aluguer_sala-sala_id` (`sala_id`),
  KEY `cinema_id` (`cinema_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `aluguer_sala`
--

INSERT INTO `aluguer_sala` (`id`, `cliente_id`, `cinema_id`, `sala_id`, `data`, `hora_inicio`, `hora_fim`, `estado`, `tipo_evento`, `observacoes`) VALUES
(1, 14, 1, 1, '2026-01-29', '10:00:00', '12:00:00', 'pendente', 'Festa de aniversário', 'Somos um grupo de 8 pessoas e vamos celebrar um aniversário de forma simples. Vou levar alguns balões e uma pequena decoração para colocar na entrada da sala, por isso agradecia se pudesse entrar uns 15 minutos mais cedo. Também vou levar um bolo, por isso precisava de um pequeno espaço para o pousar antes de começarmos a sessão.');

-- --------------------------------------------------------

--
-- Table structure for table `auth_assignment`
--

DROP TABLE IF EXISTS `auth_assignment`;
CREATE TABLE IF NOT EXISTS `auth_assignment` (
  `item_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `user_id` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `created_at` int DEFAULT NULL,
  PRIMARY KEY (`item_name`,`user_id`),
  KEY `idx-auth_assignment-user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `auth_assignment`
--

INSERT INTO `auth_assignment` (`item_name`, `user_id`, `created_at`) VALUES
('admin', '1', 1761852169),
('cliente', '14', 1762016693),
('cliente', '15', 1764708269),
('cliente', '16', 1764708322),
('funcionario', '10', 1762528324),
('funcionario', '11', 1761899669),
('funcionario', '12', 1761899704),
('funcionario', '13', 1761899750),
('funcionario', '5', 1762535118),
('funcionario', '6', 1762108148),
('funcionario', '7', 1761859681),
('funcionario', '8', 1761858274),
('funcionario', '9', 1761899566),
('gerente', '2', 1762543612),
('gerente', '3', 1762543597),
('gerente', '4', 1761658002);

-- --------------------------------------------------------

--
-- Table structure for table `auth_item`
--

DROP TABLE IF EXISTS `auth_item`;
CREATE TABLE IF NOT EXISTS `auth_item` (
  `name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `type` smallint NOT NULL,
  `description` text CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci,
  `rule_name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `data` blob,
  `created_at` int DEFAULT NULL,
  `updated_at` int DEFAULT NULL,
  PRIMARY KEY (`name`),
  KEY `rule_name` (`rule_name`),
  KEY `idx-auth_item-type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `auth_item`
--

INSERT INTO `auth_item` (`name`, `type`, `description`, `rule_name`, `data`, `created_at`, `updated_at`) VALUES
('admin', 1, NULL, NULL, NULL, 1764361114, 1764361114),
('alterarEstadoFuncionario', 2, 'Ativar/Desativar funcionários do cinema', 'matchCinema', NULL, 1764361114, 1764361114),
('cliente', 1, NULL, NULL, NULL, 1764361114, 1764361114),
('confirmarBilhetes', 2, 'Confirmar bilhetes', NULL, NULL, 1764361114, 1764361114),
('confirmarBilhetesCinema', 2, 'Confirmar bilhetes de um cinema', 'matchCinema', NULL, 1764361114, 1764361114),
('criarAluguer', 2, 'Criar pedido de aluguer de sala', NULL, NULL, 1764361114, 1764361114),
('criarCompra', 2, 'Criar uma compra', NULL, NULL, 1764361114, 1764361114),
('criarFuncionariosCinema', 2, 'Criar funcionário para o seu cinema', 'matchCinema', NULL, 1764361114, 1764361114),
('editarCinema', 2, 'Editar dados gerais do cinema', 'matchCinema', NULL, 1764361114, 1764361114),
('editarPerfil', 2, 'Editar o seu perfil', 'isOwnPerfil', NULL, 1764361114, 1764361114),
('eliminarAluguer', 2, 'Eliminar pedido de aluguer de sala pendente', 'isCliente', NULL, 1764361114, 1764361114),
('eliminarPerfil', 2, 'Eliminar o seu perfil', 'isOwnPerfil', NULL, 1764361114, 1764361114),
('funcionario', 1, NULL, NULL, NULL, 1764361114, 1764361114),
('gerente', 1, NULL, NULL, NULL, 1764361114, 1764361114),
('gerirAlugueres', 2, 'Gerir todos os alugueres', NULL, NULL, 1764361114, 1764361114),
('gerirAlugueresCinema', 2, 'Gerir alugueres do cinema', 'matchCinema', NULL, 1764361114, 1764361114),
('gerirCinemas', 2, 'Gerir todos os cinemas', NULL, NULL, 1764361114, 1764361114),
('gerirFilmes', 2, 'Gerir filmes', NULL, NULL, 1764361114, 1764361114),
('gerirGeneros', 2, 'Gerir géneros', NULL, NULL, 1764361114, 1764361114),
('gerirSalas', 2, 'Gerir todas as salas', NULL, NULL, 1764361114, 1764361114),
('gerirSalasCinema', 2, 'Gerir salas do cinema', 'matchCinema', NULL, 1764361114, 1764361114),
('gerirSessoes', 2, 'Gerir todas as sessões', NULL, NULL, 1764361114, 1764361114),
('gerirSessoesCinema', 2, 'Gerir sessões do cinema', 'matchCinema', NULL, 1764361114, 1764361114),
('gerirUtilizadores', 2, 'Gerir todos os utilizadores', NULL, NULL, 1764361114, 1764361114),
('verAlugueres', 2, 'Ver os seus aluguers', 'isCliente', NULL, 1764361114, 1764361114),
('verAlugueresCinema', 2, 'Ver alugueres de sala de um cinema', 'matchCinema', NULL, 1764361114, 1764361114),
('verCinema', 2, 'Ver o seu cinema', 'matchCinema', NULL, 1764361114, 1764361114),
('verCompras', 2, 'Ver as suas compras', 'isCliente', NULL, 1764361114, 1764361114),
('verComprasCinema', 2, 'Ver compras de um cinema', 'matchCinema', NULL, 1764361114, 1764361114),
('verEstatisticas', 2, 'Ver estatísticas globais', NULL, NULL, 1764361114, 1764361114),
('verEstatisticasCinema', 2, 'Ver estatísticas de um cinema', 'matchCinema', NULL, 1764361114, 1764361114),
('verFuncionariosCinema', 2, 'Ver funcionários do seu cinema', 'matchCinema', NULL, 1764361114, 1764361114),
('verPerfil', 2, 'Ver o seu perfil', 'isOwnPerfil', NULL, 1764361114, 1764361114),
('verSalasCinema', 2, 'Ver salas de um cinema', 'matchCinema', NULL, 1764361114, 1764361114),
('verSessoesCinema', 2, 'Ver sessões de um cinema', 'matchCinema', NULL, 1764361114, 1764361114),
('verTodasCompras', 2, 'Ver todas as compras', NULL, NULL, 1764361114, 1764361114);

-- --------------------------------------------------------

--
-- Table structure for table `auth_item_child`
--

DROP TABLE IF EXISTS `auth_item_child`;
CREATE TABLE IF NOT EXISTS `auth_item_child` (
  `parent` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `child` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  PRIMARY KEY (`parent`,`child`),
  KEY `child` (`child`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `auth_item_child`
--

INSERT INTO `auth_item_child` (`parent`, `child`) VALUES
('gerente', 'alterarEstadoFuncionario'),
('admin', 'confirmarBilhetes'),
('funcionario', 'confirmarBilhetesCinema'),
('cliente', 'criarAluguer'),
('cliente', 'criarCompra'),
('gerente', 'criarFuncionariosCinema'),
('gerente', 'editarCinema'),
('cliente', 'editarPerfil'),
('cliente', 'eliminarAluguer'),
('cliente', 'eliminarPerfil'),
('gerente', 'funcionario'),
('admin', 'gerirAlugueres'),
('gerente', 'gerirAlugueresCinema'),
('admin', 'gerirCinemas'),
('admin', 'gerirFilmes'),
('admin', 'gerirGeneros'),
('admin', 'gerirSalas'),
('gerente', 'gerirSalasCinema'),
('admin', 'gerirSessoes'),
('gerente', 'gerirSessoesCinema'),
('admin', 'gerirUtilizadores'),
('cliente', 'verAlugueres'),
('funcionario', 'verAlugueresCinema'),
('funcionario', 'verCinema'),
('cliente', 'verCompras'),
('funcionario', 'verComprasCinema'),
('admin', 'verEstatisticas'),
('funcionario', 'verEstatisticasCinema'),
('gerente', 'verFuncionariosCinema'),
('cliente', 'verPerfil'),
('funcionario', 'verSalasCinema'),
('funcionario', 'verSessoesCinema'),
('admin', 'verTodasCompras');

-- --------------------------------------------------------

--
-- Table structure for table `auth_rule`
--

DROP TABLE IF EXISTS `auth_rule`;
CREATE TABLE IF NOT EXISTS `auth_rule` (
  `name` varchar(64) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `data` blob,
  `created_at` int DEFAULT NULL,
  `updated_at` int DEFAULT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `auth_rule`
--

INSERT INTO `auth_rule` (`name`, `data`, `created_at`, `updated_at`) VALUES
('isCliente', 0x4f3a32343a22636f6e736f6c655c726261635c436c69656e746552756c65223a333a7b733a343a226e616d65223b733a393a226973436c69656e7465223b733a393a22637265617465644174223b693a313736343336313131343b733a393a22757064617465644174223b693a313736343336313131343b7d, 1764361114, 1764361114),
('isOwnPerfil', 0x4f3a32363a22636f6e736f6c655c726261635c4f776e50657266696c52756c65223a333a7b733a343a226e616d65223b733a31313a2269734f776e50657266696c223b733a393a22637265617465644174223b693a313736343336313131343b733a393a22757064617465644174223b693a313736343336313131343b7d, 1764361114, 1764361114),
('matchCinema', 0x4f3a32383a22636f6e736f6c655c726261635c4d6174636843696e656d6152756c65223a333a7b733a343a226e616d65223b733a31313a226d6174636843696e656d61223b733a393a22637265617465644174223b693a313736343336313131343b733a393a22757064617465644174223b693a313736343336313131343b7d, 1764361114, 1764361114);

-- --------------------------------------------------------

--
-- Table structure for table `bilhete`
--

DROP TABLE IF EXISTS `bilhete`;
CREATE TABLE IF NOT EXISTS `bilhete` (
  `id` int NOT NULL AUTO_INCREMENT,
  `compra_id` int NOT NULL,
  `lugar` varchar(3) NOT NULL,
  `preco` decimal(5,2) NOT NULL,
  `codigo` varchar(45) NOT NULL,
  `estado` enum('pendente','confirmado','cancelado') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `idx-bilhete-compra_id` (`compra_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cinema`
--

DROP TABLE IF EXISTS `cinema`;
CREATE TABLE IF NOT EXISTS `cinema` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(80) NOT NULL,
  `rua` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `codigo_postal` varchar(8) NOT NULL,
  `cidade` varchar(50) NOT NULL,
  `latitude` decimal(10,6) NOT NULL,
  `longitude` decimal(10,6) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefone` int NOT NULL,
  `horario_abertura` time NOT NULL,
  `horario_fecho` time NOT NULL,
  `estado` enum('ativo','encerrado') NOT NULL,
  `gerente_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx-cinema-gerente_id` (`gerente_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cinema`
--

INSERT INTO `cinema` (`id`, `nome`, `rua`, `codigo_postal`, `cidade`, `latitude`, `longitude`, `email`, `telefone`, `horario_abertura`, `horario_fecho`, `estado`, `gerente_id`) VALUES
(1, 'CineLive Leiria', 'Rua Dr. Francisco Sá Carneiro Nº25', '2400-149', 'Leiria', 39.743620, -8.807049, 'leiria@cinelive.pt', 244123456, '10:00:00', '23:30:00', 'ativo', 2),
(2, 'CineLive Lisboa', 'Avenida da Liberdade Nº180', '1250-146', 'Lisboa', 38.720345, -9.144021, 'lisboa@cinelive.pt', 213456789, '09:30:00', '23:30:00', 'ativo', 3),
(3, 'CineLive Porto', 'Rua de Santa Catarina Nº428', '4000-446', 'Porto', 41.149610, -8.606277, 'porto@cinelive.pt', 222456789, '10:00:00', '23:45:00', 'ativo', 4);

-- --------------------------------------------------------

--
-- Table structure for table `compra`
--

DROP TABLE IF EXISTS `compra`;
CREATE TABLE IF NOT EXISTS `compra` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cliente_id` int DEFAULT NULL,
  `sessao_id` int NOT NULL,
  `data` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `pagamento` enum('mbway','cartao','multibanco') NOT NULL,
  `estado` enum('confirmada','cancelada') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx-compra-cliente_id` (`cliente_id`),
  KEY `sessao_id` (`sessao_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `filme`
--

DROP TABLE IF EXISTS `filme`;
CREATE TABLE IF NOT EXISTS `filme` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `sinopse` text NOT NULL,
  `duracao` int NOT NULL,
  `rating` enum('Todos','M3','M6','M12','M14','M16','M18') NOT NULL,
  `estreia` date NOT NULL,
  `idioma` varchar(50) NOT NULL,
  `realizacao` varchar(80) NOT NULL,
  `trailer_url` varchar(255) NOT NULL,
  `poster_path` varchar(255) NOT NULL,
  `estado` enum('brevemente','em_exibicao','terminado') NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `filme`
--

INSERT INTO `filme` (`id`, `titulo`, `sinopse`, `duracao`, `rating`, `estreia`, `idioma`, `realizacao`, `trailer_url`, `poster_path`, `estado`) VALUES
(1, 'Interstellar', 'Um grupo de exploradores espaciais viaja através de um buraco de minhoca em busca de um novo planeta habitável, enquanto a Terra enfrenta um colapso ambiental e humano.', 169, 'M12', '2025-10-23', 'Inglês', 'Christopher Nolan', 'https://www.youtube.com/watch?v=2LqzF5WauAw', 'poster_68fa01aecd6d2.jpg', 'brevemente'),
(2, 'The Truman Show', 'Truman Burbank vive uma vida aparentemente perfeita até começar a suspeitar que tudo à sua volta é parte de um gigantesco programa televisivo.', 103, 'M12', '2025-10-22', 'Inglês', 'Peter Weir', 'https://www.youtube.com/watch?v=dlnmQbPGuls', 'poster_68fa080f7e03f.jpg', 'em_exibicao'),
(3, 'Shutter Island', 'Um agente federal norte-americano investiga o desaparecimento de uma paciente num hospital psiquiátrico isolado, mas à medida que aprofunda o caso começa a duvidar da sua própria sanidade.', 139, 'M16', '2010-02-19', 'Inglês', 'Martin Scorsese', 'https://www.youtube.com/watch?v=gN02XJ9pDAU', 'poster_690329f4e86ee.jpg', 'em_exibicao'),
(4, 'The Social Network', 'Um estudante de Harvard cria uma plataforma online que rapidamente se transforma no Facebook, mas o sucesso traz consigo conflitos pessoais e batalhas legais pela sua verdadeira autoria.', 120, 'M12', '2010-10-01', 'Inglês', 'David Fincher', 'https://www.youtube.com/watch?v=lB95KLmpLR4', 'poster_69032dee2ed44.jpg', 'em_exibicao'),
(5, 'Prisoners', 'Duas raparigas desaparecem durante um feriado em família e, enquanto a polícia investiga, o pai de uma delas decide assumir o caso sozinho, mergulhando numa espiral de desespero e vingança.', 153, 'M16', '2013-09-20', 'Inglês', 'Denis Villeneuve', 'https://www.youtube.com/watch?v=bpXfcTF6iVk', 'poster_69032e821fed0.jpg', 'brevemente'),
(6, 'Zodiac', 'Um desenhador de cartoons de São Francisco torna-se obcecado em decifrar a identidade do assassino em série apelidado de “Zodíaco”, que aterroriza a área da baía com cartas cifradas, assassinatos e o troçar da polícia.', 157, 'M16', '2007-03-02', 'Inglês', 'David Fincher', 'https://www.youtube.com/watch?v=yNncHPl1UXg', 'poster_69032f4f900e9.jpg', 'brevemente'),
(7, 'Toy Story', 'Um brinquedo vê a sua posição ameaçada quando um novo boneco espacial chega ao quarto, desencadeando uma aventura onde ambos terão de superar rivalidades e trabalhar juntos para regressar a casa do dono.', 81, 'M6', '1996-03-29', 'Português', 'John Lasseter', 'https://www.youtube.com/watch?v=v-PjgYDrg70', 'poster_6910b61fbd2d6.jpg', 'brevemente'),
(8, 'Carros 2', 'Um grande campeão das pistas é lançado numa corrida internacional enquanto o seu amigo Mate é apanhado num enredo de espionagem que põe à prova a amizade de ambos e mostra que coragem pode surgir dos lugares mais improváveis.', 106, 'M6', '2011-06-14', 'Português', 'John Lasseter', 'https://www.youtube.com/watch?v=oFTfAdauCOo', 'poster_6910b6ad1f9ea.jpg', 'em_exibicao'),
(9, 'The Prestige', 'Dois mágicos rivais da era vitoriana competem obsessivamente para superar-se, à medida que o truque se torna cada vez mais perigoso, ambos descobrem que o preço da obsessão pode ser a própria identidade.', 130, 'M14', '2006-12-28', 'Inglês', 'Christopher Nolan', 'https://www.youtube.com/watch?v=RLtaA9fFNXU', 'poster_6918b2d190384.jpg', 'em_exibicao'),
(10, 'Seven', 'Dois detetives perseguem um serial killer cujos crimes representam os sete pecados capitais numa investigação sombria que descobre o horror nas mais triviais escolhas humanas.', 127, 'M16', '1996-02-02', 'Inglês', 'David Fincher', 'https://www.youtube.com/watch?v=znmZoVkCjpI', 'poster_6918b3d09bd73.jpg', 'em_exibicao'),
(11, 'Divertida-Mente', 'Riley tem 11 anos e muda-se com a família para São Francisco. No quartel-general da sua mente vivem as emoções Alegria, Tristeza, Medo, Raiva e Repulsa, que tentam orientar-na, mas quando os sentimentos entram em conflito, Riley afronta uma nova vida, nova escola e a incerteza de crescer.', 94, 'Todos', '2015-06-18', 'Português', 'Pete Docter', 'https://www.youtube.com/watch?v=yRUAzGQ3nSY', 'poster_6918b4c3cf56d.jpg', 'brevemente'),
(12, 'Monstros e Companhia', 'Na fábrica Monstros S.A., os monstros recolhem gritos de crianças para gerar energia, mas tudo muda quando uma menina humana entra no mundo dos monstros, obrigando Sulley e Mike a enfrentarem medos, conspirações e a descobrir que a amizade pode ser a força mais poderosa de todas.', 92, 'M3', '2002-03-14', 'Português', 'Pete Docter', 'https://www.youtube.com/watch?v=CGbgaHoapFM', 'poster_6918b5ace5025.jpg', 'brevemente'),
(13, 'Inception', 'Dom Cobb é um especialista em invadir sonhos para roubar segredos do subconsciente. Para recuperar a sua vida e regressar aos filhos, aceita uma missão arriscada: implantar uma ideia na mente de um alvo, algo considerado quase impossível.', 148, 'M12', '2010-07-16', 'Inglês', 'Christopher Nolan', 'https://www.youtube.com/watch?v=YoHD9XEInc0', 'poster_692df41aa80ee.jpg', 'brevemente'),
(14, 'Tenet', 'Um agente secreto é recrutado por uma organização chamada Tenet para impedir uma ameaça global ligada à inversão do tempo. Enfrentando um inimigo capaz de manipular o futuro, embarca numa missão que desafia as leis da física.', 150, 'M14', '2025-12-02', 'Inglês', 'Christopher Nolan', 'https://www.youtube.com/watch?v=ASTU3rFyOm4', 'poster_692f54ee29783.jpg', 'brevemente'),
(15, 'The Batman', 'Bruce Wayne assume o papel de vigilante numa Gotham mergulhada no crime e na corrupção. Enquanto investiga uma série de assassinatos cometidos pelo enigmático Riddler, descobre segredos sombrios que envolvem a elite da cidade.', 176, 'M14', '2022-03-03', 'Inglês', 'Matt Reeves', 'https://www.youtube.com/watch?v=mqqft2x_Aa4', 'poster_692f562166fe4.jpg', 'em_exibicao'),
(16, 'O Panda do Kung Fu', 'Po é um panda preguiçoso que trabalha na loja de noodles da família e sonha ser mestre de kung fu. Por obra do destino, acaba escolhido como o “Guerreiro-Dragão”, o único capaz de salvar o Vale da Paz de um perigoso vilão.', 92, 'M6', '2008-06-06', 'Português', 'John Stevenson', 'https://www.youtube.com/watch?v=PXi3Mv6KMzY', 'poster_692f57459cac6.jpg', 'em_exibicao');

-- --------------------------------------------------------

--
-- Table structure for table `filme_genero`
--

DROP TABLE IF EXISTS `filme_genero`;
CREATE TABLE IF NOT EXISTS `filme_genero` (
  `filme_id` int NOT NULL,
  `genero_id` int NOT NULL,
  KEY `idx-filme_genero-filme_id` (`filme_id`),
  KEY `idx-filme_genero-genero_id` (`genero_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `filme_genero`
--

INSERT INTO `filme_genero` (`filme_id`, `genero_id`) VALUES
(7, 8),
(7, 9),
(7, 4),
(3, 9),
(3, 1),
(3, 10),
(4, 1),
(4, 3),
(5, 2),
(5, 1),
(5, 10),
(10, 2),
(10, 10),
(10, 3),
(8, 8),
(8, 9),
(8, 1),
(12, 2),
(12, 8),
(12, 4),
(9, 2),
(9, 1),
(9, 3),
(11, 8),
(11, 9),
(11, 4),
(2, 2),
(2, 1),
(2, 3),
(13, 2),
(13, 9),
(13, 7),
(6, 2),
(6, 1),
(6, 10),
(1, 2),
(1, 1),
(1, 7),
(14, 2),
(14, 7),
(14, 3),
(15, 2),
(15, 10),
(15, 3),
(16, 8),
(16, 9),
(16, 4);

-- --------------------------------------------------------

--
-- Table structure for table `genero`
--

DROP TABLE IF EXISTS `genero`;
CREATE TABLE IF NOT EXISTS `genero` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(80) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `genero`
--

INSERT INTO `genero` (`id`, `nome`) VALUES
(2, 'Ação'),
(8, 'Animação'),
(9, 'Aventura'),
(4, 'Comédia'),
(1, 'Drama'),
(7, 'Ficção Científica'),
(10, 'Policial'),
(5, 'Romance'),
(3, 'Suspense'),
(6, 'Terror');

-- --------------------------------------------------------

--
-- Table structure for table `migration`
--

DROP TABLE IF EXISTS `migration`;
CREATE TABLE IF NOT EXISTS `migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `migration`
--

INSERT INTO `migration` (`version`, `apply_time`) VALUES
('m000000_000000_base', 1761138446),
('m130524_201442_init', 1761138447),
('m140506_102106_rbac_init', 1761139159),
('m170907_052038_rbac_add_index_on_auth_assignment_user_id', 1761139159),
('m180523_151638_rbac_updates_indexes_without_prefix', 1761139159),
('m190124_110200_add_verification_token_column_to_user_table', 1761138447),
('m200409_110543_rbac_update_mssql_trigger', 1761139159),
('m251022_130422_init_cinelive', 1761138449);

-- --------------------------------------------------------

--
-- Table structure for table `sala`
--

DROP TABLE IF EXISTS `sala`;
CREATE TABLE IF NOT EXISTS `sala` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cinema_id` int NOT NULL,
  `numero` int NOT NULL,
  `num_filas` int NOT NULL,
  `num_colunas` int NOT NULL,
  `preco_bilhete` decimal(5,2) NOT NULL,
  `estado` enum('ativa','encerrada') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx-sala-cinema_id` (`cinema_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sala`
--

INSERT INTO `sala` (`id`, `cinema_id`, `numero`, `num_filas`, `num_colunas`, `preco_bilhete`, `estado`) VALUES
(1, 1, 1, 10, 12, 8.00, 'ativa'),
(2, 1, 2, 10, 10, 10.00, 'ativa'),
(3, 1, 3, 10, 14, 8.00, 'ativa'),
(4, 1, 4, 10, 10, 40.00, 'ativa'),
(5, 1, 5, 10, 12, 6.00, 'ativa'),
(6, 1, 6, 10, 10, 8.00, 'ativa'),
(7, 2, 1, 10, 14, 12.00, 'ativa'),
(8, 2, 2, 10, 10, 8.00, 'ativa'),
(9, 2, 3, 8, 10, 12.00, 'ativa'),
(10, 2, 4, 10, 10, 6.00, 'ativa'),
(11, 3, 1, 12, 14, 8.00, 'ativa'),
(12, 3, 2, 10, 10, 10.00, 'ativa'),
(13, 3, 3, 12, 12, 6.00, 'ativa'),
(14, 3, 4, 10, 10, 6.00, 'ativa'),
(15, 3, 5, 10, 12, 8.00, 'ativa'),
(16, 3, 6, 10, 12, 12.00, 'ativa');

-- --------------------------------------------------------

--
-- Table structure for table `sessao`
--

DROP TABLE IF EXISTS `sessao`;
CREATE TABLE IF NOT EXISTS `sessao` (
  `id` int NOT NULL AUTO_INCREMENT,
  `data` date NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fim` time NOT NULL,
  `filme_id` int NOT NULL,
  `sala_id` int NOT NULL,
  `cinema_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx-sessao-filme_id` (`filme_id`),
  KEY `idx-sessao-sala_id` (`sala_id`),
  KEY `idx-sessao-cinema_id` (`cinema_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sessao`
--

INSERT INTO `sessao` (`id`, `data`, `hora_inicio`, `hora_fim`, `filme_id`, `sala_id`, `cinema_id`) VALUES
(1, '2026-02-11', '13:00:00', '18:33:00', 8, 1, 1),
(2, '2026-01-26', '10:00:00', '12:00:00', 4, 10, 2),
(3, '2026-01-18', '15:00:00', '16:32:00', 16, 4, 1),
(4, '2026-01-15', '19:00:00', '21:56:00', 15, 8, 2),
(5, '2026-01-22', '10:00:00', '11:46:00', 8, 9, 2),
(6, '2026-01-14', '11:00:00', '13:19:00', 8, 5, 1),
(7, '2026-01-21', '16:00:00', '17:46:00', 7, 1, 1),
(8, '2026-01-11', '10:00:00', '11:43:00', 2, 2, 1),
(9, '2026-02-16', '20:00:00', '21:46:00', 8, 7, 2),
(10, '2026-01-29', '20:00:00', '22:19:00', 3, 3, 1),
(12, '2026-01-29', '18:00:00', '20:56:00', 15, 2, 1),
(13, '2026-01-29', '20:00:00', '22:56:00', 15, 1, 1),
(14, '2026-02-02', '19:30:00', '21:37:00', 10, 4, 1),
(15, '2026-01-30', '21:00:00', '23:00:00', 4, 1, 1),
(16, '2026-01-31', '17:00:00', '18:32:00', 16, 8, 2),
(17, '2026-01-30', '20:00:00', '22:56:00', 15, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `auth_key` varchar(32) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `password_reset_token` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `status` smallint NOT NULL DEFAULT '10',
  `created_at` int NOT NULL,
  `updated_at` int NOT NULL,
  `verification_token` varchar(255) CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `password_reset_token` (`password_reset_token`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `auth_key`, `password_hash`, `password_reset_token`, `email`, `status`, `created_at`, `updated_at`, `verification_token`) VALUES
(1, 'admin', 'RiJaKAf7Hp424dBk1iQOV1vVBCH9JPtg', '$2y$13$MOGtdUncKuf2Ha0vfkZLbeeclpAAZZKLvTNK8Xq3tCDw36Vy0MetO', NULL, 'admin@cinelive.pt', 10, 1761155589, 1761852169, 'y0Xwr7YzzzQDbI9pyjshvWWe8vkNsDnE_1761155589'),
(2, 'gerente_leiria', 'U7lb3yNGR61TWnaV-JKGQEXpBbBlESaN', '$2y$13$hyMYwc1Xh8Yzgbz7bCbyXuFERsN6cov/L9YUqgkVvz08d3B89YWWS', NULL, 'joao.santos@cinelive.pt', 10, 1761566974, 1764696349, 'WX40mKBrd5X42eoDi89ebQiOWVOoEE01_1761566974'),
(3, 'gerente_lisboa', 'D2gMIv_2TEHAR0LZvdfcoQdRW1bk2Dqo', '$2y$13$RAEqwyWe8IXsmqmKNKwX7OnaVLqSiFkIJw8lSqoovnT.y2BorI1ae', NULL, 'ana.costa@cinelive.pt', 10, 1761567081, 1762543597, 'txaOr3YHbo46trhQNh5Zbcp4XORyCuz9_1761567081'),
(4, 'gerente_porto', 'kzSJHLcffp4AgkJ8K_EsE3owsm1I7n4V', '$2y$13$o5wT5jDBuhUEwlNU9crq5eJBJdtNKsgWbGuTS4brEY/f9DuFKaxGG', NULL, 'jose.lopes@cinelive.pt', 10, 1761567155, 1761658002, 'Y4gdxRCr8ivtj3GylOPMqhmS1bGnni0G_1761567155'),
(5, 'funcionario1_leiria', 'DKliNxbAxkV0AS1k9a6iOUhwihcX1yRI', '$2y$13$TMdaGK8E56AYDaTuwK2.subQR4YjLe8X8QykBynB2HQpaciQQDetC', NULL, 'pedro.gaspar@cinelive.pt', 10, 1761567277, 1762535118, 'mzJQdLRZ46gM83QEnGPHyf0_zSBKytD-_1761567277'),
(6, 'funcionario2_leiria', 'bOq92Yc4xgkx8r5gZIlQZmPXcybcNPTB', '$2y$13$WXJqNUuruvEP2t4nBa8Eg.Mtyy39mxIA71TGJygIswDmanCuneLuO', NULL, 'mario.lopes@cinelive.pt', 10, 1761739108, 1762108148, 'MiBN8jQSLjsJnC-Xk6VtnHL5mMGjeG8M_1761739108'),
(7, 'funcionario3_leiria', 'EMxEfUEPGMctUh8-s99OdgHqaXjEhOHk', '$2y$13$W/vkfQLvB2AGxTcs3d3pSuKzWrZ8WvUMJlg/jaVzKQnMwRnLvNp4e', NULL, 'joana.matos@cinelive.pt', 10, 1761843131, 1764707487, 'a90HbTqSLK7WPgpFg10jkJLYu6Ujriy6_1761843131'),
(8, 'funcionario1_lisboa', 'z2GqE3NcqpkUTceDye_sboypxEmVTglQ', '$2y$13$AUAV5/fqdDuxHawP2snZC.RUVqdHqs27kEmXOb1Ix/xmWEm9PELBm', NULL, 'nuno.borges@cinelive.pt', 10, 1761843283, 1761858274, 'ivjsG6OhTExYJ0lgpuyR4NBnmmgL2BAS_1761843283'),
(9, 'funcionario2_lisboa', 'sIojR6MIsPTDqlhPeEq1vh0dBLZDGa2h', '$2y$13$PMEOqBqxFkVSpiwQKA/aW.5c0eHH1cicSPbrNwU4ACy2jCHQRCu42', NULL, 'tiago.silva@cinelive.pt', 10, 1761899566, 1761899566, 'VLg3DAHhUIROGS8IWvb08kB_9oVNeTyz_1761899566'),
(10, 'funcionario3_lisboa', '3j4zo6Ppo6QZw-Ryj6Vq-sSWQkoAztcM', '$2y$13$BmeXq.fZ6INZrjUEvQwDwOBoLYCY.pmhwo8H9wga2pWR2IKcC.Hzu', NULL, 'joao.pereira@cinelive.pt', 10, 1761899622, 1762528324, 'ZIGTe969TCk-xPbVNL2tW_woMU_Xd_ai_1761899622'),
(11, 'funcionario1_porto', 'fWDeR3W4Oz1y_-lTFb3SQJA_mNZ2sZbC', '$2y$13$VxxddZCR/LXbcxkhLOBI2.3Q2PiARz37s/dacQZizcah60jRCpn5G', NULL, 'marta.costa@cinelive.pt', 10, 1761899669, 1761899669, 'XmigJ8rUfPT0PuJHE5YLdve1FyQwoy25_1761899669'),
(12, 'funcionario2_porto', 'wmqDy7TILwqyTMGL8oLX4SpLs0nEVogZ', '$2y$13$tfkNqWtTTnjgqbhyyy2qmOOvIrP5ky.lzywbuzumhZ/eFw8s7taty', NULL, 'pedro.santos@cinelive.pt', 10, 1761899704, 1761899704, 'W0IOZCEoEOeTpipvqhcN-qimarrNRFXH_1761899704'),
(13, 'funcionario3_porto', 'ltOPHO_e-vKwB_aC3NuDy9nvJRRJH23W', '$2y$13$2oYlbWTxZuKmX2OdixSh3.mYvhWng6.4QBmzKd.tYTGdhV1o9RFia', NULL, 'ana.oliveira@cinelive.pt', 10, 1761899749, 1761899749, 'cmvPY0EH0N_XgTKdIZC0JaxQt_fGvfrG_1761899749'),
(14, 'cliente1', 'B3BmNn_leTEb-maw16V0vOqZIYABiY54', '$2y$13$7GePk56MTJ1DK3DfO0PTZuWn2yNyJRmdixpa2J2fnR0IJRkSC3KbC', NULL, 'miguel.ribeiro@email.com', 10, 1761946487, 1764442055, NULL),
(15, 'cliente2', 'fMlyme1W2HIpj0ifglLZmSQ7J1f4MLdI', '$2y$13$j2E.l0WhJWfNhW6W5uCSF.9dCvXARYOrZGAmC140gLqPiFkYOYXRm', NULL, 'luis.marques@email.com', 10, 1764708269, 1764708269, 'lReE4fICppu6YHBvBGo-vClLFL1sBBX6_1764708269'),
(16, 'cliente3', 'z1kKgBz1X4ZTbiHTDV0Vq036KqgLOQhi', '$2y$13$73gH5tKRKSRD6VqWtvgg5OPvKdiHrDCYyDYxjFyYJgXrQOgMzqeGK', NULL, 'rodrigo.costa@email.com', 10, 1764708322, 1764708322, 'jzlDxup_UK664pyU_5DwEFIttWkMxwKo_1764708322');

-- --------------------------------------------------------

--
-- Table structure for table `user_profile`
--

DROP TABLE IF EXISTS `user_profile`;
CREATE TABLE IF NOT EXISTS `user_profile` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `cinema_id` int DEFAULT NULL,
  `nome` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `telemovel` varchar(9) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx-user_profile-user_id` (`user_id`),
  KEY `idx-user_profile-cinema_id` (`cinema_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_profile`
--

INSERT INTO `user_profile` (`id`, `user_id`, `cinema_id`, `nome`, `telemovel`) VALUES
(1, 1, NULL, 'Admin', '910123456'),
(2, 2, 1, 'João Santos', '912453654'),
(3, 3, 2, 'Ana Costa', '933246456'),
(4, 4, 3, 'José Lopes', '913453453'),
(5, 5, 1, 'Pedro Gaspar', '911432654'),
(6, 6, 1, 'Mário Lopes', '914435436'),
(7, 7, 1, 'Joana Matos', '913324254'),
(8, 8, 2, 'Nuno Borges', '964353455'),
(9, 9, 2, 'Tiago Silva', '913243244'),
(10, 10, 2, 'João Pereira', '934356345'),
(11, 11, 3, 'Marta Costa', '913556765'),
(12, 12, 3, 'Pedro Santos', '934325345'),
(13, 13, 3, 'Ana Oliveira', '911345788'),
(14, 14, NULL, 'Miguel Ribeiro', '912345678'),
(15, 15, NULL, 'Luís Marques', '961454344'),
(16, 16, NULL, 'Rodrigo Costa', '914324324');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `aluguer_sala`
--
ALTER TABLE `aluguer_sala`
  ADD CONSTRAINT `aluguer_sala_ibfk_1` FOREIGN KEY (`cinema_id`) REFERENCES `cinema` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk-aluguer_sala-cliente_id` FOREIGN KEY (`cliente_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk-aluguer_sala-sala_id` FOREIGN KEY (`sala_id`) REFERENCES `sala` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `auth_assignment`
--
ALTER TABLE `auth_assignment`
  ADD CONSTRAINT `auth_assignment_ibfk_1` FOREIGN KEY (`item_name`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `auth_item`
--
ALTER TABLE `auth_item`
  ADD CONSTRAINT `auth_item_ibfk_1` FOREIGN KEY (`rule_name`) REFERENCES `auth_rule` (`name`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `auth_item_child`
--
ALTER TABLE `auth_item_child`
  ADD CONSTRAINT `auth_item_child_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `auth_item_child_ibfk_2` FOREIGN KEY (`child`) REFERENCES `auth_item` (`name`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `bilhete`
--
ALTER TABLE `bilhete`
  ADD CONSTRAINT `fk-bilhete-compra_id` FOREIGN KEY (`compra_id`) REFERENCES `compra` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `cinema`
--
ALTER TABLE `cinema`
  ADD CONSTRAINT `fk-cinema-gerente_id` FOREIGN KEY (`gerente_id`) REFERENCES `user` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `compra`
--
ALTER TABLE `compra`
  ADD CONSTRAINT `compra_ibfk_1` FOREIGN KEY (`sessao_id`) REFERENCES `sessao` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `compra_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT;

--
-- Constraints for table `filme_genero`
--
ALTER TABLE `filme_genero`
  ADD CONSTRAINT `fk-filme_genero-filme_id` FOREIGN KEY (`filme_id`) REFERENCES `filme` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk-filme_genero-genero_id` FOREIGN KEY (`genero_id`) REFERENCES `genero` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `sala`
--
ALTER TABLE `sala`
  ADD CONSTRAINT `fk-sala-cinema_id` FOREIGN KEY (`cinema_id`) REFERENCES `cinema` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `sessao`
--
ALTER TABLE `sessao`
  ADD CONSTRAINT `fk-sessao-cinema_id` FOREIGN KEY (`cinema_id`) REFERENCES `cinema` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk-sessao-filme_id` FOREIGN KEY (`filme_id`) REFERENCES `filme` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk-sessao-sala_id` FOREIGN KEY (`sala_id`) REFERENCES `sala` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `user_profile`
--
ALTER TABLE `user_profile`
  ADD CONSTRAINT `fk-user_profile-cinema_id` FOREIGN KEY (`cinema_id`) REFERENCES `cinema` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `fk-user_profile-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
