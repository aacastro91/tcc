-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema saciq
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema saciq
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `saciq` DEFAULT CHARACTER SET utf8 ;
USE `saciq` ;

-- -----------------------------------------------------
-- Table `saciq`.`campus`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `saciq`.`campus` ;

CREATE TABLE IF NOT EXISTS `saciq`.`campus` (
  `id` INT(11) NOT NULL,
  `uasg` VARCHAR(10) NULL DEFAULT NULL,
  `nome` VARCHAR(50) NULL DEFAULT NULL,
  `sigla` VARCHAR(3) NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `saciq`.`cessao`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `saciq`.`cessao` ;

CREATE TABLE IF NOT EXISTS `saciq`.`cessao` (
  `id` INT(11) NOT NULL,
  `numeroCessao` VARCHAR(30) NULL DEFAULT NULL,
  `data` DATE NULL DEFAULT NULL,
  `aprovado` TINYINT(1) NULL DEFAULT NULL,
  `campus_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_cessao_campus1_idx` (`campus_id` ASC),
  CONSTRAINT `fk_cessao_campus1`
    FOREIGN KEY (`campus_id`)
    REFERENCES `saciq`.`campus` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `saciq`.`fornecedor`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `saciq`.`fornecedor` ;

CREATE TABLE IF NOT EXISTS `saciq`.`fornecedor` (
  `id` INT(11) NOT NULL,
  `nome` VARCHAR(150) NULL DEFAULT NULL,
  `cnpj` CHAR(14) NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `saciq`.`funcionalidade`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `saciq`.`funcionalidade` ;

CREATE TABLE IF NOT EXISTS `saciq`.`funcionalidade` (
  `id` INT(11) NOT NULL,
  `nome` VARCHAR(100) NULL DEFAULT NULL,
  `classe` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `classe_UNIQUE` (`classe` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `saciq`.`grupo`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `saciq`.`grupo` ;

CREATE TABLE IF NOT EXISTS `saciq`.`grupo` (
  `id` INT(11) NOT NULL,
  `nome` VARCHAR(45) NULL DEFAULT NULL,
  `sigla` VARCHAR(10) NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `saciq`.`grupo_funcionalidade`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `saciq`.`grupo_funcionalidade` ;

CREATE TABLE IF NOT EXISTS `saciq`.`grupo_funcionalidade` (
  `id` INT(11) NOT NULL,
  `grupo_id` INT(11) NOT NULL,
  `funcionalidade_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `grupo_funcionalidade_unique` (`grupo_id` ASC, `funcionalidade_id` ASC),
  INDEX `fk_grupo_has_funcionalidade_funcionalidade1_idx` (`funcionalidade_id` ASC),
  INDEX `fk_grupo_has_funcionalidade_grupo1_idx` (`grupo_id` ASC),
  CONSTRAINT `fk_grupo_has_funcionalidade_funcionalidade1`
    FOREIGN KEY (`funcionalidade_id`)
    REFERENCES `saciq`.`funcionalidade` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_grupo_has_funcionalidade_grupo1`
    FOREIGN KEY (`grupo_id`)
    REFERENCES `saciq`.`grupo` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `saciq`.`natureza`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `saciq`.`natureza` ;

CREATE TABLE IF NOT EXISTS `saciq`.`natureza` (
  `id` INT(11) NOT NULL,
  `descricao` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `saciq`.`srp`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `saciq`.`srp` ;

CREATE TABLE IF NOT EXISTS `saciq`.`srp` (
  `id` INT(11) NOT NULL,
  `numeroSRP` VARCHAR(10) NULL DEFAULT NULL,
  `numeroIRP` VARCHAR(10) NULL DEFAULT NULL,
  `numeroProcesso` VARCHAR(20) NULL DEFAULT NULL,
  `uasg` INT(11) NULL DEFAULT NULL,
  `validade` DATE NULL DEFAULT NULL,
  `nome` VARCHAR(300) NULL DEFAULT NULL,
  `natureza_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_srp_natureza_idx` (`natureza_id` ASC),
  UNIQUE INDEX `numeroSRP_UNIQUE` (`numeroSRP` ASC),
  CONSTRAINT `fk_srp_natureza`
    FOREIGN KEY (`natureza_id`)
    REFERENCES `saciq`.`natureza` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `saciq`.`subelemento`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `saciq`.`subelemento` ;

CREATE TABLE IF NOT EXISTS `saciq`.`subelemento` (
  `id` INT(11) NOT NULL,
  `descricao` VARCHAR(150) NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `saciq`.`item`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `saciq`.`item` ;

CREATE TABLE IF NOT EXISTS `saciq`.`item` (
  `id` INT(11) NOT NULL,
  `numeroItem` INT(11) NOT NULL,
  `descricaoSumaria` VARCHAR(150) NULL DEFAULT NULL,
  `descricaoCompleta` TEXT NULL DEFAULT NULL,
  `descricaoPosLicitacao` TEXT NULL DEFAULT NULL,
  `unidadeMedida` VARCHAR(30) NULL DEFAULT NULL,
  `marca` VARCHAR(80) NULL DEFAULT NULL,
  `valorUnitario` DECIMAL(14,2) NULL DEFAULT NULL,
  `quantidadeDisponivel` INT(11) NULL DEFAULT NULL,
  `fabricante` VARCHAR(50) NULL DEFAULT NULL,
  `fornecedor_id` INT(11) NOT NULL,
  `subelemento_id` INT(11) NOT NULL,
  `srp_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_unique1` (`numeroItem` ASC, `srp_id` ASC),
  INDEX `fk_item_fornecedor1_idx` (`fornecedor_id` ASC),
  INDEX `fk_item_subelemento1_idx` (`subelemento_id` ASC),
  INDEX `fk_item_srp1_idx` (`srp_id` ASC),
  CONSTRAINT `fk_item_fornecedor1`
    FOREIGN KEY (`fornecedor_id`)
    REFERENCES `saciq`.`fornecedor` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_item_srp1`
    FOREIGN KEY (`srp_id`)
    REFERENCES `saciq`.`srp` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_item_subelemento1`
    FOREIGN KEY (`subelemento_id`)
    REFERENCES `saciq`.`subelemento` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `saciq`.`item_cessao`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `saciq`.`item_cessao` ;

CREATE TABLE IF NOT EXISTS `saciq`.`item_cessao` (
  `id` INT(11) NOT NULL,
  `item_id` INT(11) NOT NULL,
  `cessao_id` INT(11) NOT NULL,
  `quantidade` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_unique1` (`item_id` ASC, `cessao_id` ASC),
  INDEX `fk_item_has_cessao_cessao1_idx` (`cessao_id` ASC),
  INDEX `fk_item_has_cessao_item1_idx` (`item_id` ASC),
  CONSTRAINT `fk_item_has_cessao_cessao1`
    FOREIGN KEY (`cessao_id`)
    REFERENCES `saciq`.`cessao` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_item_has_cessao_item1`
    FOREIGN KEY (`item_id`)
    REFERENCES `saciq`.`item` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `saciq`.`requisicao`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `saciq`.`requisicao` ;

CREATE TABLE IF NOT EXISTS `saciq`.`requisicao` (
  `id` INT(11) NOT NULL,
  `numeroProcesso` VARCHAR(30) NULL DEFAULT NULL,
  `data` DATE NULL DEFAULT NULL,
  `aprovado` TINYINT(1) NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `saciq`.`item_requisicao`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `saciq`.`item_requisicao` ;

CREATE TABLE IF NOT EXISTS `saciq`.`item_requisicao` (
  `id` INT(11) NOT NULL,
  `item_id` INT(11) NOT NULL,
  `requisicao_id` INT(11) NOT NULL,
  `justificativa` VARCHAR(100) NULL DEFAULT NULL,
  `quantidade` INT(11) NULL DEFAULT NULL,
  `prazoEntrega` VARCHAR(20) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idx_unique1` (`item_id` ASC, `requisicao_id` ASC),
  INDEX `fk_item_has_requisicao_requisicao1_idx` (`requisicao_id` ASC),
  INDEX `fk_item_has_requisicao_item1_idx` (`item_id` ASC),
  CONSTRAINT `fk_item_has_requisicao_item1`
    FOREIGN KEY (`item_id`)
    REFERENCES `saciq`.`item` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_item_has_requisicao_requisicao1`
    FOREIGN KEY (`requisicao_id`)
    REFERENCES `saciq`.`requisicao` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `saciq`.`usuario`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `saciq`.`usuario` ;

CREATE TABLE IF NOT EXISTS `saciq`.`usuario` (
  `id` INT(11) NOT NULL,
  `nome` VARCHAR(60) NULL DEFAULT NULL,
  `prontuario` VARCHAR(10) NULL DEFAULT NULL,
  `senha` VARCHAR(32) NULL DEFAULT NULL,
  `email` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `prontuario_UNIQUE` (`prontuario` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `saciq`.`usuario_funcionalidade`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `saciq`.`usuario_funcionalidade` ;

CREATE TABLE IF NOT EXISTS `saciq`.`usuario_funcionalidade` (
  `id` INT(11) NOT NULL,
  `usuario_id` INT(11) NOT NULL,
  `funcionalidade_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `usuario_funcionalidade` (`usuario_id` ASC, `funcionalidade_id` ASC),
  INDEX `fk_usuario_has_funcionalidade_funcionalidade1_idx` (`funcionalidade_id` ASC),
  INDEX `fk_usuario_has_funcionalidade_usuario1_idx` (`usuario_id` ASC),
  CONSTRAINT `fk_usuario_has_funcionalidade_funcionalidade1`
    FOREIGN KEY (`funcionalidade_id`)
    REFERENCES `saciq`.`funcionalidade` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_usuario_has_funcionalidade_usuario1`
    FOREIGN KEY (`usuario_id`)
    REFERENCES `saciq`.`usuario` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `saciq`.`usuario_grupo`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `saciq`.`usuario_grupo` ;

CREATE TABLE IF NOT EXISTS `saciq`.`usuario_grupo` (
  `id` INT(11) NOT NULL,
  `usuario_id` INT(11) NOT NULL,
  `grupo_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_usuario_has_grupo_grupo1_idx` (`grupo_id` ASC),
  INDEX `fk_usuario_has_grupo_usuario1_idx` (`usuario_id` ASC),
  CONSTRAINT `fk_usuario_has_grupo_grupo1`
    FOREIGN KEY (`grupo_id`)
    REFERENCES `saciq`.`grupo` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_usuario_has_grupo_usuario1`
    FOREIGN KEY (`usuario_id`)
    REFERENCES `saciq`.`usuario` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
