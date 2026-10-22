-- =====================================================================
-- UrbanEats Cloud Kitchen  |  Component A: Identity, Access, Administration
-- Tables: roles, users, login_attempts, audit_log, password_resets
-- Import via phpMyAdmin (Import tab). Safe to re-run: it drops these tables first.
-- Test accounts: see bottom of this file. Password for ALL seed users: Test@1234
-- =====================================================================
CREATE DATABASE IF NOT EXISTS urbaneats_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE urbaneats_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS password_resets;
DROP TABLE IF EXISTS audit_log;
DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS roles;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- roles: lookup table (3NF - role name stored once, users reference it)
-- ---------------------------------------------------------------------
CREATE TABLE roles (
    role_id     TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    role_name   VARCHAR(30)      NOT NULL,
    description VARCHAR(150)     NULL,
    PRIMARY KEY (role_id),
    UNIQUE KEY uq_roles_name (role_name)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- users: SHARED CONTRACT TABLE (orders.user_id etc. reference users.user_id)
-- ---------------------------------------------------------------------
CREATE TABLE users (
    user_id       INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    role_id       TINYINT UNSIGNED NOT NULL,
    full_name     VARCHAR(100)     NOT NULL,
    email         VARCHAR(150)     NOT NULL,
    password_hash VARCHAR(255)     NOT NULL,
    phone         VARCHAR(20)      NULL,
    is_active     TINYINT(1)       NOT NULL DEFAULT 1,
    created_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role (role_id),
    KEY idx_users_active (is_active),
    KEY idx_users_name (full_name),
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles (role_id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- login_attempts: used for throttling (5 consecutive failures = lock).
-- No FK on purpose: attempts for emails that do not exist must be logged too.
-- ---------------------------------------------------------------------
CREATE TABLE login_attempts (
    attempt_id     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    email          VARCHAR(150)    NOT NULL,
    ip_address     VARCHAR(45)     NULL,
    was_successful TINYINT(1)      NOT NULL DEFAULT 0,
    attempted_at   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (attempt_id),
    KEY idx_attempts_email_time (email, attempted_at)
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- audit_log: written by log_action(); components B and C call it too.
-- user_id is NULL-able and SET NULL on delete so history is never lost.
-- ---------------------------------------------------------------------
CREATE TABLE audit_log (
    log_id     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    INT UNSIGNED    NULL,
    action     VARCHAR(50)     NOT NULL,
    details    VARCHAR(255)    NULL,
    ip_address VARCHAR(45)     NULL,
    created_at TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (log_id),
    KEY idx_audit_user (user_id),
    KEY idx_audit_action (action),
    KEY idx_audit_created (created_at),
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users (user_id)
        ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB;

-- ---------------------------------------------------------------------
-- password_resets: only the SHA-256 hash of the token is stored.
-- ---------------------------------------------------------------------
CREATE TABLE password_resets (
    reset_id   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    INT UNSIGNED NOT NULL,
    token_hash CHAR(64)     NOT NULL,
    expires_at DATETIME     NOT NULL,
    used_at    DATETIME     NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (reset_id),
    UNIQUE KEY uq_reset_token (token_hash),
    KEY idx_reset_user (user_id),
    CONSTRAINT fk_reset_user FOREIGN KEY (user_id) REFERENCES users (user_id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================================
-- SEED DATA
-- =====================================================================
INSERT INTO roles (role_id, role_name, description) VALUES
(1, 'administrator', 'Manages users, roles, audit log and sales reports'),
(2, 'staff',         'Kitchen staff: dishes, stock and order processing'),
(3, 'customer',      'Browses the menu, orders and tracks orders');

INSERT INTO users (role_id, full_name, email, password_hash, phone, is_active, created_at) VALUES
(1, 'Nethmi Perera', 'admin@urbaneats.lk', '$2y$10$J/7RaRuXL9cwH2qZJsp73OmBjWS1tM7Xwo3WoaWTLOycIVKLQgNPG', '0711234501', 1, NOW() - INTERVAL 58 DAY),
(2, 'Kasun Fernando', 'staff1@urbaneats.lk', '$2y$10$AyOmp1vA5cIrIj4IMe2pDuDy/ocpDdNf8STExjU84PcXgxiGexhvy', '0711234502', 1, NOW() - INTERVAL 56 DAY),
(2, 'Dilani Jayasinghe', 'staff2@urbaneats.lk', '$2y$10$kgAv/QA852vY0QwOzb.ioee5z24erejcG7ObQS3ypEjRET4ApyU6C', '0711234503', 1, NOW() - INTERVAL 54 DAY),
(2, 'Ruwan Silva', 'staff3@urbaneats.lk', '$2y$10$Qo0ZeWpo96YqKzw1sOVaUOzByo8W60GhCP7BgDkA7Ped7FFUkcgxq', '0711234504', 1, NOW() - INTERVAL 52 DAY),
(3, 'Amaya Wickramasinghe', 'customer1@urbaneats.lk', '$2y$10$Q/ps6rR/Y2.sQobKITvcS.s2f3Co.rkK6YeaOojjKeMMfE3.jIOOO', '0723530829', 1, NOW() - INTERVAL 50 DAY);

INSERT INTO login_attempts (email, ip_address, was_successful, attempted_at) VALUES
('customer1@urbaneats.lk', '192.168.1.10', 1, NOW() - INTERVAL 30 DAY),
('admin@urbaneats.lk', '192.168.1.11', 1, NOW() - INTERVAL 29 DAY),
('staff1@urbaneats.lk', '192.168.1.12', 1, NOW() - INTERVAL 28 DAY),
('customer1@urbaneats.lk', '192.168.1.13', 0, NOW() - INTERVAL 27 DAY),
('customer1@urbaneats.lk', '192.168.1.14', 1, NOW() - INTERVAL 26 DAY),
('staff2@urbaneats.lk', '192.168.1.15', 1, NOW() - INTERVAL 25 DAY),
('staff3@urbaneats.lk', '192.168.1.16', 1, NOW() - INTERVAL 24 DAY),
('admin@urbaneats.lk', '192.168.1.17', 0, NOW() - INTERVAL 23 DAY),
('admin@urbaneats.lk', '192.168.1.18', 1, NOW() - INTERVAL 22 DAY),
('customer1@urbaneats.lk', '192.168.1.19', 1, NOW() - INTERVAL 21 DAY),
('staff1@urbaneats.lk', '192.168.1.20', 1, NOW() - INTERVAL 20 DAY),
('customer1@urbaneats.lk', '192.168.1.21', 0, NOW() - INTERVAL 19 DAY),
('customer1@urbaneats.lk', '192.168.1.22', 1, NOW() - INTERVAL 18 DAY),
('staff2@urbaneats.lk', '192.168.1.23', 1, NOW() - INTERVAL 17 DAY),
('admin@urbaneats.lk', '192.168.1.24', 1, NOW() - INTERVAL 16 DAY),
('admin@urbaneats.lk', '192.168.1.25', 0, NOW() - INTERVAL 15 DAY),
('staff3@urbaneats.lk', '192.168.1.26', 1, NOW() - INTERVAL 14 DAY),
('customer1@urbaneats.lk', '192.168.1.27', 1, NOW() - INTERVAL 13 DAY),
('staff1@urbaneats.lk', '192.168.1.28', 1, NOW() - INTERVAL 12 DAY),
('customer1@urbaneats.lk', '192.168.1.29', 0, NOW() - INTERVAL 11 DAY),
('unknown.person@gmail.com', '203.94.10.55', 0, NOW() - INTERVAL 2 DAY);

INSERT INTO audit_log (user_id, action, details, ip_address, created_at) VALUES
(1, 'LOGIN', 'User logged in successfully', '192.168.1.10', NOW() - INTERVAL 24 DAY),
(2, 'REGISTER', 'New customer account created', '192.168.1.11', NOW() - INTERVAL 23 DAY),
(3, 'LOGOUT', 'User logged out', '192.168.1.12', NOW() - INTERVAL 22 DAY),
(4, 'PROFILE_UPDATE', 'Profile details updated', '192.168.1.13', NOW() - INTERVAL 21 DAY),
(5, 'PASSWORD_CHANGE', 'Password changed', '192.168.1.14', NOW() - INTERVAL 20 DAY),
(1, 'ROLE_CHANGE', 'Role changed by administrator', '192.168.1.15', NOW() - INTERVAL 19 DAY),
(1, 'USER_DEACTIVATED', 'Account deactivated by administrator', '192.168.1.16', NOW() - INTERVAL 18 DAY),
(1, 'LOGIN', 'User logged in successfully', '192.168.1.17', NOW() - INTERVAL 17 DAY),
(2, 'LOGIN', 'Staff member logged in', '192.168.1.18', NOW() - INTERVAL 16 DAY),
(3, 'LOGOUT', 'User logged out', '192.168.1.19', NOW() - INTERVAL 15 DAY),
(5, 'PROFILE_UPDATE', 'Profile details updated', '192.168.1.20', NOW() - INTERVAL 14 DAY),
(5, 'PASSWORD_CHANGE', 'Password changed', '192.168.1.21', NOW() - INTERVAL 13 DAY),
(1, 'ROLE_CHANGE', 'Role changed by administrator', '192.168.1.22', NOW() - INTERVAL 12 DAY),
(1, 'USER_ACTIVATED', 'Account activated by administrator', '192.168.1.23', NOW() - INTERVAL 11 DAY),
(5, 'LOGIN', 'User logged in successfully', '192.168.1.24', NOW() - INTERVAL 10 DAY),
(2, 'LOGIN', 'Staff member logged in', '192.168.1.25', NOW() - INTERVAL 9 DAY),
(5, 'LOGOUT', 'User logged out', '192.168.1.26', NOW() - INTERVAL 8 DAY),
(5, 'PROFILE_UPDATE', 'Profile details updated', '192.168.1.27', NOW() - INTERVAL 7 DAY),
(5, 'PASSWORD_CHANGE', 'Password changed', '192.168.1.28', NOW() - INTERVAL 6 DAY),
(1, 'ROLE_CHANGE', 'Role changed by administrator', '192.168.1.29', NOW() - INTERVAL 5 DAY),
(1, 'LOGIN', 'User logged in successfully', '192.168.1.30', NOW() - INTERVAL 4 DAY),
(2, 'LOGIN', 'User logged in successfully', '192.168.1.31', NOW() - INTERVAL 3 DAY),
(5, 'LOGIN', 'New customer logged in', '192.168.1.32', NOW() - INTERVAL 2 DAY),
(5, 'LOGOUT', 'User logged out', '192.168.1.33', NOW() - INTERVAL 1 DAY);

-- =====================================================================
-- TEST ACCOUNTS (password for every account: Test@1234)
--   administrator : admin@urbaneats.lk
--   staff         : staff1@urbaneats.lk, staff2@urbaneats.lk, staff3@urbaneats.lk
--   customer      : customer1@urbaneats.lk
-- =====================================================================
