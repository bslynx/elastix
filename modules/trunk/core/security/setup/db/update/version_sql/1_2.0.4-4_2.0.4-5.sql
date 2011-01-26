ALTER TABLE filter ADD COLUMN state varchar(50);

INSERT INTO filter(traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated,state) VALUES ('INPUT','ANY','','0.0.0.0/0','0.0.0.0/0','STATE','','','','','ACCEPT',19,1,'Established,Related');