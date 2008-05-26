#!/bin/bash
#Script para mejorar la presentacion de los menus. A tres niveles.
#Esto solo es un parche para corregir lo que actualmete el paloSantoInstaller y paloSantoModuloXML
#no esta implementa del soporte para tres niveles de menus.
existeMenuForm=`sqlite3 /var/www/db/menu.db "select count(*) from menu where id='forms'"`
existeMenuReportIngoingCall=`sqlite3 /var/www/db/menu.db "select count(*) from menu where id='reports_ingoing_call'"`

if [ $existeMenuForm -eq 0 ]; then
    sqlite3 /var/www/db/menu.db  "insert into menu values ('forms','call_center','','Forms','module');"
    sqlite3 /var/www/db/acl.db   "insert into acl_resource(name,description) values ('forms','Forms'); insert into acl_group_permission(id_action,id_group,id_resource) values (1,1,(select last_insert_rowid()));"
fi

if [ $existeMenuReportIngoingCall -eq 0 ]; then
sqlite3 /var/www/db/menu.db  "insert into menu values ('reports_ingoing_call','call_center','','Reports','module');"
sqlite3 /var/www/db/acl.db   "insert into acl_resource(name,description) values ('reports_ingoing_call','Reports'); insert into acl_group_permission(id_action,id_group,id_resource) values (1,1,(select last_insert_rowid()));"
fi

sqlite3 /var/www/db/menu.db  "update menu set idparent='forms' where id='form_designer';"
sqlite3 /var/www/db/menu.db  "update menu set idparent='forms' where id='form_list';"
sqlite3 /var/www/db/menu.db  "update menu set idparent='reports_ingoing_call' where id='reports_break';"
sqlite3 /var/www/db/menu.db  "update menu set idparent='reports_ingoing_call' where id='calls_detail';"
sqlite3 /var/www/db/menu.db  "update menu set idparent='reports_ingoing_call' where id='calls_per_hour';" 
sqlite3 /var/www/db/menu.db  "update menu set idparent='reports_ingoing_call' where id='calls_per_agent';"
sqlite3 /var/www/db/menu.db  "update menu set idparent='reports_ingoing_call' where id='hold_time';"
sqlite3 /var/www/db/menu.db  "update menu set idparent='reports_ingoing_call' where id='login_logout';"
sqlite3 /var/www/db/menu.db  "update menu set idparent='reports_ingoing_call' where id='ingoings_calls_success';"
sqlite3 /var/www/db/menu.db  "update menu set idparent='reports_ingoing_call' where id='graphic_calls';"
