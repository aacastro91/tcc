-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema SACIQ
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema SACIQ
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `SACIQ` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `SACIQ` ;

-- -----------------------------------------------------
-- Table `SACIQ`.`natureza`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `SACIQ`.`natureza` (
  `id` INT NOT NULL,
  `descricao` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SACIQ`.`srp`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `SACIQ`.`srp` (
  `id` INT NOT NULL,
  `numeroSRP` VARCHAR(10) NULL,
  `numeroIRP` VARCHAR(10) NULL,
  `numeroProcesso` VARCHAR(20) NULL,
  `uasg` INT NULL,
  `validade` DATE NULL,
  `nome` VARCHAR(300) NULL,
  `natureza_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_srp_natureza_idx` (`natureza_id` ASC),
  CONSTRAINT `fk_srp_natureza`
    FOREIGN KEY (`natureza_id`)
    REFERENCES `SACIQ`.`natureza` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SACIQ`.`fornecedor`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `SACIQ`.`fornecedor` (
  `id` INT NOT NULL,
  `nome` VARCHAR(150) NULL,
  `cnpj` CHAR(14) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SACIQ`.`subelemento`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `SACIQ`.`subelemento` (
  `id` INT NOT NULL,
  `descricao` VARCHAR(150) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SACIQ`.`item`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `SACIQ`.`item` (
  `id` INT NOT NULL,
  `numeroItem` INT NOT NULL,
  `descricaoSumaria` VARCHAR(150) NULL,
  `descricaoCompleta` VARCHAR(600) NULL,
  `descricaoPosLicitacao` VARCHAR(600) NULL,
  `unidadeMedida` CHAR(2) NULL,
  `marca` VARCHAR(80) NULL,
  `valorUnitario` DECIMAL(14,2) NULL,
  `quantidadeDisponivel` INT NULL,
  `fabricante` VARCHAR(50) NULL,
  `fornecedor_id` INT NOT NULL,
  `subelemento_id` INT NOT NULL,
  `srp_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_item_fornecedor1_idx` (`fornecedor_id` ASC),
  INDEX `fk_item_subelemento1_idx` (`subelemento_id` ASC),
  INDEX `fk_item_srp1_idx` (`srp_id` ASC),
  UNIQUE INDEX `idx_unique1` (`numeroItem` ASC, `srp_id` ASC),
  CONSTRAINT `fk_item_fornecedor1`
    FOREIGN KEY (`fornecedor_id`)
    REFERENCES `SACIQ`.`fornecedor` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_item_subelemento1`
    FOREIGN KEY (`subelemento_id`)
    REFERENCES `SACIQ`.`subelemento` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_item_srp1`
    FOREIGN KEY (`srp_id`)
    REFERENCES `SACIQ`.`srp` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SACIQ`.`requisicao`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `SACIQ`.`requisicao` (
  `id` INT NOT NULL,
  `numeroProcesso` VARCHAR(30) NULL,
  `data` DATE NULL,
  `aprovado` TINYINT(1) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SACIQ`.`campus`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `SACIQ`.`campus` (
  `id` INT NOT NULL,
  `uasg` VARCHAR(10) NULL,
  `nome` VARCHAR(50) NULL,
  `sigla` VARCHAR(3) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SACIQ`.`cessao`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `SACIQ`.`cessao` (
  `id` INT NOT NULL,
  `numeroCessao` VARCHAR(30) NULL,
  `data` DATE NULL,
  `aprovado` TINYINT(1) NULL,
  `campus_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_cessao_campus1_idx` (`campus_id` ASC),
  CONSTRAINT `fk_cessao_campus1`
    FOREIGN KEY (`campus_id`)
    REFERENCES `SACIQ`.`campus` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SACIQ`.`item_requisicao`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `SACIQ`.`item_requisicao` (
  `id` INT NOT NULL,
  `item_id` INT NOT NULL,
  `requisicao_id` INT NOT NULL,
  `justificativa` VARCHAR(100) NULL,
  `quantidade` INT NULL,
  `prazoEntrega` INT NULL,
  `valorTotal` DECIMAL(14,2) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_item_has_requisicao_requisicao1_idx` (`requisicao_id` ASC),
  INDEX `fk_item_has_requisicao_item1_idx` (`item_id` ASC),
  UNIQUE INDEX `idx_unique1` (`item_id` ASC, `requisicao_id` ASC),
  CONSTRAINT `fk_item_has_requisicao_item1`
    FOREIGN KEY (`item_id`)
    REFERENCES `SACIQ`.`item` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_item_has_requisicao_requisicao1`
    FOREIGN KEY (`requisicao_id`)
    REFERENCES `SACIQ`.`requisicao` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SACIQ`.`item_cessao`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `SACIQ`.`item_cessao` (
  `id` INT NOT NULL,
  `item_id` INT NOT NULL,
  `cessao_id` INT NOT NULL,
  `quantidade` INT NULL,
  `valorTotal` DECIMAL(14,2) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_item_has_cessao_cessao1_idx` (`cessao_id` ASC),
  INDEX `fk_item_has_cessao_item1_idx` (`item_id` ASC),
  UNIQUE INDEX `idx_unique1` (`item_id` ASC, `cessao_id` ASC),
  CONSTRAINT `fk_item_has_cessao_item1`
    FOREIGN KEY (`item_id`)
    REFERENCES `SACIQ`.`item` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_item_has_cessao_cessao1`
    FOREIGN KEY (`cessao_id`)
    REFERENCES `SACIQ`.`cessao` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SACIQ`.`usuario`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `SACIQ`.`usuario` (
  `id` INT NOT NULL,
  `nome` VARCHAR(60) NULL,
  `prontuario` VARCHAR(10) NULL,
  `senha` VARCHAR(32) NULL,
  `email` VARCHAR(100) NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `prontuario_UNIQUE` (`prontuario` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SACIQ`.`grupo`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `SACIQ`.`grupo` (
  `id` INT NOT NULL,
  `nome` VARCHAR(45) NULL,
  `sigla` VARCHAR(10) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SACIQ`.`usuario_grupo`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `SACIQ`.`usuario_grupo` (
  `id` INT NOT NULL,
  `usuario_id` INT NOT NULL,
  `grupo_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_usuario_has_grupo_grupo1_idx` (`grupo_id` ASC),
  INDEX `fk_usuario_has_grupo_usuario1_idx` (`usuario_id` ASC),
  CONSTRAINT `fk_usuario_has_grupo_usuario1`
    FOREIGN KEY (`usuario_id`)
    REFERENCES `SACIQ`.`usuario` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_usuario_has_grupo_grupo1`
    FOREIGN KEY (`grupo_id`)
    REFERENCES `SACIQ`.`grupo` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SACIQ`.`funcionalidade`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `SACIQ`.`funcionalidade` (
  `id` INT NOT NULL,
  `nome` VARCHAR(100) NULL,
  `classe` VARCHAR(100) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SACIQ`.`usuario_funcionalidade`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `SACIQ`.`usuario_funcionalidade` (
  `id` INT NOT NULL,
  `usuario_id` INT NOT NULL,
  `funcionalidade_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_usuario_has_funcionalidade_funcionalidade1_idx` (`funcionalidade_id` ASC),
  INDEX `fk_usuario_has_funcionalidade_usuario1_idx` (`usuario_id` ASC),
  CONSTRAINT `fk_usuario_has_funcionalidade_usuario1`
    FOREIGN KEY (`usuario_id`)
    REFERENCES `SACIQ`.`usuario` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_usuario_has_funcionalidade_funcionalidade1`
    FOREIGN KEY (`funcionalidade_id`)
    REFERENCES `SACIQ`.`funcionalidade` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SACIQ`.`grupo_funcionalidade`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `SACIQ`.`grupo_funcionalidade` (
  `id` INT NOT NULL,
  `grupo_id` INT NOT NULL,
  `funcionalidade_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_grupo_has_funcionalidade_funcionalidade1_idx` (`funcionalidade_id` ASC),
  INDEX `fk_grupo_has_funcionalidade_grupo1_idx` (`grupo_id` ASC),
  CONSTRAINT `fk_grupo_has_funcionalidade_grupo1`
    FOREIGN KEY (`grupo_id`)
    REFERENCES `SACIQ`.`grupo` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_grupo_has_funcionalidade_funcionalidade1`
    FOREIGN KEY (`funcionalidade_id`)
    REFERENCES `SACIQ`.`funcionalidade` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
