/** PROCCESS TO CREATE DATABASE BY ELASTIX-DBPROCESS **/
CREATE DATABASE IF NOT EXISTS mya2billing;

USE mya2billing;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,   
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 * 
 * @copyright   Copyright (C) 2004-2009 - Star2billing S.L. 
 * @author      Belaid Arezqui <areski@gmail.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 *
 * Software License Agreement (GNU Affero General Public License)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
**/


--
-- A2Billing database script - Create user & create database for MYSQL 5.X
--

-- Usage:
-- mysql -u root -p"root password" < a2billing-mysql-schema-v1.3.0.sql 


--
-- A2Billing database - Create database schema
--
 

/** PROCCESS TO TABLES BY ELASTIX-DBPROCESS **/

CREATE TABLE cc_didgroup (
    id 								BIGINT NOT NULL AUTO_INCREMENT,
    iduser 							INT DEFAULT 0 NOT NULL,
    didgroupname 					CHAR(50) NOT NULL,    
    creationdate   					TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;


CREATE TABLE cc_did_use (
    id 								BIGINT NOT NULL AUTO_INCREMENT,
    id_cc_card 						BIGINT ,
    id_did 							BIGINT NOT NULL,
    reservationdate 				TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    releasedate 					TIMESTAMP,
    activated 						INT	DEFAULT 0,
    month_payed 					INT DEFAULT 0,
    PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;

CREATE TABLE cc_did (
    id 								BIGINT NOT NULL AUTO_INCREMENT,	
    id_cc_didgroup 					BIGINT NOT NULL,
    activated 						INT DEFAULT '1' NOT NULL,
    reserved 						INT DEFAULT '0',
    iduser 						BIGINT DEFAULT '0' NOT NULL,
    did 							CHAR(50) NOT NULL,
    creationdate  					TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    startingdate  					TIMESTAMP,
    expirationdate 					TIMESTAMP,
    description 					MEDIUMTEXT,
    secondusedreal 					INT DEFAULT 0,
    billingtype 					INT DEFAULT 0,
    fixrate 						FLOAT DEFAULT 0 NOT NULL,
    PRIMARY KEY (id),
    UNIQUE cons_cc_did_did (did)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;


-- billtype: 0 = fix per month + dialoutrate, 1= fix per month, 2 = dialoutrate, 3 = free



CREATE TABLE cc_did_destination (
    id 									BIGINT NOT NULL AUTO_INCREMENT,	
    destination 						CHAR(50) NOT NULL,
    priority 							INT DEFAULT 0 NOT NULL,
    id_cc_card 							BIGINT NOT NULL,
    id_cc_did 							BIGINT NOT NULL,	
    creationdate  						TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    activated 							INT DEFAULT 1 NOT NULL,
    secondusedreal 						INT DEFAULT 0,	
    voip_call 							INT DEFAULT 0,
    PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;




CREATE TABLE cc_charge (
    id 									BIGINT NOT NULL AUTO_INCREMENT,
    id_cc_card 							BIGINT NOT NULL,
    iduser 								INT DEFAULT '0' NOT NULL,
    creationdate 						TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    amount 								FLOAT DEFAULT 0 NOT NULL,
	currency 							CHAR(3) DEFAULT 'USD',
    chargetype 							INT DEFAULT 0,    
    description 						MEDIUMTEXT,
    id_cc_did 							BIGINT DEFAULT 0,
	id_cc_subscription_fee				BIGINT DEFAULT 0,
    PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;

CREATE INDEX ind_cc_charge_id_cc_card				ON cc_charge (id_cc_card);
CREATE INDEX ind_cc_charge_id_cc_subscription_fee 	ON cc_charge (id_cc_subscription_fee);
CREATE INDEX ind_cc_charge_creationdate 			ON cc_charge (creationdate);



CREATE TABLE cc_paypal (
    id 								INT (11) NOT NULL AUTO_INCREMENT,
    payer_id 						VARCHAR(50) DEFAULT NULL,
    payment_date 					VARCHAR(30) DEFAULT NULL,
    txn_id 							VARCHAR(30) DEFAULT NULL,
    first_name 						VARCHAR(40) DEFAULT NULL,
    last_name 						VARCHAR(40) DEFAULT NULL,
    payer_email 					VARCHAR(55) DEFAULT NULL,
    payer_status 					VARCHAR(30) DEFAULT NULL,
    payment_type 					VARCHAR(30) DEFAULT NULL,
    memo 							TINYTEXT,
    item_name 						VARCHAR(70) DEFAULT NULL,
    item_number 					VARCHAR(70) DEFAULT NULL,
    quantity 						INT (11) NOT NULL DEFAULT '0',
    mc_gross 						DECIMAL(9,2) DEFAULT NULL,
    mc_fee 							DECIMAL(9,2) DEFAULT NULL,
    tax 							DECIMAL(9,2) DEFAULT NULL,
    mc_currency 					CHAR(3) DEFAULT NULL,
    address_name 					VARCHAR(50) NOT NULL DEFAULT '',
    address_street 					VARCHAR(80) NOT NULL DEFAULT '',
    address_city 					VARCHAR(40) NOT NULL DEFAULT '',
    address_state 					VARCHAR(40) NOT NULL DEFAULT '',
    address_zip 					VARCHAR(20) NOT NULL DEFAULT '',
    address_country 				VARCHAR(30) NOT NULL DEFAULT '',
    address_status 					VARCHAR(30) NOT NULL DEFAULT '',
    payer_business_name 			VARCHAR(40) NOT NULL DEFAULT '',
    payment_status					VARCHAR(30) NOT NULL DEFAULT '',
    pending_reason 					VARCHAR(50) NOT NULL DEFAULT '',
    reason_code 					VARCHAR(30) NOT NULL DEFAULT '',
    txn_type 						VARCHAR(30) NOT NULL DEFAULT '',
    PRIMARY KEY  (id),
    UNIQUE KEY txn_id (txn_id),
    KEY txn_id_2 (txn_id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;



CREATE TABLE cc_voucher (
    id 										BIGINT NOT NULL AUTO_INCREMENT,   
    creationdate 							TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    usedate 								TIMESTAMP,
    expirationdate 							TIMESTAMP,
    voucher 								CHAR(50) NOT NULL,
    usedcardnumber 							CHAR(50),
    tag 									CHAR(50),
    credit 									FLOAT DEFAULT 0 NOT NULL,
    activated 								CHAR(1) DEFAULT 'f' NOT NULL,
    used 									INT DEFAULT 0,    
    currency 								CHAR(3) DEFAULT 'USD',
    PRIMARY KEY (id),
    UNIQUE cons_cc_voucher_voucher (voucher)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;



CREATE TABLE cc_service (
    id 										BIGINT NOT NULL AUTO_INCREMENT,	
    name 									CHAR(100) NOT NULL, 
    amount 									FLOAT NOT NULL,	
    period 									INT NOT NULL DEFAULT '1',	
    rule 									INT NOT NULL DEFAULT '0',
    daynumber 								INT NOT NULL DEFAULT '0',
    stopmode 								INT NOT NULL DEFAULT '0',
    maxnumbercycle 							INT NOT NULL DEFAULT '0',	
    status 									INT NOT NULL DEFAULT '0',	
    numberofrun 							INT NOT NULL DEFAULT '0',	
    datecreate 								TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    datelastrun 							TIMESTAMP,
    emailreport 							CHAR(100) NOT NULL,
    totalcredit 							FLOAT NOT NULL DEFAULT '0',
    totalcardperform 						INT NOT NULL DEFAULT '0',
    PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;
	


CREATE TABLE cc_service_report (
    id 										BIGINT NOT NULL AUTO_INCREMENT,
    cc_service_id 							BIGINT NOT NULL,
    daterun 								TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    totalcardperform 						INT ,
    totalcredit 							FLOAT ,
    PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;



CREATE TABLE cc_callerid (
    id 										BIGINT NOT NULL AUTO_INCREMENT,
    cid 									CHAR(100) NULL,
    id_cc_card 								BIGINT NOT NULL,
    activated 								CHAR(1) DEFAULT 't' NOT NULL,
    PRIMARY KEY (id),
    UNIQUE cons_cc_callerid_cid (cid)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;


CREATE TABLE cc_ui_authen (
    userid 									BIGINT NOT NULL AUTO_INCREMENT,
    login 									CHAR(50) NOT NULL,
    password 								CHAR(50) NOT NULL,
    groupid 								INT ,
    perms 									INT ,
    confaddcust 							INT ,
    name 									CHAR(50),
    direction 								CHAR(80),
    zipcode 								CHAR(20),
    state 									CHAR(20),
    phone 									CHAR(30),
    fax 									CHAR(30),
    datecreation 							TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (userid),
    UNIQUE cons_cc_ui_authen_login (login)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;



CREATE TABLE cc_call (
    id 									bigINT (20) NOT NULL AUTO_INCREMENT,
    sessionid 							char(40) NOT NULL,
    uniqueid 							char(30) NOT NULL,
    username 							char(40) NOT NULL,
    nasipaddress 						char(30) DEFAULT NULL,
    starttime 							timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    stoptime 							timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
    sessiontime 						INT (11) DEFAULT NULL,
    calledstation 						char(30) DEFAULT NULL,
    startdelay 							INT (11) DEFAULT NULL,
    stopdelay 							INT (11) DEFAULT NULL,
    terminatecause 						char(20) DEFAULT NULL,
    usertariff 							char(20) DEFAULT NULL,
    calledprovider 						char(20) DEFAULT NULL,
    calledcountry 						char(30) DEFAULT NULL,
    calledsub 							char(20) DEFAULT NULL,
    calledrate 							FLOAT DEFAULT NULL,
    sessionbill 						FLOAT DEFAULT NULL,
    destination 						char(40) DEFAULT NULL,
    id_tariffgroup 						INT (11) DEFAULT NULL,
    id_tariffplan 						INT (11) DEFAULT NULL,
    id_ratecard 						INT (11) DEFAULT NULL,
    id_trunk 							INT (11) DEFAULT NULL,
    sipiax 								INT (11) DEFAULT '0',
    src 								char(40) DEFAULT NULL,
    id_did 								INT (11) DEFAULT NULL,
    buyrate 							DECIMAL(15,5) DEFAULT 0,
    buycost 							DECIMAL(15,5) DEFAULT 0,
	id_card_package_offer 				INT (11) DEFAULT 0,
    PRIMARY KEY  (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;


CREATE TABLE cc_templatemail (
    mailtype 						CHAR(50),
    fromemail 						CHAR(70),
    fromname 						CHAR(70),
    subject 						CHAR(70),
    messagetext 					LONGTEXT,
    messagehtml 					LONGTEXT,
    UNIQUE cons_cc_templatemail_mailtype (mailtype)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;




CREATE TABLE cc_tariffgroup (
    id 								INT NOT NULL AUTO_INCREMENT,
    iduser 							INT DEFAULT 0 NOT NULL,
    idtariffplan 					INT DEFAULT 0 NOT NULL,
    tariffgroupname 				CHAR(50) NOT NULL,
    lcrtype 						INT DEFAULT 0 NOT NULL,
    creationdate  					TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    removeinterprefix 				INT DEFAULT 0 NOT NULL,
	id_cc_package_offer 			BIGINT NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;


CREATE TABLE cc_tariffgroup_plan (
    idtariffgroup 					INT NOT NULL,
    idtariffplan 					INT NOT NULL,
    PRIMARY KEY (idtariffgroup, idtariffplan)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;


CREATE TABLE cc_tariffplan (
    id 								INT NOT NULL AUTO_INCREMENT,
    iduser 							INT DEFAULT 0 NOT NULL,
    tariffname 						CHAR(50) NOT NULL,
    creationdate 					TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    startingdate 					TIMESTAMP,
    expirationdate 					TIMESTAMP,
    description 					MEDIUMTEXT,
    id_trunk 						INT DEFAULT 0,
    secondusedreal 					INT DEFAULT 0,
    secondusedcarrier 				INT DEFAULT 0,
    secondusedratecard 				INT DEFAULT 0,
    reftariffplan 					INT DEFAULT 0,
    idowner 						INT DEFAULT 0,
    dnidprefix 						CHAR(30) NOT NULL DEFAULT 'all',
	calleridprefix 					CHAR(30) NOT NULL DEFAULT 'all',
    PRIMARY KEY (id),
    UNIQUE cons_cc_tariffplan_iduser_tariffname (iduser,tariffname)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;


CREATE TABLE cc_card (
    id 								BIGINT NOT NULL AUTO_INCREMENT,
    creationdate 					TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    firstusedate 					TIMESTAMP,
    expirationdate 					TIMESTAMP,
    enableexpire 					INT DEFAULT 0,
    expiredays 						INT DEFAULT 0,
    username 						CHAR(50) NOT NULL,
    useralias 						CHAR(50) NOT NULL,
    userpass 						CHAR(50) NOT NULL,
    uipass 							CHAR(50),
    credit 							DECIMAL(15,5) DEFAULT 0 NOT NULL,
    tariff 							INT DEFAULT 0,
    id_didgroup 					INT DEFAULT 0,
    activated 						CHAR(1) DEFAULT 'f' NOT NULL,
    lastname 						CHAR(50),
    firstname 						CHAR(50),
    address 						CHAR(100),
    city 							CHAR(40),
    state 							CHAR(40),
    country 						CHAR(40),
    zipcode 						CHAR(20),
    phone 							CHAR(20),
    email 							CHAR(70),
    fax 							CHAR(20),
    inuse 							INT DEFAULT 0,
    simultaccess 					INT DEFAULT 0,
    currency 						CHAR(3) DEFAULT 'USD',
    lastuse  						TIMESTAMP,
    nbused 							INT DEFAULT 0,
    typepaid 						INT DEFAULT 0,
    creditlimit 					INT DEFAULT 0,
    voipcall 						INT DEFAULT 0,
    sip_buddy 						INT DEFAULT 0,
    iax_buddy 						INT DEFAULT 0,
    language 						CHAR(5) DEFAULT 'en',
    redial 							CHAR(50),
    runservice 						INT DEFAULT 0,
	nbservice 						INT DEFAULT 0,
    id_campaign						INT DEFAULT 0,
    num_trials_done 				BIGINT DEFAULT 0,
    callback 						CHAR(50),
	vat 							FLOAT DEFAULT 0 NOT NULL,
	servicelastrun 					TIMESTAMP,
	initialbalance 					DECIMAL(15,5) DEFAULT 0 NOT NULL,
	invoiceday 						INT DEFAULT 1,
	autorefill 						INT DEFAULT 0,
    loginkey 						CHAR(40),
    activatedbyuser 				CHAR(1) DEFAULT 't' NOT NULL,
	id_subscription_fee 			INT DEFAULT 0,
	mac_addr						CHAR(17) DEFAULT '00-00-00-00-00-00' NOT NULL,
    PRIMARY KEY (id),
    UNIQUE cons_cc_card_username (username),
    UNIQUE cons_cc_card_useralias (useralias)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;


CREATE TABLE cc_ratecard (
    id 								INT NOT NULL AUTO_INCREMENT,
    idtariffplan 					INT DEFAULT 0 NOT NULL,
    dialprefix 						CHAR(30) NOT NULL,
    destination 					CHAR(50) NOT NULL,
    buyrate 						FLOAT DEFAULT 0 NOT NULL,
    buyrateinitblock 				INT DEFAULT 0 NOT NULL,
    buyrateincrement 				INT DEFAULT 0 NOT NULL,
    rateinitial 					FLOAT DEFAULT 0 NOT NULL,
    initblock 						INT DEFAULT 0 NOT NULL,
    billingblock 					INT DEFAULT 0 NOT NULL,
    connectcharge 					FLOAT DEFAULT 0 NOT NULL,
    disconnectcharge 				FLOAT DEFAULT 0 NOT NULL,
    stepchargea 					FLOAT DEFAULT 0 NOT NULL,
    chargea 						FLOAT DEFAULT 0 NOT NULL,
    timechargea 					INT DEFAULT 0 NOT NULL,
    billingblocka 					INT DEFAULT 0 NOT NULL,
    stepchargeb 					FLOAT DEFAULT 0 NOT NULL,
    chargeb 						FLOAT DEFAULT 0 NOT NULL,
    timechargeb 					INT DEFAULT 0 NOT NULL,
    billingblockb 					INT DEFAULT 0 NOT NULL,
    stepchargec 					FLOAT DEFAULT 0 NOT NULL,
    chargec 						FLOAT DEFAULT 0 NOT NULL,
    timechargec 					INT DEFAULT 0 NOT NULL,
    billingblockc 					INT DEFAULT 0 NOT NULL,
    startdate 						TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    stopdate 						TIMESTAMP,
    starttime 						SMALLINT (5) unsigned DEFAULT '0',
    endtime 						SMALLINT (5) unsigned DEFAULT '10079',
    id_trunk 						INT DEFAULT -1,
    musiconhold 					CHAR(100) NOT NULL,
	freetimetocall_package_offer 	INT NOT NULL DEFAULT 0,
	id_outbound_cidgroup INT DEFAULT -1,
    PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;
CREATE INDEX ind_cc_ratecard_dialprefix ON cc_ratecard (dialprefix);


CREATE TABLE cc_logrefill (
    id 								INT NOT NULL AUTO_INCREMENT,
    date 							TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    credit 							FLOAT NOT NULL,
    card_id 						BIGINT NOT NULL,
    reseller_id 					BIGINT ,
    PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;


CREATE TABLE cc_logpayment (
    id 								INT NOT NULL AUTO_INCREMENT,
    date 							TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    payment 						FLOAT NOT NULL,
    card_id 						BIGINT NOT NULL,
    reseller_id 					BIGINT ,
    PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;



CREATE TABLE cc_trunk (
    id_trunk 						INT NOT NULL AUTO_INCREMENT,
    trunkcode 						CHAR(20) NOT NULL,
    trunkprefix 					CHAR(20),
    providertech 					CHAR(20) NOT NULL,
    providerip 						CHAR(80) NOT NULL,
    removeprefix 					CHAR(20),
    secondusedreal 					INT DEFAULT 0,
    secondusedcarrier 				INT DEFAULT 0,
    secondusedratecard 				INT DEFAULT 0,
    creationdate 					TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    failover_trunk 					INT ,
    addparameter 					CHAR(120),
    id_provider 					INT ,
    PRIMARY KEY (id_trunk)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;




CREATE TABLE cc_sip_buddies (
    id 								INT NOT NULL AUTO_INCREMENT,
    id_cc_card 						INT DEFAULT 0 NOT NULL,
    name 							CHAR(80) DEFAULT '' NOT NULL,
    accountcode 					CHAR(20),
    regexten 						CHAR(20),
    amaflags 						CHAR(7),
    callgroup 						CHAR(10),
    callerid 						CHAR(80),
    canreinvite 					CHAR(3) DEFAULT 'yes',
    context 						CHAR(80),
    DEFAULTip 						CHAR(15),
    dtmfmode 						CHAR(7)  DEFAULT 'RFC2833' NOT NULL,	 
    fromuser 						CHAR(80),
    fromdomain 						CHAR(80),
    host 							CHAR(31) DEFAULT '' NOT NULL,
    insecure 						CHAR(20),
    language 						CHAR(2),
    mailbox 						CHAR(50),
    md5secret 						CHAR(80),
    nat 							CHAR(3) DEFAULT 'yes',
    permit 							CHAR(95),
    deny 							CHAR(95),
    mask 							CHAR(95),
    pickupgroup 					CHAR(10),
    port 							CHAR(5) DEFAULT '' NOT NULL,
    qualify 						CHAR(7) DEFAULT 'yes',
    restrictcid 					CHAR(1),
    rtptimeout 						CHAR(3),
    rtpholdtimeout 					CHAR(3),
    secret 							CHAR(80),
    type 							CHAR(6) DEFAULT 'friend' NOT NULL,
    username 						CHAR(80) DEFAULT '' NOT NULL,
    disallow 						CHAR(100) DEFAULT 'all',
    allow 							CHAR(100) DEFAULT 'gsm,ulaw,alaw',
    musiconhold 					CHAR(100),
    regseconds 						INT DEFAULT 0 NOT NULL,
    ipaddr 							CHAR(15) DEFAULT '' NOT NULL,
    cancallforward 					CHAR(3) DEFAULT 'yes',
    fullcontact 					VARCHAR(80) DEFAULT NULL,
    setvar 							VARCHAR(100) NOT NULL DEFAULT '',
    PRIMARY KEY (id),
    UNIQUE cons_cc_sip_buddies_name (name)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;


CREATE TABLE cc_iax_buddies (
    id 										INT NOT NULL AUTO_INCREMENT,
    id_cc_card 								INT DEFAULT 0 NOT NULL,
    name 									CHAR(80) DEFAULT '' NOT NULL,
    accountcode 							CHAR(20),
    regexten 								CHAR(20),
    amaflags 								CHAR(7),
    callgroup 								CHAR(10),
    callerid 								CHAR(80),
    canreinvite 							CHAR(3) DEFAULT 'yes',
    context 								CHAR(80),
    DEFAULTip 								CHAR(15),
    dtmfmode 								CHAR(7)  DEFAULT 'RFC2833' NOT NULL,	 
    fromuser 								CHAR(80),
    fromdomain 								CHAR(80),
    host 									CHAR(31) DEFAULT '' NOT NULL,
    insecure 								CHAR(20),
    language 								CHAR(2),
    mailbox 								CHAR(50),
    md5secret 								CHAR(80),
    nat 									CHAR(3) DEFAULT 'yes',
    permit 									CHAR(95),
    deny 									CHAR(95),
    mask 									CHAR(95),
    pickupgroup 							CHAR(10),
    port 									CHAR(5) DEFAULT '' NOT NULL,
    qualify 								CHAR(7) DEFAULT 'yes',
    restrictcid 							CHAR(1),
    rtptimeout 								CHAR(3),
    rtpholdtimeout 							CHAR(3),
    secret 									CHAR(80),
    type 									CHAR(6) DEFAULT 'friend' NOT NULL,
    username 								CHAR(80) DEFAULT '' NOT NULL,
    disallow 								CHAR(100) DEFAULT 'all',
    allow 									CHAR(100) DEFAULT 'gsm,ulaw,alaw',
    musiconhold 							CHAR(100),
    regseconds 								INT DEFAULT 0 NOT NULL,
    ipaddr 									CHAR(15) DEFAULT '' NOT NULL,
    cancallforward 							CHAR(3) DEFAULT 'yes',
    PRIMARY KEY (id),
    UNIQUE cons_cc_iax_buddies_name (name)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;


INSERT INTO cc_ui_authen VALUES (1, 'root', 'myroot', 0, 32767, NULL, NULL, NULL, NULL, NULL, NULL, NULL, CURRENT_TIMESTAMP);

INSERT INTO cc_templatemail (mailtype, fromemail, fromname,  subject, messagetext, messagehtml) VALUES ('signup', 'info@mydomainname.com', 'Call-Labs', 'SIGNUP CONFIRMATION', '
Thank you for registering with us

Please click on below link to activate your account.

http://myaccount.mydomainname.com/activate.php?key=$loginkey$

Please make sure you active your account by making payment to us either by
credit card, wire transfer, money order, cheque, and western union money
transfer, money Gram, and Pay pal.


Kind regards,
/My Company Name
', '');
INSERT INTO cc_templatemail (mailtype, fromemail, fromname,  subject, messagetext, messagehtml) VALUES ('reminder', 'info@mydomainname.com', 'Call-Labs', 'REMINDER', '
Our record indicates that you have less than $min_credit usd in your "$cardnumber$" account.

We hope this message provides you with enough notice to refill your account.
We value your business, but our system can disconnect you automatically
when you reach your pre-paid balance.
Please login to your account through our website to check your account
details. Plus, you can pay by credit card, on demand.

If you believe this information to be incorrect please contact
info@mydomainname.com
immediately.


Kind regards,
/My Company Name
', '');

INSERT INTO cc_templatemail (mailtype, fromemail, fromname,  subject, messagetext, messagehtml) VALUES ('forgetpassword', 'info@mydomainname.com', 'Call-Labs', 'Login Information', 'Your login information is as below:

Your account is $cardnumber$

Your password is $password$

Your login is $cardalias$

http://myaccount.mydomainname.com/

Kind regards,
/My Company Name
http://www.mydomainname.com
', '');

INSERT INTO cc_templatemail (mailtype, fromemail, fromname,  subject, messagetext, messagehtml) VALUES ('signupconfirmed', 'info@mydomainname.com', 'Call-Labs', 'SIGNUP CONFIRMATION', 'Thank you for registering with us

Please make sure you active your account by making payment to us either by
credit card, wire transfer, money order, cheque, and western union money
transfer, money Gram, and Pay pal.

Your account is $cardnumber$

Your password is $password$

To go to your account :
http://myaccount.mydomainname.com/

Kind regards,
/My Company Name
', '');

INSERT INTO cc_templatemail (mailtype, fromemail, fromname,  subject, messagetext, messagehtml) VALUES ('epaymentverify', 'info@mydomainname.com', 'Call-Labs', 'Epayment Gateway Security Verification Failed', 'Dear Administrator

Please check the Epayment Log, System has logged a Epayment Security failure. that may be a possible attack on epayment processing.

Time of Transaction: $time$
Payment Gateway: $paymentgateway$
Amount: $itemAmount$



Kind regards,
/My Company Name
http://www.mydomainname.com
', '');


INSERT INTO cc_templatemail (mailtype, fromemail, fromname,  subject, messagetext, messagehtml) VALUES ('payment', 'info@mydomainname.com', 'Call-Labs', 'PAYMENT CONFIRMATION', 'Thank you for shopping at Call-Labs.

Shopping details is as below.

Item Name = <b>$itemName$</b>
Item ID = <b>$itemID$</b>
Amount = <b>$itemAmount$</b>
Payment Method = <b>$paymentMethod$</b>
Status = <b>$paymentStatus$</b>


Kind regards,
/My Company Name
', '');

INSERT INTO cc_templatemail (mailtype, fromemail, fromname,  subject, messagetext, messagehtml) VALUES ('invoice', 'info@mydomainname.com', 'Call-Labs', 'A2BILLING INVOICE', 'Dear Customer.

Attached is the invoice.

Kind regards,
/My Company Name
http://www.mydomainname.com
', '');

INSERT INTO cc_trunk VALUES (1, 'DEFAULT', '011', 'IAX2', 'kiki@switch-2.kiki.net', '', 0, 0, 0, CURRENT_TIMESTAMP, 0, '', NULL);



--
-- Country table : Store the iso country list
--

CREATE TABLE cc_country (
    id 								BIGINT NOT NULL AUTO_INCREMENT,
    countrycode 					CHAR(80) NOT NULL,
    countryprefix 					CHAR(80) NOT NULL,
    countryname 					CHAR(80) NOT NULL,
    PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;

INSERT INTO cc_country VALUES (1, 'AFG' ,'93', 'Afghanistan');
INSERT INTO cc_country VALUES (2, 'ALB' ,'355',  'Albania');
INSERT INTO cc_country VALUES (3, 'DZA' ,'213',  'Algeria');
INSERT INTO cc_country VALUES (4, 'ASM' ,'684',  'American Samoa');
INSERT INTO cc_country VALUES (5, 'AND' ,'376',  'Andorra');
INSERT INTO cc_country VALUES (6, 'AGO' ,'244',  'Angola');
INSERT INTO cc_country VALUES (7, 'AIA' ,'1264',  'Anguilla');
INSERT INTO cc_country VALUES (8, 'ATA' ,'672',  'Antarctica');
INSERT INTO cc_country VALUES (9, 'ATG' ,'1268',  'Antigua And Barbuda');
INSERT INTO cc_country VALUES (10, 'ARG' ,'54',  'Argentina');
INSERT INTO cc_country VALUES (11, 'ARM' ,'374',  'Armenia');
INSERT INTO cc_country VALUES (12, 'ABW' ,'297', 'Aruba');
INSERT INTO cc_country VALUES (13, 'AUS' ,'61',  'Australia');
INSERT INTO cc_country VALUES (14, 'AUT' ,'43',  'Austria');
INSERT INTO cc_country VALUES (15, 'AZE' ,'994',  'Azerbaijan');
INSERT INTO cc_country VALUES (16, 'BHS' ,'1242',  'Bahamas');
INSERT INTO cc_country VALUES (17, 'BHR' ,'973',  'Bahrain');
INSERT INTO cc_country VALUES (18, 'BGD' ,'880',  'Bangladesh');
INSERT INTO cc_country VALUES (19, 'BRB' ,'1246',  'Barbados');
INSERT INTO cc_country VALUES (20, 'BLR' ,'375',  'Belarus');
INSERT INTO cc_country VALUES (21, 'BEL' ,'32',  'Belgium');
INSERT INTO cc_country VALUES (22, 'BLZ' ,'501',  'Belize');
INSERT INTO cc_country VALUES (23, 'BEN' ,'229',  'Benin');
INSERT INTO cc_country VALUES (24, 'BMU' ,'1441', 'Bermuda');
INSERT INTO cc_country VALUES (25,  'BTN' ,'975', 'Bhutan');
INSERT INTO cc_country VALUES (26,  'BOL' ,'591', 'Bolivia');
INSERT INTO cc_country VALUES (27,  'BIH' ,'387', 'Bosnia And Herzegovina');
INSERT INTO cc_country VALUES (28,  'BWA' ,'267', 'Botswana');
INSERT INTO cc_country VALUES (29,  'BVT' ,'0', 'Bouvet Island');
INSERT INTO cc_country VALUES (30,  'BRA' ,'55', 'Brazil');
INSERT INTO cc_country VALUES (31,  'IOT' ,'1284', 'British Indian Ocean Territory');
INSERT INTO cc_country VALUES (32,  'BRN' ,'673', 'Brunei Darussalam');
INSERT INTO cc_country VALUES (33,  'BGR' ,'359', 'Bulgaria');
INSERT INTO cc_country VALUES (34,  'BFA' ,'226', 'Burkina Faso');
INSERT INTO cc_country VALUES (35,  'BDI' ,'257', 'Burundi');
INSERT INTO cc_country VALUES (36,  'KHM' ,'855', 'Cambodia');
INSERT INTO cc_country VALUES (37,  'CMR' ,'237', 'Cameroon');
INSERT INTO cc_country VALUES (38,  'CAN' ,'1', 'Canada');
INSERT INTO cc_country VALUES (39, 'CPV' ,'238',  'Cape Verde');
INSERT INTO cc_country VALUES (40, 'CYM' ,'1345',  'Cayman Islands');
INSERT INTO cc_country VALUES (41, 'CAF' ,'236',  'Central African Republic');
INSERT INTO cc_country VALUES (42, 'TCD' ,'235',  'Chad');
INSERT INTO cc_country VALUES (43, 'CHL' ,'56',  'Chile');
INSERT INTO cc_country VALUES (44, 'CHN' ,'86', 'China');
INSERT INTO cc_country VALUES (45,  'CXR' ,'618', 'Christmas Island');
INSERT INTO cc_country VALUES (46, 'CCK' ,'61',  'Cocos (Keeling); Islands');
INSERT INTO cc_country VALUES (47, 'COL' ,'57', 'Colombia');
INSERT INTO cc_country VALUES (48, 'COM' ,'269', 'Comoros');
INSERT INTO cc_country VALUES (49, 'COG' ,'242', 'Congo');
INSERT INTO cc_country VALUES (50, 'COD' ,'243','Congo, The Democratic Republic Of The');
INSERT INTO cc_country VALUES (51, 'COK' ,'682', 'Cook Islands');
INSERT INTO cc_country VALUES (52, 'CRI' ,'506', 'Costa Rica');
INSERT INTO cc_country VALUES (54, 'HRV' ,'385', 'Croatia');
INSERT INTO cc_country VALUES (55, 'CUB' ,'53', 'Cuba');
INSERT INTO cc_country VALUES (56, 'CYP' ,'357', 'Cyprus');
INSERT INTO cc_country VALUES (57, 'CZE' ,'420', 'Czech Republic');
INSERT INTO cc_country VALUES (58, 'DNK' ,'45', 'Denmark');
INSERT INTO cc_country VALUES (59, 'DJI' ,'253', 'Djibouti');
INSERT INTO cc_country VALUES (60, 'DMA' ,'1767', 'Dominica');
INSERT INTO cc_country VALUES (61, 'DOM' ,'1809', 'Dominican Republic');
INSERT INTO cc_country VALUES (62, 'ECU' ,'593', 'Ecuador');
INSERT INTO cc_country VALUES (63, 'EGY' ,'20', 'Egypt');
INSERT INTO cc_country VALUES (64, 'SLV' ,'503', 'El Salvador');
INSERT INTO cc_country VALUES (65, 'GNQ' ,'240', 'Equatorial Guinea');
INSERT INTO cc_country VALUES (66, 'ERI' ,'291', 'Eritrea');
INSERT INTO cc_country VALUES (67, 'EST' ,'372', 'Estonia');
INSERT INTO cc_country VALUES (68, 'ETH' ,'251', 'Ethiopia');
INSERT INTO cc_country VALUES (69, 'FLK' ,'500', 'Falkland Islands (Malvinas);');
INSERT INTO cc_country VALUES (70, 'FRO' ,'298', 'Faroe Islands');
INSERT INTO cc_country VALUES (71, 'FJI' ,'679', 'Fiji');
INSERT INTO cc_country VALUES (72, 'FIN' ,'358', 'Finland');
INSERT INTO cc_country VALUES (73, 'FRA' ,'33', 'France');
INSERT INTO cc_country VALUES (74, 'GUF' ,'596', 'French Guiana');
INSERT INTO cc_country VALUES (75, 'PYF' ,'594', 'French Polynesia');
INSERT INTO cc_country VALUES (76, 'ATF' ,'689', 'French Southern Territories');
INSERT INTO cc_country VALUES (77, 'GAB' ,'241', 'Gabon');
INSERT INTO cc_country VALUES (78, 'GMB' ,'220', 'Gambia');
INSERT INTO cc_country VALUES (79, 'GEO' ,'995', 'Georgia');
INSERT INTO cc_country VALUES (80, 'DEU' ,'49', 'Germany');
INSERT INTO cc_country VALUES (81, 'GHA' ,'233', 'Ghana');
INSERT INTO cc_country VALUES (82, 'GIB' ,'350', 'Gibraltar');
INSERT INTO cc_country VALUES (83, 'GRC' ,'30', 'Greece');
INSERT INTO cc_country VALUES (84, 'GRL' ,'299', 'Greenland');
INSERT INTO cc_country VALUES (85, 'GRD' ,'1473', 'Grenada');
INSERT INTO cc_country VALUES (86, 'GLP' ,'590', 'Guadeloupe');
INSERT INTO cc_country VALUES (87, 'GUM' ,'1671', 'Guam');
INSERT INTO cc_country VALUES (88, 'GTM' ,'502', 'Guatemala');
INSERT INTO cc_country VALUES (89, 'GIN' ,'224', 'Guinea');
INSERT INTO cc_country VALUES (90, 'GNB' ,'245', 'Guinea-Bissau');
INSERT INTO cc_country VALUES (91, 'GUY' ,'592', 'Guyana');
INSERT INTO cc_country VALUES (92, 'HTI' ,'509', 'Haiti');
INSERT INTO cc_country VALUES (93, 'HMD' ,'0', 'Heard Island And McDonald Islands');
INSERT INTO cc_country VALUES (94, 'VAT' ,'0', 'Holy See (Vatican City State);');
INSERT INTO cc_country VALUES (95, 'HND' ,'504', 'Honduras');
INSERT INTO cc_country VALUES (96, 'HKG' ,'852', 'Hong Kong');
INSERT INTO cc_country VALUES (97, 'HUN' ,'36', 'Hungary');
INSERT INTO cc_country VALUES (98, 'ISL' ,'354', 'Iceland');
INSERT INTO cc_country VALUES (99, 'IND' ,'91', 'India');
INSERT INTO cc_country VALUES (100, 'IDN' ,'62', 'Indonesia');
INSERT INTO cc_country VALUES (101, 'IRN' ,'98', 'Iran, Islamic Republic Of');
INSERT INTO cc_country VALUES (102, 'IRQ' ,'964', 'Iraq');
INSERT INTO cc_country VALUES (103, 'IRL' ,'353', 'Ireland');
INSERT INTO cc_country VALUES (104, 'ISR' ,'972', 'Israel');
INSERT INTO cc_country VALUES (105, 'ITA' ,'39', 'Italy');
INSERT INTO cc_country VALUES (106, 'JAM' ,'1876', 'Jamaica');
INSERT INTO cc_country VALUES (107, 'JPN' ,'81', 'Japan');
INSERT INTO cc_country VALUES (108, 'JOR' ,'962', 'Jordan');
INSERT INTO cc_country VALUES (109, 'KAZ' ,'7', 'Kazakhstan');
INSERT INTO cc_country VALUES (110, 'KEN' ,'254', 'Kenya');
INSERT INTO cc_country VALUES (111, 'KIR' ,'686', 'Kiribati');
INSERT INTO cc_country VALUES (112, 'PRK' ,'850', 'Korea, Democratic People''s Republic Of');
INSERT INTO cc_country VALUES (113, 'KOR' ,'82', 'Korea, Republic of');
INSERT INTO cc_country VALUES (114, 'KWT' ,'965', 'Kuwait');
INSERT INTO cc_country VALUES (115, 'KGZ' ,'996', 'Kyrgyzstan');
INSERT INTO cc_country VALUES (116, 'LAO' ,'856', 'Lao People''s Democratic Republic');
INSERT INTO cc_country VALUES (117, 'LVA' ,'371', 'Latvia');
INSERT INTO cc_country VALUES (118, 'LBN' ,'961', 'Lebanon');
INSERT INTO cc_country VALUES (119, 'LSO' ,'266', 'Lesotho');
INSERT INTO cc_country VALUES (120, 'LBR' ,'231', 'Liberia');
INSERT INTO cc_country VALUES (121, 'LBY' ,'218', 'Libyan Arab Jamahiriya');
INSERT INTO cc_country VALUES (122, 'LIE' ,'423', 'Liechtenstein');
INSERT INTO cc_country VALUES (123, 'LTU' ,'370', 'Lithuania');
INSERT INTO cc_country VALUES (124, 'LUX' ,'352', 'Luxembourg');
INSERT INTO cc_country VALUES (125, 'MAC' ,'853', 'Macao');
INSERT INTO cc_country VALUES (126, 'MKD' ,'389', 'Macedonia, The Former Yugoslav Republic Of');
INSERT INTO cc_country VALUES (127, 'MDG' ,'261', 'Madagascar');
INSERT INTO cc_country VALUES (128, 'MWI' ,'265', 'Malawi');
INSERT INTO cc_country VALUES (129, 'MYS' ,'60', 'Malaysia');
INSERT INTO cc_country VALUES (130, 'MDV' ,'960', 'Maldives');
INSERT INTO cc_country VALUES (131, 'MLI' ,'223', 'Mali');
INSERT INTO cc_country VALUES (132, 'MLT' ,'356', 'Malta');
INSERT INTO cc_country VALUES (133, 'MHL' ,'692', 'Marshall islands');
INSERT INTO cc_country VALUES (134, 'MTQ' ,'596', 'Martinique');
INSERT INTO cc_country VALUES (135, 'MRT' ,'222', 'Mauritania');
INSERT INTO cc_country VALUES (136, 'MUS' ,'230', 'Mauritius');
INSERT INTO cc_country VALUES (137, 'MYT' ,'269', 'Mayotte');
INSERT INTO cc_country VALUES (138, 'MEX' ,'52', 'Mexico');
INSERT INTO cc_country VALUES (139, 'FSM' ,'691', 'Micronesia, Federated States Of');
INSERT INTO cc_country VALUES (140, 'MDA' ,'1808', 'Moldova, Republic Of');
INSERT INTO cc_country VALUES (141, 'MCO' ,'377', 'Monaco');
INSERT INTO cc_country VALUES (142, 'MNG' ,'976', 'Mongolia');
INSERT INTO cc_country VALUES (143, 'MSR' ,'1664', 'Montserrat');
INSERT INTO cc_country VALUES (144, 'MAR' ,'212', 'Morocco');
INSERT INTO cc_country VALUES (145, 'MOZ' ,'258', 'Mozambique');
INSERT INTO cc_country VALUES (146, 'MMR' ,'95', 'Myanmar');
INSERT INTO cc_country VALUES (147, 'NAM' ,'264', 'Namibia');
INSERT INTO cc_country VALUES (148, 'NRU' ,'674', 'Nauru');
INSERT INTO cc_country VALUES (149, 'NPL' ,'977', 'Nepal');
INSERT INTO cc_country VALUES (150, 'NLD' ,'31', 'Netherlands');
INSERT INTO cc_country VALUES (151, 'ANT' ,'599', 'Netherlands Antilles');
INSERT INTO cc_country VALUES (152, 'NCL' ,'687', 'New Caledonia');
INSERT INTO cc_country VALUES (153, 'NZL' ,'64', 'New Zealand');
INSERT INTO cc_country VALUES (154, 'NIC' ,'505', 'Nicaragua');
INSERT INTO cc_country VALUES (155, 'NER' ,'227', 'Niger');
INSERT INTO cc_country VALUES (156, 'NGA' ,'234', 'Nigeria');
INSERT INTO cc_country VALUES (157, 'NIU' ,'683', 'Niue');
INSERT INTO cc_country VALUES (158, 'NFK' ,'672', 'Norfolk Island');
INSERT INTO cc_country VALUES (159, 'MNP' ,'1670', 'Northern Mariana Islands');
INSERT INTO cc_country VALUES (160, 'NOR' ,'47', 'Norway');
INSERT INTO cc_country VALUES (161, 'OMN' ,'968', 'Oman');
INSERT INTO cc_country VALUES (162, 'PAK' ,'92', 'Pakistan');
INSERT INTO cc_country VALUES (163, 'PLW' ,'680', 'Palau');
INSERT INTO cc_country VALUES (164, 'PSE' ,'970', 'Palestinian Territory, Occupied');
INSERT INTO cc_country VALUES (165, 'PAN' ,'507', 'Panama');
INSERT INTO cc_country VALUES (166, 'PNG' ,'675', 'Papua New Guinea');
INSERT INTO cc_country VALUES (167, 'PRY' ,'595', 'Paraguay');
INSERT INTO cc_country VALUES (168, 'PER' ,'51', 'Peru');
INSERT INTO cc_country VALUES (169, 'PHL' ,'63', 'Philippines');
INSERT INTO cc_country VALUES (170, 'PCN' ,'0', 'Pitcairn');
INSERT INTO cc_country VALUES (171, 'POL' ,'48', 'Poland');
INSERT INTO cc_country VALUES (172, 'PRT' ,'351', 'Portugal');
INSERT INTO cc_country VALUES (173, 'PRI' ,'1787', 'Puerto Rico');
INSERT INTO cc_country VALUES (174, 'QAT' ,'974', 'Qatar');
INSERT INTO cc_country VALUES (175, 'REU' ,'262', 'Reunion');
INSERT INTO cc_country VALUES (176, 'ROU' ,'40', 'Romania');
INSERT INTO cc_country VALUES (177, 'RUS' ,'7', 'Russian Federation');
INSERT INTO cc_country VALUES (178, 'RWA' ,'250', 'Rwanda');
INSERT INTO cc_country VALUES (179, 'SHN' ,'290', 'SaINT Helena');
INSERT INTO cc_country VALUES (180, 'KNA' ,'1869', 'SaINT Kitts And Nevis');
INSERT INTO cc_country VALUES (181, 'LCA' ,'1758', 'SaINT Lucia');
INSERT INTO cc_country VALUES (182, 'SPM' ,'508', 'SaINT Pierre And Miquelon');
INSERT INTO cc_country VALUES (183, 'VCT' ,'1784', 'SaINT Vincent And The Grenadines');
INSERT INTO cc_country VALUES (184, 'WSM' ,'685', 'Samoa');
INSERT INTO cc_country VALUES (185, 'SMR' ,'378', 'San Marino');
INSERT INTO cc_country VALUES (186, 'STP' ,'239', 'São Tomé And Principe');
INSERT INTO cc_country VALUES (187, 'SAU' ,'966', 'Saudi Arabia');
INSERT INTO cc_country VALUES (188, 'SEN' ,'221', 'Senegal');
INSERT INTO cc_country VALUES (189, 'SYC' ,'248', 'Seychelles');
INSERT INTO cc_country VALUES (190, 'SLE' ,'232', 'Sierra Leone');
INSERT INTO cc_country VALUES (191, 'SGP' ,'65', 'Singapore');
INSERT INTO cc_country VALUES (192, 'SVK' ,'421', 'Slovakia');
INSERT INTO cc_country VALUES (193, 'SVN' ,'386', 'Slovenia');
INSERT INTO cc_country VALUES (194, 'SLB' ,'677', 'Solomon Islands');
INSERT INTO cc_country VALUES (195, 'SOM' ,'252', 'Somalia');
INSERT INTO cc_country VALUES (196, 'ZAF' ,'27', 'South Africa');
INSERT INTO cc_country VALUES (197, 'SGS' ,'0', 'South Georgia And The South Sandwich Islands');
INSERT INTO cc_country VALUES (198, 'ESP' ,'34', 'Spain');
INSERT INTO cc_country VALUES (199, 'LKA' ,'94', 'Sri Lanka');
INSERT INTO cc_country VALUES (200, 'SDN' ,'249', 'Sudan');
INSERT INTO cc_country VALUES (201, 'SUR' ,'597', 'Suriname');
INSERT INTO cc_country VALUES (202, 'SJM' ,'0', 'Svalbard and Jan Mayen');
INSERT INTO cc_country VALUES (203, 'SWZ' ,'268', 'Swaziland');
INSERT INTO cc_country VALUES (204, 'SWE' ,'46', 'Sweden');
INSERT INTO cc_country VALUES (205, 'CHE' ,'41', 'Switzerland');
INSERT INTO cc_country VALUES (206, 'SYR' ,'963', 'Syrian Arab Republic');
INSERT INTO cc_country VALUES (207, 'TWN' ,'886', 'Taiwan, Province Of China');
INSERT INTO cc_country VALUES (208, 'TJK' ,'992', 'Tajikistan');
INSERT INTO cc_country VALUES (209, 'TZA' ,'255', 'Tanzania, United Republic Of');
INSERT INTO cc_country VALUES (210, 'THA' ,'66', 'Thailand');
INSERT INTO cc_country VALUES (211, 'TLS' ,'670', 'Timor-Leste');
INSERT INTO cc_country VALUES (212, 'TGO' ,'228', 'Togo');
INSERT INTO cc_country VALUES (213, 'TKL' ,'690', 'Tokelau');
INSERT INTO cc_country VALUES (214, 'TON' ,'676', 'Tonga');
INSERT INTO cc_country VALUES (215, 'TTO' ,'1868', 'Trinidad And Tobago');
INSERT INTO cc_country VALUES (216, 'TUN' ,'216', 'Tunisia');
INSERT INTO cc_country VALUES (217, 'TUR' ,'90', 'Turkey');
INSERT INTO cc_country VALUES (218, 'TKM' ,'993', 'Turkmenistan');
INSERT INTO cc_country VALUES (219, 'TCA' ,'1649', 'Turks And Caicos Islands');
INSERT INTO cc_country VALUES (220, 'TUV' ,'688', 'Tuvalu');
INSERT INTO cc_country VALUES (221, 'UGA' ,'256', 'Uganda');
INSERT INTO cc_country VALUES (222, 'UKR' ,'380', 'Ukraine');
INSERT INTO cc_country VALUES (223, 'ARE' ,'971', 'United Arab Emirates');
INSERT INTO cc_country VALUES (224, 'GBR' ,'44', 'United Kingdom');
INSERT INTO cc_country VALUES (225, 'USA' ,'1', 'United States');
INSERT INTO cc_country VALUES (226, 'UMI' ,'0', 'United States Minor Outlying Islands');
INSERT INTO cc_country VALUES (227, 'URY' ,'598', 'Uruguay');
INSERT INTO cc_country VALUES (228, 'UZB' ,'998', 'Uzbekistan');
INSERT INTO cc_country VALUES (229, 'VUT' ,'678', 'Vanuatu');
INSERT INTO cc_country VALUES (230, 'VEN' ,'58', 'Venezuela');
INSERT INTO cc_country VALUES (231, 'VNM' ,'84', 'Vietnam');
INSERT INTO cc_country VALUES (232, 'VGB','1284', 'Virgin Islands, British');
INSERT INTO cc_country VALUES (233, 'VIR','808', 'Virgin Islands, U.S.');
INSERT INTO cc_country VALUES (234, 'WLF' ,'681', 'Wallis And Futuna');
INSERT INTO cc_country VALUES (235, 'ESH' ,'0', 'Western Sahara');
INSERT INTO cc_country VALUES (236, 'YEM' ,'967', 'Yemen');
INSERT INTO cc_country VALUES (237, 'YUG' ,'0', 'Yugoslavia');
INSERT INTO cc_country VALUES (238, 'ZMB' ,'260', 'Zambia');
INSERT INTO cc_country VALUES (239, 'ZWE' ,'263', 'Zimbabwe');
INSERT INTO cc_country VALUES (240, 'ASC' ,'0', 'Ascension Island');
INSERT INTO cc_country VALUES (241, 'DGA' ,'0', 'Diego Garcia');
INSERT INTO cc_country VALUES (242, 'XNM' ,'0', 'Inmarsat');
INSERT INTO cc_country VALUES (243, 'TMP' ,'0', 'East timor');
INSERT INTO cc_country VALUES (244, 'AK' ,'0', 'Alaska');
INSERT INTO cc_country VALUES (245, 'HI' ,'0', 'Hawaii');
INSERT INTO cc_country VALUES (53, 'CIV' ,'225', 'Côte d''Ivoire');
INSERT INTO cc_country VALUES (246, 'ALA' ,'35818', 'Aland Islands');
INSERT INTO cc_country VALUES (247, 'BLM' ,'0', 'Saint Barthelemy');
INSERT INTO cc_country VALUES (248, 'GGY' ,'441481', 'Guernsey');
INSERT INTO cc_country VALUES (249, 'IMN' ,'441624', 'Isle of Man');
INSERT INTO cc_country VALUES (250, 'JEY' ,'441534', 'Jersey');
INSERT INTO cc_country VALUES (251, 'MAF' ,'0', 'Saint Martin');
INSERT INTO cc_country VALUES (252, 'MNE' ,'382', 'Montenegro, Republic of');
INSERT INTO cc_country VALUES (253, 'SRB' ,'381', 'Serbia, Republic of');
INSERT INTO cc_country VALUES (254, 'CPT' ,'0', 'Clipperton Island');
INSERT INTO cc_country VALUES (255, 'TAA' ,'0', 'Tristan da Cunha');




--
-- Auto Dialer update  database - Create database schema
--



CREATE TABLE cc_campaign (
    id 							INT NOT NULL AUTO_INCREMENT,    
    campaign_name 				CHAR(50) NOT NULL,
    creationdate 				TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    startingdate 				TIMESTAMP, 
    expirationdate 				TIMESTAMP,
    description 				MEDIUMTEXT,
    id_trunk 					INT DEFAULT 0,
    secondusedreal 				INT DEFAULT 0,
    nb_callmade 				INT DEFAULT 0,
    enable INT 					DEFAULT 0 NOT NULL,	
    PRIMARY KEY (id),
    UNIQUE cons_cc_campaign_campaign_name (campaign_name)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;


CREATE TABLE cc_phonelist (
    id 							INT NOT NULL AUTO_INCREMENT,
    id_cc_campaign 				INT DEFAULT 0 NOT NULL,
    id_cc_card 					INT DEFAULT 0 NOT NULL,
    numbertodial 				CHAR(50) NOT NULL,
    name 						CHAR(60) NOT NULL,
    inuse 						INT DEFAULT 0,
    enable 						INT DEFAULT 1 NOT NULL,    
    num_trials_done 			INT DEFAULT 0,
    creationdate 				TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,	
    last_attempt 				TIMESTAMP,
    secondusedreal 				INT DEFAULT 0,
    additionalinfo 				MEDIUMTEXT,
    PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;
CREATE INDEX ind_cc_phonelist_numbertodial ON cc_phonelist (numbertodial);


CREATE TABLE cc_provider(
    id 							INT NOT NULL AUTO_INCREMENT,
    provider_name 				CHAR(30) NOT NULL,
    creationdate 				TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    description 				MEDIUMTEXT,
    PRIMARY KEY (id),
    UNIQUE cons_cc_provider_provider_name (provider_name)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;
		
--
--  cc_currencies table
--

CREATE TABLE cc_currencies (
    id 								SMALLINT (5) unsigned NOT NULL AUTO_INCREMENT,
    currency 						CHAR(3) NOT NULL DEFAULT '',
    name 							VARCHAR(30) NOT NULL DEFAULT '',
    value 							FLOAT (7,5) unsigned NOT NULL DEFAULT '0.00000',
    lastupdate 						TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    basecurrency 					CHAR(3) NOT NULL DEFAULT 'USD',
    PRIMARY KEY  (id),
    UNIQUE cons_cc_currencies_currency (currency)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin AUTO_INCREMENT=150;


INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (1, 'ALL', 'Albanian Lek (ALL)', 0.00974,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (2, 'DZD', 'Algerian Dinar (DZD)', 0.01345,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (3, 'XAL', 'Aluminium Ounces (XAL)', 1.08295,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (4, 'ARS', 'Argentine Peso (ARS)', 0.32455,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (5, 'AWG', 'Aruba Florin (AWG)', 0.55866,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (6, 'AUD', 'Australian Dollar (AUD)', 0.73384,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (7, 'BSD', 'Bahamian Dollar (BSD)', 1.00000,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (8, 'BHD', 'Bahraini Dinar (BHD)', 2.65322,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (9, 'BDT', 'Bangladesh Taka (BDT)', 0.01467,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (10, 'BBD', 'Barbados Dollar (BBD)', 0.50000,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (11, 'BYR', 'Belarus Ruble (BYR)', 0.00046,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (12, 'BZD', 'Belize Dollar (BZD)', 0.50569,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (13, 'BMD', 'Bermuda Dollar (BMD)', 1.00000,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (14, 'BTN', 'Bhutan Ngultrum (BTN)', 0.02186,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (15, 'BOB', 'Bolivian Boliviano (BOB)', 0.12500,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (16, 'BRL', 'Brazilian Real (BRL)', 0.46030, 'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (17, 'GBP', 'British Pound (GBP)', 1.73702,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (18, 'BND', 'Brunei Dollar (BND)', 0.61290,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (19, 'BGN', 'Bulgarian Lev (BGN)', 0.60927,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (20, 'BIF', 'Burundi Franc (BIF)', 0.00103,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (21, 'KHR', 'Cambodia Riel (KHR)', 0.00000,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (22, 'CAD', 'Canadian Dollar (CAD)', 0.86386,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (23, 'KYD', 'Cayman Islands Dollar (KYD)', 1.16496,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (24, 'XOF', 'CFA Franc (BCEAO) (XOF)', 0.00182,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (25, 'XAF', 'CFA Franc (BEAC) (XAF)', 0.00182, 'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (26, 'CLP', 'Chilean Peso (CLP)', 0.00187,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (27, 'CNY', 'Chinese Yuan (CNY)', 0.12425,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (28, 'COP', 'Colombian Peso (COP)', 0.00044,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (29, 'KMF', 'Comoros Franc (KMF)', 0.00242,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (30, 'XCP', 'Copper Ounces (XCP)', 2.16403,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (31, 'CRC', 'Costa Rica Colon (CRC)', 0.00199,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (32, 'HRK', 'Croatian Kuna (HRK)', 0.16249,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (33, 'CUP', 'Cuban Peso (CUP)', 1.00000,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (34, 'CYP', 'Cyprus Pound (CYP)', 2.07426, 'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (35, 'CZK', 'Czech Koruna (CZK)', 0.04133,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (36, 'DKK', 'Danish Krone (DKK)', 0.15982,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (37, 'DJF', 'Dijibouti Franc (DJF)', 0.00000,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (38, 'DOP', 'Dominican Peso (DOP)', 0.03035,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (39, 'XCD', 'East Caribbean Dollar (XCD)', 0.37037,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (40, 'ECS', 'Ecuador Sucre (ECS)', 0.00004,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (41, 'EGP', 'Egyptian Pound (EGP)', 0.17433,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (42, 'SVC', 'El Salvador Colon (SVC)', 0.11426,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (43, 'ERN', 'Eritrea Nakfa (ERN)', 0.00000,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (44, 'EEK', 'Estonian Kroon (EEK)', 0.07615,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (45, 'ETB', 'Ethiopian Birr (ETB)', 0.11456,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (46, 'EUR', 'Euro (EUR)', 1.19175,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (47, 'FKP', 'Falkland Islands Pound (FKP)', 0.00000,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (48, 'GMD', 'Gambian Dalasi (GMD)', 0.03515,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (49, 'GHC', 'Ghanian Cedi (GHC)', 0.00011,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (50, 'GIP', 'Gibraltar Pound (GIP)', 0.00000,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (51, 'XAU', 'Gold Ounces (XAU)', 555.55556,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (52, 'GTQ', 'Guatemala Quetzal (GTQ)', 0.13103,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (53, 'GNF', 'Guinea Franc (GNF)', 0.00022,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (54, 'HTG', 'Haiti Gourde (HTG)', 0.02387,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (55, 'HNL', 'Honduras Lempira (HNL)', 0.05292,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (56, 'HKD', 'Hong Kong Dollar (HKD)', 0.12884,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (57, 'HUF', 'Hungarian ForINT (HUF)', 0.00461,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (58, 'ISK', 'Iceland Krona (ISK)', 0.01436,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (59, 'INR', 'Indian Rupee (INR)', 0.02253,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (60, 'IDR', 'Indonesian Rupiah (IDR)', 0.00011,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (61, 'IRR', 'Iran Rial (IRR)', 0.00011, 'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (62, 'ILS', 'Israeli Shekel (ILS)', 0.21192,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (63, 'JMD', 'Jamaican Dollar (JMD)', 0.01536,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (64, 'JPY', 'Japanese Yen (JPY)', 0.00849,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (65, 'JOD', 'Jordanian Dinar (JOD)', 1.41044,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (66, 'KZT', 'Kazakhstan Tenge (KZT)', 0.00773,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (67, 'KES', 'Kenyan Shilling (KES)', 0.01392,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (68, 'KRW', 'Korean Won (KRW)', 0.00102,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (69, 'KWD', 'Kuwaiti Dinar (KWD)', 3.42349,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (70, 'LAK', 'Lao Kip (LAK)', 0.00000, 'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (71, 'LVL', 'Latvian Lat (LVL)', 1.71233,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (72, 'LBP', 'Lebanese Pound (LBP)', 0.00067,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (73, 'LSL', 'Lesotho Loti (LSL)', 0.15817,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (74, 'LYD', 'Libyan Dinar (LYD)', 0.00000,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (75, 'LTL', 'Lithuanian Lita (LTL)', 0.34510, 'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (76, 'MOP', 'Macau Pataca (MOP)', 0.12509,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (77, 'MKD', 'Macedonian Denar (MKD)', 0.01945,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (78, 'MGF', 'Malagasy Franc (MGF)', 0.00011,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (79, 'MWK', 'Malawi Kwacha (MWK)', 0.00752, 'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (80, 'MYR', 'Malaysian Ringgit (MYR)', 0.26889,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (81, 'MVR', 'Maldives Rufiyaa (MVR)', 0.07813,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (82, 'MTL', 'Maltese Lira (MTL)', 2.77546,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (83, 'MRO', 'Mauritania Ougulya (MRO)', 0.00369,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (84, 'MUR', 'Mauritius Rupee (MUR)', 0.03258,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (85, 'MXN', 'Mexican Peso (MXN)', 0.09320,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (86, 'MDL', 'Moldovan Leu (MDL)', 0.07678,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (87, 'MNT', 'Mongolian Tugrik (MNT)', 0.00084,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (88, 'MAD', 'Moroccan Dirham (MAD)', 0.10897,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (89, 'MZM', 'Mozambique Metical (MZM)', 0.00004,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (90, 'NAD', 'Namibian Dollar (NAD)', 0.15817, 'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (91, 'NPR', 'Nepalese Rupee (NPR)', 0.01408, 'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (92, 'ANG', 'Neth Antilles Guilder (ANG)', 0.55866,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (93, 'TRY', 'New Turkish Lira (TRY)', 0.73621,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (94, 'NZD', 'New Zealand Dollar (NZD)', 0.65096,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (95, 'NIO', 'Nicaragua Cordoba (NIO)', 0.05828,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (96, 'NGN', 'Nigerian Naira (NGN)', 0.00777,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (97, 'NOK', 'Norwegian Krone (NOK)', 0.14867,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (98, 'OMR', 'Omani Rial (OMR)', 2.59740,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (99, 'XPF', 'Pacific Franc (XPF)', 0.00999,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (100, 'PKR', 'Pakistani Rupee (PKR)', 0.01667,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (101, 'XPD', 'Palladium Ounces (XPD)', 277.77778,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (102, 'PAB', 'Panama Balboa (PAB)', 1.00000,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (103, 'PGK', 'Papua New Guinea Kina (PGK)', 0.33125,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (104, 'PYG', 'Paraguayan Guarani (PYG)', 0.00017,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (105, 'PEN', 'Peruvian Nuevo Sol (PEN)', 0.29999,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (106, 'PHP', 'Philippine Peso (PHP)', 0.01945,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (107, 'XPT', 'Platinum Ounces (XPT)', 1000.00000,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (108, 'PLN', 'Polish Zloty (PLN)', 0.30574, 'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (109, 'QAR', 'Qatar Rial (QAR)', 0.27476,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (110, 'ROL', 'Romanian Leu (ROL)', 0.00000,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (111, 'RON', 'Romanian New Leu (RON)', 0.34074,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (112, 'RUB', 'Russian Rouble (RUB)', 0.03563,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (113, 'RWF', 'Rwanda Franc (RWF)', 0.00185,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (114, 'WST', 'Samoa Tala (WST)', 0.35492,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (115, 'STD', 'Sao Tome Dobra (STD)', 0.00000,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (116, 'SAR', 'Saudi Arabian Riyal (SAR)', 0.26665,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (117, 'SCR', 'Seychelles Rupee (SCR)', 0.18114,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (118, 'SLL', 'Sierra Leone Leone (SLL)', 0.00034,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (119, 'XAG', 'Silver Ounces (XAG)', 9.77517,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (120, 'SGD', 'Singapore Dollar (SGD)', 0.61290,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (121, 'SKK', 'Slovak Koruna (SKK)', 0.03157, 'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (122, 'SIT', 'Slovenian Tolar (SIT)', 0.00498,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (123, 'SOS', 'Somali Shilling (SOS)', 0.00000, 'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (124, 'ZAR', 'South African Rand (ZAR)', 0.15835, 'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (125, 'LKR', 'Sri Lanka Rupee (LKR)', 0.00974,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (126, 'SHP', 'St Helena Pound (SHP)', 0.00000,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (127, 'SDD', 'Sudanese Dinar (SDD)', 0.00427,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (128, 'SRG', 'Surinam Guilder (SRG)', 0.36496,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (129, 'SZL', 'Swaziland Lilageni (SZL)', 0.15817,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (130, 'SEK', 'Swedish Krona (SEK)', 0.12609,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (131, 'CHF', 'Swiss Franc (CHF)', 0.76435,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (132, 'SYP', 'Syrian Pound (SYP)', 0.00000,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (133, 'TWD', 'Taiwan Dollar (TWD)', 0.03075,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (134, 'TZS', 'Tanzanian Shilling (TZS)', 0.00083,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (135, 'THB', 'Thai Baht (THB)', 0.02546,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (136, 'TOP', 'Tonga Paanga (TOP)', 0.48244, 'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (137, 'TTD', 'Trinidad&Tobago Dollar (TTD)', 0.15863,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (138, 'TND', 'Tunisian Dinar (TND)', 0.73470,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (139, 'USD', 'U.S. Dollar (USD)', 1.00000,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (140, 'AED', 'UAE Dirham (AED)', 0.27228,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (141, 'UGX', 'Ugandan Shilling (UGX)', 0.00055, 'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (142, 'UAH', 'Ukraine Hryvnia (UAH)', 0.19755,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (143, 'UYU', 'Uruguayan New Peso (UYU)', 0.04119,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (144, 'VUV', 'Vanuatu Vatu (VUV)', 0.00870,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (145, 'VEB', 'Venezuelan Bolivar (VEB)', 0.00037,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (146, 'VND', 'Vietnam Dong (VND)', 0.00006,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (147, 'YER', 'Yemen Riyal (YER)', 0.00510,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (148, 'ZMK', 'Zambian Kwacha (ZMK)', 0.00031, 'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (149, 'ZWD', 'Zimbabwe Dollar (ZWD)', 0.00001,  'USD');
INSERT INTO cc_currencies (id, currency, name, value, basecurrency) VALUES (150, 'GYD', 'Guyana Dollar (GYD)', 0.00527,  'USD');



--
-- Backup Database
--

CREATE TABLE cc_backup (
    id 								BIGINT NOT NULL AUTO_INCREMENT ,
    name 							VARCHAR( 255 ) NOT NULL ,
    path 							VARCHAR( 255 ) NOT NULL ,
    creationdate 					TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
    PRIMARY KEY ( id ) ,
    UNIQUE cons_cc_backup_name(name)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;





-- 
-- E-Commerce Table
--


CREATE TABLE cc_ecommerce_product (
    id 										BIGINT NOT NULL AUTO_INCREMENT,
    product_name 							VARCHAR(255) NOT NULL,	
    creationdate 							TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    description 							MEDIUMTEXT,
    expirationdate 							TIMESTAMP,
    enableexpire 							INT DEFAULT 0,
    expiredays 								INT DEFAULT 0,
    mailtype 								VARCHAR(50) NOT NULL,
    credit 									FLOAT DEFAULT 0 NOT NULL,
    tariff 									INT DEFAULT 0,
    id_didgroup 							INT DEFAULT 0,
    activated 								CHAR(1) DEFAULT 'f' NOT NULL,
    simultaccess 							INT DEFAULT 0,
    currency 								CHAR(3) DEFAULT 'USD',
    typepaid 								INT DEFAULT 0,
    creditlimit 							INT DEFAULT 0,
    language 								CHAR(5) DEFAULT 'en',
    runservice 								INT DEFAULT 0,
    sip_friend 								INT DEFAULT 0,
    iax_friend 								INT DEFAULT 0,
    PRIMARY KEY ( id )
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;



-- 
-- Speed Dial Table
--

CREATE TABLE cc_speeddial (
    id 										BIGINT NOT NULL AUTO_INCREMENT,
    id_cc_card 								BIGINT NOT NULL DEFAULT 0,
    phone 									VARCHAR(100) NOT NULL,	
    name 									VARCHAR(100) NOT NULL,	
    speeddial 								INT DEFAULT 0,
    creationdate 							TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY ( id ),
    UNIQUE cons_cc_speeddial_id_cc_card_speeddial (id_cc_card, speeddial)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;



-- Auto Refill Report Table	
CREATE TABLE cc_autorefill_report (
	id 										BIGINT NOT NULL AUTO_INCREMENT,    
	daterun 								TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
	totalcardperform 						INT ,
	totalcredit 							DECIMAL(15,5),
	PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;








-- cc_prefix Table	

CREATE TABLE cc_prefix (
	id 											BIGINT NOT NULL AUTO_INCREMENT,
	prefixe 									VARCHAR(50) NOT NULL,
	destination 								VARCHAR(100) NOT NULL,
	id_cc_country 								BIGINT ,
	PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;


INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Afghanistan','93','1');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Albania','355','2');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Algeria','213','3');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('American Samoa','684','4');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Andorra','376','5');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Angola','244','6');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Anguilla','1264','7');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Antarctica','672','8');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Antigua','1268',9);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Argentina','54','10');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Armenia','374','11');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Aruba','297','12');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Ascension','247',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Australia','61','13');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Australian External Territories','672','13');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Austria','43','14');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Azerbaijan','994','15');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Bahamas','1242','16');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Bahrain','973','17');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Bangladesh','880','18');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Barbados','1246','19');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Barbuda','1268',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Belarus','375','20');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Belgium','32','21');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Belize','501','22');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Benin','229','23');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Bermuda','1441','24');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Bhutan','975','25');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Bolivia','591','26');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Bosnia & Herzegovina','387','27');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Botswana','267','28');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Brazil','55','30');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Brasil Telecom','5514','30');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Brazil Telefonica','5515','30');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Brazil Embratel','5521','30');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Brazil Intelig','5523','30');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Brazil Telemar','5531','30');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Brazil mobile phones','550','30');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('British Virgin Islands','1284','31');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Brunei Darussalam','673','32');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Bulgaria','359','33');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Burkina Faso','226','34');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Burundi','257','35');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Cambodia','855','36');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Cameroon','237','37');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Canada','1','38');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Cape Verde Islands','238','39');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Cayman Islands','1345','40');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Central African Republic','236','41');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Chad','235','42');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Chatham Island (New Zealand)','64',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Chile','56','43');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('China (PRC)','86','44');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Christmas Island','618','45');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Cocos-Keeling Islands','61','46');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Colombia','57','47');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Colombia Mobile Phones','573','47');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Colombia Orbitel','575','47');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Colombia ETB','577','47');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Colombia Telecom','579','47');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Comoros','269','48');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Congo','242','49');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Congo, Dem. Rep. of  (former Zaire)','243',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Cook Islands','682','51');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Costa Rica','506','52');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Côte d''Ivoire (Ivory Coast)','225','53');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Croatia','385','54');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Cuba','53','55');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Cuba (Guantanamo Bay)','5399','55');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Curaâo','599',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Cyprus','357','56');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Czech Republic','420','57');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Denmark','45','58');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Diego Garcia','246','241');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Djibouti','253','59');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Dominica','1767','60');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Dominican Republic','1809','61');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('East Timor','670','211');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Easter Island','56',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Ecuador','593','62');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Egypt','20','63');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('El Salvador','503','64');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Ellipso (Mobile Satellite service)','8812',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('EMSAT (Mobile Satellite service)','88213',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Equatorial Guinea','240','65');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Eritrea','291','66');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Estonia','372','67');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Ethiopia','251','68');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Falkland Islands (Malvinas)','500','69');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Faroe Islands','298','70');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Fiji Islands','679','71');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Finland','358','72');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('France','33','73');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('French Antilles','596','74');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('French Guiana','594','75');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('French Polynesia','689','76');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Gabonese Republic','241','77');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Gambia','220','78');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Georgia','995','79');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Germany','49','80');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Ghana','233','81');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Gibraltar','350','82');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Global Mobile Satellite System (GMSS)','881',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('ICO Global','8810-8811',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Ellipso','8812-8813',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Iridium','8816-8817',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Globalstar','8818-8819',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Globalstar (Mobile Satellite Service)','8818-8819',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Greece','30','83');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Greenland','299','84');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Grenada','1473','85');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Guadeloupe','590','86');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Guam','1671','87');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Guantanamo Bay','5399',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Guatemala','502','88');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Guinea-Bissau','245','90');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Guinea','224','89');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Guyana','592','91');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Haiti','509','92');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Honduras','504','95');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Hong Kong','852','96');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Hungary','36','97');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('ICO Global (Mobile Satellite Service)','8810-8811',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Iceland','354','98');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('India','91','99');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Indonesia','62','100');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Inmarsat (Atlantic Ocean - East)','871','242');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Inmarsat (Atlantic Ocean - West)','874','242');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Inmarsat (Indian Ocean)','873','242');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Inmarsat (Pacific Ocean)','872','242');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Inmarsat SNAC','870','242');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('International Freephone Service','800',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('International Shared Cost Service (ISCS)','808',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Iran','98','101');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Iraq','964','102');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Ireland','353','103');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Iridium (Mobile Satellite service)','8816-8817',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Israel','972','104');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Italy','39','105');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Jamaica','1876','106');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Japan','81','107');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Jordan','962','108');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Kazakhstan','7','109');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Kenya','254','110');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Kiribati','686','111');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Korea (North)','850','112');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Korea (South)','82','113');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Kuwait','965','114');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Kyrgyz Republic','996','115');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Laos','856','116');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Latvia','371','117');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Lebanon','961','118');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Lesotho','266','119');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Liberia','231','120');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Libya','218','121');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Liechtenstein','423','122');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Lithuania','370','123');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Luxembourg','352','124');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Macao','853','125');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Macedonia (Former Yugoslav Rep of.)','389','126');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Madagascar','261','127');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Malawi','265','128');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Malaysia','60','129');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Maldives','960','130');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Mali Republic','223','131');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Malta','356','132');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Marshall Islands','692','133');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Martinique','596','134');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Mauritania','222','135');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Mauritius','230','136');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Mayotte Island','269','137');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Mexico','52','138');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Micronesia, (Federal States of)','691','139');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Midway Island','1808',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Moldova','373','140');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Monaco','377','141');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Mongolia','976','142');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Montserrat','1664','143');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Morocco','212','144');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Mozambique','258','145');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Myanmar','95','146');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Namibia','264','147');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Nauru','674','148');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Nepal','977','149');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Netherlands','31','150');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Netherlands Antilles','599','151');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Nevis','1869',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('New Caledonia','687','152');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('New Zealand','64','153');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Nicaragua','505','154');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Niger','227','155');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Nigeria','234','156');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Niue','683','157');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Norfolk Island','672','158');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Northern Marianas Islands(Saipan, Rota, & Tinian)','1670','159');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Norway','47','160');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Oman','968','161');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Pakistan','92','162');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Palau','680','163');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Palestinian Settlements','970','164');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Panama','507','165');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Papua New Guinea','675','166');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Paraguay','595','167');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Peru','51','168');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Philippines','63','169');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Poland','48','171');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Portugal','351','172');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Puerto Rico','1787','173');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Qatar','974','174');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Réunion Island','262','175');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Romania','40','176');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Russia','7','177');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Rwandese Republic','250','178');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('St. Helena','290','179');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('St. Kitts/Nevis','1869','180');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('St. Lucia','1758','181');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('St. Pierre & Miquelon','508','182');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('St. Vincent & Grenadines','1784','183');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('San Marino','378','185');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('São Tomé and Principe','239','186');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Saudi Arabia','966','187');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Senegal','221','188');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Serbia and Montenegro','381',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Seychelles Republic','248','189');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Sierra Leone','232','190');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Singapore','65','191');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Slovak Republic','421','192');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Slovenia','386','193');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Solomon Islands','677','194');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Somali Democratic Republic','252','195');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('South Africa','27','196');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Spain','34','198');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Sri Lanka','94','199');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Sudan','249','200');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Suriname','597','201');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Swaziland','268','203');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Sweden','46','204');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Switzerland','41','205');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Syria','963','206');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Taiwan','886','207');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Tajikistan','992','208');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Tanzania','255','209');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Thailand','66','210');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Thuraya (Mobile Satellite service)','88216',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Togolese Republic','228','212');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Tokelau','690','213');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Tonga Islands','676','214');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Trinidad & Tobago','1868','215');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Tunisia','216','216');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Turkey','90','217');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Turkmenistan','993','218');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Turks and Caicos Islands','1649','219');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Tuvalu','688','220');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Uganda','256','221');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Ukraine','380','222');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('United Arab Emirates','971','223');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('United Kingdom','44','224');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('United States of America','1','225');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('US Virgin Islands','1340','225');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Universal Personal Telecommunications (UPT)','878',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Uruguay','598','227');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Uzbekistan','998','228');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Vanuatu','678','229');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Vatican City','39',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Venezuela','58','230');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Venezuela Etelix','58102','230');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Venezuela http://www.multiphone.net.ve','58107','230');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Venezuela CANTV','58110','230');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Venezuela Convergence Comunications','58111','230');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Venezuela Telcel, C.A.','58114','230');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Venezuela Totalcom Venezuela','58119','230');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Venezuela Orbitel de Venezuela, C.A. ENTEL Venezuela','58123','230');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Venezuela LD Telecomunicaciones, C.A.','58150','230');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Venezuela Telecomunicaciones NGTV','58133','230');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Venezuela Veninfotel Comunicaciones','58199','230');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Vietnam','84','231');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Wake Island','808',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Wallis and Futuna Islands','681',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Western Samoa','685','184');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Yemen','967','236');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Zambia','260','238');
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Zanzibar','255',NULL);
INSERT INTO cc_prefix (destination,prefixe,id_cc_country) VALUES ('Zimbabwe','263','239');



