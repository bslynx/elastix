BEGIN TRANSACTION;

CREATE TABLE card (
    id           INTEGER PRIMARY KEY,
    id_card      INTEGER,
    type         varchar (80),
    additonal    varchar (80)
);
INSERT INTO "card" VALUES(1, 1, 'TE4/0/1 ', '"T4XXP (PCI) Card 0 Span 1" (MASTER) HDB3/CCS/CRC4 RED');
INSERT INTO "card" VALUES(2, 2, 'TE4/0/2 ', '"T4XXP (PCI) Card 0 Span 2" HDB3/CCS/CRC4 RED');
INSERT INTO "card" VALUES(3, 3, 'TE4/0/3 ', '"T4XXP (PCI) Card 0 Span 3" HDB3/CCS/CRC4 RED');
INSERT INTO "card" VALUES(4, 4, 'TE4/0/4 ', '"T4XXP (PCI) Card 0 Span 4" HDB3/CCS/CRC4 RED');
INSERT INTO "card" VALUES(5, 5, 'WCTDM/4 ', '"Wildcard TDM400P REV E/F Board 5"');


CREATE TABLE echo_canceller (
    id              INTEGER PRIMARY KEY,
    num_port        varchar(10),
    name_port       varchar(10),
    echocanceller   varchar (10),
    id_card         INTEGER,
    FOREIGN KEY(id_card) REFERENCES card(id)
);
INSERT INTO "echo_canceller" VALUES(1, '1', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(2, '2', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(3, '3', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(4, '4', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(5, '5', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(6, '6', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(7, '7', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(8, '8', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(9, '9', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(10, '10', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(11, '11', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(12, '12', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(13, '13', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(14, '14', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(15, '15', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(16, '17', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(17, '18', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(18, '19', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(19, '20', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(20, '21', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(21, '22', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(22, '23', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(23, '24', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(24, '25', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(25, '26', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(26, '27', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(27, '28', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(28, '29', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(29, '30', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(30, '31', 'PRI', 'OSLEC', 1);
INSERT INTO "echo_canceller" VALUES(31, '32', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(32, '33', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(33, '34', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(34, '35', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(35, '36', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(36, '37', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(37, '38', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(38, '39', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(39, '40', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(40, '41', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(41, '42', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(42, '43', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(43, '44', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(44, '45', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(45, '46', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(46, '48', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(47, '49', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(48, '50', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(49, '51', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(50, '52', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(51, '53', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(52, '54', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(53, '55', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(54, '56', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(55, '57', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(56, '58', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(57, '59', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(58, '60', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(59, '61', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(60, '62', 'PRI', 'OSLEC', 2);
INSERT INTO "echo_canceller" VALUES(61, '63', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(62, '64', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(63, '65', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(64, '66', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(65, '67', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(66, '68', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(67, '69', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(68, '70', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(69, '71', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(70, '72', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(71, '73', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(72, '74', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(73, '75', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(74, '76', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(75, '77', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(76, '79', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(77, '80', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(78, '81', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(79, '82', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(80, '83', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(81, '84', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(82, '85', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(83, '86', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(84, '87', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(85, '88', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(86, '89', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(87, '90', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(88, '91', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(89, '92', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(90, '93', 'PRI', 'OSLEC', 3);
INSERT INTO "echo_canceller" VALUES(91, '94', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(92, '95', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(93, '96', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(94, '97', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(95, '98', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(96, '99', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(97, '100', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(98, '101', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(99, '102', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(100, '103', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(101, '104', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(102, '105', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(103, '106', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(104, '107', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(105, '108', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(106, '110', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(107, '111', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(108, '112', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(109, '113', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(110, '114', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(111, '115', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(112, '116', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(113, '117', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(114, '118', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(115, '119', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(116, '120', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(117, '121', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(118, '122', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(119, '123', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(120, '124', 'PRI', 'OSLEC', 4);
INSERT INTO "echo_canceller" VALUES(121, '126', 'FXO', 'OSLEC', 5);


CREATE TABLE span_parameter (
       id               INTEGER PRIMARY KEY,
       span_num         INTEGER,
       timing_source    INTEGER,
       linebuildout     INTEGER,
       framing          varchar(10),
       coding           varchar(10),
       id_card          INTEGER,
       FOREIGN KEY(id_card) REFERENCES card(id)
);
INSERT INTO "span_parameter" VALUES(1, 1, 1, 1, 'ccs', 'hdb3', 1);
INSERT INTO "span_parameter" VALUES(2, 2, 2, 2, 'ccs', 'hdb3', 2);
INSERT INTO "span_parameter" VALUES(3, 3, 3, 3, 'ccs', 'hdb3', 3);
INSERT INTO "span_parameter" VALUES(4, 4, 4, 4, 'ccs', 'hdb3', 4);


CREATE TABLE card_parameter (
        id            INTEGER PRIMARY KEY,
        manufacturer  varchar(40),
        num_serie     varchar(40),
        id_card       INTEGER,
        FOREIGN KEY(id_card) REFERENCES card(id)
);
INSERT INTO "card_parameter" VALUES(1, 'Digium', 'elastix1234', 1);
INSERT INTO "card_parameter" VALUES(2, ' ', ' ', 2);
INSERT INTO "card_parameter" VALUES(3, ' ', ' ', 3);
INSERT INTO "card_parameter" VALUES(4, ' ', ' ', 4);
INSERT INTO "card_parameter" VALUES(5, ' ', ' ', 5);


CREATE TABLE car_system (
        id          INTEGER PRIMARY KEY, 
        hwd         varchar (80), 
        module      varchar (80),
        vendor      varchar (80),
        num_serie   varchar(40),
        data        varchar(200) 
);

COMMIT;
