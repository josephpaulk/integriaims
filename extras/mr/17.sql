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
-- Table tobject_type_field
-- ---------------------------------------------------------------------
ALTER TABLE `tobject_type_field` ADD COLUMN `parent_table_name` text default null;
ALTER TABLE `tobject_type_field` ADD COLUMN `parent_reference_field` text default null;
