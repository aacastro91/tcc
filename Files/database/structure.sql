SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------

-- Table `campus`

-- -----------------------------------------------------

DROP TABLE IF EXISTS `campus` ;



CREATE  TABLE IF NOT EXISTS `campus` (

  `id` INT(11) NOT NULL ,

  `uasg` VARCHAR(10) NOT NULL ,

  `nome` VARCHAR(50) NOT NULL ,

  `sigla` VARCHAR(3) NOT NULL ,

  PRIMARY KEY (`id`) ,

  UNIQUE INDEX `sigla_UNIQUE` (`sigla` ASC) )

DEFAULT CHARACTER SET = utf8;





-- -----------------------------------------------------

-- Table `natureza`

-- -----------------------------------------------------

DROP TABLE IF EXISTS `natureza` ;



CREATE  TABLE IF NOT EXISTS `natureza` (

  `id` INT(11) NOT NULL ,

  `descricao` VARCHAR(50) NOT NULL ,

  PRIMARY KEY (`id`) )

DEFAULT CHARACTER SET = utf8;





-- -----------------------------------------------------

-- Table `srp`

-- -----------------------------------------------------

DROP TABLE IF EXISTS `srp` ;



CREATE  TABLE IF NOT EXISTS `srp` (

  `id` INT(11) NOT NULL ,

  `numeroSRP` VARCHAR(10) NOT NULL ,

  `numeroIRP` VARCHAR(10) NOT NULL ,

  `numeroProcesso` VARCHAR(20) NOT NULL ,

  `uasg` VARCHAR(10) NOT NULL ,

  `validade` DATE NOT NULL ,

  `nome` VARCHAR(300) NOT NULL ,

  `natureza_id` INT(11) NOT NULL ,

  PRIMARY KEY (`id`) ,

  INDEX `fk_srp_natureza_idx` (`natureza_id` ASC) ,

  UNIQUE INDEX `numeroSRP_UNIQUE` (`numeroSRP` ASC) ,

  CONSTRAINT `fk_srp_natureza`

    FOREIGN KEY (`natureza_id` )

    REFERENCES `natureza` (`id` )

    ON DELETE NO ACTION

    ON UPDATE NO ACTION)

DEFAULT CHARACTER SET = utf8;





-- -----------------------------------------------------

-- Table `cessao`

-- -----------------------------------------------------

DROP TABLE IF EXISTS `cessao` ;



CREATE  TABLE IF NOT EXISTS `cessao` (

  `id` INT(11) NOT NULL ,

  `numeroCessao` VARCHAR(30) NOT NULL ,

  `emissao` DATE NOT NULL ,

  `aprovado` TINYINT(1) NOT NULL ,

  `campus_id` INT(11) NOT NULL ,

  `srp_id` INT(11) NOT NULL ,

  PRIMARY KEY (`id`) ,

  INDEX `fk_cessao_campus1_idx` (`campus_id` ASC) ,

  INDEX `fk_cessao_srp1` (`srp_id` ASC) ,

  UNIQUE INDEX `numeroCessao_UNIQUE` (`numeroCessao` ASC) ,

  CONSTRAINT `fk_cessao_campus1`

    FOREIGN KEY (`campus_id` )

    REFERENCES `campus` (`id` )

    ON DELETE NO ACTION

    ON UPDATE NO ACTION,

  CONSTRAINT `fk_cessao_srp1`

    FOREIGN KEY (`srp_id` )

    REFERENCES `srp` (`id` )

    ON DELETE NO ACTION

    ON UPDATE NO ACTION)

DEFAULT CHARACTER SET = utf8;





-- -----------------------------------------------------

-- Table `fornecedor`

-- -----------------------------------------------------

DROP TABLE IF EXISTS `fornecedor` ;



CREATE  TABLE IF NOT EXISTS `fornecedor` (

  `id` INT(11) NOT NULL ,

  `nome` VARCHAR(150) NOT NULL ,

  `cnpj` CHAR(14) NOT NULL ,

  PRIMARY KEY (`id`) ,

  UNIQUE INDEX `cnpj_UNIQUE` (`cnpj` ASC) )

DEFAULT CHARACTER SET = utf8;





-- -----------------------------------------------------

-- Table `funcionalidade`

-- -----------------------------------------------------

DROP TABLE IF EXISTS `funcionalidade` ;



