CREATE TABLE tx_otlib_api_tokens (
  uid INT AUTO_INCREMENT PRIMARY KEY,
  pid INT DEFAULT 0,
  selector VARCHAR(32) NOT NULL UNIQUE,
  validator_hash VARCHAR(255) NOT NULL,
  user_uid INT DEFAULT NULL,
  scopes VARCHAR(255) DEFAULT NULL,
  expires INT NOT NULL,
  revoked TINYINT(1) DEFAULT 0,
  crdate INT(11) DEFAULT 0
);

