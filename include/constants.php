<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2012 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.


/*Incident statistics constants*/
define("INCIDENT_METRIC_USER", "user_time");
define("INCIDENT_METRIC_STATUS", "status_time");
define("INCIDENT_METRIC_GROUP", "group_time");
define("INCIDENT_METRIC_TOTAL_TIME", "total_time");
define("INCIDENT_METRIC_TOTAL_TIME_NO_THIRD", "total_w_third");

/*Incident tracking constants*/
define ('INCIDENT_CREATED', 0);
define ('INCIDENT_UPDATED', 1);
define ('INCIDENT_WORKUNIT_ADDED', 2);
define ('INCIDENT_FILE_ADDED', 3);
define ('INCIDENT_NOTE_ADDED', 4);
define ('INCIDENT_FILE_REMOVED', 5);
define ('INCIDENT_PRIORITY_CHANGED', 6);
define ('INCIDENT_STATUS_CHANGED', 7);
define ('INCIDENT_RESOLUTION_CHANGED', 8);
define ('INCIDENT_NOTE_DELETED', 9);
define ('INCIDENT_INVENTORY_ADDED', 10);
define ('INCIDENT_USER_CHANGED', 17);
define ('INCIDENT_DELETED', 18);
define ('INCIDENT_CONTACT_ADDED', 19);
define ('INCIDENT_GROUP_CHANGED', 28);

/*Task tracking constants*/
define ('TASK_CREATED', 11);
define ('TASK_UPDATED', 12);
define ('TASK_NOTE_ADDED', 13);
define ('TASK_WORKUNIT_ADDED', 14);
define ('TASK_FILE_ADDED', 15);
define ('TASK_COMPLETION_CHANGED', 16);
define ('TASK_FINISHED', 17);
define ('TASK_MEMBER_ADDED', 18);
define ('TASK_MOVED', 19);
define ('TASK_MEMBER_DELETED', 20);

/*Project tracking constants*/
define ('PROJECT_CREATED', 21);
define ('PROJECT_UPDATED', 22);
define ('PROJECT_DISABLED', 23);
define ('PROJECT_ACTIVATED', 24);
define ('PROJECT_DELETED', 25);
define ('PROJECT_TASK_ADDED', 26);
define ('PROJECT_TASK_DELETED', 27);

// Incident status constants
define ('STATUS_NEW', 1);
define ('STATUS_UNCONFIRMED', 2);
define ('STATUS_ASSIGNED', 3);
define ('STATUS_REOPENED', 4);
define ('STATUS_VERIFIED', 5);
define ('STATUS_RESOLVED', 6);
define ('STATUS_PENDING_THIRD_PERSON', 6);
define ('STATUS_CLOSED', 7);

// Incident resolution constants
define ('RES_FIXED', 1);
define ('RES_INVALID', 2);
define ('RES_WONTFIX', 3);
define ('RES_DUPLICATE', 4);
define ('RES_WORKSFORME', 5);
define ('RES_INCOMPLETE', 6);
define ('RES_EXPIRED', 7);
define ('RES_MOVED', 8);
define ('RES_INPROCESS', 9);

?>