CREATE  TABLE IF NOT EXISTS `funcionalidade` (

  `id` INT(11) NOT NULL ,

  `nome` VARCHAR(100) NOT NULL ,

  `classe` VARCHAR(100) NOT NULL ,

  PRIMARY KEY (`id`) ,

  UNIQUE INDEX `classe_UNIQUE` (`classe` ASC) )

DEFAULT CHARACTER SET = utf8;





-- -----------------------------------------------------

-- Table `grupo`

-- -----------------------------------------------------

DROP TABLE IF EXISTS `grupo` ;



CREATE  TABLE IF NOT EXISTS `grupo` (

  `id` INT(11) NOT NULL ,

  `nome` VARCHAR(45) NOT NULL ,

  `sigla` VARCHAR(10) NOT NULL ,

  PRIMARY KEY (`id`) ,

  UNIQUE INDEX `sigla_UNIQUE` (`sigla` ASC) )

DEFAULT CHARACTER SET = utf8;





-- -----------------------------------------------------

-- Table `grupo_funcionalidade`

-- -----------------------------------------------------

DROP TABLE IF EXISTS `grupo_funcionalidade` ;



CREATE  TABLE IF NOT EXISTS `grupo_funcionalidade` (

  `id` INT(11) NOT NULL ,

  `grupo_id` INT(11) NOT NULL ,

  `funcionalidade_id` INT(11) NOT NULL ,

  PRIMARY KEY (`id`) ,

  UNIQUE INDEX `grupo_funcionalidade_unique` (`grupo_id` ASC, `funcionalidade_id` ASC) ,

  INDEX `fk_grupo_has_funcionalidade_funcionalidade1_idx` (`funcionalidade_id` ASC) ,

  INDEX `fk_grupo_has_funcionalidade_grupo1_idx` (`grupo_id` ASC) ,

  CONSTRAINT `fk_grupo_has_funcionalidade_funcionalidade1`

    FOREIGN KEY (`funcionalidade_id` )

    REFERENCES `funcionalidade` (`id` )

    ON DELETE NO ACTION

    ON UPDATE NO ACTION,

  CONSTRAINT `fk_grupo_has_funcionalidade_grupo1`

    FOREIGN KEY (`grupo_id` )

    REFERENCES `grupo` (`id` )

    ON DELETE NO ACTION

    ON UPDATE NO ACTION)

DEFAULT CHARACTER SET = utf8;





-- -----------------------------------------------------

-- Table `subelemento`

-- -----------------------------------------------------

DROP TABLE IF EXISTS `subelemento` ;



CREATE  TABLE IF NOT EXISTS `subelemento` (

  `id` INT(11) NOT NULL ,

  `descricao` VARCHAR(150) NOT NULL ,

  PRIMARY KEY (`id`) )

DEFAULT CHARACTER SET = utf8;





-- -----------------------------------------------------

-- Table `item`

-- -----------------------------------------------------

DROP TABLE IF EXISTS `item` ;



CREATE  TABLE IF NOT EXISTS `item` (

  `id` INT(11) NOT NULL ,

  `numeroItem` INT(11) NOT NULL ,

  `descricaoSumaria` VARCHAR(150) NOT NULL ,

  `descricaoCompleta` TEXT NOT NULL ,

  `descricaoPosLicitacao` TEXT NOT NULL ,

  `unidadeMedida` VARCHAR(30) NULL ,

  `marca` VARCHAR(80) NULL ,

  `valorUnitario` DECIMAL(14,2) NOT NULL ,

  `quantidadeDisponivel` INT(11) NOT NULL ,

  `estoqueDisponivel` INT(11) NOT NULL ,

  `fabricante` VARCHAR(50) NULL ,

  `fornecedor_id` INT(11) NOT NULL ,

  `subelemento_id` INT(11) NOT NULL ,

  `srp_id` INT(11) NOT NULL ,

  PRIMARY KEY (`id`) ,

  UNIQUE INDEX `idx_unique1` (`numeroItem` ASC, `srp_id` ASC) ,

  INDEX `fk_item_fornecedor1_idx` (`fornecedor_id` ASC) ,

  INDEX `fk_item_subelemento1_idx` (`subelemento_id` ASC) ,

  INDEX `fk_item_srp1_idx` (`srp_id` ASC) ,

  CONSTRAINT `fk_item_fornecedor1`

    FOREIGN KEY (`fornecedor_id` )

    REFERENCES `fornecedor` (`id` )

    ON DELETE NO ACTION

    ON UPDATE NO ACTION,

  CONSTRAINT `fk_item_srp1`

    FOREIGN KEY (`srp_id` )

    REFERENCES `srp` (`id` )

    ON DELETE NO ACTION

    ON UPDATE NO ACTION,

  CONSTRAINT `fk_item_subelemento1`

    FOREIGN KEY (`subelemento_id` )

    REFERENCES `subelemento` (`id` )

    ON DELETE NO ACTION

    ON UPDATE NO ACTION)

