-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema saciq
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Table `campus`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `campus` ;

CREATE TABLE IF NOT EXISTS `campus` (
  `id` INT(11) NOT NULL,
  `uasg` VARCHAR(10) NULL DEFAULT NULL,
  `nome` VARCHAR(50) NULL DEFAULT NULL,
  `sigla` VARCHAR(3) NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `natureza`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `natureza` ;

CREATE TABLE IF NOT EXISTS `natureza` (
  `id` INT(11) NOT NULL,
  `descricao` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `srp`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `srp` ;

CREATE TABLE IF NOT EXISTS `srp` (
  `id` INT(11) NOT NULL,
  `numeroSRP` VARCHAR(10) NULL DEFAULT NULL,
  `numeroIRP` VARCHAR(10) NULL DEFAULT NULL,
  `numeroProcesso` VARCHAR(20) NULL DEFAULT NULL,
  `uasg` VARCHAR(10) NULL DEFAULT NULL,
  `validade` DATE NULL DEFAULT NULL,
  `nome` VARCHAR(300) NULL DEFAULT NULL,
  `natureza_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_srp_natureza_idx` (`natureza_id` ASC),
  UNIQUE INDEX `numeroSRP_UNIQUE` (`numeroSRP` ASC),
  CONSTRAINT `fk_srp_natureza`
    FOREIGN KEY (`natureza_id`)
    REFERENCES `natureza` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `cessao`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `cessao` ;

CREATE TABLE IF NOT EXISTS `cessao` (
  `id` INT(11) NOT NULL,
  `numeroCessao` VARCHAR(30) NULL DEFAULT NULL,
  `emissao` DATE NULL DEFAULT NULL,
  `aprovado` TINYINT(1) NULL DEFAULT NULL,
  `campus_id` INT(11) NOT NULL,
  `srp_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_cessao_campus1_idx` (`campus_id` ASC),
  INDEX `fk_cessao_srp1` (`srp_id` ASC),
  CONSTRAINT `fk_cessao_campus1`
    FOREIGN KEY (`campus_id`)
    REFERENCES `campus` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_cessao_srp1`
    FOREIGN KEY (`srp_id`)
    REFERENCES `srp` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `fornecedor`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `fornecedor` ;

CREATE TABLE IF NOT EXISTS `fornecedor` (
  `id` INT(11) NOT NULL,
  `nome` VARCHAR(150) NULL DEFAULT NULL,
  `cnpj` CHAR(14) NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `funcionalidade`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `funcionalidade` ;

CREATE TABLE IF NOT EXISTS `funcionalidade` (
  `id` INT(11) NOT NULL,
  `nome` VARCHAR(100) NULL DEFAULT NULL,
  `classe` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `classe_UNIQUE` (`classe` ASC))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `grupo`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `grupo` ;

CREATE TABLE IF NOT EXISTS `grupo` (
  `id` INT(11) NOT NULL,
  `nome` VARCHAR(45) NULL DEFAULT NULL,
  `sigla` VARCHAR(10) NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `grupo_funcionalidade`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `grupo_funcionalidade` ;

CREATE TABLE IF NOT EXISTS `grupo_funcionalidade` (
  `id` INT(11) NOT NULL,
  `grupo_id` INT(11) NOT NULL,
  `funcionalidade_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `grupo_funcionalidade_unique` (`grupo_id` ASC, `funcionalidade_id` ASC),
  INDEX `fk_grupo_has_funcionalidade_funcionalidade1_idx` (`funcionalidade_id` ASC),
  INDEX `fk_grupo_has_funcionalidade_grupo1_idx` (`grupo_id` ASC),
  CONSTRAINT `fk_grupo_has_funcionalidade_funcionalidade1`
    FOREIGN KEY (`funcionalidade_id`)
    REFERENCES `funcionalidade` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_grupo_has_funcionalidade_grupo1`
    FOREIGN KEY (`grupo_id`)
    REFERENCES `grupo` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `subelemento`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `subelemento` ;

CREATE TABLE IF NOT EXISTS `subelemento` (
  `id` INT(11) NOT NULL,
  `descricao` VARCHAR(150) NULL DEFAULT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `item`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `item` ;

CREATE TABLE IF NOT EXISTS `item` (
  `id` INT(11) NOT NULL,
  `numeroItem` INT(11) NOT NULL,
  `descricaoSumaria` VARCHAR(150) NULL DEFAULT NULL,
  `descricaoCompleta` TEXT NULL DEFAULT NULL,
  `descricaoPosLicitacao` TEXT NULL DEFAULT NULL,
  `unidadeMedida` VARCHAR(30) NULL DEFAULT NULL,
  `marca` VARCHAR(80) NULL DEFAULT NULL,
  `valorUnitario` DECIMAL(14,2) NULL DEFAULT NULL,
  `quantidadeDisponivel` INT(11) NULL DEFAULT NULL,
  `estoqueDisponivel` INT(11) NULL,
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
    REFERENCES `fornecedor` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_item_srp1`
    FOREIGN KEY (`srp_id`)
    REFERENCES `srp` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_item_subelemento1`
    FOREIGN KEY (`subelemento_id`)
    REFERENCES `subelemento` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `item_cessao`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `item_cessao` ;

CREATE TABLE IF NOT EXISTS `item_cessao` (
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
    REFERENCES `cessao` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_item_has_cessao_item1`
    FOREIGN KEY (`item_id`)
    REFERENCES `item` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `requisicao`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `requisicao` ;

CREATE TABLE IF NOT EXISTS `requisicao` (
  `id` INT(11) NOT NULL,
  `numeroProcesso` VARCHAR(30) NULL DEFAULT NULL,
  `emissao` DATE NULL DEFAULT NULL,
  `aprovado` TINYINT(1) NULL DEFAULT NULL,
  `srp_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_requisicao_srp1` (`srp_id` ASC),
  CONSTRAINT `fk_requisicao_srp1`
    FOREIGN KEY (`srp_id`)
    REFERENCES `srp` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `item_requisicao`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `item_requisicao` ;

CREATE TABLE IF NOT EXISTS `item_requisicao` (
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
    REFERENCES `item` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_item_has_requisicao_requisicao1`
    FOREIGN KEY (`requisicao_id`)
    REFERENCES `requisicao` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `usuario`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `usuario` ;

CREATE TABLE IF NOT EXISTS `usuario` (
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
-- Table `usuario_funcionalidade`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `usuario_funcionalidade` ;

CREATE TABLE IF NOT EXISTS `usuario_funcionalidade` (
  `id` INT(11) NOT NULL,
  `usuario_id` INT(11) NOT NULL,
  `funcionalidade_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `usuario_funcionalidade` (`usuario_id` ASC, `funcionalidade_id` ASC),
  INDEX `fk_usuario_has_funcionalidade_funcionalidade1_idx` (`funcionalidade_id` ASC),
  INDEX `fk_usuario_has_funcionalidade_usuario1_idx` (`usuario_id` ASC),
  CONSTRAINT `fk_usuario_has_funcionalidade_funcionalidade1`
    FOREIGN KEY (`funcionalidade_id`)
    REFERENCES `funcionalidade` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_usuario_has_funcionalidade_usuario1`
    FOREIGN KEY (`usuario_id`)
    REFERENCES `usuario` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `usuario_grupo`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `usuario_grupo` ;

CREATE TABLE IF NOT EXISTS `usuario_grupo` (
  `id` INT(11) NOT NULL,
  `usuario_id` INT(11) NOT NULL,
  `grupo_id` INT(11) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_usuario_has_grupo_grupo1_idx` (`grupo_id` ASC),
  INDEX `fk_usuario_has_grupo_usuario1_idx` (`usuario_id` ASC),
  CONSTRAINT `fk_usuario_has_grupo_grupo1`
    FOREIGN KEY (`grupo_id`)
    REFERENCES `grupo` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_usuario_has_grupo_usuario1`
    FOREIGN KEY (`usuario_id`)
    REFERENCES `usuario` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `referencia`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `referencia` ;

CREATE TABLE IF NOT EXISTS `referencia` (
  `id` INT NOT NULL,
  `nome` VARCHAR(100) NULL,
  `referencia` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `referencia_UNIQUE` (`referencia` ASC))
ENGINE = InnoDB;


DELIMITER $$

DROP TRIGGER IF EXISTS `item_cessao_BEFORE_DELETE` $$
CREATE
DEFINER=`root`@`localhost`
TRIGGER `saciq`.`item_cessao_BEFORE_DELETE`
BEFORE DELETE ON `saciq`.`item_cessao`
FOR EACH ROW
begin
	set @quantidade = old.quantidade;
	if (@quantidade <> 0) then
		UPDATE `item` SET `estoqueDisponivel` = `estoqueDisponivel` + @quantidade WHERE `id` = old.item_id;
	end if;
end$$


DROP TRIGGER IF EXISTS `item_cessao_BEFORE_INSERT` $$
CREATE
DEFINER=`root`@`localhost`
TRIGGER `saciq`.`item_cessao_BEFORE_INSERT`
BEFORE INSERT ON `saciq`.`item_cessao`
FOR EACH ROW
begin
	set @quantidade = NEW.quantidade;
	if (@quantidade <> 0) then
		UPDATE `item` SET `estoqueDisponivel` = `estoqueDisponivel` - @quantidade WHERE `id` = new.item_id;
	end if;
end$$


DROP TRIGGER IF EXISTS `item_cessao_BEFORE_UPDATE` $$
CREATE
DEFINER=`root`@`localhost`
TRIGGER `saciq`.`item_cessao_BEFORE_UPDATE`
BEFORE UPDATE ON `saciq`.`item_cessao`
FOR EACH ROW
begin
	set @quantidade = NEW.quantidade;
    set @oldQuantidade = OLD.quantidade;
    
	if (@quantidade <> @oldQuantidade) then
		UPDATE `item` SET `estoqueDisponivel` = `estoqueDisponivel` + @oldQuantidade - @quantidade WHERE `id` = new.item_id;
	end if;

end$$


DROP TRIGGER IF EXISTS `item_requisicao_BEFORE_DELETE` $$
CREATE
DEFINER=`root`@`localhost`
TRIGGER `saciq`.`item_requisicao_BEFORE_DELETE`
BEFORE DELETE ON `saciq`.`item_requisicao`
FOR EACH ROW
begin
	set @quantidade = old.quantidade;
	if (@quantidade <> 0) then
		UPDATE `item` SET `estoqueDisponivel` = `estoqueDisponivel` + @quantidade WHERE `id` = old.item_id;
	end if;
end$$


DROP TRIGGER IF EXISTS `item_requisicao_BEFORE_INSERT` $$
CREATE
DEFINER=`root`@`localhost`
TRIGGER `saciq`.`item_requisicao_BEFORE_INSERT`
BEFORE INSERT ON `saciq`.`item_requisicao`
FOR EACH ROW
begin
	set @quantidade = NEW.quantidade;
	if (@quantidade <> 0) then
		UPDATE `item` SET `estoqueDisponivel` = `estoqueDisponivel` - @quantidade WHERE `id` = new.item_id;
	end if;
end$$


DROP TRIGGER IF EXISTS `item_requisicao_BEFORE_UPDATE` $$
CREATE
DEFINER=`root`@`localhost`
TRIGGER `saciq`.`item_requisicao_BEFORE_UPDATE`
BEFORE UPDATE ON `saciq`.`item_requisicao`
FOR EACH ROW
begin
	set @quantidade = NEW.quantidade;
    set @oldQuantidade = OLD.quantidade;
    
	if (@quantidade <> @oldQuantidade) then
		UPDATE `item` SET `estoqueDisponivel` = `estoqueDisponivel` + @oldQuantidade - @quantidade WHERE `id` = new.item_id;
	end if;

end$$


DELIMITER ;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
