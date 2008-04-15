CREATE TABLE form (
    id              INTEGER PRIMARY KEY,
    nombre          VARCHAR(40) NOT NULL,
    descripcion     VARCHAR(150) NOT NULL,
    estatus         VARCHAR(1) NOT NULL DEFAULT 'A'
);

CREATE TABLE form_field (
    id              INTEGER PRIMARY KEY,
    id_form         INTEGER NOT NULL,
    etiqueta        VARCHAR(40) NOT NULL,
    value           VARCHAR(250) NOT NULL,
    tipo            VARCHAR(25) NOT NULL,
    orden           INTEGER NOT NULL,
    FOREIGN KEY     (id_form)   REFERENCES form(id)
);

CREATE TABLE campaign
(
    id      		INTEGER  PRIMARY KEY,
    id_form         int unsigned    NOT NULL,

    name    		varchar(64)     NOT NULL,
    datetime_init   date    NOT NULL,
    datetime_end    date    NOT NULL,
    daytime_init    time    NOT NULL,
    daytime_end     time    NOT NULL,
    retries 		int unsigned    NOT NULL    DEFAULT 1,
    
    trunk           varchar(16)     NOT NULL,
    context         varchar(32)     NOT NULL,
    queue           varchar(16)     NOT NULL,
    
    max_canales int unsigned    NOT NULL DEFAULT 0,
    
    num_completadas int unsigned,
    promedio        double,
    desviacion      double,
    
    script			text	NOT NULL,

    /* 'A' para campaña activa, 
       'I' para inactiva 
       'T' para terminada
     */
    estatus			varchar(1)	NOT NULL	DEFAULT 'A',
    
    FOREIGN KEY		(id_form)   REFERENCES form(id)
);
CREATE TABLE calls
(
    id          INTEGER  PRIMARY KEY,
    id_campaign int unsigned    NOT NULL,
    phone       varchar(32)     NOT NULL,
    status      varchar(32),
    
    Uniqueid    varchar(32),
    fecha_llamada datetime,		/* Fecha de originación de llamada */
    start_time  datetime,       /* Fecha de OnLink */
    end_time    datetime,       /* Fecha de OnUnlink */
    retries     int unsigned    NOT NULL DEFAULT 0,
    duration    int unsigned,
    
    agentnum	varchar(16),
    
    FOREIGN KEY (id_campaign)   REFERENCES campaign(id)
);

CREATE TABLE form_data_recolected (
    id              INTEGER PRIMARY KEY,
    id_calls        INTEGER NOT NULL,
    id_form_field   INTEGER NOT NULL,
    value           VARCHAR(250) NOT NULL,
    FOREIGN KEY     (id_form_field)   REFERENCES form_field(id),
    FOREIGN KEY     (id_calls)   REFERENCES calls(id)
);

CREATE TABLE call_attribute
(
    id          INTEGER    PRIMARY KEY,
    id_call     int unsigned    NOT NULL,
    key         varchar(32)     NOT NULL,
    value       varchar(128)    NOT NULL,
    
    FOREIGN KEY (id_call)   REFERENCES calls(id)
);
CREATE TABLE current_calls
(
    id          INTEGER     PRIMARY KEY,
    fecha_inicio datetime   NOT NULL,
    Uniqueid    varchar(32) NOT NULL,
    queue       varchar(16) NOT NULL,
    agentnum    varchar(16) NOT NULL,    
    id_call     int unsigned NOT NULL,
    
    event       varchar(32) NOT NULL,
    Channel     varchar(32) NOT NULL DEFAULT '',
    
    FOREIGN KEY (id_call) REFERENCES calls(id)
);

CREATE TABLE campaign_form
(
    id_campaign    int unsigned NOT NULL,
    id_form        int unsigned NOT NULL,
    FOREIGN KEY (id_campaign) REFERENCES campaign(id),
    FOREIGN KEY (id_form) REFERENCES form(id)
);
