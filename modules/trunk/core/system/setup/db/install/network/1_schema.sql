BEGIN TRANSACTION;

CREATE TABLE dhcp_conf (
        id           INTEGER PRIMARY KEY,
        hostname     varchar(20),
        ipaddress    varchar(20),
        macaddress   varchar(20)
);
INSERT INTO "dhcp_conf" VALUES(1, 'srv1', '192.168.1.1', '00:19:D1:22:CE:A6');
INSERT INTO "dhcp_conf" VALUES(2, 'srv2', '192.168.1.2', '00:19:D1:82:2F:86');
INSERT INTO "dhcp_conf" VALUES(3, 'srv3', '192.168.1.3', '00:19:D1:7C:F6:64');
INSERT INTO "dhcp_conf" VALUES(4, 'srv4', '192.168.1.4', '00:40:63:D4:B0:92');
INSERT INTO "dhcp_conf" VALUES(5, 'srv5', '192.168.1.5', '00:1C:25:DD:BF:C3');
INSERT INTO "dhcp_conf" VALUES(6, 'srv6', '192.168.1.6', '00:1C:C0:8E:D3:1A');
INSERT INTO "dhcp_conf" VALUES(7, 'srv7', '192.168.1.7', '00:19:D1:B0:C6:D5');
INSERT INTO "dhcp_conf" VALUES(8, 'srv8', '192.168.1.8', '00:1C:C0:71:AC:C3');
INSERT INTO "dhcp_conf" VALUES(9, 'srv9', '192.168.1.9', '00:19:D1:B0:CE:B6');
INSERT INTO "dhcp_conf" VALUES(10, 'm3_eth0', '192.168.1.113', '00:23:8b:7b:cb:f7');
INSERT INTO "dhcp_conf" VALUES(11, 'm3_mauro', '192.168.1.113', '00:24:2B:A6:DF:AF');
INSERT INTO "dhcp_conf" VALUES(12, 'nokia_cell_mauro', '192.168.1.114', '00:21:FE:3C:B0:63');
INSERT INTO "dhcp_conf" VALUES(13, 'edgar_lapto', '192.168.1.99', '00:23:5A:45:09:2B');
INSERT INTO "dhcp_conf" VALUES(14, 'ventas_lapto_lenovo', '192.168.1.143', '00:19:7E:14:68:A0');
INSERT INTO "dhcp_conf" VALUES(15, 'prueba_fisicas_elastix', '192.168.1.90', '00:1C:C0:1E:3B:26');
INSERT INTO "dhcp_conf" VALUES(16, 'joffre_pilay', '192.168.1.50', '00:19:21:33:8d:9d');
INSERT INTO "dhcp_conf" VALUES(17, 'nokia_cell_bruno', '192.168.1.137', '00:16:bc:a4:79:05');
INSERT INTO "dhcp_conf" VALUES(18, 'pc_bruno', '192.168.1.71', '00:19:66:1b:35:8b');
INSERT INTO "dhcp_conf" VALUES(19, 'Oscar', '192.168.5.128', '00:20:22:1c:10:00');
INSERT INTO "dhcp_conf" VALUES(20, 'Katherine', '192.168.5.60', '00:21:cE:3C:b0:63');

COMMIT;