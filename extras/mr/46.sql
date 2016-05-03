-- INTEGRIA - the ITIL Management System
-- http://integria.sourceforge.net
-- ==================================================
-- Copyright (c) 2007-2012 Ártica Soluciones Tecnológicas
-- http://www.artica.es  <info@artica.es>

-- This program is free software; you can redistribute it and/or
-- modify it under the terms of the GNU General Public License
-- as published by the Free Software Foundation; version 2
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.


-- ---------------------------------------------------------------------
-- Table tmenu_visibility
-- ---------------------------------------------------------------------
ALTER TABLE `tprofile` ADD `rr` tinyint(1) NOT NULL default '0';
ALTER TABLE `tprofile` ADD `rw` tinyint(1) NOT NULL default '0';
ALTER TABLE `tprofile` ADD `rm` tinyint(1) NOT NULL default '0';

UPDATE `tconfig` SET `value` = '5.0' WHERE `token` = 'db_scheme_version';