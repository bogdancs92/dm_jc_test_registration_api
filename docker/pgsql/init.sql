-- Creation of users table
CREATE TABLE users (
  id                        SERIAL PRIMARY KEY,
  email                     VARCHAR(200) NOT NULL,
  password                  VARCHAR(200) NOT NULL,
  activated                 BOOLEAN DEFAULT FALSE,
  registered_at             TIMESTAMP,
  activation_code           CHAR(4),
  activation_code_expire_at TIMESTAMP,
  activated_at              TIMESTAMP,
  UNIQUE(email)
);