DEFAULT CHARACTER SET = utf8;





-- -----------------------------------------------------

-- Table `item_cessao`

-- -----------------------------------------------------

DROP TABLE IF EXISTS `item_cessao` ;



CREATE  TABLE IF NOT EXISTS `item_cessao` (

  `id` INT(11) NOT NULL ,

  `item_id` INT(11) NOT NULL ,

  `cessao_id` INT(11) NOT NULL ,

  `quantidade` INT(11) NOT NULL ,

  PRIMARY KEY (`id`) ,

  UNIQUE INDEX `idx_unique1` (`item_id` ASC, `cessao_id` ASC) ,

  INDEX `fk_item_has_cessao_cessao1_idx` (`cessao_id` ASC) ,

  INDEX `fk_item_has_cessao_item1_idx` (`item_id` ASC) ,

  CONSTRAINT `fk_item_has_cessao_cessao1`

    FOREIGN KEY (`cessao_id` )

    REFERENCES `cessao` (`id` )

    ON DELETE NO ACTION

    ON UPDATE NO ACTION,

  CONSTRAINT `fk_item_has_cessao_item1`

    FOREIGN KEY (`item_id` )

    REFERENCES `item` (`id` )

    ON DELETE NO ACTION

    ON UPDATE NO ACTION)

DEFAULT CHARACTER SET = utf8;





-- -----------------------------------------------------

-- Table `requisicao`

-- -----------------------------------------------------

DROP TABLE IF EXISTS `requisicao` ;



CREATE  TABLE IF NOT EXISTS `requisicao` (

  `id` INT(11) NOT NULL ,

  `numeroProcesso` VARCHAR(30) NOT NULL ,

  `emissao` DATE NOT NULL ,

  `aprovado` TINYINT(1) NOT NULL ,

  `srp_id` INT(11) NOT NULL ,

  PRIMARY KEY (`id`) ,

  INDEX `fk_requisicao_srp1` (`srp_id` ASC) ,

  UNIQUE INDEX `numeroProcesso_UNIQUE` (`numeroProcesso` ASC) ,

  CONSTRAINT `fk_requisicao_srp1`

    FOREIGN KEY (`srp_id` )

    REFERENCES `srp` (`id` )

    ON DELETE NO ACTION

    ON UPDATE NO ACTION)

DEFAULT CHARACTER SET = utf8;





-- -----------------------------------------------------

-- Table `item_requisicao`

-- -----------------------------------------------------

DROP TABLE IF EXISTS `item_requisicao` ;



CREATE  TABLE IF NOT EXISTS `item_requisicao` (

  `id` INT(11) NOT NULL ,

  `item_id` INT(11) NOT NULL ,

  `requisicao_id` INT(11) NOT NULL ,

  `justificativa` VARCHAR(100) NOT NULL ,

  `quantidade` INT(11) NOT NULL ,

  `prazoEntrega` VARCHAR(20) NOT NULL ,

  PRIMARY KEY (`id`) ,

  UNIQUE INDEX `idx_unique1` (`item_id` ASC, `requisicao_id` ASC) ,

  INDEX `fk_item_has_requisicao_requisicao1_idx` (`requisicao_id` ASC) ,

  INDEX `fk_item_has_requisicao_item1_idx` (`item_id` ASC) ,

  CONSTRAINT `fk_item_has_requisicao_item1`

    FOREIGN KEY (`item_id` )

    REFERENCES `item` (`id` )

    ON DELETE NO ACTION

    ON UPDATE NO ACTION,

  CONSTRAINT `fk_item_has_requisicao_requisicao1`

    FOREIGN KEY (`requisicao_id` )

    REFERENCES `requisicao` (`id` )

    ON DELETE NO ACTION

    ON UPDATE NO ACTION)

DEFAULT CHARACTER SET = utf8;





-- -----------------------------------------------------

-- Table `usuario`

-- -----------------------------------------------------

DROP TABLE IF EXISTS `usuario` ;



CREATE  TABLE IF NOT EXISTS `usuario` (

  `id` INT(11) NOT NULL ,

  `nome` VARCHAR(60) NOT NULL ,

  `prontuario` VARCHAR(10) NOT NULL ,

  `senha` VARCHAR(32) NOT NULL ,

  `email` VARCHAR(100) NOT NULL ,

  PRIMARY KEY (`id`) ,

  UNIQUE INDEX `prontuario_UNIQUE` (`prontuario` ASC) )

