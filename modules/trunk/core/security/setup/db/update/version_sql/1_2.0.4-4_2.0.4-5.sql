ALTER TABLE filter ADD COLUMN state varchar(50);

UPDATE filter set order=20 where traffic='INPUT' and eth_in='ANY' and eth_out='' and ip_source='0.0.0.0/0' and ip_destiny='0.0.0.0/0' and protocol='ALL' and sport='' and dport='' and icmp_type='' and number_ip='' and target='REJECT';
UPDATE filter set order=21 where traffic='FORWARD' and eth_in='ANY' and eth_out='ANY' and ip_source='0.0.0.0/0' and ip_destiny='0.0.0.0/0' and protocol='ALL' and sport='' and dport='' and icmp_type='' and number_ip='' and target='REJECT';

INSERT INTO filter(traffic,eth_in,eth_out,ip_source,ip_destiny,protocol,sport,dport,icmp_type,number_ip,target,rule_order,activated,state) VALUES ('INPUT','ANY','','0.0.0.0/0','0.0.0.0/0','STATE','','','','','ACCEPT',19,1,'Established,Related');

