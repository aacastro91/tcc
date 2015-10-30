DROP USER 'login';

CREATE USER 'login'@'%' IDENTIFIED BY 'ifspifsp';

GRANT SELECT ON `usuario_grupo` TO 'login'@'%';

GRANT SELECT ON `usuario_funcionalidade` TO 'login'@'%';

GRANT SELECT ON `grupo_funcionalidade` TO 'login'@'%';

GRANT SELECT ON `usuario` TO 'login'@'%';

GRANT SELECT ON `grupo` TO 'login'@'%';

GRANT SELECT ON `funcionalidade` TO 'login'@'%';