DEFAULT CHARACTER SET = utf8;





-- -----------------------------------------------------

-- Table `usuario_funcionalidade`

-- -----------------------------------------------------

DROP TABLE IF EXISTS `usuario_funcionalidade` ;



CREATE  TABLE IF NOT EXISTS `usuario_funcionalidade` (

  `id` INT(11) NOT NULL ,

  `usuario_id` INT(11) NOT NULL ,

  `funcionalidade_id` INT(11) NOT NULL ,

  PRIMARY KEY (`id`) ,

  UNIQUE INDEX `usuario_funcionalidade` (`usuario_id` ASC, `funcionalidade_id` ASC) ,

  INDEX `fk_usuario_has_funcionalidade_funcionalidade1_idx` (`funcionalidade_id` ASC) ,

  INDEX `fk_usuario_has_funcionalidade_usuario1_idx` (`usuario_id` ASC) ,

  CONSTRAINT `fk_usuario_has_funcionalidade_funcionalidade1`

    FOREIGN KEY (`funcionalidade_id` )

    REFERENCES `funcionalidade` (`id` )

    ON DELETE NO ACTION

    ON UPDATE NO ACTION,

  CONSTRAINT `fk_usuario_has_funcionalidade_usuario1`

    FOREIGN KEY (`usuario_id` )

    REFERENCES `usuario` (`id` )

    ON DELETE NO ACTION

    ON UPDATE NO ACTION)

DEFAULT CHARACTER SET = utf8;





-- -----------------------------------------------------

-- Table `usuario_grupo`

-- -----------------------------------------------------

DROP TABLE IF EXISTS `usuario_grupo` ;



CREATE  TABLE IF NOT EXISTS `usuario_grupo` (

  `id` INT(11) NOT NULL ,

  `usuario_id` INT(11) NOT NULL ,

  `grupo_id` INT(11) NOT NULL ,

  PRIMARY KEY (`id`) ,

  INDEX `fk_usuario_has_grupo_grupo1_idx` (`grupo_id` ASC) ,

  INDEX `fk_usuario_has_grupo_usuario1_idx` (`usuario_id` ASC) ,

  CONSTRAINT `fk_usuario_has_grupo_grupo1`

    FOREIGN KEY (`grupo_id` )

    REFERENCES `grupo` (`id` )

    ON DELETE NO ACTION

    ON UPDATE NO ACTION,

  CONSTRAINT `fk_usuario_has_grupo_usuario1`

    FOREIGN KEY (`usuario_id` )

    REFERENCES `usuario` (`id` )

    ON DELETE NO ACTION

    ON UPDATE NO ACTION)

DEFAULT CHARACTER SET = utf8;





-- -----------------------------------------------------

-- Table `referencia`

-- -----------------------------------------------------

DROP TABLE IF EXISTS `referencia` ;


CREATE  TABLE IF NOT EXISTS `referencia` (

  `id` INT NOT NULL ,

  `nome` VARCHAR(100) NOT NULL ,

  `referencia` VARCHAR(100) NOT NULL ,

  PRIMARY KEY (`id`) ,

  UNIQUE INDEX `referencia_UNIQUE` (`referencia` ASC) );

DELIMITER $$

DROP TRIGGER IF EXISTS `item_cessao_BEFORE_DELETE` $$
CREATE TRIGGER `item_cessao_BEFORE_DELETE`
BEFORE DELETE ON `item_cessao`
FOR EACH ROW
begin
	set @quantidade = old.quantidade;
	if (@quantidade <> 0) then
		UPDATE `item` SET `estoqueDisponivel` = `estoqueDisponivel` + @quantidade WHERE `id` = old.item_id;
	end if;
end$$


DROP TRIGGER IF EXISTS `item_cessao_BEFORE_INSERT` $$
CREATE TRIGGER `item_cessao_BEFORE_INSERT`
BEFORE INSERT ON `item_cessao`
FOR EACH ROW
begin
	set @quantidade = NEW.quantidade;
	if (@quantidade <> 0) then
		UPDATE `item` SET `estoqueDisponivel` = `estoqueDisponivel` - @quantidade WHERE `id` = new.item_id;
	end if;
end$$