CREATE TABLE cc_alarm (
    id 										BIGINT NOT NULL AUTO_INCREMENT,
    name 									TEXT NOT NULL,
    periode 								INT NOT NULL DEFAULT 1,
    type 									INT NOT NULL DEFAULT 1,
    maxvalue 								FLOAT NOT NULL,
    minvalue 								FLOAT NOT NULL DEFAULT -1,
    id_trunk 								INT ,
    status 									INT NOT NULL DEFAULT 0,
    numberofrun 							INT NOT NULL DEFAULT 0,
    numberofalarm 							INT NOT NULL DEFAULT 0,   
	datecreate    							TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,	
	datelastrun    							TIMESTAMP,
    emailreport 							VARCHAR(50),
    PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;


 CREATE TABLE cc_alarm_report (
    id 										BIGINT NOT NULL AUTO_INCREMENT,
    cc_alarm_id 							BIGINT NOT NULL,
    calculatedvalue 						FLOAT NOT NULL,
    daterun 								TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;




CREATE TABLE cc_callback_spool (
    id 								BIGINT NOT NULL AUTO_INCREMENT,
    uniqueid 						VARCHAR(40),
    entry_time 						TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status 							VARCHAR(80),
    server_ip 						VARCHAR(40),
    num_attempt 					INT NOT NULL DEFAULT 0,
    last_attempt_time 				TIMESTAMP,
    manager_result 					VARCHAR(60),
    agi_result 						VARCHAR(60),
    callback_time 					TIMESTAMP,
    channel 						VARCHAR(60),
    exten 							VARCHAR(60),
    context 						VARCHAR(60),
    priority 						VARCHAR(60),
    application 					VARCHAR(60),
    data 							VARCHAR(60),
    timeout 						VARCHAR(60),
    callerid 						VARCHAR(60),
    variable 						VARCHAR(100),
    account 						VARCHAR(60),
    async 							VARCHAR(60),
    actionid 						VARCHAR(60),
	id_server						INT,
	id_server_group					INT,
    PRIMARY KEY (id),
    UNIQUE cc_callback_spool_uniqueid_key (uniqueid)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;

CREATE TABLE cc_server_manager (
    id 								BIGINT NOT NULL AUTO_INCREMENT,
	id_group						INT DEFAULT 1,
    server_ip 						VARCHAR(40),
    manager_host 					VARCHAR(50),
    manager_username 				VARCHAR(50),
    manager_secret 					VARCHAR(50),
	lasttime_used		 			TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;

INSERT INTO cc_server_manager (id_group, server_ip, manager_host, manager_username, manager_secret) VALUES (1, 'localhost', 'localhost', 'myasterisk', 'mycode');


CREATE TABLE cc_server_group (
	id 								BIGINT NOT NULL AUTO_INCREMENT,
	name 							VARCHAR(60),
	description						MEDIUMTEXT,
    PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;
INSERT INTO cc_server_group (id, name, description) VALUES (1, 'default', 'default group of server');




CREATE TABLE cc_invoices (
    id 										INT NOT NULL AUTO_INCREMENT,    
    cardid 									bigINT NOT NULL,
	orderref 								VARCHAR(50),
    invoicecreated_date 					TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	cover_startdate 						TIMESTAMP,
    cover_enddate 							TIMESTAMP,	
    amount 									DECIMAL(15,5) DEFAULT 0,
	tax 									DECIMAL(15,5) DEFAULT 0,
	total 									DECIMAL(15,5) DEFAULT 0,
	invoicetype 							INT ,
	filename 								VARCHAR(250),
	payment_date		 					TIMESTAMP,
	payment_status							INT DEFAULT 0,
    PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;
CREATE INDEX ind_cc_invoices ON cc_invoices (cover_startdate);


CREATE TABLE cc_invoice_history (
    id 										INT NOT NULL AUTO_INCREMENT,    
    invoiceid 								INT NOT NULL,	
    invoicesent_date 						TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    invoicestatus 							INT ,    
    PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;
CREATE INDEX ind_cc_invoice_history ON cc_invoice_history (invoicesent_date);





CREATE TABLE cc_package_offer (
    id 					BIGINT NOT NULL AUTO_INCREMENT,
    creationdate 		TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    label 				VARCHAR(70) NOT NULL,
    packagetype 		INT NOT NULL,
	billingtype 		INT NOT NULL,
	startday 			INT NOT NULL,
	freetimetocall 		INT NOT NULL,
    PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;
-- packagetype : Free minute + Unlimited ; Free minute ; Unlimited ; Normal
-- billingtype : Monthly ; Weekly 
-- startday : according to billingtype ; if monthly value 1-31 ; if Weekly value 1-7 (Monday to Sunday) 


CREATE TABLE cc_card_package_offer (
    id 					BIGINT NOT NULL AUTO_INCREMENT,
	id_cc_card 			BIGINT NOT NULL,
	id_cc_package_offer BIGINT NOT NULL,
    date_consumption 	TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	used_secondes 		BIGINT NOT NULL,
    PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;
CREATE INDEX ind_cc_card_package_offer_id_card 			ON cc_card_package_offer (id_cc_card);
CREATE INDEX ind_cc_card_package_offer_id_package_offer ON cc_card_package_offer (id_cc_package_offer);
CREATE INDEX ind_cc_card_package_offer_date_consumption ON cc_card_package_offer (date_consumption);

CREATE TABLE cc_subscription_fee (
    id 								BIGINT NOT NULL AUTO_INCREMENT,
    label 							TEXT NOT NULL,
    fee 							FLOAT DEFAULT 0 NOT NULL,
	currency 						CHAR(3) DEFAULT 'USD',
    `status` 							INT DEFAULT '0' NOT NULL,
    numberofrun 					INT DEFAULT '0' NOT NULL,
    datecreate 						TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    datelastrun 					TIMESTAMP,
    emailreport 					TEXT,
    totalcredit 					FLOAT NOT NULL DEFAULT 0,
    totalcardperform 				INT DEFAULT '0' NOT NULL,
    PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;

-- ## 	INSTEAD USE CC_CHARGE  ##
-- CREATE TABLE cc_subscription_fee_card (
--     id 						BIGINT NOT NULL AUTO_INCREMENT,
--     id_cc_card 				BIGINT NOT NULL,
--     id_cc_subscription_fee 	BIGINT NOT NULL,
--     datefee 				TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
--     fee 					FLOAT DEFAULT 0 NOT NULL,	
-- 	fee_converted 			FLOAT DEFAULT 0 NOT NULL,
-- 	currency 				CHAR(3) DEFAULT 'USD',
--     PRIMARY KEY (id)
-- )ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;
-- 
-- CREATE INDEX ind_cc_subscription_fee_card_id_cc_card  				ON cc_subscription_fee_card (id_cc_card);
-- CREATE INDEX ind_cc_subscription_fee_card_id_cc_subscription_fee 	ON cc_subscription_fee_card (id_cc_subscription_fee);
-- CREATE INDEX ind_cc_subscription_fee_card_datefee 					ON cc_subscription_fee_card (datefee);


-- Table Name: cc_outbound_cid_group
-- For outbound CID Group
-- group_name: Name of the Group Created.

CREATE TABLE cc_outbound_cid_group (
    id 					INT NOT NULL AUTO_INCREMENT,
    creationdate 		TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    group_name 			VARCHAR(70) NOT NULL,    
    PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;


-- Table Name: cc_outbound_cid_list
-- For outbound CIDs 
-- outbound_cid_group: Foreign Key of the CID Group
-- cid: Caller ID
-- activated Field for Activated or Disabled t=activated.

CREATE TABLE cc_outbound_cid_list (
    id 					INT NOT NULL AUTO_INCREMENT,
	outbound_cid_group	INT NOT NULL,
	cid					CHAR(100) NULL,    
    activated 			INT	NOT NULL DEFAULT 0,
    creationdate 		TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,    
    PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;







-- Payment Methods Table
CREATE TABLE cc_payment_methods (
    id 						INT NOT NULL AUTO_INCREMENT,
    payment_method 				CHAR(100) NOT NULL,
    payment_filename 				CHAR(200) NOT NULL,
    active 						CHAR(1) DEFAULT 'f' NOT NULL,
    PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;

INSERT INTO cc_payment_methods (payment_method,payment_filename,active) VALUES ('paypal','paypal.php','t');
INSERT INTO cc_payment_methods (payment_method,payment_filename,active) VALUES ('Authorize.Net','authorizenet.php','t');
INSERT INTO cc_payment_methods (payment_method,payment_filename,active) VALUES ('MoneyBookers','moneybookers.php','t');


CREATE TABLE cc_payments (
  id 							INT NOT NULL AUTO_INCREMENT,
  customers_id 					VARCHAR(60) NOT NULL,
  customers_name 				VARCHAR(200) NOT NULL,
  customers_email_address 		VARCHAR(96) NOT NULL,
  item_name 					VARCHAR(127),
  item_id 						VARCHAR(127),
  item_quantity 				INT NOT NULL DEFAULT 0,
  payment_method 				VARCHAR(32) NOT NULL,
  cc_type 						VARCHAR(20),
  cc_owner 						VARCHAR(64),
  cc_number 					VARCHAR(32),
  cc_expires 					VARCHAR(4),
  orders_status 				INT (5) NOT NULL,
  orders_amount 				DECIMAL(14,6),
  last_modified 				DATETIME,
  date_purchased 				DATETIME,
  orders_date_finished 			DATETIME,
  currency 						CHAR(3),
  currency_value 				DECIMAL(14,6),
  PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;

-- Payment Status Lookup Table
CREATE TABLE cc_payments_status (
  id 							INT NOT NULL AUTO_INCREMENT,
  status_id 					INT NOT NULL,
  status_name 					VARCHAR(200) NOT NULL,
  PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;

INSERT INTO cc_payments_status (status_id,status_name) VALUES (-2, 'Failed');
INSERT INTO cc_payments_status (status_id,status_name) VALUES (-1, 'Denied');
INSERT INTO cc_payments_status (status_id,status_name) VALUES (0, 'Pending');
INSERT INTO cc_payments_status (status_id,status_name) VALUES (1, 'In-Progress');
INSERT INTO cc_payments_status (status_id,status_name) VALUES (2, 'Completed');
INSERT INTO cc_payments_status (status_id,status_name) VALUES (3, 'Processed');
INSERT INTO cc_payments_status (status_id,status_name) VALUES (4, 'Refunded');
INSERT INTO cc_payments_status (status_id,status_name) VALUES (5, 'Unknown');


CREATE TABLE cc_configuration (
  configuration_id 					INT NOT NULL AUTO_INCREMENT,
  configuration_title 				VARCHAR(64) NOT NULL,
  configuration_key 				VARCHAR(64) NOT NULL,
  configuration_value 				VARCHAR(255) NOT NULL,
  configuration_description 		VARCHAR(255) NOT NULL,
  configuration_type 				INT NOT NULL DEFAULT 0,
  use_function 						VARCHAR(255) NULL,
  set_function 						VARCHAR(255) NULL,
  PRIMARY KEY (configuration_id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;

INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description) VALUES ('Login Username', 'MODULE_PAYMENT_AUTHORIZENET_LOGIN', 'testing', 'The login username used for the Authorize.net service');
INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description) VALUES ('Transaction Key', 'MODULE_PAYMENT_AUTHORIZENET_TXNKEY', 'Test', 'Transaction Key used for encrypting TP data');
INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description, set_function) VALUES ('Transaction Mode', 'MODULE_PAYMENT_AUTHORIZENET_TESTMODE', 'Test', 'Transaction mode used for processing orders', 'tep_cfg_select_option(array(\'Test\', \'Production\'), ');
INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description, set_function) VALUES ('Transaction Method', 'MODULE_PAYMENT_AUTHORIZENET_METHOD', 'Credit Card', 'Transaction method used for processing orders', 'tep_cfg_select_option(array(\'Credit Card\', \'eCheck\'), ');
INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description, set_function) VALUES ('Customer Notifications', 'MODULE_PAYMENT_AUTHORIZENET_EMAIL_CUSTOMER', 'False', 'Should Authorize.Net e-mail a receipt to the customer?', 'tep_cfg_select_option(array(\'True\', \'False\'), ');
INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description, set_function) VALUES ('Enable Authorize.net Module', 'MODULE_PAYMENT_AUTHORIZENET_STATUS', 'True', 'Do you want to accept Authorize.net payments?', 'tep_cfg_select_option(array(\'True\', \'False\'), ');

INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description, set_function) VALUES ('Enable PayPal Module', 'MODULE_PAYMENT_PAYPAL_STATUS', 'True', 'Do you want to accept PayPal payments?','tep_cfg_select_option(array(\'True\', \'False\'), ');
INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description) VALUES ('E-Mail Address', 'MODULE_PAYMENT_PAYPAL_ID', 'you@yourbusiness.com', 'The e-mail address to use for the PayPal service');
INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description, set_function) VALUES ('Transaction Currency', 'MODULE_PAYMENT_PAYPAL_CURRENCY', 'Selected Currency', 'The currency to use for credit card transactions', 'tep_cfg_select_option(array(\'Selected Currency\',\'USD\',\'CAD\',\'EUR\',\'GBP\',\'JPY\'), ');

INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description) VALUES ('E-Mail Address', 'MODULE_PAYMENT_MONEYBOOKERS_ID', 'you@yourbusiness.com', 'The eMail address to use for the moneybookers service');
INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description) VALUES ('Referral ID', 'MODULE_PAYMENT_MONEYBOOKERS_REFID', '989999', 'Your personal Referral ID from moneybookers.com');
INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description, set_function) VALUES ('Transaction Currency', 'MODULE_PAYMENT_MONEYBOOKERS_CURRENCY', 'Selected Currency', 'The default currency for the payment transactions', 'tep_cfg_select_option(array(\'Selected Currency\',\'EUR\', \'USD\', \'GBP\', \'HKD\', \'SGD\', \'JPY\', \'CAD\', \'AUD\', \'CHF\', \'DKK\', \'SEK\', \'NOK\', \'ILS\', \'MYR\', \'NZD\', \'TWD\', \'THB\', \'CZK\', \'HUF\', \'SKK\', \'ISK\', \'INR\'), ');
INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description, set_function) VALUES ('Transaction Language', 'MODULE_PAYMENT_MONEYBOOKERS_LANGUAGE', 'Selected Language', 'The default language for the payment transactions', 'tep_cfg_select_option(array(\'Selected Language\',\'EN\', \'DE\', \'ES\', \'FR\'), ');
INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description, set_function) VALUES ('Enable moneybookers Module', 'MODULE_PAYMENT_MONEYBOOKERS_STATUS', 'True', 'Do you want to accept moneybookers payments?','tep_cfg_select_option(array(\'True\', \'False\'), ');

CREATE TABLE cc_epayment_log (
    id 								INT NOT NULL AUTO_INCREMENT,
    cardid 							INT DEFAULT 0 NOT NULL,
    amount 							FLOAT DEFAULT 0 NOT NULL,
	vat 							FLOAT DEFAULT 0 NOT NULL,
    paymentmethod	 				CHAR(50) NOT NULL,     
  	cc_owner 						VARCHAR(64),
  	cc_number 						VARCHAR(32),
  	cc_expires 						VARCHAR(7),						   
    creationdate  					TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status 							INT DEFAULT 0 NOT NULL,
    PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;


CREATE TABLE cc_system_log (
    id 								INT NOT NULL AUTO_INCREMENT,
    iduser 							INT DEFAULT 0 NOT NULL,
    loglevel	 					INT DEFAULT 0 NOT NULL,
    action			 				TEXT NOT NULL,
    description						MEDIUMTEXT,    
    data			 				BLOB,
	tablename						VARCHAR(255),
	pagename			 			VARCHAR(255),
	ipaddress						VARCHAR(255),	
    creationdate  					TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
)ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE utf8_bin;

/*
*/


/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,   
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 * 
 * @copyright   Copyright (C) 2004-2009 - Star2billing S.L. 
 * @author      Belaid Arezqui <areski@gmail.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 *
 * Software License Agreement (GNU Affero General Public License)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
**/

use mya2billing;
ALTER TABLE cc_callback_spool CHANGE variable variable VARCHAR( 300 ) DEFAULT NULL;

-- fix various uses of ISO-3166-1 alpha-2 rather than alpha-3
UPDATE cc_country SET countrycode='BVT' WHERE countrycode='BV';
UPDATE cc_country SET countrycode='IOT' WHERE countrycode='IO';
UPDATE cc_country SET countrycode='HMD' WHERE countrycode='HM';
UPDATE cc_country SET countrycode='PCN' WHERE countrycode='PN';
UPDATE cc_country SET countrycode='SGS' WHERE countrycode='GS';
UPDATE cc_country SET countrycode='SJM' WHERE countrycode='SJ';
UPDATE cc_country SET countrycode='TLS' WHERE countrycode='TL';
UPDATE cc_country SET countrycode='UMI' WHERE countrycode='UM';
UPDATE cc_country SET countrycode='ESH' WHERE countrycode='EH';

-- integrate changes from ISO-3166-1 newsletters V-1 to V-12
UPDATE cc_country SET countryname='Lao People''s Democratic Republic' WHERE countrycode='LAO';
UPDATE cc_country SET countryname='Timor-Leste', countryprefix='670' WHERE countrycode='TLS';
UPDATE cc_country SET countryprefix='0' WHERE countrycode='TMP';

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,   
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 * 
 * @copyright   Copyright (C) 2004-2009 - Star2billing S.L. 
 * @author      Belaid Arezqui <areski@gmail.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 *
 * Software License Agreement (GNU Affero General Public License)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
**/


-- Never too late to add some indexes :D

ALTER TABLE cc_call ADD INDEX ( username );
ALTER TABLE cc_call ADD INDEX ( starttime );
ALTER TABLE cc_call ADD INDEX ( terminatecause );
ALTER TABLE cc_call ADD INDEX ( calledstation );


ALTER TABLE cc_card ADD INDEX ( creationdate );
ALTER TABLE cc_card ADD INDEX ( username );



OPTIMIZE TABLE cc_card;
OPTIMIZE TABLE cc_call;

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,   
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 * 
 * @copyright   Copyright (C) 2004-2009 - Star2billing S.L. 
 * @author      Belaid Arezqui <areski@gmail.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 *
 * Software License Agreement (GNU Affero General Public License)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
**/

--
-- A2Billing database script - Update database for MYSQL 5.X
-- 
-- 
-- Usage:
-- mysql -u root -p"root password" < UPDATE-a2billing-v1.3.0-to-v1.4.0.sql
--




CREATE TABLE cc_invoice_items (
	id bigint(20) NOT NULL auto_increment,
	invoiceid int(11) NOT NULL,
	invoicesection text,
	designation text,
	sub_designation text,
	start_date date default NULL,
	end_date date default NULL,
	bill_date date default NULL,
	calltime int(11) default NULL,
	nbcalls int(11) default NULL,
	quantity int(11) default NULL,
	price decimal(15,5) default NULL,
	buy_price decimal(15,5) default NULL,
	PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE cc_invoice (
	id int(11) NOT NULL auto_increment,
	cardid bigint(20) NOT NULL,
	invoicecreated_date timestamp NOT NULL default CURRENT_TIMESTAMP,
	amount decimal(15,5) default '0.00000',
	tax decimal(15,5) default '0.00000',
	total decimal(15,5) default '0.00000',
	filename varchar(250) collate utf8_bin default NULL,
	payment_status int(11) default '0',
	cover_call_startdate timestamp NOT NULL default '0000-00-00 00:00:00',
	cover_call_enddate timestamp NOT NULL default '0000-00-00 00:00:00',
	cover_charge_startdate timestamp NOT NULL default '0000-00-00 00:00:00',
	cover_charge_enddate timestamp NOT NULL default '0000-00-00 00:00:00',
	currency varchar(3) collate utf8_bin default NULL,
	previous_balance decimal(15,5) default NULL,
	current_balance decimal(15,5) default NULL,
	templatefile varchar(250) collate utf8_bin default NULL,
	username char(50) collate utf8_bin default NULL,
	lastname char(50) collate utf8_bin default NULL,
	firstname char(50) collate utf8_bin default NULL,
	address char(100) collate utf8_bin default NULL,
	city char(40) collate utf8_bin default NULL,
	state char(40) collate utf8_bin default NULL,
	country char(40) collate utf8_bin default NULL,
	zipcode char(20) collate utf8_bin default NULL,
	phone char(20) collate utf8_bin default NULL,
	email char(70) collate utf8_bin default NULL,
	fax char(20) collate utf8_bin default NULL,
	vat float default NULL,
	PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE cc_charge DROP COLUMN id_cc_subscription_fee;

ALTER TABLE cc_charge ADD COLUMN id_cc_card_subscription BIGINT;
ALTER TABLE cc_charge ADD COLUMN cover_from DATE;
ALTER TABLE cc_charge ADD COLUMN cover_to 	DATE;

ALTER TABLE cc_trunk ADD COLUMN inuse INT DEFAULT 0;
ALTER TABLE cc_trunk ADD COLUMN maxuse INT DEFAULT -1;
ALTER TABLE cc_trunk ADD COLUMN status INT DEFAULT 1;
ALTER TABLE cc_trunk ADD COLUMN if_max_use INT DEFAULT 0;


CREATE TABLE cc_card_subscription (
	id BIGINT NOT NULL AUTO_INCREMENT,
	id_cc_card BIGINT ,
	id_subscription_fee INT,
	startdate TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
	stopdate TIMESTAMP,
	product_id VARCHAR( 100 ),
	product_name VARCHAR( 100 ),
	PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


ALTER TABLE cc_card DROP id_subscription_fee;
ALTER TABLE cc_card ADD COLUMN id_timezone INT DEFAULT 0;


CREATE TABLE cc_config_group (
	id 								INT NOT NULL auto_increment,
	group_title 					VARCHAR(64) NOT NULL,
	group_description 				VARCHAR(255) NOT NULL,
	PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO cc_config_group (group_title, group_description) VALUES ('global', 'This configuration group handles the global settings for application.');
INSERT INTO cc_config_group (group_title, group_description) VALUES ('callback', 'This configuration group handles calllback settings.');
INSERT INTO cc_config_group (group_title, group_description) VALUES ('webcustomerui', 'This configuration group handles Web Customer User Interface.');
INSERT INTO cc_config_group (group_title, group_description) VALUES ('sip-iax-info', 'SIP & IAX client configuration information.');
INSERT INTO cc_config_group (group_title, group_description) VALUES ('epayment_method', 'Epayment Methods Configuration.');
INSERT INTO cc_config_group (group_title, group_description) VALUES ('signup', 'This configuration group handles the signup related settings.');
INSERT INTO cc_config_group (group_title, group_description) VALUES ('backup', 'This configuration group handles the backup/restore related settings.');
INSERT INTO cc_config_group (group_title, group_description) VALUES ('webui', 'This configuration group handles the WEBUI and API Configuration.');
INSERT INTO cc_config_group (group_title, group_description) VALUES ('peer_friend', 'This configuration group define parameters for the friends creation.');
INSERT INTO cc_config_group (group_title, group_description) VALUES ('log-files', 'This configuration group handles the Log Files Directory Paths.');
INSERT INTO cc_config_group (group_title, group_description) VALUES ('agi-conf1', 'This configuration group handles the AGI Configuration.');



CREATE TABLE cc_config (
	id 								INT NOT NULL auto_increment,
	config_title		 			VARCHAR( 100 )  NOT NULL,
	config_key 						VARCHAR( 100 )  NOT NULL,
	config_value 					VARCHAR( 100 )  NOT NULL,
	config_description 				TEXT NOT NULL,
	config_valuetype				INT NOT NULL DEFAULT 0,
	config_group_id 				INT NOT NULL,
	config_listvalues				VARCHAR( 100 ) ,
	PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Card Number length', 'interval_len_cardnumber', '10-15', 'Card Number length, You can define a Range e.g: 10-15.', 0, 1, '10-15,5-20,10-30');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Card Alias length', 'len_aliasnumber', '15', 'Card Number Alias Length e.g: 15.', 0, 1, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Voucher length', 'len_voucher', '15', 'Voucher Number Length.', 0, 1, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Base Currency', 'base_currency', 'usd', 'Base Currency to use for application.', 0, 1, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Invoice Image', 'invoice_image', 'asterisk01.jpg', 'Image to Display on the Top of Invoice', 0, 1, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Admin Email', 'admin_email', 'root@localhost', 'Web Administrator Email Address.', 0, 1, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('DID Bill Payment Day', 'didbilling_daytopay', '5', 'DID Bill Payment Day of Month', 0, 1, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Manager Host', 'manager_host', 'localhost', 'Manager Host Address', 0, 1, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Manager User ID', 'manager_username', 'myasterisk', 'Manger Host User Name', 0, 1, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Manager Password', 'manager_secret', 'mycode', 'Manager Host Password', 0, 1, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Use SMTP Server', 'smtp_server', '0', 'Define if you want to use an STMP server or Send Mail (value yes for server SMTP)', 1, 1, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('SMTP Host', 'smtp_host', 'localhost', 'SMTP Hostname', 0, 1, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('SMTP UserName', 'smtp_username', '', 'User Name to connect on the SMTP server', 0, 1, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('SMTP Password', 'smtp_password', '', 'Password to connect on the SMTP server', 0, 1, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Use Realtime', 'use_realtime', '1', 'if Disabled, it will generate the config files and offer an option to reload asterisk after an update on the Voip settings', 1, 1, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Go To Customer', 'customer_ui_url', '../../customer/index.php', 'Link to the customer account', 0, 1, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Context Callback', 'context_callback', 'a2billing-callback', 'Contaxt to use in Callback', 0, 2, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Extension', 'extension', '1000', 'Extension to call while callback.', 0, 2, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Wait before callback', 'sec_wait_before_callback', '10', 'Seconds to wait before callback.', 0, 2, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Avoid Repeat Duration', 'sec_avoid_repeate', '10', 'Number of seconds before the call-back can be re-initiated from the web page to prevent repeated and unwanted calls.', 0, 2, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Time out', 'timeout', '20', 'if the callback doesnt succeed within the value below, then the call is deemed to have failed.', 0, 2, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Answer on Call', 'answer_call', '1', 'if we want to manage the answer on the call. Disabling this for callback trigger numbers makes it ring not hang up.', 1, 2, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('No of Predictive Calls', 'nb_predictive_call', '10', 'number of calls an agent will do when the call button is clicked.', 0, 2, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Delay for Availability', 'nb_day_wait_before_retry', '1', 'Number of days to wait before the number becomes available to call again.', 0, 2, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('PD Contect', 'context_preditctivedialer', 'a2billing-predictivedialer', 'The context to redirect the call for the predictive dialer.', 0, 2, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Max Time to call', 'predictivedialer_maxtime_tocall', '5400', 'When a call is made we need to limit the call duration : amount in seconds.', 0, 2, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('PD Caller ID', 'callerid', '123456', 'Set the callerID for the predictive dialer and call-back.', 0, 2, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Callback CallPlan ID', 'all_callback_tariff', '1', 'ID Call Plan to use when you use the all-callback mode, check the ID in the "list Call Plan" - WebUI.', 0, 2, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Server Group ID', 'id_server_group', '1', 'Define the group of servers that are going to be used by the callback.', 0, 2, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Audio Intro', 'callback_audio_intro', 'prepaid-callback_intro', 'Audio intro message when the callback is initiate.', 0, 2, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Signup URL', 'signup_page_url', '', 'url of the signup page to show up on the sign in page (if empty no link will show up).', 0, 3, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Payment Method', 'paymentmethod', 1, 'Enable or disable the payment methods; yes for multi-payment or no for single payment method option.', 1, 3, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Personal Info', 'personalinfo', 1, 'Enable or disable the page which allow customer to modify its personal information.', 1, 3, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Payment Info', 'customerinfo', 1, 'Enable display of the payment interface - yes or no.', 1, 3, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('SIP/IAX Info', 'sipiaxinfo', 1, 'Enable display of the sip/iax info - yes or no.', 1, 3, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('CDR', 'cdr', 1, 'Enable the Call history - yes or no.', 1, 3, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Invoices', 'invoice', 1, 'Enable invoices - yes or no.', 1, 3, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Voucher Screen', 'voucher', 1, 'Enable the voucher screen - yes or no.', 1, 3, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Paypal', 'paypal', 1, 'Enable the paypal payment buttons - yes or no.', 1, 3, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Speed Dial', 'speeddial', 1, 'Allow Speed Dial capabilities - yes or no.', 1, 3, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('DID', 'did', 1, 'Enable the DID (Direct Inwards Dialling) interface - yes or no.', 1, 3, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('RateCard', 'ratecard', 1, 'Show the ratecards - yes or no.', 1, 3, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Simulator', 'simulator', 1, 'Offer simulator option on the customer interface - yes or no.', 1, 3, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('CallBack', 'callback', 1, 'Enable the callback option on the customer interface - yes or no.', 1, 3, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Predictive Dialer', 'predictivedialer', 1, 'Enable the predictivedialer option on the customer interface - yes or no.', 1, 3, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('WebPhone', 'webphone', 1, 'Let users use SIP/IAX Webphone (Options : yes/no).', 1, 3, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('WebPhone Server', 'webphoneserver', 'localhost', 'IP address or domain name of asterisk server that would be used by the web-phone.', 0, 3, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Caller ID', 'callerid', 1, 'Let the users add new callerid.', 1, 3, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Password', 'password', 1, 'Let the user change the webui password.', 1, 3, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('CallerID Limit', 'limit_callerid', '5', 'The total number of callerIDs for CLI Recognition that can be add by the customer.', 0, 3, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Trunk Name', 'sip_iax_info_trunkname', 'mytrunkname', 'Trunk Name to show in sip/iax info.', 0, 4, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Codecs Allowed', 'sip_iax_info_allowcodec', 'g729', 'Allowed Codec, ulaw, gsm, g729.', 0, 4, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Host', 'sip_iax_info_host', 'mydomainname.com', 'Host information.', 0, 4, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('IAX Parms', 'iax_additional_parameters', 'canreinvite = no', 'IAX Additional Parameters.', 0, 4, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('SIP Parms', 'sip_additional_parameters', 'trustrpid = yes | sendrpid = yes | canreinvite = no', 'SIP Additional Parameters.', 0, 4, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Enable', 'enable', 1, 'Enable/Disable.', 1, 5, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('HTTP Server Customer', 'http_server', 'http://www.mydomainname.com', 'Set the Server Address of Customer Website, It should be empty for productive Servers.', 0, 5, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('HTTPS Server Customer', 'https_server', 'https://www.mydomainname.com', 'https://localhost - Enter here your Secure Customers Server Address, should not be empty for productive servers.', 0, 5, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Server Customer IP/Domain', 'http_cookie_domain', '26.63.165.200', 'Enter your Domain Name or IP Address for the Customers application, eg, 26.63.165.200.', 0, 5, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Secure Server Customer IP/Domain', 'https_cookie_domain', '26.63.165.200', 'Enter your Secure server Domain Name or IP Address for the Customers application, eg, 26.63.165.200.', 0, 5, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Application Customer Path', 'http_cookie_path', '/customer/', 'Enter the Physical path of your Customers Application on your server.', 0, 5, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Secure Application Customer Path', 'https_cookie_path', '/customer/', 'Enter the Physical path of your Customers Application on your Secure Server.', 0, 5, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Application Customer Physical Path', 'dir_ws_http_catalog', '/customer/', 'Enter the Physical path of your Customers Application on your server.', 0, 5, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Secure Application Customer Physical Path', 'dir_ws_https_catalog', '/customer/', 'Enter the Physical path of your Customers Application on your Secure server.', 0, 5, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Enable SSL', 'enable_ssl', 1, 'secure webserver for checkout procedure?', 1, 5, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('HTTP Domain', 'http_domain', '26.63.165.200', 'Http Address.', 0, 5, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Directory Path', 'dir_ws_http', '/customer/', 'Directory Path.', 0, 5, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Payment Amount', 'purchase_amount', '1:2:5:10:20', 'define the different amount of purchase that would be available - 5 amount maximum (5:10:15).', 0, 5, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Item Name', 'item_name', 'Credit Purchase', 'Item name that would be display to the user when he will buy credit.', 0, 5, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Currency Code', 'currency_code', 'USD', 'Currency for the Credit purchase, only one can be define here.', 0, 5, NULL);
-- https://www.sandbox.paypal.com/cgi-bin/webscr
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Paypal Payment URL', 'paypal_payment_url', 'https://secure.paypal.com/cgi-bin/webscr', 'Define here the URL of paypal gateway the payment (to test with paypal sandbox).', 0, 5, NULL);
-- www.sandbox.paypal.com
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Paypal Verify URL', 'paypal_verify_url', 'ssl://www.paypal.com', 'paypal transaction verification url.', 0, 5, NULL);
-- https://test.authorize.net/gateway/transact.dll
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Authorize.NET Payment URL', 'authorize_payment_url', 'https://secure.authorize.net/gateway/transact.dll', 'Define here the URL of Authorize gateway.', 0, 5, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('PayPal Store Name', 'store_name', 'Asterisk2Billing', 'paypal store name to show in the paypal site when customer will go to pay.', 0, 5, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Transaction Key', 'transaction_key', 'asdf1212fasd121554sd4f5s45sdf', 'Transaction Key for security of Epayment Max length of 60 Characters.', 0, 5, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Secret Word', 'moneybookers_secretword', '', 'Moneybookers secret word.', 0, 5, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Enable', 'enable_signup', 1, 'Enable Signup Module.', 1, 6, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Captcha Security', 'enable_captcha', 1, 'enable Captcha on the signup module (value : YES or NO).', 1, 6, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Credit', 'credit', '0', 'amount of credit applied to a new user.', 0, 6, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('CallPlan ID List', 'callplan_id_list', '1,2', 'the list of id of call plans which will be shown in signup.', 0, 6, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Card Activation', 'activated', '0', 'Specify whether the card is created as Active or New.', 1, 6, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Access Type', 'simultaccess', '0', 'Simultaneous or non concurrent access with the card - 0 = INDIVIDUAL ACCESS or 1 = SIMULTANEOUS ACCESS.', 0, 6, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Paid Type', 'typepaid', '0', 'PREPAID CARD  =  0 - POSTPAY CARD  =  1.', 0, 6, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Credit Limit', 'creditlimit', '0', 'Define credit limit, which is only used for a POSTPAY card.', 0, 6, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Run Service', 'runservice', '0', 'Authorise the recurring service to apply on this card  -  Yes 1 - No 0.', 0, 6, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Enable Expire', 'enableexpire', '0', 'Enable the expiry of the card  -  Yes 1 - No 0.', 0, 6, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Date Format', 'expirationdate', '', 'Expiry Date format YYYY-MM-DD HH:MM:SS. For instance 2004-12-31 00:00:00', 0, 6, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Expire Limit', 'expiredays', '0', 'The number of days after which the card will expire.', 0, 6, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Create SIP', 'sip_account', 1, 'Create a sip account from signup ( default : yes ).', 1, 6, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Create IAX', 'iax_account', 1, 'Create an iax account from signup ( default : yes ).', 1, 6, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Activate Card', 'activatedbyuser', 0, 'active card after the new signup. if No, the Signup confirmation is needed and an email will be sent to the user with a link for activation (need to put the link into the Signup mail template).', 1, 6, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Customer Interface URL', 'urlcustomerinterface', 'http://localhost/customer/', 'url of the customer interface to display after activation.', 0, 6, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Asterisk Reload', 'reload_asterisk_if_sipiax_created', '0', 'Define if you want to reload Asterisk when a SIP / IAX Friend is created at signup time.', 1, 6, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Backup Path', 'backup_path', '/tmp', 'Path to store backup of database.', 0, 7, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('GZIP Path', 'gzip_exe', '/bin/gzip', 'Path for gzip.', 0, 7, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('GunZip Path', 'gunzip_exe', '/bin/gunzip', 'Path for gunzip .', 0, 7, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('MySql Dump Path', 'mysqldump', '/usr/bin/mysqldump', 'path for mysqldump.', 0, 7, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('PGSql Dump Path', 'pg_dump', '/usr/bin/pg_dump', 'path for pg_dump.', 0, 7, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('MySql Path', 'mysql', '/usr/bin/mysql', 'Path for MySql.', 0, 7, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('PSql Path', 'psql', '/usr/bin/psql', 'Path for PSql.', 0, 7, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('SIP File Path', 'buddy_sip_file', '/etc/asterisk/additional_a2billing_sip.conf', 'Path to store the asterisk configuration files SIP.', 0, 8, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('IAX File Path', 'buddy_iax_file', '/etc/asterisk/additional_a2billing_iax.conf', 'Path to store the asterisk configuration files IAX.', 0, 8, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('API Security Key', 'api_security_key', 'Ae87v56zzl34v', 'API have a security key to validate the http request, the key has to be sent after applying md5, Valid characters are [a-z,A-Z,0-9].', 0, 8, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Authorized IP', 'api_ip_auth', '127.0.0.1', 'API to restrict the IPs authorised to make a request, Define The the list of ips separated by '';''.', 0, 8, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Admin Email', 'email_admin', 'root@localhost', 'Administative Email.', 0, 8, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('MOH Directory', 'dir_store_mohmp3', '/var/lib/asterisk/mohmp3', 'MOH (Music on Hold) base directory.', 0, 8, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('MOH Classes', 'num_musiconhold_class', '10', 'Number of MOH classes you have created in musiconhold.conf : acc_1, acc_2... acc_10 class	etc....', 0, 8, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Display Help', 'show_help', 1, 'Display the help section inside the admin interface  (YES - NO).', 1, 8, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Max File Upload Size', 'my_max_file_size_import', '1024000', 'File Upload parameters, PLEASE CHECK ALSO THE VALUE IN YOUR PHP.INI THE LIMIT IS 2MG BY DEFAULT .', 0, 8, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Audio Directory Path', 'dir_store_audio', '/var/lib/asterisk/sounds/a2billing', 'Not used yet, The goal is to upload files and use them in the IVR.', 0, 8, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Max Audio File Size', 'my_max_file_size_audio', '3072000', 'upload maximum file size.', 0, 8, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Extensions Allowed', 'file_ext_allow', 'gsm, mp3, wav', 'File type extensions permitted to be uploaded such as "gsm, mp3, wav" (separated by ,).', 0, 8, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Muzic Files Allowed', 'file_ext_allow_musiconhold', 'mp3', 'File type extensions permitted to be uploaded for the musiconhold such as "gsm, mp3, wav" (separate by ,).', 0, 8, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Link Audio', 'link_audio_file', '0', 'Enable link on the CDR viewer to the recordings. (YES - NO).', 1, 8, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Monitor Path', 'monitor_path', '/var/spool/asterisk/monitor', 'Path to link the recorded monitor files.', 0, 8, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Monitor Format', 'monitor_formatfile', 'gsm', 'FORMAT OF THE RECORDED MONITOR FILE.', 0, 8, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Invoice Icon', 'show_icon_invoice', 1, 'Display the icon in the invoice.', 1, 8, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Show Top Frame', 'show_top_frame', '0', 'Display the top frame (useful if you want to save space on your little tiny screen ) .', 1, 8, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Currency', 'currency_choose', 'usd, eur, cad, hkd', 'Allow the customer to chose the most appropriate currency ("all" can be used).', 0, 8, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Card Export Fields', 'card_export_field_list', 'cc_card.id, username, useralias, lastname, credit, tariff, activated, language, inuse, currency, sip_buddy, iax_buddy, nbused, mac_addr', 'Fields to export in csv format from cc_card table.', 0, 8, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Vouvher Export Fields', 'voucher_export_field_list', 'voucher, credit, tag, activated, usedcardnumber, usedate, currency', 'Field to export in csv format from cc_voucher table.', 0, 8, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Advance Mode', 'advanced_mode', '0', 'Advanced mode - Display additional configuration options on the ratecard (progressive rates, musiconhold, ...).', 1, 8, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('SIP/IAX Delete', 'delete_fk_card', 1, 'Delete the SIP/IAX Friend & callerid when a card is deleted.', 1, 8, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Type', 'type', 'friend', 'Refer to sip.conf & iax.conf documentation for the meaning of those parameters.', 0, 9, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Allow', 'allow', 'ulaw,alaw,gsm,g729', 'Refer to sip.conf & iax.conf documentation for the meaning of those parameters.', 0, 9, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Context', 'context', 'a2billing', 'Refer to sip.conf & iax.conf documentation for the meaning of those parameters.', 0, 9, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Nat', 'nat', 'yes', 'Refer to sip.conf & iax.conf documentation for the meaning of those parameters.', 0, 9, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('AMA Flag', 'amaflag', 'billing', 'Refer to sip.conf & iax.conf documentation for the meaning of those parameters.', 0, 9, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Qualify', 'qualify', 'yes', 'Refer to sip.conf & iax.conf documentation for the meaning of those parameters.', 0, 9, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Host', 'host', 'dynamic', 'Refer to sip.conf & iax.conf documentation for the meaning of those parameters.', 0, 9, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('DTMF Mode', 'dtmfmode', 'RFC2833', 'Refer to sip.conf & iax.conf documentation for the meaning of those parameters.', 0, 9, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Alarm Log File', 'cront_alarm', '/var/log/a2billing/cront_a2b_alarm.log', 'To disable application logging, remove/comment the log file name aside service.', 0, 10, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Auto refill Log File', 'cront_autorefill', '/var/log/a2billing/cront_a2b_autorefill.log', 'To disable application logging, remove/comment the log file name aside service.', 0, 10, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Bactch Process Log File', 'cront_batch_process', '/var/log/a2billing/cront_a2b_batch_process.log', 'To disable application logging, remove/comment the log file name aside service .', 0, 10, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Archive Log File', 'cront_archive_data', '/var/log/a2billing/cront_a2b_archive_data.log', 'To disable application logging, remove/comment the log file name aside service .', 0, 10, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('DID Billing Log File', 'cront_bill_diduse', '/var/log/a2billing/cront_a2b_bill_diduse.log', 'To disable application logging, remove/comment the log file name aside service .', 0, 10, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Subscription Fee Log File', 'cront_subscriptionfee', '/var/log/a2billing/cront_a2b_subscription_fee.log', 'To disable application logging, remove/comment the log file name aside service.', 0, 10, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Currency Cront Log File', 'cront_currency_update', '/var/log/a2billing/cront_a2b_currency_update.log', 'To disable application logging, remove/comment the log file name aside service.', 0, 10, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Invoice Cront Log File', 'cront_invoice', '/var/log/a2billing/cront_a2b_invoice.log', 'To disable application logging, remove/comment the log file name aside service.', 0, 10, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Cornt Log File', 'cront_check_account', '/var/log/a2billing/cront_a2b_check_account.log', 'To disable application logging, remove/comment the log file name aside service .', 0, 10, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Paypal Log File', 'paypal', '/var/log/a2billing/a2billing_paypal.log', 'paypal log file, to log all the transaction & error.', 0, 10, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('EPayment Log File', 'epayment', '/var/log/a2billing/a2billing_epayment.log', 'epayment log file, to log all the transaction & error .', 0, 10, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('ECommerce Log File', 'api_ecommerce', '/var/log/a2billing/a2billing_api_ecommerce_request.log', 'Log file to store the ecommerce API requests .', 0, 10, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Callback Log File', 'api_callback', '/var/log/a2billing/a2billing_api_callback_request.log', 'Log file to store the CallBack API requests.', 0, 10, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Webservice Card Log File', 'api_card', '/var/log/a2billing/a2billing_api_card.log', 'Log file to store the Card Webservice Logs', 0, 10, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('AGI Log File', 'agi', '/var/log/a2billing/a2billing_agi.log', 'File to log.', 0, 10, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Description', 'description', 'agi-config', 'Description/notes field', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Asterisk Version', 'asterisk_version', '1_4', 'Asterisk Version Information, 1_1,1_2,1_4 By Default it will take 1_2 or higher .', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Answer Call', 'answer_call', 1, 'Manage the answer on the call. Disabling this for callback trigger numbers makes it ring not hang up.', 1, 11, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Play Audio', 'play_audio', 1, 'Play audio - this will disable all stream file but not the Get Data , for wholesale ensure that the authentication works and than number_try = 1.', 1, 11, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Say GoodBye', 'say_goodbye', '0', 'play the goodbye message when the user has finished.', 1, 11, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Play Language Menu', 'play_menulanguage', '0', 'enable the menu to choose the language, press 1 for English, pulsa 2 para el español, Pressez 3 pour Français', 1, 11, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Force Language', 'force_language', '', 'force the use of a language, if you dont want to use it leave the option empty, Values : ES, EN, FR, etc... (according to the audio you have installed).', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Intro Prompt', 'intro_prompt', '', 'Introduction prompt : to specify an additional prompt to play at the beginning of the application .', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Min Call Credit', 'min_credit_2call', '0', 'Minimum amount of credit to use the application .', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Min Bill Duration', 'min_duration_2bill', '0', 'this is the minimum duration in seconds of a call in order to be billed any call with a length less than min_duration_2bill will have a 0 cost useful not to charge callers for system errors when a call was answered but it actually didn''t connect.', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Not Enough Credit', 'notenoughcredit_cardnumber', 0, 'if user doesn''t have enough credit to call a destination, prompt him to enter another cardnumber .', 1, 11, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('New Caller ID', 'notenoughcredit_assign_newcardnumber_cid', 0, 'if notenoughcredit_cardnumber = YES  then	assign the CallerID to the new cardnumber.', 1, 11, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Use DNID', 'use_dnid', '0', 'if YES it will use the DNID and try to dial out, without asking for the phonenumber to call.', 1, 11, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Not Use DNID', 'no_auth_dnid', '2400,2300', 'list the dnid on which you want to avoid the use of the previous option "use_dnid" .', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Try Count', 'number_try', '3', 'number of times the user can dial different number.', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Force CallPlan', 'force_callplan_id', '', 'this will force to select a specific call plan by the Rate Engine.', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Say Balance After Auth', 'say_balance_after_auth', 1, 'Play the balance to the user after the authentication (values : yes - no).', 1, 11, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Say Balance After Call', 'say_balance_after_call', '0', 'Play the balance to the user after the call (values : yes - no).', 1, 11, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Say Rate', 'say_rateinitial', '0', 'Play the initial cost of the route (values : yes - no)', 1, 11, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Say Duration', 'say_timetocall', 1, 'Play the amount of time that the user can call (values : yes - no).', 1, 11, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Auto Set CLID', 'auto_setcallerid', 1, 'enable the setup of the callerID number before the outbound is made, by default the user callerID value will be use.', 1, 11, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Force CLID', 'force_callerid', '', 'If auto_setcallerid is enabled, the value of force_callerid will be set as CallerID.', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('CLID Sanitize', 'cid_sanitize', '0', 'If force_callerid is not set, then the following option ensures that CID is set to one of the card''s configured caller IDs or blank if none available.(NO - disable this feature, caller ID can be anything, CID - Caller ID must be one of the customers caller IDs, DID - Caller ID must be one of the customers DID nos, BOTH - Caller ID must be one of the above two items)', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('CLID Enable', 'cid_enable', '0', 'enable the callerid authentication if this option is active the CC system will check the CID of caller  .', 1, 11, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Ask PIN', 'cid_askpincode_ifnot_callerid', 1, 'if the CID does not exist, then the caller will be prompt to enter his cardnumber .', 1, 11, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('FailOver LCR/LCD Prefix', 'failover_lc_prefix', 0, 'if we will failover for LCR/LCD prefix. For instance if you have 346 and 34 for if 346 fail it will try to outbound with 34 route.', 1, 11, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Auto CLID', 'cid_auto_assign_card_to_cid', 1, 'if the callerID authentication is enable and the authentication fails then the user will be prompt to enter his cardnumber;this option will bound the cardnumber entered to the current callerID so that next call will be directly authenticate.', 1, 11, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Auto Create Card', 'cid_auto_create_card', '0', 'if the callerID is captured on a2billing, this option will create automatically a new card and add the callerID to it.', 1, 11, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Auto Create Card Length', 'cid_auto_create_card_len', '10', 'set the length of the card that will be auto create (ie, 10).', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Auto Create Card Type', 'cid_auto_create_card_typepaid', 'POSTPAY', 'billing type of the new card( value : POSTPAY or PREPAY) .', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Auto Create Card Credit', 'cid_auto_create_card_credit', '0', 'amount of credit of the new card.', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Auto Create Card Limit', 'cid_auto_create_card_credit_limit', '1000', 'if postpay, define the credit limit for the card.', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Auto Create Card TariffGroup', 'cid_auto_create_card_tariffgroup', '6', 'the tariffgroup to use for the new card (this is the ID that you can find on the admin web interface) .', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Auto CLID Security', 'callerid_authentication_over_cardnumber', '0', 'to check callerID over the cardnumber authentication (to guard against spoofing).', 1, 11, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('SIP Call', 'sip_iax_friends', '0', 'enable the option to call sip/iax friend for free (values : YES - NO).', 1, 11, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('SIP Call Prefix', 'sip_iax_pstn_direct_call_prefix', '555', 'if SIP_IAX_FRIENDS is active, you can define a prefix for the dialed digits to call a pstn number .', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Direct Call', 'sip_iax_pstn_direct_call', '0', 'this will enable a prompt to enter your destination number. if number start by sip_iax_pstn_direct_call_prefix we do directly a sip iax call, if not we do a normal call.', 1, 11, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('IVR Voucher Refill', 'ivr_voucher', '0', 'enable the option to refill card with voucher in IVR (values : YES - NO) .', 1, 11, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('IVR Voucher Prefix', 'ivr_voucher_prefix', '8', 'if ivr_voucher is active, you can define a prefix for the voucher number to refill your card, values : number - don''t forget to change prepaid-refill_card_with_voucher audio accordingly .', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('IVR Low Credit', 'jump_voucher_if_min_credit', 0, 'When the user credit are below the minimum credit to call min_credit jump directly to the voucher IVR menu  (values: YES - NO) .', 1, 11, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Dail Command Parms', 'dialcommand_param', '|60|HRrL(%timeout%:61000:30000)', 'More information about the Dial : http://voip-info.org/wiki-Asterisk+cmd+dial<br>30 :  The timeout parameter is optional. If not specifed, the Dial command will wait indefinitely, exiting only when the originating channel hangs up, or all the dialed channels return a busy or error condition. Otherwise it specifies a maximum time, in seconds, that the Dial command is to wait for a channel to answer.<br>H: Allow the caller to hang up by dialing * <br>r: Generate a ringing tone for the calling party<br>R: Indicate ringing to the calling party when the called party indicates ringing, pass no audio until answered.<br>m: Provide Music on Hold to the calling party until the called channel answers.<br>L(x[:y][:z]): Limit the call to ''x'' ms, warning when ''y'' ms are left, repeated every ''z'' ms)<br>%timeout% tag is replaced by the calculated timeout according the credit & destination rate!.', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('SIP/IAX Dial Command Parms', 'dialcommand_param_sipiax_friend', '|60|HL(3600000:61000:30000)', 'by default (3600000  =  1HOUR MAX CALL).', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Outbound Call', 'switchdialcommand', '0', 'Define the order to make the outbound call<br>YES -> SIP/dialedphonenumber@gateway_ip - NO  SIP/gateway_ip/dialedphonenumber<br>Both should work exactly the same but i experimented one case when gateway was supporting dialedphonenumber@gateway_ip, So in case of trouble, try it out.', 1, 11, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Failover Retry Limit', 'failover_recursive_limit', '2', 'failover recursive search - define how many time we want to authorize the research of the failover trunk when a call fails (value : 0 - 20) .', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Max Time', 'maxtime_tocall_negatif_free_route', '5400', 'This setting specifies an upper limit for the duration of a call to a destination for which the selling rate is less than or equal to 0.', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Send Reminder', 'send_reminder', '0', 'Send a reminder email to the user when they are under min_credit_2call.', 1, 11, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Record Call', 'record_call', '0', 'enable to monitor the call (to record all the conversations) value : YES - NO .', 1, 11, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Monitor File Format', 'monitor_formatfile', 'gsm', 'format of the recorded monitor file.', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('AGI Force Currency', 'agi_force_currency', '', 'Force to play the balance to the caller in a predefined currency, to use the currency set for by the customer leave this field empty.', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Currency Associated', 'currency_association', 'usd:dollars,mxn:pesos,eur:euros,all:credit', 'Define all the audio (without file extensions) that you want to play according to currency (use , to separate, ie "usd:prepaid-dollar,mxn:pesos,eur:Euro,all:credit").', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Minor Currency Associated', 'currency_association_minor', 'usd:prepaid-cents,eur:prepaid-cents,gbp:prepaid-pence,all:credit', 'Define all the audio (without file extensions) that you want to play according to minor currency (use , to separate, ie "usd:prepaid-cents,eur:prepaid-cents,gbp:prepaid-pence,all:credit").', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('File Enter Destination', 'file_conf_enter_destination', 'prepaid-enter-dest', 'Please enter the file name you want to play when we prompt the calling party to enter the destination number, file_conf_enter_destination = prepaid-enter-number-u-calling-1-or-011.', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('File Language Menu', 'file_conf_enter_menulang', 'prepaid-menulang2', 'Please enter the file name you want to play when we prompt the calling party to choose the prefered language .', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Bill Callback', 'callback_bill_1stleg_ifcall_notconnected', 1, 'Define if you want to bill the 1st leg on callback even if the call is not connected to the destination.', 1, 11, 'yes,no');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('International prefixes', 'international_prefixes', '011,00,09,1', 'List the prefixes you want stripped off if the call plan requires it', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Server GMT', 'server_GMT', 'GMT+1:00', 'Define the sever gmt time', 0, 1, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Invoice Template Path', 'invoice_template_path', '../invoice/', 'gives invoice template path from default one', 0, 1, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Outstanding Template Path', 'outstanding_template_path', '../outstanding/', 'gives outstanding template path from default one', 0, 1, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Sales Template Path', 'sales_template_path', '../sales/', 'gives sales template path from default one', 0, 1, NULL);




CREATE TABLE cc_timezone (
    id 								INT NOT NULL AUTO_INCREMENT,
    gmtzone							VARCHAR(255),
    gmttime		 					VARCHAR(255),
	gmtoffset						BIGINT NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT-12:00) International Date Line West', 'GMT-12:00', '-43200');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT-11:00) Midway Island, Samoa', 'GMT-11:00', '-39600');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT-10:00) Hawaii', 'GMT-10:00', '-36000');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT-09:00) Alaska', 'GMT-09:00', '-32400');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT-08:00) Pacific Time (US & Canada) Tijuana', 'GMT-08:00', '-28800');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT-07:00) Arizona', 'GMT-07:00', '-25200');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT-07:00) Chihuahua, La Paz, Mazatlan', 'GMT-07:00', '-25200');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT-07:00) Mountain Time(US & Canada)', 'GMT-07:00', '-25200');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT-06:00) Central America', 'GMT-06:00', '-21600');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT-06:00) Central Time (US & Canada)', 'GMT-06:00', '-21600');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT-06:00) Guadalajara, Mexico City, Monterrey', 'GMT-06:00', '-21600');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT-06:00) Saskatchewan', 'GMT-06:00', '-21600');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT-05:00) Bogota, Lima, Quito', 'GMT-05:00', '-18000');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT-05:00) Eastern Time (US & Canada)', 'GMT-05:00', '-18000');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT-05:00) Indiana (East)', 'GMT-05:00', '-18000');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT-04:00) Atlantic Time (Canada)', 'GMT-04:00', '-14400');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT-04:00) Caracas, La Paz', 'GMT-04:00', '-14400');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT-04:00) Santiago', 'GMT-04:00', '-14400');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT-03:30) NewFoundland', 'GMT-03:30', '-12600');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT-03:00) Brasillia', 'GMT-03:00', '-10800');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT-03:00) Buenos Aires, Georgetown', 'GMT-03:00', '-10800');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT-03:00) Greenland', 'GMT-03:00', '-10800');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT-03:00) Mid-Atlantic', 'GMT-03:00', '-10800');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT-01:00) Azores', 'GMT-01:00', '-3600');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT-01:00) Cape Verd Is.', 'GMT-01:00', '-3600');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT) Casablanca, Monrovia', 'GMT+00:00', '0');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT) Greenwich Mean Time : Dublin, Edinburgh, Lisbon,  London', 'GMT', '0');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+01:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna', 'GMT+01:00', '3600');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague', 'GMT+01:00', '3600');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+01:00) Brussels, Copenhagen, Madrid, Paris', 'GMT+01:00', '3600');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+01:00) Sarajevo, Skopje, Warsaw, Zagreb', 'GMT+01:00', '3600');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+01:00) West Central Africa', 'GMT+01:00', '3600');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+02:00) Athens, Istanbul, Minsk', 'GMT+02:00', '7200');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+02:00) Bucharest', 'GMT+02:00', '7200');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+02:00) Cairo', 'GMT+02:00', '7200');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+02:00) Harere, Pretoria', 'GMT+02:00', '7200');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+02:00) Helsinki, Kyiv, Riga, Sofia, Tallinn, Vilnius', 'GMT+02:00', '7200');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+02:00) Jeruasalem', 'GMT+02:00', '7200');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+03:00) Baghdad', 'GMT+03:00', '10800');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+03:00) Kuwait, Riyadh', 'GMT+03:00', '10800');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+03:00) Moscow, St.Petersburg, Volgograd', 'GMT+03:00', '10800');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+03:00) Nairobi', 'GMT+03:00', '10800');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+03:30) Tehran', 'GMT+03:30', '12600');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+04:00) Abu Dhabi, Muscat', 'GMT+04:00', '14400');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+04:00) Baku, Tbillisi, Yerevan', 'GMT+04:00', '14400');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+04:30) Kabul', 'GMT+04:30', '16200');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+05:00) Ekaterinburg', 'GMT+05:00', '18000');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+05:00) Islamabad, Karachi, Tashkent', 'GMT+05:00', '18000');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+05:30) Chennai, Kolkata, Mumbai, New Delhi', 'GMT+05:30', '19800');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+05:45) Kathmandu', 'GMT+05:45', '20700');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+06:00) Almaty, Novosibirsk', 'GMT+06:00', '21600');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+06:00) Astana, Dhaka', 'GMT+06:00', '21600');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+06:00) Sri Jayawardenepura', 'GMT+06:00', '21600');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+06:30) Rangoon', 'GMT+06:30', '23400');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+07:00) Bangkok, Hanoi, Jakarta', 'GMT+07:00', '25200');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+07:00) Krasnoyarsk', 'GMT+07:00', '25200');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+08:00) Beijiing, Chongging, Hong Kong, Urumqi', 'GMT+08:00', '28800');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+08:00) Irkutsk, Ulaan Bataar', 'GMT+08:00', '28800');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+08:00) Kuala Lumpur, Singapore', 'GMT+08:00', '28800');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+08:00) Perth', 'GMT+08:00', '28800');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+08:00) Taipei', 'GMT+08:00', '28800');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+09:00) Osaka, Sapporo, Tokyo', 'GMT+09:00', '32400');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+09:00) Seoul', 'GMT+09:00', '32400');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+09:00) Yakutsk', 'GMT+09:00', '32400');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+09:00) Adelaide', 'GMT+09:00', '32400');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+09:30) Darwin', 'GMT+09:30', '34200');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+10:00) Brisbane', 'GMT+10:00', '36000');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+10:00) Canberra, Melbourne, Sydney', 'GMT+10:00', '36000');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+10:00) Guam, Port Moresby', 'GMT+10:00', '36000');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+10:00) Hobart', 'GMT+10:00', '36000');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+10:00) Vladivostok', 'GMT+10:00', '36000');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+11:00) Magadan, Solomon Is., New Caledonia', 'GMT+11:00', '39600');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+12:00) Auckland, Wellington', 'GMT+1200', '43200');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+12:00) Fiji, Kamchatka, Marshall Is.', 'GMT+12:00', '43200');
