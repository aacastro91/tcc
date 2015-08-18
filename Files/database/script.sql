CREATE DATABASE  IF NOT EXISTS `saciq` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `saciq`;
-- MySQL dump 10.13  Distrib 5.6.17, for Win32 (x86)
--
-- Host: 127.0.0.1    Database: saciq
-- ------------------------------------------------------
-- Server version	5.6.23-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+03:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `campus`
--

DROP TABLE IF EXISTS `campus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `campus` (
  `id` int(11) NOT NULL,
  `uasg` varchar(10) DEFAULT NULL,
  `nome` varchar(50) DEFAULT NULL,
  `sigla` varchar(3) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `campus`
--

LOCK TABLES `campus` WRITE;
/*!40000 ALTER TABLE `campus` DISABLE KEYS */;
INSERT INTO `campus` VALUES (1,'158154','IFSP Campus Capivari','CPV');
/*!40000 ALTER TABLE `campus` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cessao`
--

DROP TABLE IF EXISTS `cessao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cessao` (
  `id` int(11) NOT NULL,
  `numeroCessao` varchar(30) DEFAULT NULL,
  `data` date DEFAULT NULL,
  `aprovado` tinyint(1) DEFAULT NULL,
  `campus_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_cessao_campus1_idx` (`campus_id`),
  CONSTRAINT `fk_cessao_campus1` FOREIGN KEY (`campus_id`) REFERENCES `campus` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cessao`
--

LOCK TABLES `cessao` WRITE;
/*!40000 ALTER TABLE `cessao` DISABLE KEYS */;
/*!40000 ALTER TABLE `cessao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fornecedor`
--

DROP TABLE IF EXISTS `fornecedor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `fornecedor` (
  `id` int(11) NOT NULL,
  `nome` varchar(150) DEFAULT NULL,
  `cnpj` char(14) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fornecedor`
--

LOCK TABLES `fornecedor` WRITE;
/*!40000 ALTER TABLE `fornecedor` DISABLE KEYS */;
/*!40000 ALTER TABLE `fornecedor` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `funcionalidade`
--

DROP TABLE IF EXISTS `funcionalidade`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `funcionalidade` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) DEFAULT NULL,
  `classe` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `classe_UNIQUE` (`classe`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `funcionalidade`
--

LOCK TABLES `funcionalidade` WRITE;
/*!40000 ALTER TABLE `funcionalidade` DISABLE KEYS */;
INSERT INTO `funcionalidade` VALUES (1,'Lista de Funcionalidades','FuncionalidadeList'),(2,'Lista de Usuários','UsuarioList'),(3,'Lista de Grupos','GrupoList'),(4,'Cadastro de Funcionalidades','FuncionalidadeForm'),(5,'Cadastro de Grupos','GrupoForm'),(6,'Cadastro de Usuários','UsuarioForm'),(7,'Pagina padrao - somente pra teste','CommonPage'),(8,'Cadastro de Campus','CampusForm'),(9,'Lista de Campus','CampusList'),(10,'Relatório Campus','CampusReport'),(11,'Importar tabela','ImportForm');
/*!40000 ALTER TABLE `funcionalidade` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grupo`
--

DROP TABLE IF EXISTS `grupo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grupo` (
  `id` int(11) NOT NULL,
  `nome` varchar(45) DEFAULT NULL,
  `sigla` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupo`
--

LOCK TABLES `grupo` WRITE;
/*!40000 ALTER TABLE `grupo` DISABLE KEYS */;
INSERT INTO `grupo` VALUES (1,'Administrador','ADM');
/*!40000 ALTER TABLE `grupo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grupo_funcionalidade`
--

DROP TABLE IF EXISTS `grupo_funcionalidade`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `grupo_funcionalidade` (
  `id` int(11) NOT NULL,
  `grupo_id` int(11) NOT NULL,
  `funcionalidade_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `grupo_funcionalidade_unique` (`grupo_id`,`funcionalidade_id`),
  KEY `fk_grupo_has_funcionalidade_funcionalidade1_idx` (`funcionalidade_id`),
  KEY `fk_grupo_has_funcionalidade_grupo1_idx` (`grupo_id`),
  CONSTRAINT `fk_grupo_has_funcionalidade_funcionalidade1` FOREIGN KEY (`funcionalidade_id`) REFERENCES `funcionalidade` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_grupo_has_funcionalidade_grupo1` FOREIGN KEY (`grupo_id`) REFERENCES `grupo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupo_funcionalidade`
--

LOCK TABLES `grupo_funcionalidade` WRITE;
/*!40000 ALTER TABLE `grupo_funcionalidade` DISABLE KEYS */;
INSERT INTO `grupo_funcionalidade` VALUES (1,1,1),(11,1,2),(2,1,3),(3,1,4),(4,1,5),(5,1,6),(6,1,7),(7,1,8),(8,1,9),(9,1,10),(10,1,11);
/*!40000 ALTER TABLE `grupo_funcionalidade` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `item`
--

DROP TABLE IF EXISTS `item`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item` (
  `id` int(11) NOT NULL,
  `numeroItem` int(11) NOT NULL,
  `descricaoSumaria` varchar(150) DEFAULT NULL,
  `descricaoCompleta` varchar(600) DEFAULT NULL,
  `descricaoPosLicitacao` varchar(600) DEFAULT NULL,
  `unidadeMedida` char(2) DEFAULT NULL,
  `marca` varchar(80) DEFAULT NULL,
  `valorUnitario` decimal(14,2) DEFAULT NULL,
  `quantidadeDisponivel` int(11) DEFAULT NULL,
  `fabricante` varchar(50) DEFAULT NULL,
  `fornecedor_id` int(11) NOT NULL,
  `subelemento_id` int(11) NOT NULL,
  `srp_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_unique1` (`numeroItem`,`srp_id`),
  KEY `fk_item_fornecedor1_idx` (`fornecedor_id`),
  KEY `fk_item_subelemento1_idx` (`subelemento_id`),
  KEY `fk_item_srp1_idx` (`srp_id`),
  CONSTRAINT `fk_item_fornecedor1` FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedor` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_item_srp1` FOREIGN KEY (`srp_id`) REFERENCES `srp` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_item_subelemento1` FOREIGN KEY (`subelemento_id`) REFERENCES `subelemento` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `item`
--

LOCK TABLES `item` WRITE;
/*!40000 ALTER TABLE `item` DISABLE KEYS */;
/*!40000 ALTER TABLE `item` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `item_cessao`
--

DROP TABLE IF EXISTS `item_cessao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item_cessao` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `cessao_id` int(11) NOT NULL,
  `quantidade` int(11) DEFAULT NULL,
  `valorTotal` decimal(14,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_unique1` (`item_id`,`cessao_id`),
  KEY `fk_item_has_cessao_cessao1_idx` (`cessao_id`),
  KEY `fk_item_has_cessao_item1_idx` (`item_id`),
  CONSTRAINT `fk_item_has_cessao_cessao1` FOREIGN KEY (`cessao_id`) REFERENCES `cessao` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_item_has_cessao_item1` FOREIGN KEY (`item_id`) REFERENCES `item` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `item_cessao`
--

LOCK TABLES `item_cessao` WRITE;
/*!40000 ALTER TABLE `item_cessao` DISABLE KEYS */;
/*!40000 ALTER TABLE `item_cessao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `item_requisicao`
--

DROP TABLE IF EXISTS `item_requisicao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `item_requisicao` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `requisicao_id` int(11) NOT NULL,
  `justificativa` varchar(100) DEFAULT NULL,
  `quantidade` int(11) DEFAULT NULL,
  `prazoEntrega` int(11) DEFAULT NULL,
  `valorTotal` decimal(14,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_unique1` (`item_id`,`requisicao_id`),
  KEY `fk_item_has_requisicao_requisicao1_idx` (`requisicao_id`),
  KEY `fk_item_has_requisicao_item1_idx` (`item_id`),
  CONSTRAINT `fk_item_has_requisicao_item1` FOREIGN KEY (`item_id`) REFERENCES `item` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_item_has_requisicao_requisicao1` FOREIGN KEY (`requisicao_id`) REFERENCES `requisicao` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `item_requisicao`
--

LOCK TABLES `item_requisicao` WRITE;
/*!40000 ALTER TABLE `item_requisicao` DISABLE KEYS */;
/*!40000 ALTER TABLE `item_requisicao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `natureza`
--

DROP TABLE IF EXISTS `natureza`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `natureza` (
  `id` int(11) NOT NULL,
  `descricao` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `natureza`
--

LOCK TABLES `natureza` WRITE;
/*!40000 ALTER TABLE `natureza` DISABLE KEYS */;
/*!40000 ALTER TABLE `natureza` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `requisicao`
--

DROP TABLE IF EXISTS `requisicao`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `requisicao` (
  `id` int(11) NOT NULL,
  `numeroProcesso` varchar(30) DEFAULT NULL,
  `data` date DEFAULT NULL,
  `aprovado` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `requisicao`
--

LOCK TABLES `requisicao` WRITE;
/*!40000 ALTER TABLE `requisicao` DISABLE KEYS */;
/*!40000 ALTER TABLE `requisicao` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `srp`
--

DROP TABLE IF EXISTS `srp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `srp` (
  `id` int(11) NOT NULL,
  `numeroSRP` varchar(10) DEFAULT NULL,
  `numeroIRP` varchar(10) DEFAULT NULL,
  `numeroProcesso` varchar(20) DEFAULT NULL,
  `uasg` int(11) DEFAULT NULL,
  `validade` date DEFAULT NULL,
  `nome` varchar(300) DEFAULT NULL,
  `natureza_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_srp_natureza_idx` (`natureza_id`),
  CONSTRAINT `fk_srp_natureza` FOREIGN KEY (`natureza_id`) REFERENCES `natureza` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `srp`
--

LOCK TABLES `srp` WRITE;
/*!40000 ALTER TABLE `srp` DISABLE KEYS */;
/*!40000 ALTER TABLE `srp` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subelemento`
--

DROP TABLE IF EXISTS `subelemento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subelemento` (
  `id` int(11) NOT NULL,
  `descricao` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subelemento`
--

LOCK TABLES `subelemento` WRITE;
/*!40000 ALTER TABLE `subelemento` DISABLE KEYS */;
/*!40000 ALTER TABLE `subelemento` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `nome` varchar(60) DEFAULT NULL,
  `prontuario` varchar(10) DEFAULT NULL,
  `senha` varchar(32) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `prontuario_UNIQUE` (`prontuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario`
--

LOCK TABLES `usuario` WRITE;
/*!40000 ALTER TABLE `usuario` DISABLE KEYS */;
INSERT INTO `usuario` VALUES (1,'Anderson A Castro','1320351','81dc9bdb52d04dc20036dbd8313ed055','anderson_matogrosso@hotmail.com');
/*!40000 ALTER TABLE `usuario` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario_funcionalidade`
--

DROP TABLE IF EXISTS `usuario_funcionalidade`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuario_funcionalidade` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `funcionalidade_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_funcionalidade` (`usuario_id`,`funcionalidade_id`),
  KEY `fk_usuario_has_funcionalidade_funcionalidade1_idx` (`funcionalidade_id`),
  KEY `fk_usuario_has_funcionalidade_usuario1_idx` (`usuario_id`),
  CONSTRAINT `fk_usuario_has_funcionalidade_funcionalidade1` FOREIGN KEY (`funcionalidade_id`) REFERENCES `funcionalidade` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_usuario_has_funcionalidade_usuario1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario_funcionalidade`
--

LOCK TABLES `usuario_funcionalidade` WRITE;
/*!40000 ALTER TABLE `usuario_funcionalidade` DISABLE KEYS */;
INSERT INTO `usuario_funcionalidade` VALUES (1,1,1);
/*!40000 ALTER TABLE `usuario_funcionalidade` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuario_grupo`
--

DROP TABLE IF EXISTS `usuario_grupo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuario_grupo` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `grupo_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_usuario_has_grupo_grupo1_idx` (`grupo_id`),
  KEY `fk_usuario_has_grupo_usuario1_idx` (`usuario_id`),
  CONSTRAINT `fk_usuario_has_grupo_grupo1` FOREIGN KEY (`grupo_id`) REFERENCES `grupo` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_usuario_has_grupo_usuario1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuario_grupo`
--

LOCK TABLES `usuario_grupo` WRITE;
/*!40000 ALTER TABLE `usuario_grupo` DISABLE KEYS */;
INSERT INTO `usuario_grupo` VALUES (1,1,1);
/*!40000 ALTER TABLE `usuario_grupo` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'saciq'
--

--
-- Dumping routines for database 'saciq'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-08-18 18:24:29