DROP TRIGGER IF EXISTS `item_cessao_BEFORE_UPDATE` $$
CREATE TRIGGER `item_cessao_BEFORE_UPDATE`
BEFORE UPDATE ON `item_cessao`
FOR EACH ROW
begin
	set @quantidade = NEW.quantidade;
    set @oldQuantidade = OLD.quantidade;
    
	if (@quantidade <> @oldQuantidade) then
		UPDATE `item` SET `estoqueDisponivel` = `estoqueDisponivel` + @oldQuantidade - @quantidade WHERE `id` = new.item_id;
	end if;

end$$


DROP TRIGGER IF EXISTS `item_requisicao_BEFORE_DELETE` $$
CREATE TRIGGER `item_requisicao_BEFORE_DELETE`
BEFORE DELETE ON `item_requisicao`
FOR EACH ROW
begin
	set @quantidade = old.quantidade;
	if (@quantidade <> 0) then
		UPDATE `item` SET `estoqueDisponivel` = `estoqueDisponivel` + @quantidade WHERE `id` = old.item_id;
	end if;
end$$


DROP TRIGGER IF EXISTS `item_requisicao_BEFORE_INSERT` $$
CREATE TRIGGER `item_requisicao_BEFORE_INSERT`
BEFORE INSERT ON `item_requisicao`
FOR EACH ROW
begin
	set @quantidade = NEW.quantidade;
	if (@quantidade <> 0) then
		UPDATE `item` SET `estoqueDisponivel` = `estoqueDisponivel` - @quantidade WHERE `id` = new.item_id;
	end if;
end$$


DROP TRIGGER IF EXISTS `item_requisicao_BEFORE_UPDATE` $$
CREATE TRIGGER `item_requisicao_BEFORE_UPDATE`
BEFORE UPDATE ON `item_requisicao`
FOR EACH ROW
begin
	set @quantidade = NEW.quantidade;
    set @oldQuantidade = OLD.quantidade;
    
	if (@quantidade <> @oldQuantidade) then
		UPDATE `item` SET `estoqueDisponivel` = `estoqueDisponivel` + @oldQuantidade - @quantidade WHERE `id` = new.item_id;
	end if;

end$$


DELIMITER ;

GRANT USAGE ON *.* TO 'login'@'%' IDENTIFIED BY 'xxx';
DROP USER 'login';

CREATE USER 'login'@'%' IDENTIFIED BY 'ifspifsp';

GRANT SELECT ON `usuario_grupo` TO 'login'@'%';

GRANT SELECT ON `usuario_funcionalidade` TO 'login'@'%';

GRANT SELECT ON `grupo_funcionalidade` TO 'login'@'%';

GRANT SELECT ON `usuario` TO 'login'@'%';

GRANT SELECT ON `grupo` TO 'login'@'%';

GRANT SELECT ON `funcionalidade` TO 'login'@'%';


