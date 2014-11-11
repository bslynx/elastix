BEGIN TRANSACTION;
ALTER TABLE contact ADD COLUMN cell_phone varchar(50);
ALTER TABLE contact ADD COLUMN home_phone varchar(50);
ALTER TABLE contact ADD COLUMN fax1 varchar(50);
ALTER TABLE contact ADD COLUMN fax2 varchar(50);
ALTER TABLE contact ADD COLUMN province varchar(100);
ALTER TABLE contact ADD COLUMN city varchar(100);
ALTER TABLE contact ADD COLUMN company_contact varchar(100);
ALTER TABLE contact ADD COLUMN contact_rol varchar(50);
ALTER TABLE contact ADD COLUMN directory varchar(8) DEFAULT 'external';
COMMIT;