INSERT INTO cc_timezone (gmtzone, gmttime, gmtoffset) VALUES ('(GMT+13:00) Nuku alofa', 'GMT+13:00', '46800');


CREATE TABLE cc_iso639 (
    code character(2) NOT NULL,
    name character(16) NOT NULL,
    lname character(16),
    `charset` character(16) NOT NULL DEFAULT 'ISO-8859-1',
    CONSTRAINT iso639_name_key UNIQUE (name),
    CONSTRAINT iso639_pkey PRIMARY KEY (code)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ab', 'Abkhazian       ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('om', 'Afan (Oromo)    ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('aa', 'Afar            ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('af', 'Afrikaans       ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('sq', 'Albanian        ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('am', 'Amharic         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ar', 'Arabic          ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('hy', 'Armenian        ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('as', 'Assamese        ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ay', 'Aymara          ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('az', 'Azerbaijani     ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ba', 'Bashkir         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('eu', 'Basque          ', 'Euskera         ', 'ISO-8859-15     ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('bn', 'Bengali Bangla  ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('dz', 'Bhutani         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('bh', 'Bihari          ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('bi', 'Bislama         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('br', 'Breton          ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('bg', 'Bulgarian       ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('my', 'Burmese         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('be', 'Byelorussian    ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('km', 'Cambodian       ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ca', 'Catalan         ', '          \t\t    ', 'ISO-8859-15     ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('zh', 'Chinese         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('co', 'Corsican        ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('hr', 'Croatian        ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('cs', 'Czech           ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('da', 'Danish          ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('nl', 'Dutch           ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('en', 'English         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('eo', 'Esperanto       ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('et', 'Estonian        ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('fo', 'Faroese         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('fj', 'Fiji            ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('fi', 'Finnish         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('fr', 'French          ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('fy', 'Frisian         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('gl', 'Galician        ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ka', 'Georgian        ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('de', 'German          ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('el', 'Greek           ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('kl', 'Greenlandic     ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('gn', 'Guarani         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('gu', 'Gujarati        ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ha', 'Hausa           ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('he', 'Hebrew          ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('hi', 'Hindi           ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('hu', 'Hungarian       ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('is', 'Icelandic       ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('id', 'Indonesian      ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ia', 'Interlingua     ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ie', 'Interlingue     ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('iu', 'Inuktitut       ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ik', 'Inupiak         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ga', 'Irish           ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('it', 'Italian         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ja', 'Japanese        ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('jv', 'Javanese        ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('kn', 'Kannada         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ks', 'Kashmiri        ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('kk', 'Kazakh          ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('rw', 'Kinyarwanda     ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ky', 'Kirghiz         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('rn', 'Kurundi         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ko', 'Korean          ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ku', 'Kurdish         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('lo', 'Laothian        ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('la', 'Latin           ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('lv', 'Latvian Lettish ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ln', 'Lingala         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('lt', 'Lithuanian      ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('mk', 'Macedonian      ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('mg', 'Malagasy        ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ms', 'Malay           ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ml', 'Malayalam       ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('mt', 'Maltese         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('mi', 'Maori           ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('mr', 'Marathi         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('mo', 'Moldavian       ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('mn', 'Mongolian       ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('na', 'Nauru           ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ne', 'Nepali          ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('no', 'Norwegian       ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('oc', 'Occitan         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('or', 'Oriya           ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ps', 'Pashto Pushto   ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('fa', 'Persian (Farsi) ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('pl', 'Polish          ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('pt', 'Portuguese      ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('pa', 'Punjabi         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('qu', 'Quechua         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('rm', 'Rhaeto-Romance  ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ro', 'Romanian        ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ru', 'Russian         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('sm', 'Samoan          ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('sg', 'Sangho          ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('sa', 'Sanskrit        ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('gd', 'Scots Gaelic    ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('sr', 'Serbian         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('sh', 'Serbo-Croatian  ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('st', 'Sesotho         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('tn', 'Setswana        ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('sn', 'Shona           ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('sd', 'Sindhi          ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('si', 'Singhalese      ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ss', 'Siswati         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('sk', 'Slovak          ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('sl', 'Slovenian       ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('so', 'Somali          ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('es', 'Spanish         ', '         \t\t     ', 'ISO-8859-15     ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('su', 'Sundanese       ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('sw', 'Swahili         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('sv', 'Swedish         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('tl', 'Tagalog         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('tg', 'Tajik           ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ta', 'Tamil           ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('tt', 'Tatar           ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('te', 'Telugu          ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('th', 'Thai            ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('bo', 'Tibetan         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ti', 'Tigrinya        ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('to', 'Tonga           ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ts', 'Tsonga          ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('tr', 'Turkish         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('tk', 'Turkmen         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('tw', 'Twi             ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ug', 'Uigur           ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('uk', 'Ukrainian       ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('ur', 'Urdu            ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('uz', 'Uzbek           ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('vi', 'Vietnamese      ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('vo', 'Volapuk         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('cy', 'Welsh           ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('wo', 'Wolof           ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('xh', 'Xhosa           ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('yi', 'Yiddish         ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('yo', 'Yoruba          ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('za', 'Zhuang          ', '                ', 'ISO-8859-1      ');
INSERT INTO cc_iso639 (code, name, lname, charset) VALUES ('zu', 'Zulu            ', '                ', 'ISO-8859-1      ');

ALTER TABLE cc_templatemail DROP INDEX cons_cc_templatemail_mailtype;
ALTER TABLE cc_templatemail ADD id INT NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST, ADD id_language CHAR( 20 ) NOT NULL DEFAULT 'en' AFTER id ;
ALTER TABLE cc_templatemail CHANGE id id INT( 11 ) NOT NULL ;
ALTER TABLE cc_templatemail DROP PRIMARY KEY;
ALTER TABLE cc_templatemail ADD UNIQUE cons_cc_templatemail_id_language ( mailtype, id_language );


ALTER TABLE cc_card ADD status INT NOT NULL DEFAULT '1' AFTER activated ;
update cc_card set status = 1 where activated = 't';
update cc_card set status = 0 where activated = 'f';

CREATE TABLE cc_status_log (
	id 				BIGINT(20) NOT NULL AUTO_INCREMENT,
	status 			INT(11) NOT NULL,
	id_cc_card 		BIGINT(20) NOT NULL,
	updated_date 	TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


ALTER TABLE cc_card ADD COLUMN tag CHAR(50);
ALTER TABLE cc_ratecard ADD COLUMN rounding_calltime INT NOT NULL DEFAULT 0;
ALTER TABLE cc_ratecard ADD COLUMN rounding_threshold INT NOT NULL DEFAULT 0;
ALTER TABLE cc_ratecard ADD COLUMN additional_block_charge DECIMAL(15,5) NOT NULL DEFAULT 0;
ALTER TABLE cc_ratecard ADD COLUMN additional_block_charge_time INT NOT NULL DEFAULT 0;
ALTER TABLE cc_ratecard ADD COLUMN tag CHAR(50);
ALTER TABLE cc_ratecard ADD COLUMN disconnectcharge_after INT NOT NULL DEFAULT 0;

ALTER TABLE cc_card ADD COLUMN template_invoice VARCHAR( 100 ) ;
ALTER TABLE cc_card ADD COLUMN template_outstanding VARCHAR( 100 ) ;

CREATE TABLE cc_card_history (
	id 					BIGINT NOT NULL AUTO_INCREMENT,
	id_cc_card 			BIGINT,
	datecreated 		TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	description			TEXT,
	PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



ALTER TABLE cc_callback_spool CHANGE variable variable VARCHAR( 300 ) DEFAULT NULL;


ALTER TABLE cc_call ADD COLUMN real_sessiontime INT (11) DEFAULT NULL;


-- ?? update this when release 1.4
CREATE TABLE cc_call_archive (
	id 									bigINT (20) NOT NULL AUTO_INCREMENT,
	sessionid 							char(40) NOT NULL,
	uniqueid 							char(30) NOT NULL,
	username 							char(40) NOT NULL,
	nasipaddress 						char(30) DEFAULT NULL,
	starttime 							timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	stoptime 							timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	sessiontime 						INT (11) DEFAULT NULL,
	calledstation 						char(30) DEFAULT NULL,
	startdelay 							INT (11) DEFAULT NULL,
	stopdelay 							INT (11) DEFAULT NULL,
	terminatecause 						char(20) DEFAULT NULL,
	usertariff 							char(20) DEFAULT NULL,
	calledprovider 						char(20) DEFAULT NULL,
	calledcountry 						char(30) DEFAULT NULL,
	calledsub 							char(20) DEFAULT NULL,
	calledrate 							FLOAT DEFAULT NULL,
	sessionbill 						FLOAT DEFAULT NULL,
	destination 						char(40) DEFAULT NULL,
	id_tariffgroup 						INT (11) DEFAULT NULL,
	id_tariffplan 						INT (11) DEFAULT NULL,
	id_ratecard 						INT (11) DEFAULT NULL,
	id_trunk 							INT (11) DEFAULT NULL,
	sipiax 								INT (11) DEFAULT '0',
	src 								char(40) DEFAULT NULL,
	id_did 								INT (11) DEFAULT NULL,
	buyrate 							DECIMAL(15,5) DEFAULT 0,
	buycost 							DECIMAL(15,5) DEFAULT 0,
	id_card_package_offer 				INT (11) DEFAULT 0,
	real_sessiontime					INT (11) DEFAULT NULL,
	PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE cc_call_archive ADD INDEX ( username );
ALTER TABLE cc_call_archive ADD INDEX ( starttime );
ALTER TABLE cc_call_archive ADD INDEX ( terminatecause );
ALTER TABLE cc_call_archive ADD INDEX ( calledstation );



ALTER TABLE cc_card DROP COLUMN userpass;

CREATE TABLE cc_card_archive (
	id 								BIGINT NOT NULL,
	creationdate 					TIMESTAMP DEFAULT  CURRENT_TIMESTAMP NOT NULL,
	firstusedate 					TIMESTAMP,
	expirationdate 					TIMESTAMP,
	enableexpire 					INT DEFAULT 0,
	expiredays 						INT DEFAULT 0,
	username 						CHAR(50) NOT NULL,
	useralias 						CHAR(50) NOT NULL,
	uipass 							CHAR(50),
	credit 							DECIMAL(15,5) DEFAULT 0 NOT NULL,
	tariff 							INT DEFAULT 0,
	id_didgroup 					INT DEFAULT 0,
	activated 						CHAR(1) DEFAULT 'f' NOT NULL,
	status							INT DEFAULT 1,
	lastname 						CHAR(50),
	firstname 						CHAR(50),
	address 						CHAR(100),
	city 							CHAR(40),
	state 							CHAR(40),
	country 						CHAR(40),
	zipcode 						CHAR(20),
	phone 							CHAR(20),
	email 							CHAR(70),
	fax 							CHAR(20),
	inuse 							INT DEFAULT 0,
	simultaccess 					INT DEFAULT 0,
	currency 						CHAR(3) DEFAULT 'USD',
	lastuse  						TIMESTAMP,
	nbused 							INT DEFAULT 0,
	typepaid 						INT DEFAULT 0,
	creditlimit 					INT DEFAULT 0,
	voipcall 						INT DEFAULT 0,
	sip_buddy 						INT DEFAULT 0,
	iax_buddy 						INT DEFAULT 0,
	language 						CHAR(5) DEFAULT 'en',
	redial 							CHAR(50),
	runservice 						INT DEFAULT 0,
	nbservice 						INT DEFAULT 0,
	id_campaign						INT DEFAULT 0,
	num_trials_done 				BIGINT DEFAULT 0,
	callback 						CHAR(50),
	vat 							FLOAT DEFAULT 0 NOT NULL,
	servicelastrun 					TIMESTAMP,
	initialbalance 					DECIMAL(15,5) DEFAULT 0 NOT NULL,
	invoiceday 						INT DEFAULT 1,
	autorefill 						INT DEFAULT 0,
	loginkey 						CHAR(40),
	activatedbyuser 				CHAR(1) DEFAULT 't' NOT NULL,
	id_timezone 					INT DEFAULT 0,
	tag char(50) 					collate utf8_bin default NULL,
	template_invoice 				text collate utf8_bin,
	template_outstanding			text collate utf8_bin,
	mac_addr						CHAR(17) DEFAULT '00-00-00-00-00-00' NOT NULL,
	PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



ALTER TABLE cc_card_archive ADD INDEX ( creationdate );
ALTER TABLE cc_card_archive ADD INDEX ( username );
ALTER TABLE cc_ratecard ADD COLUMN is_merged INT DEFAULT 0;

UPDATE cc_config SET config_title='Dial Command Params', config_description='More information about the Dial : http://voip-info.org/wiki-Asterisk+cmd+dial<br>30 :  The timeout parameter is optional. If not specifed, the Dial command will wait indefinitely, exiting only when the originating channel hangs up, or all the dialed channels return a busy or error condition. Otherwise it specifies a maximum time, in seconds, that the Dial command is to wait for a channel to answer.<br>H: Allow the caller to hang up by dialing * <br>r: Generate a ringing tone for the calling party<br>R: Indicate ringing to the calling party when the called party indicates ringing, pass no audio until answered.<br>g: When the called party hangs up, exit to execute more commands in the current context. (new in 1.4)<br>i: Asterisk will ignore any forwarding (302 Redirect) requests received. Essential for DID usage to prevent fraud. (new in 1.4)<br>m: Provide Music on Hold to the calling party until the called channel answers.<br>L(x[:y][:z]): Limit the call to ''x'' ms, warning when ''y'' ms are left, repeated every ''z'' ms)<br>%timeout% tag is replaced by the calculated timeout according the credit & destination rate!.' WHERE  config_key='dialcommand_param';
UPDATE cc_config SET config_title='SIP/IAX Dial Command Params', config_value='|60|HiL(3600000:61000:30000)' WHERE config_key='dialcommand_param_sipiax_friend';





-- VOICEMAIL CHANGES
ALTER TABLE cc_card ADD voicemail_permitted INTEGER DEFAULT 0 NOT NULL;
ALTER TABLE cc_card ADD voicemail_activated SMALLINT DEFAULT 0 NOT NULL;



-- ADD MISSING extracharge_did settings
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Extra charge DIDs', 'extracharge_did', '1800,1900', 'Add extra per-minute charges to this comma-separated list of DNIDs; needs "extracharge_fee" and "extracharge_buyfee"', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Extra charge DID fees', 'extracharge_fee', '0,0', 'Comma-separated list of extra sell-rate charges corresponding to the DIDs in "extracharge_did" - ie : 0.08,0.18', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Extra charge DID buy fees', 'extracharge_buyfee', '0,0', 'Comma-separated list of extra buy-rate charges corresponding to the DIDs in "extracharge_did" - ie : 0.04,0.13', 0, 11, NULL);


-- These triggers are to prevent bogus regexes making it into the database
DELIMITER //
CREATE TRIGGER cc_ratecard_validate_regex_ins BEFORE INSERT ON cc_ratecard
FOR EACH ROW
BEGIN
  DECLARE valid INTEGER;
  SELECT '0' REGEXP REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(CONCAT('^', NEW.dialprefix, '$'), 'X', '[0-9]'), 'Z', '[1-9]'), 'N', '[2-9]'), '.', '.+'), '_', '') INTO valid;
END
//
CREATE TRIGGER cc_ratecard_validate_regex_upd BEFORE UPDATE ON cc_ratecard
FOR EACH ROW
BEGIN
  DECLARE valid INTEGER;
  SELECT '0' REGEXP REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(CONCAT('^', NEW.dialprefix, '$'), 'X', '[0-9]'), 'Z', '[1-9]'), 'N', '[2-9]'), '.', '.+'), '_', '') INTO valid;
END
//
DELIMITER ;

ALTER TABLE cc_currencies CHANGE value value NUMERIC (12,5) unsigned NOT NULL DEFAULT '0.00000';



-- More info into log payment
ALTER TABLE cc_logpayment ADD COLUMN id_logrefill BIGINT DEFAULT NULL;


-- Support / Ticket section

CREATE TABLE cc_support (
	id smallint(5) NOT NULL auto_increment,
	`name` varchar(50) collate utf8_bin NOT NULL,
	PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE cc_support_component (
	id smallint(5) NOT NULL auto_increment,
	id_support smallint(5) NOT NULL,
	name varchar(50) collate utf8_bin NOT NULL,
	activated smallint(6) NOT NULL default '1',
	PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE cc_ticket (
	id bigint(10) NOT NULL auto_increment,
	id_component smallint(5) NOT NULL,
	title varchar(100) collate utf8_bin NOT NULL,
	description text collate utf8_bin,
	priority smallint(6) NOT NULL default '0',
	creationdate timestamp NOT NULL default CURRENT_TIMESTAMP,
	creator bigint(20) NOT NULL,
	status smallint(6) NOT NULL default '0',
	PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE cc_ticket_comment (
	id bigint(20) NOT NULL auto_increment,
	date timestamp NOT NULL default CURRENT_TIMESTAMP,
	id_ticket bigint(10) NOT NULL,
	description text collate utf8_bin,
	creator bigint(20) NOT NULL,
	is_admin char(1) collate utf8_bin NOT NULL default 'f',
	PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


INSERT INTO cc_config ( config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ( 'Support Modules', 'support', '1', 'Enable or Disable the module of support', 1, 3, 'yes,no');



-- change charset to use LIKE without "casse"
ALTER TABLE cc_ratecard CHANGE destination destination CHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;


-- section for notification

INSERT INTO cc_config_group (group_title ,group_description) VALUES
 ( 'notifications', 'This configuration group handles the notifcations configuration');

INSERT INTO cc_config (config_title ,config_key ,config_value ,config_description ,config_valuetype ,config_group_id ,config_listvalues)
VALUES ( 'List of possible values to notify', 'values_notifications', '10:20:50:100:500:1000', 'Possible values to choose when the user receive a notification. You can define a List e.g: 10:20:100.', '0', '12', NULL);

INSERT INTO cc_config ( config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues)
 VALUES ( 'Notifications Modules', 'notification', '1', 'Enable or Disable the module of notification for the customers', 1, 3, 'yes,no');


INSERT INTO cc_config (config_title ,config_key ,config_value ,config_description ,config_valuetype ,config_group_id ,config_listvalues)
VALUES ( 'Notications Cron Module', 'cron_notifications', '1', 'Enable or Disable the cron module of notification for the customers. If it correctly configured in the crontab', '0', '12', 'yes,no');


INSERT INTO cc_config (config_title ,config_key ,config_value ,config_description ,config_valuetype ,config_group_id ,config_listvalues)
VALUES ( 'Notications Delay', 'delay_notifications', '1', 'Delay in number of days to send an other notification for the customers. If the value is 0, it will notify the user everytime the cront is running.', '0', '12', NULL);

ALTER TABLE cc_card ADD last_notification TIMESTAMP NULL DEFAULT NULL ;


ALTER TABLE cc_card ADD email_notification CHAR( 70 ) CHARACTER SET utf8 COLLATE utf8_bin NULL ;

ALTER TABLE cc_card
ADD notify_email SMALLINT NOT NULL DEFAULT '0';

ALTER TABLE cc_card ADD credit_notification INT NOT NULL DEFAULT -1;

UPDATE cc_templatemail SET subject='Your Call-Labs account $cardnumber$ is low on credit ($currency$ $creditcurrency$)', messagetext = '

Your Call-Labs Account number $cardnumber$ is running low on credit.

There is currently only $creditcurrency$ $currency$ left on your account which is lower than the warning level defined ($credit_notification$)


Please top up your account ASAP to ensure continued service

If you no longer wish to receive these notifications or would like to change the balance amount at which these warnings are generated,
please connect on your myaccount panel and change the appropriate parameters


your account information :
Your account number for VOIP authentication : $cardnumber$

https://myaccount.mydomainname.com/
Your account login : $login$
Your account password : $password$


Thanks,
/My Company Name
-------------------------------------
http://www.mydomainname.com
 '
WHERE cc_templatemail.mailtype ='reminder' AND CONVERT( cc_templatemail.id_language USING utf8 ) = 'en' LIMIT 1 ;





-- Section for Agent

CREATE TABLE cc_agent (
	id 								BIGINT NOT NULL AUTO_INCREMENT,
    datecreation 					TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    active 							CHAR(1) DEFAULT 'f' NOT NULL,
    login 							CHAR(20) NOT NULL,
    passwd 							CHAR(40),
    location 						text,
    language 						CHAR(5) DEFAULT 'en',
    id_tariffgroup					INT,
    options 						integer NOT NULL DEFAULT 0,
    credit 							DECIMAL(15,5) DEFAULT 0 NOT NULL,
    climit 							DECIMAL(15,5) DEFAULT 0 NOT NULL,
    currency 						CHAR(3) DEFAULT 'USD',
    locale 							CHAR(10) DEFAULT 'C',
    commission 						DECIMAL(10,4) DEFAULT 0 NOT NULL,
    vat 							DECIMAL(10,4) DEFAULT 0 NOT NULL,
    banner 							TEXT,
	perms 							INT,
    lastname 						CHAR(50),
    firstname 						CHAR(50),
    address 						CHAR(100),
    city 							CHAR(40),
    state 							CHAR(40),
    country 						CHAR(40),
    zipcode 						CHAR(20),
    phone 							CHAR(20),
    email 							CHAR(70),
    fax 							CHAR(20),
	PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



ALTER TABLE cc_card ADD id_agent INT NOT NULL DEFAULT '0';

-- Add card id field in CDR to authorize filtering by agent

ALTER TABLE cc_call ADD card_id BIGINT( 20 ) NOT NULL AFTER username;

UPDATE cc_call,cc_card SET cc_call.card_id=cc_card.id WHERE cc_card.username=cc_call.username;


CREATE TABLE cc_agent_tariffgroup (
	id_agent BIGINT( 20 ) NOT NULL ,
	id_tariffgroup INT( 11 ) NOT NULL,
	PRIMARY KEY ( id_agent,id_tariffgroup )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;




-- Add new configuration payment agent

INSERT INTO cc_config ( id, config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES (NULL , 'Payment Amount', 'purchase_amount_agent', '100:200:500:1000', 'define the different amount of purchase that would be available.', '0', '5', NULL);


-- create group for the card

CREATE TABLE cc_card_group (
	id 					INT NOT NULL AUTO_INCREMENT ,
	name 				CHAR( 30 ) NOT NULL collate utf8_bin ,
	id_agi_conf 		INT NOT NULL ,
	description 		MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NULL ,
	PRIMARY KEY ( id )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


-- insert default group

INSERT INTO cc_card_group (id ,name ,id_agi_conf) VALUES ('1' , 'DEFAULT', '-1');

ALTER TABLE cc_card ADD id_group INT NOT NULL DEFAULT '1';


-- new table for the free minutes/calls package


CREATE TABLE cc_package_group (
	id INT NOT NULL AUTO_INCREMENT ,
	name CHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
	description MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NULL,
	PRIMARY KEY ( id )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE cc_packgroup_package (
	packagegroup_id INT NOT NULL ,
	package_id INT NOT NULL ,
	PRIMARY KEY ( packagegroup_id , package_id )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE cc_package_rate (
	package_id INT NOT NULL ,
	rate_id INT NOT NULL ,
	PRIMARY KEY ( package_id , rate_id )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO cc_config ( id , config_title , config_key , config_value , config_description , config_valuetype , config_group_id , config_listvalues ) VALUES ( NULL , 'Max Time For Unlimited Calls', 'maxtime_tounlimited_calls', '5400', 'For unlimited calls, limit the duration: amount in seconds .', '0', '11', NULL), (NULL , 'Max Time For Free Calls', 'maxtime_tofree_calls', '5400', 'For free calls, limit the duration: amount in seconds .', '0', '11', NULL);

ALTER TABLE cc_ratecard DROP freetimetocall_package_offer;
-- add additionnal grace to the ratecard

ALTER TABLE cc_ratecard ADD additional_grace INT NOT NULL DEFAULT '0';

-- add minimum cost option for a rate card

ALTER TABLE cc_ratecard ADD minimal_cost FLOAT NOT NULL DEFAULT '0';

-- add description for the REFILL AND PAYMENT
ALTER TABLE cc_logpayment ADD description MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NULL ;
ALTER TABLE cc_logrefill ADD description MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NULL ;


ALTER TABLE cc_config CHANGE config_description config_description TEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;


-- Deck threshold switch for callplan
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) 
VALUES ('CallPlan threshold Deck switch', 'callplan_deck_minute_threshold', '', 'CallPlan threshold Deck switch. <br/>This option will switch the user callplan from one call plan ID to and other Callplan ID
The parameters are as follow : <br/>
-- ID of the first callplan : called seconds needed to switch to the next CallplanID <br/>
-- ID of the second callplan : called seconds needed to switch to the next CallplanID <br/>
-- if not needed seconds are defined it will automatically switch to the next one <br/>
-- if defined we will sum the previous needed seconds and check if the caller had done at least the amount of calls necessary to go to the next step and have the amount of seconds needed<br/>
value example for callplan_deck_minute_threshold = 1:300, 2:60, 3', 
'0', '11', NULL);


ALTER TABLE cc_call ADD dnid CHAR( 40 );

-- update password field
ALTER TABLE cc_ui_authen CHANGE password pwd_encoded VARCHAR( 250 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;

-- CHANGE SECURITY ABOUT PASSWORD : All password will be changed to "changepassword"


ALTER TABLE cc_card ADD company_name VARCHAR( 50 ) NULL ,
ADD company_website VARCHAR( 60 ) NULL ,
ADD VAT_RN VARCHAR( 40 ) NULL ,
ADD traffic BIGINT NULL ,
ADD traffic_target MEDIUMTEXT NULL ;

ALTER TABLE cc_logpayment ADD added_refill SMALLINT NOT NULL DEFAULT '0';

-- Add payment history in customer WebUI
INSERT INTO cc_config( config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues )
VALUES ('Payment Historique Modules', 'payment', '1', 'Enable or Disable the module of payment historique for the customers', 1, 3, 'yes,no');

-- modify the field type to authoriz to search by sell rate
ALTER TABLE cc_call CHANGE calledrate calledrate DECIMAL( 15, 5 ) NULL DEFAULT NULL;

-- Delete old menufile.
 DELETE FROM cc_config WHERE config_key = 'file_conf_enter_menulang' ;

INSERT INTO cc_config (config_title ,config_key ,config_value ,config_description ,config_valuetype ,config_group_id ,config_listvalues)
VALUES ('Menu Language Order', 'conf_order_menulang', 'en:fr:es', 'Enter the list of languages authorized for the menu.Use the code language separate by a colon charactere e.g: en:es:fr', '0', '11', NULL);
INSERT INTO cc_config (config_title ,config_key ,config_value ,config_description ,config_valuetype ,config_group_id ,config_listvalues)
VALUES ( 'Disable annoucement the second of the times that the card can call', 'disable_announcement_seconds', '0', 'Desactived the annoucement of the seconds when there are more of one minutes (values : yes - no)', '1', '11', 'yes,no');
INSERT INTO cc_config (config_title ,config_key ,config_value ,config_description ,config_valuetype ,config_group_id ,config_listvalues)
VALUES ( 'Charge for the paypal extra fees', 'charge_paypal_fee', '0', 'Actived, if you want assum the fee of paypal and don''t apply it on the customer (values : yes - no)', '1', '5', 'yes,no');



-- Optimization on terminatecause
ALTER TABLE cc_call ADD COLUMN terminatecauseid INT (1) DEFAULT 1;
UPDATE cc_call SET terminatecauseid=1 WHERE terminatecause='ANSWER';
UPDATE cc_call SET terminatecauseid=1 WHERE terminatecause='ANSWERED';
UPDATE cc_call SET terminatecauseid=2 WHERE terminatecause='BUSY';
UPDATE cc_call SET terminatecauseid=3 WHERE terminatecause='NOANSWER';
UPDATE cc_call SET terminatecauseid=4 WHERE terminatecause='CANCEL';
UPDATE cc_call SET terminatecauseid=5 WHERE terminatecause='CONGESTION';
UPDATE cc_call SET terminatecauseid=6 WHERE terminatecause='CHANUNAVAIL';

ALTER TABLE cc_call DROP terminatecause;
ALTER TABLE cc_call ADD INDEX ( terminatecauseid );

-- Add index on prefix
ALTER TABLE cc_prefix ADD INDEX ( prefixe );

-- optimization on CDR
ALTER TABLE cc_call ADD COLUMN id_cc_prefix INT (11) DEFAULT 0;
ALTER TABLE cc_ratecard ADD COLUMN id_cc_prefix INT (11) DEFAULT 0;

ALTER TABLE cc_call DROP username;
ALTER TABLE cc_call DROP destination;
ALTER TABLE cc_call DROP startdelay;
ALTER TABLE cc_call DROP stopdelay;
ALTER TABLE cc_call DROP usertariff;
ALTER TABLE cc_call DROP calledprovider;
ALTER TABLE cc_call DROP calledcountry;
ALTER TABLE cc_call DROP calledsub;


-- Update all rates values to use Decimal
ALTER TABLE cc_ratecard CHANGE buyrate buyrate decimal(15,5) NOT NULL DEFAULT '0';
ALTER TABLE cc_ratecard CHANGE rateinitial rateinitial decimal(15,5) NOT NULL DEFAULT '0';
ALTER TABLE cc_ratecard CHANGE connectcharge connectcharge decimal(15,5) NOT NULL DEFAULT '0';
ALTER TABLE cc_ratecard CHANGE disconnectcharge disconnectcharge decimal(15,5) NOT NULL DEFAULT '0';
ALTER TABLE cc_ratecard CHANGE stepchargea stepchargea decimal(15,5) NOT NULL DEFAULT '0';
ALTER TABLE cc_ratecard CHANGE chargea chargea decimal(15,5) NOT NULL DEFAULT '0';
ALTER TABLE cc_ratecard CHANGE stepchargeb stepchargeb decimal(15,5) NOT NULL DEFAULT '0';
ALTER TABLE cc_ratecard CHANGE chargeb chargeb decimal(15,5) NOT NULL DEFAULT '0';
ALTER TABLE cc_ratecard CHANGE stepchargeb stepchargeb decimal(15,5) NOT NULL DEFAULT '0';
ALTER TABLE cc_ratecard CHANGE chargeb chargeb decimal(15,5) NOT NULL DEFAULT '0';
ALTER TABLE cc_ratecard CHANGE minimal_cost minimal_cost decimal(15,5) NOT NULL DEFAULT '0';



-- change perms for new menu
UPDATE cc_ui_authen SET perms = '5242879' WHERE userid=1 LIMIT 1;

-- correct card group
ALTER TABLE cc_card_group DROP id_agi_conf;


CREATE TABLE cc_cardgroup_service (
	id_card_group INT NOT NULL ,
	id_service INT NOT NULL,
	PRIMARY KEY ( id_card_group , id_service )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO cc_config (config_title ,config_key ,config_value ,config_description ,config_valuetype ,config_group_id ,config_listvalues)
VALUES ('Cents Currency Associated', 'currency_cents_association', '', 'Define all the audio (without file extensions) that you want to play according to cents currency (use , to separate, ie "amd:lumas").By default the file used is "prepaid-cents" .Use plural to define the cents currency sound, but import two sounds but cents currency defined : ending by ''s'' and not ending by ''s'' (i.e. for lumas , add 2 files : ''lumas'' and ''luma'') ', '0', '11', NULL);

ALTER TABLE cc_call DROP calledrate, DROP buyrate;


-- ------------------------------------------------------
-- for AutoDialer
-- ------------------------------------------------------

-- Create phonebook for
CREATE TABLE cc_phonebook (
	id 				INT NOT NULL AUTO_INCREMENT ,
	name 			CHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
	description 	MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NULL ,
	PRIMARY KEY ( id )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE cc_phonenumber (
	id 				BIGINT NOT NULL AUTO_INCREMENT ,
	id_phonebook 	INT NOT NULL ,
	number 			CHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
	name 			CHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_bin NULL ,
	creationdate 	TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
	status 			SMALLINT NOT NULL DEFAULT '1',
	info 			MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NULL,
	PRIMARY KEY ( id )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE cc_phonebook ADD id_card BIGINT NOT NULL ;

CREATE TABLE cc_campaign_phonebook (
	id_campaign 	INT NOT NULL ,
	id_phonebook 	INT NOT NULL,
	PRIMARY KEY ( id_campaign , id_phonebook )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE cc_campaign CHANGE campaign_name name CHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
CHANGE enable status INT( 11 ) NOT NULL DEFAULT '1';

ALTER TABLE cc_campaign ADD frequency INT NOT NULL DEFAULT '20';

CREATE TABLE cc_campaign_phonestatus (
	id_phonenumber BIGINT NOT NULL ,
	id_campaign INT NOT NULL ,
	id_callback VARCHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
	status INT NOT NULL DEFAULT '0',
	lastuse TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY ( id_phonenumber , id_campaign )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE cc_campaign CHANGE id_trunk id_card BIGINT NOT NULL DEFAULT '0';
ALTER TABLE cc_campaign ADD forward_number CHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_bin NULL;

DROP TABLE cc_phonelist;

INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES 
( 'Context Campaign''s Callback', 'context_campaign_callback', 'a2billing-campaign-callback', 'Context to use in Campaign of Callback', '0', '2', NULL);

INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES 
( 'Default Context forward Campaign''s Callback ', 'default_context_campaign', 'campaign', 'Context to use by default to forward the call in Campaign of Callback', '0', '2', NULL);

ALTER TABLE cc_campaign ADD daily_start_time TIME NOT NULL DEFAULT '10:00:00',
ADD daily_stop_time TIME NOT NULL DEFAULT '18:00:00',
ADD monday TINYINT NOT NULL DEFAULT '1',
ADD tuesday TINYINT NOT NULL DEFAULT '1',
ADD wednesday TINYINT NOT NULL DEFAULT '1',
ADD thursday TINYINT NOT NULL DEFAULT '1',
ADD friday TINYINT NOT NULL DEFAULT '1',
ADD saturday TINYINT NOT NULL DEFAULT '0',
ADD sunday TINYINT NOT NULL DEFAULT '0';

ALTER TABLE cc_campaign ADD id_cid_group INT NOT NULL ;

CREATE TABLE cc_campaign_config (
	id INT NOT NULL AUTO_INCREMENT ,
	name VARCHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
	flatrate DECIMAL(15,5) DEFAULT 0 NOT NULL,
	context VARCHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
	description MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NULL ,
	PRIMARY KEY ( id )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE cc_campaignconf_cardgroup (
	id_campaign_config INT NOT NULL ,
	id_card_group INT NOT NULL ,
	PRIMARY KEY ( id_campaign_config , id_card_group )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


ALTER TABLE cc_campaign ADD id_campaign_config INT NOT NULL ;


-- ------------------------------------------------------
-- for Agent
-- ------------------------------------------------------

ALTER TABLE cc_card ADD COLUMN discount decimal(5,2) NOT NULL DEFAULT '0';


-- New config parameter to display card list : card_show_field_list
ALTER TABLE cc_config MODIFY config_value VARCHAR( 300 );
INSERT INTO  cc_config (config_title,config_key,config_value,config_description,config_valuetype,config_group_id) values ('Card Show Fields','card_show_field_list','id:,username:, useralias:, lastname:,id_group:, id_agent:,  credit:, tariff:, status:, language:, inuse:, currency:, sip_buddy:, iax_buddy:, nbused:,','Fields to show in Customer. Order is important. You can setup size of field using "fieldname:10%" notation or "fieldname:" for harcoded size,"fieldname" for autosize. <br/>You can use:<br/> id,username, useralias, lastname, id_group, id_agent,  credit, tariff, status, language, inuse, currency, sip_buddy, iax_buddy, nbused, firstname, email, discount, callerid',0,8);


-- ------------------------------------------------------
-- Cache system with SQLite Agent
-- ------------------------------------------------------
INSERT INTO cc_config (config_title ,config_key ,config_value ,config_description ,config_valuetype ,config_group_id ,config_listvalues)
VALUES ( 'Enable CDR local cache', 'cache_enabled', '0', 'If you want enabled the local cache to save the CDR in a SQLite Database.', '1', '1', 'yes,no'),
( 'Path for the CDR cache file', 'cache_path', '/etc/asterisk/cache_a2billing', 'Defined the file that you want use for the CDR cache to save the CDR in a local SQLite database.', '0', '1', NULL);


ALTER TABLE cc_logrefill ADD COLUMN refill_type TINYINT NOT NULL DEFAULT 0;
ALTER TABLE cc_logpayment ADD COLUMN payment_type TINYINT NOT NULL DEFAULT 0;


-- ------------------------------------------------------
-- Add management of the web customer in groups
-- ------------------------------------------------------
ALTER TABLE cc_card_group ADD users_perms INT NOT NULL DEFAULT '0';



-- ------------------------------------------------------
-- PNL report
-- ------------------------------------------------------
INSERT INTO  cc_config(config_title,config_key,config_value,config_description,config_valuetype,config_group_id) values 
('PNL Pay Phones','report_pnl_pay_phones','(8887798764,0.02,0.06)','Info for PNL report. Must be in form "(number1,buycost,sellcost),(number2,buycost,sellcost)", number can be prefix, i.e 1800',0,8);
INSERT INTO  cc_config(config_title,config_key,config_value,config_description,config_valuetype,config_group_id) values
('PNL Toll Free Numbers','report_pnl_toll_free','(6136864646,0.1,0),(6477249717,0.1,0)','Info for PNL report. must be in form "(number1,buycost,sellcost),(number2,buycost,sellcost)", number can be prefix, i.e 1800',0,8);



-- ------------------------------------------------------
-- Update to use VarChar instead of Char
-- ------------------------------------------------------
ALTER TABLE cc_call CHANGE sessionid sessionid VARCHAR( 40 ) NOT NULL;
ALTER TABLE cc_call CHANGE uniqueid uniqueid VARCHAR( 30 ) NOT NULL;
ALTER TABLE cc_call CHANGE nasipaddress nasipaddress VARCHAR( 30 ) NOT NULL;
ALTER TABLE cc_call CHANGE calledstation calledstation VARCHAR( 30 ) NOT NULL;
ALTER TABLE cc_call CHANGE src src VARCHAR( 40 ) NOT NULL;
ALTER TABLE cc_call CHANGE dnid dnid VARCHAR( 40 ) NOT NULL;

ALTER TABLE cc_card CHANGE username username VARCHAR( 50 ) NOT NULL;
ALTER TABLE cc_card CHANGE useralias useralias VARCHAR( 50 ) NOT NULL;
ALTER TABLE cc_card CHANGE uipass uipass VARCHAR( 50 ) NOT NULL;
ALTER TABLE cc_card CHANGE lastname lastname VARCHAR( 50 ) NOT NULL;
ALTER TABLE cc_card CHANGE firstname firstname VARCHAR( 50 ) NOT NULL;
ALTER TABLE cc_card CHANGE address address VARCHAR( 100 ) NOT NULL;
ALTER TABLE cc_card CHANGE city city VARCHAR( 40 ) NOT NULL;
ALTER TABLE cc_card CHANGE state state VARCHAR( 40 ) NOT NULL;
ALTER TABLE cc_card CHANGE country country VARCHAR( 40 ) NOT NULL;
ALTER TABLE cc_card CHANGE zipcode zipcode VARCHAR( 20 ) NOT NULL;
ALTER TABLE cc_card CHANGE phone phone VARCHAR( 20 ) NOT NULL;
ALTER TABLE cc_card CHANGE email email VARCHAR( 70 ) NOT NULL;
ALTER TABLE cc_card CHANGE fax fax VARCHAR( 20 ) NOT NULL;
ALTER TABLE cc_card CHANGE redial redial VARCHAR( 50 ) NOT NULL;
ALTER TABLE cc_card CHANGE callback callback VARCHAR( 50 ) NOT NULL;
ALTER TABLE cc_card CHANGE loginkey loginkey VARCHAR( 40 ) NOT NULL;
ALTER TABLE cc_card CHANGE tag tag VARCHAR( 50 ) NOT NULL;
ALTER TABLE cc_card CHANGE email_notification email_notification VARCHAR( 70 ) NOT NULL;
ALTER TABLE cc_card CHANGE company_name company_name VARCHAR( 50 ) NOT NULL;
ALTER TABLE cc_card CHANGE company_website company_website VARCHAR( 60 ) NOT NULL;
ALTER TABLE cc_card CHANGE vat_rn vat_rn VARCHAR( 40 ) NOT NULL;
ALTER TABLE cc_card CHANGE traffic_target traffic_target VARCHAR( 300 ) NOT NULL;

ALTER TABLE cc_callerid CHANGE cid cid VARCHAR( 100 ) NOT NULL;


ALTER TABLE cc_iax_buddies CHANGE name name VARCHAR(80) NOT NULL;
ALTER TABLE cc_iax_buddies CHANGE accountcode accountcode VARCHAR(20) NOT NULL;
ALTER TABLE cc_iax_buddies CHANGE regexten regexten VARCHAR(20) NOT NULL;
ALTER TABLE cc_iax_buddies CHANGE callerid callerid VARCHAR(80) NOT NULL;
ALTER TABLE cc_iax_buddies CHANGE context context VARCHAR(80) NOT NULL;
ALTER TABLE cc_iax_buddies CHANGE fromuser fromuser VARCHAR(80) NOT NULL;
ALTER TABLE cc_iax_buddies CHANGE fromdomain fromdomain VARCHAR(80) NOT NULL;
ALTER TABLE cc_iax_buddies CHANGE host host VARCHAR(31) NOT NULL;
ALTER TABLE cc_iax_buddies CHANGE insecure insecure VARCHAR(20) NOT NULL;
ALTER TABLE cc_iax_buddies CHANGE mailbox mailbox VARCHAR(50) NOT NULL;
ALTER TABLE cc_iax_buddies CHANGE md5secret md5secret VARCHAR(80) NOT NULL;
ALTER TABLE cc_iax_buddies CHANGE permit permit VARCHAR(95) NOT NULL;
ALTER TABLE cc_iax_buddies CHANGE deny deny VARCHAR(95) NOT NULL;
ALTER TABLE cc_iax_buddies CHANGE mask mask VARCHAR(95) NOT NULL;
ALTER TABLE cc_iax_buddies CHANGE secret secret VARCHAR(80) NOT NULL;
ALTER TABLE cc_iax_buddies CHANGE username username VARCHAR(80) NOT NULL;
ALTER TABLE cc_iax_buddies CHANGE disallow disallow VARCHAR(100) NOT NULL;
ALTER TABLE cc_iax_buddies CHANGE allow allow VARCHAR(100) NOT NULL;
ALTER TABLE cc_iax_buddies CHANGE musiconhold musiconhold VARCHAR(100) NOT NULL;
ALTER TABLE cc_iax_buddies CHANGE canreinvite canreinvite VARCHAR(20) NOT NULL;

ALTER TABLE cc_sip_buddies CHANGE name name VARCHAR(80) NOT NULL;
ALTER TABLE cc_sip_buddies CHANGE accountcode accountcode VARCHAR(20) NOT NULL;
ALTER TABLE cc_sip_buddies CHANGE regexten regexten VARCHAR(20) NOT NULL;
ALTER TABLE cc_sip_buddies CHANGE callerid callerid VARCHAR(80) NOT NULL;
ALTER TABLE cc_sip_buddies CHANGE context context VARCHAR(80) NOT NULL;
ALTER TABLE cc_sip_buddies CHANGE fromuser fromuser VARCHAR(80) NOT NULL;
ALTER TABLE cc_sip_buddies CHANGE fromdomain fromdomain VARCHAR(80) NOT NULL;
ALTER TABLE cc_sip_buddies CHANGE host host VARCHAR(31) NOT NULL;
ALTER TABLE cc_sip_buddies CHANGE insecure insecure VARCHAR(20) NOT NULL;
ALTER TABLE cc_sip_buddies CHANGE mailbox mailbox VARCHAR(50) NOT NULL;
ALTER TABLE cc_sip_buddies CHANGE md5secret md5secret VARCHAR(80) NOT NULL;
ALTER TABLE cc_sip_buddies CHANGE permit permit VARCHAR(95) NOT NULL;
ALTER TABLE cc_sip_buddies CHANGE deny deny VARCHAR(95) NOT NULL;
ALTER TABLE cc_sip_buddies CHANGE mask mask VARCHAR(95) NOT NULL;
ALTER TABLE cc_sip_buddies CHANGE secret secret VARCHAR(80) NOT NULL;
ALTER TABLE cc_sip_buddies CHANGE username username VARCHAR(80) NOT NULL;
ALTER TABLE cc_sip_buddies CHANGE disallow disallow VARCHAR(100) NOT NULL;
ALTER TABLE cc_sip_buddies CHANGE allow allow VARCHAR(100) NOT NULL;
ALTER TABLE cc_sip_buddies CHANGE musiconhold musiconhold VARCHAR(100) NOT NULL;
ALTER TABLE cc_sip_buddies CHANGE fullcontact fullcontact VARCHAR(80) NOT NULL;
ALTER TABLE cc_sip_buddies CHANGE setvar setvar VARCHAR(100) NOT NULL;
ALTER TABLE cc_sip_buddies CHANGE canreinvite canreinvite VARCHAR(20) NOT NULL;


-- ------------------------------------------------------
-- Add restricted rules on the call system for customers 
-- ------------------------------------------------------

CREATE TABLE cc_restricted_phonenumber (
	id BIGINT NOT NULL AUTO_INCREMENT ,
	number VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
	id_card BIGINT NOT NULL,
	PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


ALTER TABLE cc_card ADD restriction TINYINT NOT NULL DEFAULT '0';

-- remove callback from card
ALTER TABLE cc_card DROP COLUMN callback;

-- ADD IAX TRUNKING
ALTER TABLE cc_iax_buddies ADD trunk CHAR(3) DEFAULT 'no';

-- Refactor Agent Section
ALTER TABLE cc_card DROP id_agent;
ALTER TABLE cc_card_group ADD id_agent INT NOT NULL DEFAULT '0';

-- remove old template invoice
ALTER TABLE cc_card DROP template_invoice;
ALTER TABLE cc_card DROP template_outstanding;

-- rename vat field
ALTER TABLE cc_card CHANGE VAT_RN vat_rn VARCHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL;

-- add amount
ALTER TABLE cc_phonenumber ADD amount INT NOT NULL DEFAULT '0';


-- add company to Agent
ALTER TABLE cc_agent ADD COLUMN company varchar(50);


-- Change AGI Verbosity & logging
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) 
VALUES ('Verbosity', 'verbosity_level', '0', '0 = FATAL; 1 = ERROR; WARN = 2 ; INFO = 3 ; DEBUG = 4', 0, 11, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) 
VALUES ('Logging', 'logging_level', '3', '0 = FATAL; 1 = ERROR; WARN = 2 ; INFO = 3 ; DEBUG = 4', 0, 11, NULL);


ALTER TABLE cc_ticket ADD creator_type TINYINT NOT NULL DEFAULT '0';
ALTER TABLE cc_ticket_comment CHANGE is_admin creator_type TINYINT NOT NULL DEFAULT '0';

ALTER TABLE cc_ratecard ADD COLUMN announce_time_correction decimal(5,3) NOT NULL DEFAULT 1.0;


ALTER TABLE cc_agent DROP climit;

CREATE TABLE cc_agent_cardgroup (
	id_agent INT NOT NULL ,
	id_card_group INT NOT NULL ,
	PRIMARY KEY ( id_agent , id_card_group )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE cc_card_group DROP id_agent;

ALTER TABLE cc_agent ADD secret VARCHAR( 20 ) NOT NULL;

-- optimization on CDR
ALTER TABLE cc_ratecard DROP destination;
ALTER TABLE cc_call DROP id_cc_prefix;
ALTER TABLE cc_ratecard DROP id_cc_prefix;
ALTER TABLE cc_call ADD COLUMN destination INT (11) DEFAULT 0;
ALTER TABLE cc_ratecard ADD COLUMN destination INT (11) DEFAULT 0;


UPDATE cc_card_group SET description = 'This group is the default group used when you create a customer. It''s forbidden to delete it because you need at least one group but you can edit it.' WHERE id = 1 LIMIT 1 ;
UPDATE cc_card_group SET users_perms = '129022' WHERE id = 1;

ALTER TABLE cc_ticket ADD viewed_cust TINYINT NOT NULL DEFAULT '1',
ADD viewed_agent TINYINT NOT NULL DEFAULT '1',
ADD viewed_admin TINYINT NOT NULL DEFAULT '1';


ALTER TABLE cc_ticket_comment ADD viewed_cust TINYINT NOT NULL DEFAULT '1',
ADD viewed_agent TINYINT NOT NULL DEFAULT '1',
ADD viewed_admin TINYINT NOT NULL DEFAULT '1';

ALTER TABLE cc_ui_authen ADD email VARCHAR( 70 ) CHARACTER SET utf8 COLLATE utf8_bin NULL ;

ALTER TABLE cc_logrefill CHANGE id id BIGINT NOT NULL AUTO_INCREMENT  ;

-- Refill table for Agent
CREATE TABLE cc_logrefill_agent (
	id BIGINT NOT NULL auto_increment,
	date timestamp NOT NULL default CURRENT_TIMESTAMP,
	credit float NOT NULL,
	agent_id BIGINT NOT NULL,
	description mediumtext collate utf8_bin,
	refill_type tinyint NOT NULL default '0',
	PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- logpayment table for Agent
CREATE TABLE cc_logpayment_agent (
	id BIGINT NOT NULL auto_increment,
	date timestamp NOT NULL default CURRENT_TIMESTAMP,
	payment float NOT NULL,
	agent_id BIGINT NOT NULL,
	id_logrefill BIGINT default NULL,
	description mediumtext collate utf8_bin,
	added_refill tinyint NOT NULL default '0',
	payment_type tinyint NOT NULL default '0',
	PRIMARY KEY  (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


-- Table structure for table cc_prefix
DROP TABLE IF EXISTS cc_prefix;
CREATE TABLE IF NOT EXISTS cc_prefix (
	prefix bigint(20) NOT NULL auto_increment,
	destination varchar(60) collate utf8_bin NOT NULL,
	PRIMARY KEY (prefix),
	KEY destination (destination)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



INSERT INTO cc_config_group (group_title ,group_description) VALUES ( 'dashboard', 'This configuration group handles the dashboard configuration');

INSERT INTO cc_config (config_title ,config_key ,config_value ,config_description ,config_valuetype ,config_group_id ,config_listvalues)
VALUES ( 'Enable info module about customers', 'customer_info_enabled', 'LEFT', 'If you want enabled the info module customer and place it somewhere on the home page.', '0', '13', 'NONE,LEFT,CENTER,RIGHT');
INSERT INTO cc_config (config_title ,config_key ,config_value ,config_description ,config_valuetype ,config_group_id ,config_listvalues)
VALUES ( 'Enable info module about refills', 'refill_info_enabled', 'CENTER', 'If you want enabled the info module refills and place it somewhere on the home page.', '0', '13', 'NONE,LEFT,CENTER,RIGHT');
INSERT INTO cc_config (config_title ,config_key ,config_value ,config_description ,config_valuetype ,config_group_id ,config_listvalues)
VALUES ( 'Enable info module about payments', 'payment_info_enabled', 'CENTER', 'If you want enabled the info module payments and place it somewhere on the home page.', '0', '13', 'NONE,LEFT,CENTER,RIGHT');
INSERT INTO cc_config (config_title ,config_key ,config_value ,config_description ,config_valuetype ,config_group_id ,config_listvalues)
VALUES ( 'Enable info module about calls', 'call_info_enabled', 'RIGHT', 'If you want enabled the info module calls and place it somewhere on the home page.', '0', '13', 'NONE,LEFT,CENTER,RIGHT');


-- New Invoice Tables
RENAME TABLE cc_invoices  TO bkp_cc_invoices;
RENAME TABLE cc_invoice  TO bkp_cc_invoice;
RENAME TABLE cc_invoice_history  TO bkp_cc_invoice_history;
RENAME TABLE cc_invoice_items  TO bkp_cc_invoice_items;

CREATE TABLE cc_invoice (
	id BIGINT NOT NULL AUTO_INCREMENT ,
	reference VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_bin NULL ,
	id_card BIGINT NOT NULL ,
	date timestamp NOT NULL default CURRENT_TIMESTAMP,
	paid_status TINYINT NOT NULL DEFAULT '0',
	status TINYINT NOT NULL DEFAULT '0',
	title VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
	description MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
	PRIMARY KEY ( id ) ,
	UNIQUE (reference)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE cc_invoice_item (
	id BIGINT NOT NULL AUTO_INCREMENT ,
	id_invoice BIGINT NOT NULL ,
	date timestamp NOT NULL default CURRENT_TIMESTAMP,
	price DECIMAL( 15, 5 ) NOT NULL DEFAULT '0',
	VAT DECIMAL( 4, 2 ) NOT NULL DEFAULT '0',
	description MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
	PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


CREATE TABLE cc_invoice_conf (
	id INT NOT NULL AUTO_INCREMENT ,
	key_val VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
	value VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
	PRIMARY KEY ( id ),
	UNIQUE (key_val)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO cc_invoice_conf (key_val ,value) 
	VALUES 	('company_name', 'My company'),
		('address', 'address'),
		('zipcode', 'xxxx'),
		('country', 'country'), 
		('city', 'city'), 
		('phone', 'xxxxxxxxxxx'), 
		('fax', 'xxxxxxxxxxx'), 
		('email', 'xxxxxxx@xxxxxxx.xxx'),
		('vat', 'xxxxxxxxxx'),
		('web', 'www.xxxxxxx.xxx');

ALTER TABLE cc_logrefill ADD added_invoice TINYINT NOT NULL DEFAULT '0';

CREATE TABLE cc_invoice_payment (
	id_invoice BIGINT NOT NULL ,
	id_payment BIGINT NOT NULL ,
	PRIMARY KEY ( id_invoice , id_payment )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description, set_function) values ('Enable PlugnPay Module', 'MODULE_PAYMENT_PLUGNPAY_STATUS', 'True', 'Do you want to accept payments through PlugnPay?', 'tep_cfg_select_option(array(\'True\', \'False\'), ');
INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description) values ('Login Username', 'MODULE_PAYMENT_PLUGNPAY_LOGIN', 'Your Login Name', 'Enter your PlugnPay account username');
INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description) values ('Publisher Email', 'MODULE_PAYMENT_PLUGNPAY_PUBLISHER_EMAIL', 'Enter Your Email Address', 'The email address you want PlugnPay conformations sent to');
INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description, set_function) values ('cURL Setup', 'MODULE_PAYMENT_PLUGNPAY_CURL', 'Not Compiled', 'Whether cURL is compiled into PHP or not.  Windows users, select not compiled.', 'tep_cfg_select_option(array(\'Not Compiled\', \'Compiled\'), ');
INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description) values ('cURL Path', 'MODULE_PAYMENT_PLUGNPAY_CURL_PATH', 'The Path To cURL', 'For Not Compiled mode only, input path to the cURL binary (i.e. c:/curl/curl)');
INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description, set_function) values ('Transaction Mode', 'MODULE_PAYMENT_PLUGNPAY_TESTMODE', 'Test', 'Transaction mode used for processing orders', 'tep_cfg_select_option(array(\'Test\', \'Test And Debug\', \'Production\'), ');
INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description, set_function) values ('Require CVV', 'MODULE_PAYMENT_PLUGNPAY_CVV', 'yes', 'Ask For CVV information', 'tep_cfg_select_option(array(\'yes\', \'no\'), ');
INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description, set_function) values ('Transaction Method', 'MODULE_PAYMENT_PLUGNPAY_PAYMETHOD', 'credit', 'Transaction method used for processing orders.<br><b>NOTE:</b> Selecting \'onlinecheck\' assumes you\'ll offer \'credit\' as well.',  'tep_cfg_select_option(array(\'credit\', \'onlinecheck\'), ');
INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description, set_function) values ('Authorization Type', 'MODULE_PAYMENT_PLUGNPAY_CCMODE', 'authpostauth', 'Credit card processing mode', 'tep_cfg_select_option(array(\'authpostauth\', \'authonly\'), ');
INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description, set_function) values ('Customer Notifications', 'MODULE_PAYMENT_PLUGNPAY_DONTSNDMAIL', 'yes', 'Should PlugnPay not email a receipt to the customer?', 'tep_cfg_select_option(array(\'yes\', \'no\'), ');
INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description, set_function) values ('Accepted Credit Cards', 'MODULE_PAYMENT_PLUGNPAY_ACCEPTED_CC', 'Mastercard, Visa', 'The credit cards you currently accept', '_selectOptions(array(\'Amex\',\'Discover\', \'Mastercard\', \'Visa\'), ');


INSERT INTO cc_payment_methods (payment_method,payment_filename,active) VALUES ('plugnpay','plugnpay.php','t');





ALTER TABLE cc_card_archive DROP COLUMN  callback;
-- already present ALTER TABLE cc_card_archive ADD COLUMN  id_timezone int(11) default '0';
ALTER TABLE cc_card_archive ADD COLUMN  voicemail_permitted int(11) NOT NULL default '0';
ALTER TABLE cc_card_archive ADD COLUMN  voicemail_activated smallint(6) NOT NULL default '0';
ALTER TABLE cc_card_archive ADD COLUMN  last_notification timestamp NULL default NULL;
ALTER TABLE cc_card_archive ADD COLUMN  email_notification char(70) collate utf8_bin default NULL;
ALTER TABLE cc_card_archive ADD COLUMN  notify_email smallint(6) NOT NULL default '0';
ALTER TABLE cc_card_archive ADD COLUMN  credit_notification int(11) NOT NULL default '-1';
ALTER TABLE cc_card_archive ADD COLUMN  id_group int(11) NOT NULL default '1';
ALTER TABLE cc_card_archive ADD COLUMN  company_name varchar(50) collate utf8_bin default NULL;
ALTER TABLE cc_card_archive ADD COLUMN  company_website varchar(60) collate utf8_bin default NULL;
ALTER TABLE cc_card_archive ADD COLUMN  VAT_RN varchar(40) collate utf8_bin default NULL;
ALTER TABLE cc_card_archive ADD COLUMN  traffic bigint(20) default NULL;
ALTER TABLE cc_card_archive ADD COLUMN  traffic_target mediumtext collate utf8_bin;
ALTER TABLE cc_card_archive ADD COLUMN  discount decimal(5,2) NOT NULL default '0.00';
ALTER TABLE cc_card_archive ADD COLUMN  restriction tinyint(4) NOT NULL default '0';
ALTER TABLE cc_card_archive DROP COLUMN template_invoice;
ALTER TABLE cc_card_archive DROP COLUMN template_outstanding;
ALTER TABLE cc_card_archive DROP COLUMN mac_addr;
ALTER TABLE cc_card_archive ADD COLUMN mac_addr char(17) collate utf8_bin NOT NULL default '00-00-00-00-00-00';

CREATE TABLE cc_billing_customer (
	id BIGINT NOT NULL AUTO_INCREMENT,
	id_card BIGINT NOT NULL ,
	date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
	id_invoice BIGINT NOT NULL ,
	PRIMARY KEY ( id )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- PLUGNPAY
ALTER TABLE cc_epayment_log ADD COLUMN cvv VARCHAR(4);
ALTER TABLE cc_epayment_log ADD COLUMN credit_card_type VARCHAR(20);
ALTER TABLE cc_epayment_log ADD COLUMN currency VARCHAR(4);


INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) 
VALUES ('PlugnPay Payment URL', 'plugnpay_payment_url', 'https://pay1.plugnpay.com/payment/pnpremote.cgi', 'Define here the URL of PlugnPay gateway.', 0, 5, NULL);


-- Currency handle update
UPDATE cc_configuration SET configuration_description = 'The alternative currency to use for credit card transactions if the system currency is not usable' WHERE configuration_key = 'MODULE_PAYMENT_PAYPAL_CURRENCY';
UPDATE cc_configuration SET configuration_title = 'Alternative Transaction Currency' WHERE configuration_key = 'MODULE_PAYMENT_PAYPAL_CURRENCY';
UPDATE cc_configuration SET configuration_description = 'The alternative currency to use for credit card transactions if the system currency is not usable' WHERE configuration_key = 'MODULE_PAYMENT_MONEYBOOKERS_CURRENCY';
UPDATE cc_configuration SET configuration_title = 'Alternative Transaction Currency' WHERE configuration_key = 'MODULE_PAYMENT_MONEYBOOKERS_CURRENCY';
UPDATE cc_configuration SET set_function = 'tep_cfg_select_option(array(''USD'',''CAD'',''EUR'',''GBP'',''JPY''), ' WHERE configuration_key = 'MODULE_PAYMENT_PAYPAL_CURRENCY';
UPDATE cc_configuration SET set_function = 'tep_cfg_select_option(array(''EUR'', ''USD'', ''GBP'', ''HKD'', ''SGD'', ''JPY'', ''CAD'', ''AUD'', ''CHF'', ''DKK'', ''SEK'', ''NOK'', ''ILS'', ''MYR'', ''NZD'', ''TWD'', ''THB'', ''CZK'', ''HUF'', ''SKK'', ''ISK'', ''INR''), '  WHERE configuration_key = 'MODULE_PAYMENT_MONEYBOOKERS_CURRENCY';

ALTER TABLE cc_payment_methods DROP active;


ALTER TABLE cc_epayment_log ADD transaction_detail LONGTEXT NULL;

ALTER TABLE cc_invoice_item ADD id_billing BIGINT NULL ,
ADD billing_type VARCHAR( 10 ) NULL ;



-- DIDX.NET 
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('DIDX ID', 'didx_id', '708XXX', 'DIDX parameter : ID', 0, 8, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('DIDX PASS', 'didx_pass', 'XXXXXXXXXX', 'DIDX parameter : Password', 0, 8, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('DIDX MIN RATING', 'didx_min_rating', '0', 'DIDX parameter : min rating', 0, 8, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('DIDX RING TO', 'didx_ring_to', '0', 'DIDX parameter : ring to', 0, 8, NULL);

-- Commission Agent
CREATE TABLE cc_agent_commission (
	id BIGINT NOT NULL AUTO_INCREMENT ,
	id_payment BIGINT NULL ,
	id_card BIGINT NOT NULL ,
	date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
	amount DECIMAL( 15, 5 ) NOT NULL ,
	PRIMARY KEY ( id )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE cc_card_group ADD id_agent INT NULL ;

DROP TABLE cc_agent_cardgroup;

ALTER TABLE cc_agent_commission ADD paid_status TINYINT NOT NULL DEFAULT '0';
ALTER TABLE cc_agent_commission ADD description MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NULL ;





-- Card Serial Number
CREATE TABLE cc_card_seria (
	id INT NOT NULL AUTO_INCREMENT ,
	name CHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
	description MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NULL,
	value	BIGINT NOT NULL DEFAULT 0,
	PRIMARY KEY ( id )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
 
ALTER TABLE cc_card ADD id_seria integer;
ALTER TABLE cc_card ADD serial BIGINT;
UPDATE cc_config SET config_description = concat(config_description,', id_seria, serial') WHERE config_key = 'card_show_field_list' ;

DELIMITER //
CREATE TRIGGER cc_card_serial_set BEFORE INSERT ON cc_card
FOR EACH ROW
BEGIN
	UPDATE cc_card_seria set value=value+1  where id=NEW.id_seria ;
	SELECT value INTO @serial from cc_card_seria where id=NEW.id_seria ;
	SET NEW.serial=@serial;
END
//
CREATE TRIGGER cc_card_serial_update BEFORE UPDATE ON cc_card
FOR EACH ROW
BEGIN
	IF NEW.id_seria<>OLD.id_seria OR OLD.id_seria IS NULL THEN
		UPDATE cc_card_seria set value=value+1  where id=NEW.id_seria ;
		SELECT value INTO @serial from cc_card_seria where id=NEW.id_seria ;
		SET NEW.serial=@serial;
	END IF;
END
//
DELIMITER ;
 

INSERT INTO  cc_config (config_title,config_key,config_value,config_description,config_valuetype,config_group_id) values('Card Serial Pad Length','card_serial_length','7','Value of zero padding for serial. If this value set to 3 serial wil looks like 001',0,8);



-- Reserve credit :
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_id, config_listvalues) VALUES ('Dial Balance reservation', 'dial_balance_reservation', '0.25', 'Credit to reserve from the balance when a call is made. This will prevent negative balance on huge peak.', 0, 11, NULL);


-- change the schema to authorize only one login
ALTER TABLE cc_agent ADD UNIQUE (login); 
ALTER TABLE cc_ui_authen ADD UNIQUE (login); 

-- update for invoice
ALTER TABLE cc_charge ADD charged_status TINYINT NOT NULL DEFAULT '0',
ADD invoiced_status TINYINT NOT NULL DEFAULT '0';
ALTER TABLE cc_did_use ADD reminded TINYINT NOT NULL DEFAULT '0';

ALTER TABLE cc_invoice_item CHANGE id_billing id_ext BIGINT( 20 ) NULL DEFAULT NULL;
ALTER TABLE cc_invoice_item CHANGE billing_type type_ext VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL;  



-- update on configuration
ALTER TABLE cc_config_group ADD UNIQUE (group_title); 
ALTER TABLE cc_config ADD config_group_title varchar(64) NOT NULL;

UPDATE cc_config SET config_group_title=(SELECT group_title FROM cc_config_group WHERE cc_config_group.id=cc_config.config_group_id);

ALTER TABLE cc_config DROP COLUMN config_group_id;


-- add receipt objects
CREATE TABLE cc_receipt (
	id BIGINT NOT NULL AUTO_INCREMENT ,
	id_card BIGINT NOT NULL ,
	date timestamp NOT NULL default CURRENT_TIMESTAMP,
	title VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
	description MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
	status TINYINT NOT NULL DEFAULT '0',
	PRIMARY KEY ( id )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE cc_receipt_item (
	id BIGINT NOT NULL AUTO_INCREMENT ,
	id_receipt BIGINT NOT NULL ,
	date timestamp NOT NULL default CURRENT_TIMESTAMP,
	price DECIMAL( 15, 5 ) NOT NULL DEFAULT '0',
	description MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
	id_ext BIGINT( 20 ) NULL DEFAULT NULL,
	type_ext VARCHAR( 10 ) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL,
	PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


ALTER TABLE cc_logpayment CHANGE payment payment DECIMAL( 15, 5 ) NOT NULL;
ALTER TABLE cc_logpayment_agent CHANGE payment payment DECIMAL( 15, 5 ) NOT NULL;  
ALTER TABLE cc_logrefill CHANGE credit credit DECIMAL( 15, 5 ) NOT NULL;
ALTER TABLE cc_logrefill_agent CHANGE credit credit DECIMAL( 15, 5 ) NOT NULL ;


-- changes from recurring services - bound to callplan
alter table cc_service add column operate_mode tinyint default 0;
alter table cc_service add column dialplan integer default 0;
alter table cc_service add column use_group tinyint default 0;

INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_title, config_listvalues) VALUES ('Rate Export Fields', 'rate_export_field_list', 'destination, dialprefix, rateinitial', 'Fields to export in csv format from rates table.Use dest_name from prefix name', 0, 'webui', NULL); 



-- ADD SIP REGSERVER
ALTER TABLE cc_sip_buddies ADD regserver varchar(20);


ALTER TABLE cc_logpayment ADD added_commission TINYINT NOT NULL DEFAULT '0';
-- Empty password view for OpenSips
CREATE VIEW cc_sip_buddies_empty AS SELECT
id, id_cc_card, name, accountcode, regexten, amaflags, callgroup, callerid, canreinvite, context, DEFAULTip, dtmfmode, fromuser, fromdomain, host, insecure, language, mailbox, md5secret, nat, permit, deny, mask, pickupgroup, port, qualify, restrictcid, rtptimeout, rtpholdtimeout, '' as secret, type, username, disallow, allow, musiconhold, regseconds, ipaddr, cancallforward, fullcontact, setvar
FROM cc_sip_buddies;


-- remove activatedbyuser
ALTER TABLE cc_card DROP activatedbyuser;


-- Agent epayment

INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_title, config_listvalues) VALUES ('HTTP Server Agent', 'http_server_agent', 'http://www.mydomainname.com', 'Set the Server Address of Agent Website, It should be empty for productive Servers.', 0, 'epayment_method', NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_title, config_listvalues) VALUES ('HTTPS Server Agent', 'https_server_agent', 'https://www.mydomainname.com', 'https://localhost - Enter here your Secure Agents Server Address, should not be empty for productive servers.', 0, 'epayment_method', NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_title, config_listvalues) VALUES ('Server Agent IP/Domain', 'http_cookie_domain_agent', '26.63.165.200', 'Enter your Domain Name or IP Address for the Agents application, eg, 26.63.165.200.', 0, 5, NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_title, config_listvalues) VALUES ('Secure Server Agent IP/Domain', 'https_cookie_domain_agent', '26.63.165.200', 'Enter your Secure server Domain Name or IP Address for the Agents application, eg, 26.63.165.200.', 0, 'epayment_method', NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_title, config_listvalues) VALUES ('Application Agent Path', 'http_cookie_path_agent', '/agent/Public/', 'Enter the Physical path of your Agents Application on your server.', 0, 'epayment_method', NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_title, config_listvalues) VALUES ('Secure Application Agent Path', 'https_cookie_path_agent', '/agent/Public/', 'Enter the Physical path of your Agents Application on your Secure Server.', 0, 'epayment_method', NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_title, config_listvalues) VALUES ('Application Agent Physical Path', 'dir_ws_http_catalog_agent', '/agent/Public/', 'Enter the Physical path of your Agents Application on your server.', 0, 'epayment_method', NULL);
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_group_title, config_listvalues) VALUES ('Secure Application Agent Physical Path', 'dir_ws_https_catalog_agent', '/agent/Public/', 'Enter the Physical path of your Agents Application on your Secure server.', 0, 'epayment_method', NULL);

CREATE TABLE cc_epayment_log_agent (
	id BIGINT NOT NULL auto_increment,
	agent_id BIGINT NOT NULL default '0',
	amount DECIMAL( 15, 5 ) NOT NULL default '0',
	vat FLOAT NOT NULL default '0',
	paymentmethod char(50) collate utf8_bin NOT NULL,
	cc_owner varchar(64) collate utf8_bin default NULL,
	cc_number varchar(32) collate utf8_bin default NULL,
	cc_expires varchar(7) collate utf8_bin default NULL,
	creationdate timestamp NOT NULL default CURRENT_TIMESTAMP,
	`status` int(11) NOT NULL default '0',
	cvv varchar(4) collate utf8_bin default NULL,
	credit_card_type varchar(20) collate utf8_bin default NULL,
	currency varchar(4) collate utf8_bin default NULL,
	transaction_detail longtext collate utf8_bin,
	PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE cc_epayment_log CHANGE id id BIGINT NOT NULL AUTO_INCREMENT ,
	CHANGE cardid cardid BIGINT NOT NULL DEFAULT '0',
	CHANGE amount amount DECIMAL( 15, 5 ) NOT NULL DEFAULT '0';

ALTER TABLE cc_payments CHANGE id id BIGINT NOT NULL AUTO_INCREMENT ,
	CHANGE customers_id customers_id BIGINT NOT NULL DEFAULT '0';

CREATE TABLE cc_payments_agent (
	id BIGINT NOT NULL auto_increment,
	agent_id BIGINT collate utf8_bin NOT NULL,
	agent_name varchar(200) collate utf8_bin NOT NULL,
	agent_email_address varchar(96) collate utf8_bin NOT NULL,
	item_name varchar(127) collate utf8_bin default NULL,
	item_id varchar(127) collate utf8_bin default NULL,
	item_quantity int(11) NOT NULL default '0',
	payment_method varchar(32) collate utf8_bin NOT NULL,
	cc_type varchar(20) collate utf8_bin default NULL,
	cc_owner varchar(64) collate utf8_bin default NULL,
	cc_number varchar(32) collate utf8_bin default NULL,
	cc_expires varchar(4) collate utf8_bin default NULL,
	orders_status int(5) NOT NULL,
	orders_amount decimal(14,6) default NULL,
	last_modified datetime default NULL,
	date_purchased datetime default NULL,
	orders_date_finished datetime default NULL,
	currency char(3) collate utf8_bin default NULL,
	currency_value decimal(14,6) default NULL,
	PRIMARY KEY (id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


ALTER TABLE cc_agent_commission ADD id_agent INT NOT NULL ;

-- remove reseller field from logpayment & log refill
ALTER TABLE cc_logpayment DROP reseller_id; 
ALTER TABLE cc_logrefill DROP reseller_id;


-- Add notification system
CREATE TABLE cc_notification (
	id 					BIGINT NOT NULL auto_increment,
	key_value 			varchar(40) collate utf8_bin default NULL,
	date 				timestamp NOT NULL default CURRENT_TIMESTAMP,
	priority 			TINYINT NOT NULL DEFAULT '0',
	from_type 			TINYINT NOT NULL ,
	from_id 			BIGINT NULL DEFAULT '0',
	PRIMARY KEY ( id )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

CREATE TABLE cc_notification_admin (
	id_notification BIGINT NOT NULL ,
	id_admin INT NOT NULL ,
	viewed TINYINT NOT NULL DEFAULT '0',
	PRIMARY KEY ( id_notification , id_admin )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


-- Add default value for support box
INSERT INTO cc_support (id ,name) VALUES (1, 'DEFAULT');
INSERT INTO cc_support_component (id ,id_support ,name ,activated) VALUES (1, 1, 'DEFAULT', 1);

DELETE FROM cc_config WHERE config_key = 'sipiaxinfo' AND config_group_title = 'webcustomerui';
DELETE FROM cc_config WHERE config_key = 'cdr' AND config_group_title = 'webcustomerui';
DELETE FROM cc_config WHERE config_key = 'invoice' AND config_group_title = 'webcustomerui';
DELETE FROM cc_config WHERE config_key = 'voucher' AND config_group_title = 'webcustomerui';
DELETE FROM cc_config WHERE config_key = 'paypal' AND config_group_title = 'webcustomerui';
DELETE FROM cc_config WHERE config_key = 'speeddial' AND config_group_title = 'webcustomerui';
DELETE FROM cc_config WHERE config_key = 'did' AND config_group_title = 'webcustomerui';
DELETE FROM cc_config WHERE config_key = 'ratecard' AND config_group_title = 'webcustomerui';
DELETE FROM cc_config WHERE config_key = 'simulator' AND config_group_title = 'webcustomerui';
DELETE FROM cc_config WHERE config_key = 'callback' AND config_group_title = 'webcustomerui';
DELETE FROM cc_config WHERE config_key = 'predictivedialer' AND config_group_title = 'webcustomerui';
DELETE FROM cc_config WHERE config_key = 'callerid' AND config_group_title = 'webcustomerui';
DELETE FROM cc_config WHERE config_key = 'webphone' AND config_group_title = 'webcustomerui';
DELETE FROM cc_config WHERE config_key = 'support' AND config_group_title = 'webcustomerui';
DELETE FROM cc_config WHERE config_key = 'payment' AND config_group_title = 'webcustomerui';

INSERT INTO cc_config_group (group_title ,group_description)
	VALUES ( 'webagentui', 'This configuration group handles Web Agent Interface.');
INSERT INTO cc_config (`config_title` ,`config_key` ,`config_value` ,`config_description` ,`config_valuetype` ,`config_listvalues` ,`config_group_title`)
	VALUES ( 'Personal Info', 'personalinfo', '1', 'Enable or disable the page which allow agent to modify its personal information.', '0', 'yes,no', 'webagentui');


-- Add index for SIP / IAX Friend
ALTER TABLE cc_iax_buddies ADD INDEX ( name );
ALTER TABLE cc_iax_buddies ADD INDEX ( host );
ALTER TABLE cc_iax_buddies ADD INDEX ( ipaddr );
ALTER TABLE cc_iax_buddies ADD INDEX ( port );

ALTER TABLE cc_sip_buddies ADD INDEX ( name );
ALTER TABLE cc_sip_buddies ADD INDEX ( host );
ALTER TABLE cc_sip_buddies ADD INDEX ( ipaddr );
ALTER TABLE cc_sip_buddies ADD INDEX ( port );


-- add parameters return_url_distant_login & return_url_distant_forgetpassword on webcustomerui
INSERT INTO `cc_config` (`config_title`, `config_key`, `config_value`, `config_description`, `config_valuetype`, `config_listvalues`, `config_group_title`) VALUES('Return URL distant Login', 'return_url_distant_login', '', 'URL for specific return if an error occur after login', 0, NULL, 'webcustomerui');
INSERT INTO `cc_config` (`config_title`, `config_key`, `config_value`, `config_description`, `config_valuetype`, `config_listvalues`, `config_group_title`) VALUES('Return URL distant Forget Password', 'return_url_distant_forgetpassword', '', 'URL for specific return if an error occur after forgetpassword', 0, NULL, 'webcustomerui');

CREATE TABLE cc_agent_signup (
	id BIGINT NOT NULL AUTO_INCREMENT ,
	id_agent INT NOT NULL ,
	code VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
	id_tariffgroup INT NOT NULL ,
	id_group INT NOT NULL ,
	PRIMARY KEY (id) ,
	UNIQUE (code)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE cc_agent DROP secret;

-- disable Authorize.net
DELETE FROM cc_payment_methods WHERE payment_method = 'Authorize.Net';
UPDATE cc_configuration SET configuration_value = 'False' WHERE configuration_key = 'MODULE_PAYMENT_AUTHORIZENET_STATUS';

ALTER TABLE cc_epayment_log CHANGE amount amount VARCHAR( 50 ) NOT NULL DEFAULT '0';
ALTER TABLE cc_epayment_log_agent CHANGE amount amount VARCHAR( 50 ) NOT NULL DEFAULT '0';

UPDATE cc_config SET config_value = 'id, username, useralias, lastname, credit, tariff, activated, language, inuse, currency, sip_buddy' WHERE config_key = 'card_export_field_list';
ALTER TABLE cc_tariffgroup CHANGE id_cc_package_offer id_cc_package_offer BIGINT( 20 ) NOT NULL DEFAULT '-1';

ALTER TABLE cc_epayment_log ADD item_type VARCHAR( 30 ) CHARACTER SET utf8 COLLATE utf8_bin NULL ,ADD item_id BIGINT NULL ;


-- Last registration
ALTER TABLE cc_sip_buddies ADD lastms varchar(11);


-- Add new SMTP Settings
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES ('SMTP Port', 'smtp_port', '25', 'Port to connect on the SMTP server', 0, NULL, 'global');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES ('SMTP Secure', 'smtp_secure', '', 'sets the prefix to the SMTP server : tls ; ssl', 0, NULL, 'global');

ALTER TABLE cc_support_component ADD type_user TINYINT NOT NULL DEFAULT '2';


/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,   
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 * 
 * @copyright   Copyright (C) 2004-2009 - Star2billing S.L. 
 * @author      Belaid Arezqui <areski@gmail.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 *
 * Software License Agreement (GNU Affero General Public License)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
**/

--
-- A2Billing database script - Update database for MYSQL 5.X
-- 
-- 
-- Usage:
-- mysql -u root -p"root password" < UPDATE-a2billing-v1.4.0-to-v1.4.1.sql
--



ALTER TABLE cc_charge DROP currency;
ALTER TABLE cc_subscription_fee DROP currency;  
ALTER TABLE cc_ui_authen ADD country VARCHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_bin NULL ;
ALTER TABLE cc_ui_authen ADD city VARCHAR( 40 ) CHARACTER SET utf8 COLLATE utf8_bin NULL ;

INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES('Option CallerID update', 'callerid_update', '0', 'Prompt the caller to update his callerID', 1, 'yes,no', 'agi-conf1');

DELETE FROM cc_config WHERE config_key = 'paymentmethod' AND config_group_title = 'webcustomerui';
DELETE FROM cc_config WHERE config_key = 'personalinfo' AND config_group_title = 'webcustomerui';
DELETE FROM cc_config WHERE config_key = 'customerinfo' AND config_group_title = 'webcustomerui';
DELETE FROM cc_config WHERE config_key = 'password' AND config_group_title = 'webcustomerui';
UPDATE cc_card_group SET users_perms = '262142' WHERE cc_card_group.id = 1;


CREATE TABLE cc_subscription_signup (
	id BIGINT NOT NULL auto_increment,
	label VARCHAR( 50 ) collate utf8_bin NOT NULL ,
	id_subscription BIGINT NULL ,
	description MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_bin NULL ,
	enable TINYINT NOT NULL DEFAULT '1',
	PRIMARY KEY ( id )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DELETE FROM cc_config WHERE config_key = 'currency_cents_association';
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title)
	VALUES ('Cents Currency Associated', 'currency_cents_association', 'usd:prepaid-cents,eur:prepaid-cents,gbp:prepaid-pence,all:credit', 'Define all the audio (without file extensions) that you want to play according to cents currency (use , to separate, ie "amd:lumas").By default the file used is "prepaid-cents" .Use plural to define the cents currency sound, but import two sounds but cents currency defined : ending by ''s'' and not ending by ''s'' (i.e. for lumas , add 2 files : ''lumas'' and ''luma'') ', '0', NULL, 'ivr_creditcard');
DELETE FROM cc_config WHERE config_key = 'currency_association_minor';


-- Local Dialing Normalisation
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES 
	('Option Local Dialing', 'local_dialing_addcountryprefix', '0', 'Add the countryprefix of the user in front of the dialed number if this one have only 1 leading zero', 1, 'yes,no', 'agi-conf1');


-- Remove E-Product from 1.4.1
DROP TABLE cc_ecommerce_product;

INSERT INTO cc_invoice_conf (key_val ,`value`) VALUES ('display_account', '0');

-- add missing agent field
ALTER TABLE cc_system_log ADD agent TINYINT DEFAULT 0;

DELETE FROM cc_config WHERE config_key = 'show_icon_invoice';
DELETE FROM cc_config WHERE config_key = 'show_top_frame';

-- add MXN currency on Paypal
UPDATE cc_configuration SET set_function = 'tep_cfg_select_option(array(''Selected Currency'',''USD'',''CAD'',''EUR'',''GBP'',''JPY'',''MXN''), ' WHERE configuration_key = 'MODULE_PAYMENT_PAYPAL_CURRENCY' ;


-- DID CALL AND BILLING
ALTER TABLE cc_didgroup DROP iduser;
ALTER TABLE cc_didgroup ADD connection_charge DECIMAL( 15, 5 ) NOT NULL DEFAULT '0',
ADD selling_rate DECIMAL( 15, 5 ) NOT NULL DEFAULT '0';

ALTER TABLE cc_did ADD UNIQUE (did);

INSERT INTO cc_config (config_title ,config_key ,config_value ,config_description ,config_valuetype ,config_listvalues ,config_group_title)
VALUES ('Call to free DID Dial Command Params', 'dialcommand_param_call_2did', '|60|HiL(%timeout%:61000:30000)',  '%timeout% is the value of the paramater : ''Max time to Call a DID no billed''', '0', NULL , 'agi-conf1');
INSERT INTO cc_config (config_title ,config_key ,config_value ,config_description ,config_valuetype ,config_listvalues ,config_group_title)
VALUES ('Max time to Call a DID no billed', 'max_call_call_2_did', '3600', 'max time to call a did of the system and not billed . this max value is in seconde and by default (3600 = 1HOUR MAX CALL).', '0', NULL , 'agi-conf1');


-- remove the Signup Link option
Delete from cc_config where config_key='signup_page_url';

-- remove the old auto create card feature
Delete from cc_config where config_key='cid_auto_create_card';
Delete from cc_config where config_key='cid_auto_create_card_len';
Delete from cc_config where config_key='cid_auto_create_card_typepaid';
Delete from cc_config where config_key='cid_auto_create_card_credit';
Delete from cc_config where config_key='cid_auto_create_card_credit_limit';
Delete from cc_config where config_key='cid_auto_create_card_tariffgroup';


-- change type in cc_config
ALTER TABLE cc_config CHANGE config_title config_title VARCHAR( 100 ); 
ALTER TABLE cc_config CHANGE config_key config_key VARCHAR( 100 ); 
ALTER TABLE cc_config CHANGE config_value config_value VARCHAR( 100 ); 
ALTER TABLE cc_config CHANGE config_listvalues config_listvalues VARCHAR( 100 ); 

-- Set Qualify at No per default
UPDATE cc_config SET config_value='no' WHERE config_key='qualify';


-- Update Paypal URL API
UPDATE cc_config SET config_value='https://www.paypal.com/cgi-bin/webscr' WHERE config_key='paypal_payment_url';

-- change type in cc_config
ALTER TABLE cc_config CHANGE config_value config_value VARCHAR( 200 ); 

ALTER TABLE cc_didgroup DROP connection_charge;
ALTER TABLE cc_didgroup DROP selling_rate;


ALTER TABLE cc_did ADD connection_charge DECIMAL( 15, 5 ) NOT NULL DEFAULT '0',
ADD selling_rate DECIMAL( 15, 5 ) NOT NULL DEFAULT '0';

ALTER TABLE cc_billing_customer ADD start_date TIMESTAMP NULL ;


/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,   
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 * 
 * @copyright   Copyright (C) 2004-2009 - Star2billing S.L. 
 * @author      Belaid Arezqui <areski@gmail.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 *
 * Software License Agreement (GNU Affero General Public License)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
**/


CREATE TABLE cc_message_agent (
    id BIGINT NOT NULL AUTO_INCREMENT ,
    id_agent INT NOT NULL ,
    message LONGTEXT CHARACTER SET utf8 COLLATE utf8_bin NULL ,
    type TINYINT NOT NULL DEFAULT '0' ,
    logo TINYINT NOT NULL DEFAULT '1',
    order_display INT NOT NULL ,
    PRIMARY KEY ( id )
) ENGINE = MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


INSERT INTO cc_config ( config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES( 'Auto Create Card', 'cid_auto_create_card', '0', 'if the callerID is captured on a2billing, this option will create automatically a new card and add the callerID to it.', 1, 'yes,no', 'agi-conf1');
INSERT INTO cc_config ( config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES( 'Auto Create Card Length', 'cid_auto_create_card_len', '10', 'set the length of the card that will be auto create (ie, 10).', 0, NULL, 'agi-conf1');
INSERT INTO cc_config ( config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES( 'Auto Create Card Type', 'cid_auto_create_card_typepaid', 'PREPAID', 'billing type of the new card( value : POSTPAID or PREPAID) .', 0, NULL, 'agi-conf1');
INSERT INTO cc_config ( config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES( 'Auto Create Card Credit', 'cid_auto_create_card_credit', '0', 'amount of credit of the new card.', 0, NULL, 'agi-conf1');
INSERT INTO cc_config ( config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES( 'Auto Create Card Limit', 'cid_auto_create_card_credit_limit', '0', 'if postpay, define the credit limit for the card.', 0, NULL, 'agi-conf1');
INSERT INTO cc_config ( config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES( 'Auto Create Card TariffGroup', 'cid_auto_create_card_tariffgroup', '1', 'the tariffgroup to use for the new card (this is the ID that you can find on the admin web interface) .', 0, NULL, 'agi-conf1');

INSERT INTO cc_config (id ,config_title ,config_key ,config_value ,config_description ,config_valuetype ,config_listvalues ,config_group_title)
    VALUES  (NULL , 'Paypal Amount Subscription', 'paypal_subscription_amount', '10' , 'amount to billed each recurrence of payment ', '0', NULL , 'epayment_method'),
	    (NULL , 'Paypal Subscription Time period number', 'paypal_subscription_period_number', '1', 'number of time periods between each recurrence', '0', NULL , 'epayment_method'),
	    (NULL , 'Paypal Subscription Time period', 'paypal_subscription_time_period', 'M', 'time period (D=days, W=weeks, M=months, Y=years)', '0', NULL , 'epayment_method'),
	    (NULL , 'Enable PayPal subscription', 'paypal_subscription_enabled', '0', 'Enable Paypal subscription on the User home page, you need a Premier or Business account.', '1', 'yes,no', 'epayment_method'),
	    (NULL , 'Paypal Subscription account', 'paypal_subscription_account', '', 'Your PayPal ID or an email address associated with your PayPal account. Email addresses must be confirmed and bound to a Premier or Business Verified Account.', '0', NULL , 'epayment_method');


-- make sure we disabled Authorize
DELETE FROM cc_payment_methods where payment_filename = 'authorizenet.php';

ALTER TABLE cc_templatemail ADD PRIMARY KEY ( id )  ;
ALTER TABLE cc_templatemail CHANGE id id INT( 11 ) NOT NULL AUTO_INCREMENT  ;

INSERT INTO cc_templatemail (id_language, mailtype, fromemail, fromname, subject, messagetext)
    VALUES	('en', 'did_paid', 'info@mydomainname.com', 'COMPANY NAME', 'DID notification - ($did$)', 'BALANCE REMAINING $balance_remaining$ $base_currency$\n\nAn automatic taking away of : $did_cost$ $base_currency$ has been carry out of your account to pay your DID ($did$)\n\nMonthly cost for DID : $did_cost$ $base_currency$\n\n'),
		('en', 'did_unpaid', 'info@mydomainname.com', 'COMPANY NAME', 'DID notification - ($did$)', 'BALANCE REMAINING $balance_remaining$ $base_currency$\n\nYour credit is not enough to pay your DID number ($did$), the monthly cost is : $did_cost$ $base_currency$\n\nYou have $days_remaining$ days to pay the invoice (REF: $invoice_ref$ ) or the DID will be automatically released \n\n'),
		('en', 'did_released', 'info@mydomainname.com', 'COMPANY NAME', 'DID released - ($did$)', 'The DID $did$ has been automatically released!\n\n');		


DELETE FROM cc_configuration WHERE configuration_key = 'MODULE_PAYMENT_PAYPAL_CURRENCY';
DELETE FROM cc_configuration WHERE configuration_key = 'MODULE_PAYMENT_MONEYBOOKERS_CURRENCY';

ALTER TABLE cc_support ADD email VARCHAR( 70 ) CHARACTER SET utf8 COLLATE utf8_bin NULL ;
ALTER TABLE cc_support ADD language CHAR( 5 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL DEFAULT 'en';
INSERT INTO cc_templatemail (id_language, mailtype, fromemail, fromname, subject, messagetext)
    VALUES	('en', 'new_ticket', 'info@mydomainname.com', 'COMPANY NAME', 'Support Ticket #$ticket_id$', 'New Ticket Open (#$ticket_id$) From $ticket_owner$.\n Title : $ticket_title$\n Priority : $ticket_priority$ \n Status : $ticket_status$ \n Description : $ticket_description$ \n'),
		('en', 'modify_ticket', 'info@mydomainname.com', 'COMPANY NAME', 'Support Ticket #$ticket_id$', 'Ticket modified (#$ticket_id$) By $comment_creator$.\n Ticket Status -> $ticket_status$\n Description : $comment_description$ \n');
DELETE FROM cc_templatemail WHERE mailtype = 'invoice';
INSERT INTO cc_templatemail (id_language, mailtype, fromemail, fromname, subject, messagetext)
    VALUES	('en', 'invoice_to_pay', 'info@mydomainname.com', 'COMPANY NAME', 'Invoice to pay Ref: $invoice_reference$', 
    'New Invoice send with the reference : $invoice_reference$ .\n 
    Title : $invoice_title$ .\n Description : $invoice_description$\n 
    TOTAL (exclude VAT) : $invoice_total$  $base_currency$\n TOTAL (invclude VAT) : $invoice_total_vat$ $base_currency$ \n\n 
    TOTAL TO PAY : $invoice_total_vat$ $base_currency$\n\n 
    You can check and pay this invoice by your account on the web interface : http://mydomainname.com/customer/  ');




/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,   
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 * 
 * @copyright   Copyright (C) 2004-2009 - Star2billing S.L. 
 * @author      Belaid Arezqui <areski@gmail.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 *
 * Software License Agreement (GNU Affero General Public License)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
**/


create index idtariffplan_index on cc_ratecard (idtariffplan);


UPDATE cc_config SET config_title='DID Billing Days to pay', config_description='Define the amount of days you want to give to the user before releasing its DIDs' WHERE config_key='didbilling_daytopay ';


-- Add new field for VT provisioning
ALTER TABLE cc_card_group ADD provisioning VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_bin NULL;


-- New setting for Base_country and Base_language
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES('Base Country', 'base_country', 'USA', 'Define the country code in 3 letters where you are located (ISO 3166-1 : "USA" for United States)', 0, '', 'global');
INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES('Base Language', 'base_language', 'en', 'Define your language code in 2 letters (ISO 639 : "en" for English)', 0, '', 'global');



-- Change lenght of field for provisioning system
ALTER TABLE cc_card_group CHANGE name name varchar( 50 );
ALTER TABLE cc_trunk CHANGE trunkcode trunkcode varchar( 50 );


-- change lenght on Notification
ALTER TABLE cc_notification CHANGE key_value key_value VARCHAR( 255 );



-- IAX Friends update

CREATE INDEX iax_friend_nh_index on cc_iax_buddies (name, host);
CREATE INDEX iax_friend_nip_index on cc_iax_buddies (name, ipaddr, port);
CREATE INDEX iax_friend_ip_index on cc_iax_buddies (ipaddr, port);
CREATE INDEX iax_friend_hp_index on cc_iax_buddies (host, port);


ALTER TABLE cc_iax_buddies
	DROP callgroup,
	DROP canreinvite,
	DROP dtmfmode,
	DROP fromuser,
	DROP fromdomain,
	DROP insecure,
	DROP mailbox,
	DROP md5secret,
	DROP nat,
	DROP pickupgroup,
	DROP restrictcid,
	DROP rtptimeout,
	DROP rtpholdtimeout,
	DROP musiconhold,
	DROP cancallforward;


ALTER TABLE cc_iax_buddies 
	ADD dbsecret varchar(40) NOT NULL default '',
	ADD regcontext varchar(40) NOT NULL default '',
	ADD sourceaddress varchar(20) NOT NULL default '',
	ADD mohinterpret varchar(20) NOT NULL default '', 
	ADD mohsuggest varchar(20) NOT NULL default '', 
	ADD inkeys varchar(40) NOT NULL default '', 
	ADD outkey varchar(40) NOT NULL default '', 
	ADD cid_number varchar(40) NOT NULL default '', 
	ADD sendani varchar(10) NOT NULL default '', 
	ADD fullname varchar(40) NOT NULL default '', 
	ADD auth varchar(20) NOT NULL default '', 
	ADD maxauthreq varchar(15) NOT NULL default '', 
	ADD encryption varchar(20) NOT NULL default '', 
	ADD transfer varchar(10) NOT NULL default '', 
	ADD jitterbuffer varchar(10) NOT NULL default '', 
	ADD forcejitterbuffer varchar(10) NOT NULL default '', 
	ADD codecpriority varchar(40) NOT NULL default '', 
	ADD qualifysmoothing varchar(10) NOT NULL default '', 
	ADD qualifyfreqok varchar(10) NOT NULL default '', 
	ADD qualifyfreqnotok varchar(10) NOT NULL default '', 
	ADD timezone varchar(20) NOT NULL default '', 
	ADD adsi varchar(10) NOT NULL default '', 
	ADD setvar varchar(200) NOT NULL default '';

-- Add IAX security settings / not support by realtime
ALTER TABLE cc_iax_buddies 
	ADD requirecalltoken varchar(20) NOT NULL default '',
	ADD maxcallnumbers varchar(10) NOT NULL default '',
	ADD maxcallnumbers_nonvalidated varchar(10) NOT NULL default '';


-- SIP Friends update

CREATE INDEX sip_friend_hp_index on cc_sip_buddies (host, port);
CREATE INDEX sip_friend_ip_index on cc_sip_buddies (ipaddr, port);


ALTER TABLE cc_sip_buddies
	ADD defaultuser varchar(40) NOT NULL default '',
	ADD auth varchar(10) NOT NULL default '',
	ADD subscribemwi varchar(10) NOT NULL default '', -- yes/no
	ADD vmexten varchar(20) NOT NULL default '',
	ADD cid_number varchar(40) NOT NULL default '',
	ADD callingpres varchar(20) NOT NULL default '',
	ADD usereqphone varchar(10) NOT NULL default '',
	ADD incominglimit varchar(10) NOT NULL default '',
	ADD subscribecontext varchar(40) NOT NULL default '',
	ADD musicclass varchar(20) NOT NULL default '',
	ADD mohsuggest varchar(20) NOT NULL default '',
	ADD allowtransfer varchar(20) NOT NULL default '',
	ADD autoframing varchar(10) NOT NULL default '', -- yes/no
	ADD maxcallbitrate varchar(15) NOT NULL default '',
	ADD outboundproxy varchar(40) NOT NULL default '',
--  ADD regserver varchar(20) NOT NULL default '',
	ADD rtpkeepalive varchar(15) NOT NULL default '';



-- ADD A2Billing Version into the Database 
CREATE TABLE cc_version (
    version varchar(30) NOT NULL
) ENGINE = MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO cc_version (version) VALUES ('1.4.3');

UPDATE cc_version SET version = '1.4.3';

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,   
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 * 
 * @copyright   Copyright (C) 2004-2009 - Star2billing S.L. 
 * @author      Belaid Arezqui <areski@gmail.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 *
 * Software License Agreement (GNU Affero General Public License)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
**/



CREATE VIEW cc_callplan_lcr AS
	SELECT cc_ratecard.destination, cc_ratecard.dialprefix, cc_ratecard.buyrate, cc_ratecard.rateinitial, cc_ratecard.startdate, cc_ratecard.stopdate, cc_ratecard.initblock, cc_ratecard.connectcharge, cc_ratecard.id_trunk , cc_ratecard.idtariffplan , cc_ratecard.id, cc_tariffgroup.id AS tariffgroup_id
	FROM cc_tariffgroup 
	RIGHT JOIN cc_tariffgroup_plan ON cc_tariffgroup_plan.idtariffgroup=cc_tariffgroup.id 
	INNER JOIN cc_tariffplan ON (cc_tariffplan.id=cc_tariffgroup_plan.idtariffplan ) 
	LEFT JOIN cc_ratecard ON cc_ratecard.idtariffplan=cc_tariffplan.id;


-- New Agent commission module
ALTER TABLE cc_agent ADD com_balance DECIMAL( 15, 5 ) NOT NULL;
ALTER TABLE cc_agent_commission DROP paid_status ;
ALTER TABLE cc_agent_commission ADD commission_type TINYINT NOT NULL ;
ALTER TABLE cc_agent_commission ADD commission_percent DECIMAL( 10, 4 ) NOT NULL ;
INSERT INTO cc_config ( config_title , config_key , config_value , config_description , config_valuetype , config_listvalues , config_group_title) 
VALUES ('Authorize Remittance Request', 'remittance_request', '1', 'Enable or disable the link which allow agent to submit a remittance request', '0', 'yes,no', 'webagentui');


ALTER TABLE cc_agent ADD threshold_remittance DECIMAL( 15, 5 ) NOT NULL ;
ALTER TABLE cc_agent ADD bank_info MEDIUMTEXT NULL ;

CREATE TABLE cc_remittance_request (
	id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	id_agent BIGINT NOT NULL ,
	amount DECIMAL( 15, 5 ) NOT NULL ,
	type TINYINT NOT NULL,
	date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
	status TINYINT NOT NULL DEFAULT '0'
) ENGINE = MYISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


-- notifiction link to the record
ALTER TABLE cc_notification ADD link_id BIGINT NULL ,
ADD link_type VARCHAR( 20 ) CHARACTER SET utf8 COLLATE utf8_bin NULL;



-- Improve CallPlan LCR
DROP VIEW cc_callplan_lcr;
CREATE VIEW cc_callplan_lcr AS
	SELECT cc_ratecard.id, cc_prefix.destination, cc_ratecard.dialprefix, cc_ratecard.buyrate, cc_ratecard.rateinitial, cc_ratecard.startdate, cc_ratecard.stopdate, cc_ratecard.initblock, cc_ratecard.connectcharge, cc_ratecard.id_trunk , cc_ratecard.idtariffplan , cc_ratecard.id as ratecard_id, cc_tariffgroup.id AS tariffgroup_id
	
	FROM cc_tariffgroup 
	RIGHT JOIN cc_tariffgroup_plan ON cc_tariffgroup_plan.idtariffgroup=cc_tariffgroup.id 
	INNER JOIN cc_tariffplan ON (cc_tariffplan.id=cc_tariffgroup_plan.idtariffplan ) 
	LEFT JOIN cc_ratecard ON cc_ratecard.idtariffplan=cc_tariffplan.id
	LEFT JOIN cc_prefix ON prefix=cc_ratecard.destination
	WHERE cc_ratecard.id IS NOT NULL;
	

-- Add Asterisk Version - Global for Callback
INSERT INTO cc_config ( config_title , config_key , config_value , config_description , config_valuetype , config_listvalues , config_group_title) 
VALUES ('Asterisk Version Global', 'asterisk_version', '1_4', 'Asterisk Version Information, 1_1, 1_2, 1_4, 1_6. By Default the version is 1_4.', 
'0', NULL, 'global');


-- UPDATE A2Billing Database Version
UPDATE cc_version SET version = '1.4.4';




/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,   
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 * 
 * @copyright   Copyright (C) 2004-2009 - Star2billing S.L. 
 * @author      Belaid Arezqui <areski@gmail.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 *
 * Software License Agreement (GNU Affero General Public License)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
**/






UPDATE cc_version SET version = '1.4.4.1';








/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,   
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 * 
 * @copyright   Copyright (C) 2004-2009 - Star2billing S.L. 
 * @author      Belaid Arezqui <areski@gmail.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 *
 * Software License Agreement (GNU Affero General Public License)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
**/



ALTER TABLE cc_did_destination CHANGE destination destination VARCHAR(120) NOT NULL;


DROP TABLE IF EXISTS cc_call_archive;
CREATE TABLE IF NOT EXISTS cc_call_archive (
    id bigint(20) NOT NULL auto_increment,
    sessionid varchar(40) collate utf8_bin NOT NULL,
    uniqueid varchar(30) collate utf8_bin NOT NULL,
    card_id bigint(20) NOT NULL,
    nasipaddress varchar(30) collate utf8_bin NOT NULL,
    starttime timestamp NOT NULL default CURRENT_TIMESTAMP,
    stoptime timestamp NOT NULL default '0000-00-00 00:00:00',
    sessiontime int(11) default NULL,
    calledstation varchar(30) collate utf8_bin NOT NULL,
    sessionbill float default NULL,
    id_tariffgroup int(11) default NULL,
    id_tariffplan int(11) default NULL,
    id_ratecard int(11) default NULL,
    id_trunk int(11) default NULL,
    sipiax int(11) default '0',
    src varchar(40) collate utf8_bin NOT NULL,
    id_did int(11) default NULL,
    buycost decimal(15,5) default '0.00000',
    id_card_package_offer int(11) default '0',
    real_sessiontime int(11) default NULL,
    dnid varchar(40) collate utf8_bin NOT NULL,
    terminatecauseid int(1) default '1',
    destination int(11) default '0',
    PRIMARY KEY  (id),
    KEY starttime (starttime),
    KEY calledstation (calledstation),
    KEY terminatecauseid (terminatecauseid)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES ('Archive Calls', 'archive_call_prior_x_month', '24', 'A cront can be enabled in order to archive your CDRs, this setting allow to define prior which month it will archive', 0, NULL, 'backup');
 

ALTER TABLE cc_logpayment ADD agent_id BIGINT NULL ;
ALTER TABLE cc_logrefill ADD agent_id BIGINT NULL ;


ALTER TABLE `cc_ratecard` CHANGE `destination` `destination` BIGINT( 20 ) NULL DEFAULT '0';


UPDATE cc_version SET version = '1.4.5';




/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,   
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 * 
 * @copyright   Copyright (C) 2004-2009 - Star2billing S.L. 
 * @author      Belaid Arezqui <areski@gmail.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 *
 * Software License Agreement (GNU Affero General Public License)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
**/


UPDATE cc_version SET version = '1.5.0';




/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,   
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 * 
 * @copyright   Copyright (C) 2004-2009 - Star2billing S.L. 
 * @author      Belaid Arezqui <areski@gmail.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 *
 * Software License Agreement (GNU Affero General Public License)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
**/



UPDATE cc_version SET version = '1.5.1';




/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,   
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 * 
 * @copyright   Copyright (C) 2004-2009 - Star2billing S.L. 
 * @author      Belaid Arezqui <areski@gmail.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 *
 * Software License Agreement (GNU Affero General Public License)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
**/


ALTER TABLE cc_subscription_fee ADD startdate TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00',
ADD stopdate TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';

RENAME TABLE cc_subscription_fee  TO cc_subscription_service ;
ALTER TABLE cc_card_subscription ADD paid_status TINYINT NOT NULL DEFAULT '0' ;
ALTER TABLE cc_card_subscription ADD last_run TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE cc_card_subscription ADD next_billing_date TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';
UPDATE cc_card_subscription SET next_billing_date = NOW();
ALTER TABLE cc_card_subscription ADD limit_pay_date TIMESTAMP NOT NULL DEFAULT '0000-00-00 00:00:00';


INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) 
			VALUES ('Days to bill before month anniversary', 'subscription_bill_days_before_anniversary', '3',
					'Numbers of days to bill a subscription service before the month anniversary', 0, NULL, 'global');

ALTER TABLE cc_templatemail CHANGE subject subject VARCHAR( 130 ) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL;

INSERT INTO cc_templatemail (id_language, mailtype, fromemail, fromname, subject, messagetext)
VALUES  ('en', 'subscription_paid', 'info@mydomainname.com', 'COMPANY NAME',
'Subscription notification - $subscription_label$ ($subscription_id$)',
'BALANCE  $credit$ $base_currency$\n\n
A decrement of: $subscription_fee$ $base_currency$ has removed from your account to pay your service. ($subscription_label$)\n\n
the monthly cost is : $subscription_fee$\n\n'),

('en', 'subscription_unpaid', 'info@mydomainname.com', 'COMPANY NAME',
'Subscription notification - $subscription_label$ ($subscription_id$)',
'BALANCE $credit$ $base_currency$\n\n
You do not have enough credit to pay your subscription,($subscription_label$), the monthly cost is : $subscription_fee$ $base_currency$\n\n
You have $days_remaining$ days to pay the invoice (REF: $invoice_ref$ ) or your service may cease \n\n'),

('en', 'subscription_disable_card', 'info@mydomainname.com', 'COMPANY NAME',
'Service deactivated - unpaid service $subscription_label$ ($subscription_id$)',
'The account has been automatically deactivated until the invoice is settled.\n\n');



ALTER TABLE cc_subscription_service CHANGE label label VARCHAR( 200 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;
ALTER TABLE cc_subscription_service CHANGE emailreport emailreport VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL;
ALTER TABLE cc_subscription_signup CHANGE description description VARCHAR( 500 ) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL;



INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title)
VALUES ('Enable info module about system', 'system_info_enable', 'LEFT', 'Enabled this if you want to display the info module and place it somewhere on the Dashboard.', 0, 'NONE,LEFT,CENTER,RIGHT', 'dashboard');

INSERT INTO cc_config (config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title)
VALUES ('Enable news module', 'news_enabled','RIGHT','Enabled this if you want to display the news module and place it somewhere on the Dashboard.', 0, 'NONE,LEFT,CENTER,RIGHT', 'dashboard');



# update destination field to a BIGINT
ALTER TABLE cc_ratecard CHANGE destination destination BIGINT( 20 ) NULL DEFAULT '0';


# query_type : 1 SQL ; 2 for shell script
# result_type : 1 Text2Speech, 2 Date, 3 Number, 4 Digits
CREATE TABLE cc_monitor (
	id BIGINT NOT NULL auto_increment,
	label VARCHAR( 50 ) collate utf8_bin NOT NULL ,
	dial_code INT NULL ,
	description VARCHAR( 250 ) collate utf8_bin NULL,
	text_intro VARCHAR( 250 ) collate utf8_bin NULL,
	query_type TINYINT NOT NULL DEFAULT '1',
	query VARCHAR( 1000 ) collate utf8_bin NULL,
	result_type TINYINT NOT NULL DEFAULT '1',
	enable TINYINT NOT NULL DEFAULT '1',
	PRIMARY KEY ( id )
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO cc_monitor (label, dial_code, description, text_intro, query_type, query, result_type, enable) VALUES
('TotalCall', 2, 'To say the total amount of calls', 'The total amount of calls on your system is', 1, 'select count(*) from cc_call;', 3, 1),
('Say Time', 1, 'just saying the current date and time', 'The current date and time is', 1, 'SELECT UNIX_TIMESTAMP( );', 2, 1),
('Test Connectivity', 3, 'Test Connectivity with Google', 'your Internet connection is', 2, 'check_connectivity.sh', 1, 1);


INSERT INTO cc_config ( config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES
( 'Busy Timeout', 'busy_timeout', '1', 'Define the timeout in second when indicate the busy condition', 0, NULL, 'agi-conf1');


ALTER TABLE cc_subscription_signup ADD id_callplan BIGINT;



-- New payment Gateway
INSERT INTO `cc_payment_methods` (`id`, `payment_method`, `payment_filename`) VALUES(5, 'iridium', 'iridium.php');

INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description)
VALUES ('MerchantID', 'MODULE_PAYMENT_IRIDIUM_MERCHANTID', 'yourMerchantId', 'Your Mechant Id provided by Iridium');
INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description)
VALUES ('Password', 'MODULE_PAYMENT_IRIDIUM_PASSWORD', 'Password', 'password for Iridium merchant');

INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description)
VALUES ('PaymentProcessor', 'MODULE_PAYMENT_IRIDIUM_GATEWAY', 'PaymentGateway URL ', 'Enter payment gateway URL');

INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description)
VALUES ('PaymentProcessorPort', 'MODULE_PAYMENT_IRIDIUM_GATEWAY_PORT', 'PaymentGateway Port ', 'Enter payment gateway port');

INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description, set_function)
VALUES ('Transaction Currency', 'MODULE_PAYMENT_IRIDIUM_CURRENCY', 'Selected Currency', 'The default currency for the payment transactions', 'tep_cfg_select_option(array(\'Selected Currency\',\'EUR\', \'USD\', \'GBP\', \'HKD\', \'SGD\', \'JPY\', \'CAD\', \'AUD\', \'CHF\', \'DKK\', \'SEK\', \'NOK\', \'ILS\', \'MYR\', \'NZD\', \'TWD\', \'THB\', \'CZK\', \'HUF\', \'SKK\', \'ISK\', \'INR\'), ');

INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description, set_function)
VALUES ('Transaction Language', 'MODULE_PAYMENT_IRIDIUM_LANGUAGE', 'Selected Language', 'The default language for the payment transactions', 'tep_cfg_select_option(array(\'Selected Language\',\'EN\', \'DE\', \'ES\', \'FR\'), ');

INSERT INTO cc_configuration (configuration_title, configuration_key, configuration_value, configuration_description, set_function)
VALUES ('Enable iridium Module', 'MODULE_PAYMENT_IRIDIUM_STATUS', 'False', 'Do you want to accept Iridium payments?','tep_cfg_select_option(array(\'True\', \'False\'), ');



INSERT INTO cc_config ( config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES
( 'Callback Reduce Balance', 'callback_reduce_balance', '1', 'Define the amount to reduce the balance on Callback in order to make sure that the B leg wont alter the account into a negative value.', 0, NULL, 'agi-conf1');


UPDATE cc_version SET version = '1.6.0';


/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,   
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 * 
 * @copyright   Copyright (C) 2004-2009 - Star2billing S.L. 
 * @author      Belaid Arezqui <areski@gmail.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 *
 * Software License Agreement (GNU Affero General Public License)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
**/


UPDATE cc_version SET version = '1.6.1';




/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,   
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 * 
 * @copyright   Copyright (C) 2004-2009 - Star2billing S.L. 
 * @author      Belaid Arezqui <areski@gmail.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 *
 * Software License Agreement (GNU Affero General Public License)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
**/


UPDATE cc_version SET version = '1.6.2';




/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,   
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 * 
 * @copyright   Copyright (C) 2004-2009 - Star2billing S.L. 
 * @author      Belaid Arezqui <areski@gmail.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 *
 * Software License Agreement (GNU Affero General Public License)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
**/


INSERT INTO cc_config (id, config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES (NULL, 'Language field', 'field_language', '1', 'Enable The Language Field -  Yes 1 - No 0.', '1', 'yes,no', 'signup');

INSERT INTO cc_config (id, config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES (NULL, 'Currency field', 'field_currency', '1', 'Enable The Currency Field - Yes 1 - No 0. ', '1', 'yes,no', 'signup');

INSERT INTO cc_config (id, config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES (NULL, 'Last Name Field', 'field_lastname', '1', 'Enable The Last Name Field - Yes 1 - No 0. ', '1', 'yes,no', 'signup');

INSERT INTO cc_config (id, config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES (NULL, 'First Name Field', 'field_firstname', '1', 'Enable The First Name Field - Yes 1 - No 0. ', '1', 'yes,no', 'signup');

INSERT INTO cc_config (id, config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES (NULL, 'Address Field', 'field_address', '1', 'Enable The Address Field - Yes 1 - No 0. ', '1', 'yes,no', 'signup');

INSERT INTO cc_config (id, config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES (NULL, 'City Field', 'field_city', '1', 'Enable The City Field - Yes 1 - No 0. ', '1', 'yes,no', 'signup');

INSERT INTO cc_config (id, config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES (NULL, 'State Field', 'field_state', '1', 'Enable The State Field - Yes 1 - No 0. ', '1', 'yes,no', 'signup');

INSERT INTO cc_config (id, config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES (NULL, 'Country Field', 'field_country', '1', 'Enable The Country Field - Yes 1 - No 0. ', '1', 'yes,no', 'signup');

INSERT INTO cc_config (id, config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES (NULL, 'Zipcode Field', 'field_zipcode', '1', 'Enable The Zipcode Field - Yes 1 - No 0. ', '1', 'yes,no', 'signup');

INSERT INTO cc_config (id, config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES (NULL, 'Timezone Field', 'field_id_timezone', '1', 'Enable The Timezone Field - Yes 1 - No 0. ', '1', 'yes,no', 'signup');

INSERT INTO cc_config (id, config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES (NULL, 'Phone Field', 'field_phone', '1', 'Enable The Phone Field - Yes 1 - No 0. ', '1', 'yes,no', 'signup');

INSERT INTO cc_config (id, config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES (NULL, 'Fax Field', 'field_fax', '1', 'Enable The Fax Field - Yes 1 - No 0. ', '1', 'yes,no', 'signup');

INSERT INTO cc_config (id, config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES (NULL, 'Company Name Field', 'field_company', '1', 'Enable The Company Name Field - Yes 1 - No 0. ', '1', 'yes,no', 'signup');

INSERT INTO cc_config (id, config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES (NULL, 'Company Website Field', 'field_company_website', '1', 'Enable The Company Website Field - Yes 1 - No 0. ', '1', 'yes,no', 'signup');

INSERT INTO cc_config (id, config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES (NULL, 'VAT Registration Number Field', 'field_VAT_RN', '1', 'Enable The VAT Registration Number Field - Yes 1 - No 0. ', '1', 'yes,no', 'signup');

INSERT INTO cc_config (id, config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES (NULL, 'Traffic Field', 'field_traffic', '1', 'Enable The Traffic Field - Yes 1 - No 0. ', '1', 'yes,no', 'signup');

INSERT INTO cc_config (id, config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title) VALUES (NULL, 'Traffic Target Field', 'field_traffic_target', '1', 'Enable The Traffic Target Field - Yes 1 - No 0. ', '1', 'yes,no', 'signup');



-- fix Realtime Bug, Permit have to be after Deny
ALTER TABLE cc_sip_buddies MODIFY COLUMN permit varchar(95) AFTER deny;
ALTER TABLE cc_iax_buddies MODIFY COLUMN permit varchar(95) AFTER deny;


-- Locking features
ALTER TABLE cc_card ADD block TINYINT NOT NULL DEFAULT '0';
ALTER TABLE cc_card ADD lock_pin VARCHAR( 15 ) NULL DEFAULT NULL;
ALTER TABLE cc_card ADD lock_date timestamp NULL;


INSERT INTO cc_config ( config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title)
VALUES( 'IVR Locking option', 'ivr_enable_locking_option', '0', 'Enable the IVR which allow the users to lock their account with an extra lock code.', 1, 'yes,no', 'agi-conf1');

INSERT INTO cc_config ( config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title)
VALUES( 'IVR Account Information', 'ivr_enable_account_information', '0', 'Enable the IVR which allow the users to retrieve different information about their account.', 1, 'yes,no', 'agi-conf1');

INSERT INTO cc_config ( config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title)
VALUES( 'IVR Speed Dial', 'ivr_enable_ivr_speeddial', '0', 'Enable the IVR which allow the users add speed dial.', 1, 'yes,no', 'agi-conf1');


ALTER TABLE cc_templatemail CHANGE messagetext messagetext VARCHAR( 3000 ) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL;
ALTER TABLE cc_templatemail CHANGE messagehtml messagehtml VARCHAR( 3000 ) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL;

ALTER TABLE cc_card_group CHANGE description description VARCHAR( 400 ) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL;

ALTER TABLE cc_config CHANGE config_description config_description VARCHAR( 500 ) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL;


  
UPDATE cc_version SET version = '1.7.0';




/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,   
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 * 
 * @copyright   Copyright (C) 2004-2009 - Star2billing S.L. 
 * @author      Belaid Arezqui <areski@gmail.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 *
 * Software License Agreement (GNU Affero General Public License)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
**/


INSERT INTO cc_config ( config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title)
VALUES( 'Play rate lower one', 'play_rate_cents_if_lower_one', '0', 'Play the initial cost even if the cents are less than one. if cost is 0.075, we will play : 7 point 5 cents per minute. (values : yes - no)', 0, 'yes,no', 'agi-conf1');  


  
UPDATE cc_version SET version = '1.7.1';




/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,   
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 * 
 * @copyright   Copyright (C) 2004-2009 - Star2billing S.L. 
 * @author      Belaid Arezqui <areski@gmail.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 *
 * Software License Agreement (GNU Affero General Public License)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
**/


ALTER TABLE cc_did_destination CHANGE destination destination VARCHAR( 100 ) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL;

ALTER TABLE cc_sip_buddies ADD COLUMN useragent VARCHAR( 80 ) DEFAULT NULL;

ALTER TABLE cc_sip_buddies ALTER disallow set DEFAULT 'ALL';
ALTER TABLE cc_sip_buddies ALTER rtpkeepalive set DEFAULT 0;
ALTER TABLE cc_sip_buddies ALTER canreinvite set DEFAULT 'YES';


ALTER TABLE  cc_callback_spool CHANGE  variable  variable VARCHAR( 2000 ) CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL;
  
UPDATE cc_version SET version = '1.7.2';




/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,   
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 * 
 * @copyright   Copyright (C) 2004-2009 - Star2billing S.L. 
 * @author      Belaid Arezqui <areski@gmail.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 *
 * Software License Agreement (GNU Affero General Public License)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
**/

UPDATE cc_version SET version = '1.8.0';




/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,   
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 * 
 * @copyright   Copyright (C) 2004-2009 - Star2billing S.L. 
 * @author      Belaid Arezqui <areski@gmail.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 *
 * Software License Agreement (GNU Affero General Public License)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
**/

INSERT INTO cc_config ( config_title, config_key, config_value, config_description, config_valuetype, config_listvalues, config_group_title)
VALUES( 'Callback Beep for Destination ', 'callback_beep_to_enter_destination', '0', 'Set to yes, this will disable the standard prompt to enter destination and play a beep instead', 1, 'yes,no', 'agi-conf1');

UPDATE cc_version SET version = '1.8.1';



/** PROCCESS TO CREATE USERS BY ELASTIX-DBPROCESS **/


/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of A2Billing (http://www.a2billing.net/)
 *
 * A2Billing, Commercial Open Source Telecom Billing platform,   
 * powered by Star2billing S.L. <http://www.star2billing.com/>
 * 
 * @copyright   Copyright (C) 2004-2009 - Star2billing S.L. 
 * @author      Belaid Arezqui <areski@gmail.com>
 * @license     http://www.fsf.org/licensing/licenses/agpl-3.0.html
 * @package     A2Billing
 *
 * Software License Agreement (GNU Affero General Public License)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
**/

--
-- A2Billing database script - Create user & create a new database
--

/* Default values - Please change them to whatever you want

Database name is: mya2billing
Database user is: a2billinguser
User password is: a2billing


Usage:

mysql -u root -p"root password" < a2billing-MYSQL-createdb-user.sql 

*/


delete from user where User='a2billinguser';
delete from db where User='a2billinguser';

GRANT ALL PRIVILEGES ON mya2billing.* TO 'a2billinguser'@'localhost' IDENTIFIED BY PASSWORD '598c5a9a7cf950c4' WITH GRANT OPTION; 