INSERT INTO `campus` VALUES (1,'158581','Araraquara','ARQ'),(2,'158582','Avaré','AVR'),(3,'158583','Barretos','BRT'),(4,'158525','Birigui','BRI'),(5,'158710','Boituva','BTV'),(6,'158344','Bragança Paulista','BRA'),(7,'158714','Campinas','CMP'),(8,'158347','Campos do Jordão','CJO'),(9,'158712','Capivari','CPV'),(10,'158349','Caraguatatuba','CAR'),(11,'158520','Catanduva','CTD'),(12,'158332','Cubatão','CBT'),(13,'158348','Guarulhos','GRU'),(14,'158578','Hortolândia','HTO'),(15,'158526','Itapetininga','ITP'),(16,'158716','Jacareí','JCR'),(17,'158711','Matão','MTO'),(18,'158528','Piracicaba','PRC'),(19,'158584','Presidente Epitácio','PEP'),(20,'158586','Registro','RGT'),(21,'158154','Reitoria','SIS'),(22,'158364','Salto','SLT'),(23,'158330','São Carlos','SCL'),(24,'158346','São João da Boa Vista','SBV'),(25,'158713','São José dos Campos','SJC'),(26,'158270','São Paulo','SPO'),(27,'158329','São Roque','SRQ'),(28,'158331','Sertãozinho','SRT'),(29,'158154','Sorocaba','SOR'),(30,'158566','Suzano','SZN'),(31,'158579','Votuporanga','VTP');
INSERT INTO `referencia` VALUES (1,'NATUREZA DE DESPESA','NATUREZA DE DESPESA'),(2,'NOME DO PROCESSO','NOME DO PROCESSO'),(3,'NÚMERO SUBELEMENTO','NÚMERO SUBELEMENTO'),(4,'DESCRIÇÃO SUBELEMENTO','DESCRIÇÃO SUBELEMENTO'),(5,'Nº IRP','Nº IRP'),(6,'Nº SRP','Nº SRP'),(7,'NÚMERO DO PROCESSO','NÚMERO DO PROCESSO'),(8,'UASG GERENCIADORA','UASG GERENCIADORA'),(9,'VALIDADE DA ATA','VALIDADE DA ATA'),(10,'ITEM','ITEM'),(11,'DESCRIÇÃO SUMÁRIA','DESCRIÇÃO SUMÁRIA'),(12,'DESCRIÇÃO COMPLETA','DESCRIÇÃO COMPLETA'),(13,'DESCRIÇÃO PÓS-LICITAÇÃO','DESCRIÇÃO PÓS-LICITAÇÃO'),(14,'UNIDADE DE MEDIDA','UNIDADE DE MEDIDA'),(15,'VALOR UNITÁRIO LICITADO','VALOR UNITÁRIO LICITADO'),(16,'FORNECEDOR','FORNECEDOR'),(17,'CNPJ','CNPJ'),(18,'FABRICANTE','FABRICANTE'),(19,'MARCA','MARCA');
INSERT INTO `funcionalidade` VALUES (1,'Lista de Funcionalidades','FuncionalidadeList'),(2,'Lista de Usuários','UsuarioList'),(3,'Lista de Grupos','GrupoList'),(4,'Cadastro de Funcionalidades','FuncionalidadeForm'),(5,'Cadastro de Grupos','GrupoForm'),(6,'Cadastro de Usuários','UsuarioForm'),(7,'Pagina padrao - somente pra teste','CommonPage'),(8,'Cadastro de Campus','CampusForm'),(9,'Lista de Campus','CampusList'),(10,'Relatório Campus','CampusReport'),(11,'Importar tabela','ImportForm'),(12,'Consulta SRP Requisicao','SrpSeekRequisicao'),(13,'Formulario de Requisicao','RequisicaoForm'),(14,'Consulta Item Requisicao','ItemSeekRequisicao'),(15,'Lista Natureza','NaturezaList'),(16,'Lista Subelemento','SubelementoList'),(17,'Lista Fornecedor','FornecedorList'),(18,'Lista Requisicao','RequisicaoList'),(19,'Aprovação de requisição','AprovarRequisicaoList'),(20,'Desaprovar Requisição','DesaprovarRequisicaoList'),(21,'Lista Cessão','CessaoList'),(22,'Aprovação de Cessão','AprovarCessaoList'),(23,'Desaprovar Cessão','DesaprovarCessaoList'),(24,'Ref. de nomes da planilha de importação','ReferenciaList'),(25,'Formulário de Cessão','CessaoForm'),(26,'Consulta Item Cessão','ItemSeekCessao'),(27,'Consulta SRP Cessão','SrpSeekCessao'),(28,'Gerar Planilha de Requisição','PlanilhaRequisicao'),(29,'Lista para Gerar Documento de Cessão','DocCessaoList'),(30,'Form para Gerar Documento de Cessão','DocCessaoForm'),(31,'Listagem SRP','SrpList'),(32,'Detalhe da SRP e seus Itens','SrpFormView'),(33,'Tela Inicial','Home'),(34,'Relatório de Requisição','RequisicaoReport'),(35,'Relatório de Cessão','CessaoReport'),(36,'Relatório de SRPs','SrpReport');
INSERT INTO `grupo` VALUES (1,'Administrador','ADM');
INSERT INTO `grupo_funcionalidade` VALUES (1,1,33),(2,1,1),(3,1,2),(4,1,3),(5,1,4),(6,1,5),(7,1,6),(8,1,7),(9,1,8),(10,1,9),(11,1,10),(12,1,11),(13,1,12),(14,1,13),(15,1,14),(16,1,15),(17,1,16),(18,1,17),(19,1,18),(20,1,19),(21,1,20),(22,1,21),(23,1,22),(24,1,23),(25,1,24),(26,1,25),(27,1,26),(28,1,27),(29,1,28),(30,1,29),(31,1,30),(32,1,31),(33,1,32),(34,1,34),(35,1,35),(36,1,36);
INSERT INTO `usuario` VALUES (1,'Administrador','999999',md5('admin'),'admin@admin.com');
INSERT INTO `usuario_grupo` VALUES (1,1,1);

SET SQL_MODE=@OLD_SQL_MODE;

SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;

SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

