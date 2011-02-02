CREATE TABLE acl_profile_properties
(
    id_profile   INTEGER NOT NULL,
    property varchar(32) NOT NULL,
    value varchar(256),

    PRIMARY KEY (id_profile, property),
    FOREIGN KEY (id_profile) REFERENCES acl_user_profile (id_profile)
);
CREATE TABLE acl_user_profile
(
    id_profile   INTEGER NOT NULL,
    id_user INTEGER NOT NULL,
    id_resource  INTEGER NOT NULL,
    profile      VARCHAR(32) NOT NULL,

    PRIMARY KEY (id_profile),
    FOREIGN KEY (id_user) REFERENCES acl_user(id),
    FOREIGN KEY (id_resource) REFERENCES acl_resource(id)
);