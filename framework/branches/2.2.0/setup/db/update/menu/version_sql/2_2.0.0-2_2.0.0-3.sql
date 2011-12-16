BEGIN TRANSACTION;
ALTER TABLE menu ADD COLUMN order_no Integer;
UPDATE menu SET order_no=0 WHERE id='system';
UPDATE menu SET order_no=2 WHERE id='sysinfo';
UPDATE menu SET order_no=4 WHERE id='usermgr';
UPDATE menu SET order_no=1 WHERE id='grouplist';
UPDATE menu SET order_no=2 WHERE id='userlist';
UPDATE menu SET order_no=3 WHERE id='group_permission';
UPDATE menu SET order_no=5 WHERE id='load_module';
UPDATE menu SET order_no=10 WHERE id='preferences';
UPDATE menu SET order_no=101 WHERE id='language';
UPDATE menu SET order_no=102 WHERE id='time_config';
UPDATE menu SET order_no=103 WHERE id='themes_system';
COMMIT;
