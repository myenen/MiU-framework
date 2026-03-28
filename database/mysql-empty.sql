SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS log;
DROP TABLE IF EXISTS mail_logs;
DROP TABLE IF EXISTS uploads;
DROP TABLE IF EXISTS api_tokens;
DROP TABLE IF EXISTS language_translations;
DROP TABLE IF EXISTS languages;
DROP TABLE IF EXISTS userProfile;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS userRole;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE userRole (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    auth TEXT NOT NULL,
    created_at BIGINT UNSIGNED NOT NULL,
    updated_at BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    KEY idx_userRole_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    surname VARCHAR(100) NOT NULL,
    email VARCHAR(191) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role INT UNSIGNED NOT NULL DEFAULT 1,
    status VARCHAR(50) NOT NULL DEFAULT 'active',
    created_at BIGINT UNSIGNED NOT NULL,
    updated_at BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_users_email (email),
    KEY idx_users_email (email),
    KEY idx_users_role (role),
    KEY idx_users_status (status),
    CONSTRAINT fk_users_role
        FOREIGN KEY (role) REFERENCES userRole(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE userProfile (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    userId INT UNSIGNED NOT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    city VARCHAR(100) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    created_at BIGINT UNSIGNED NOT NULL,
    updated_at BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_userProfile_userId (userId),
    KEY idx_userProfile_userId (userId),
    CONSTRAINT fk_userProfile_user
        FOREIGN KEY (userId) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE languages (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code VARCHAR(10) NOT NULL,
    name VARCHAR(100) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    created_at BIGINT UNSIGNED NOT NULL,
    updated_at BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_languages_code (code),
    KEY idx_languages_active (is_active),
    KEY idx_languages_default (is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE language_translations (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    language_id INT UNSIGNED NOT NULL,
    translation_key VARCHAR(191) NOT NULL,
    translation_value LONGTEXT NOT NULL,
    created_at BIGINT UNSIGNED NOT NULL,
    updated_at BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_language_translations_key (language_id, translation_key),
    KEY idx_language_translations_language_id (language_id),
    KEY idx_language_translations_key_only (translation_key),
    CONSTRAINT fk_language_translations_language
        FOREIGN KEY (language_id) REFERENCES languages(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE api_tokens (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NULL,
    name VARCHAR(150) NOT NULL,
    token VARCHAR(255) NOT NULL,
    header_name VARCHAR(100) NOT NULL DEFAULT 'X-Api-Token',
    type VARCHAR(100) NOT NULL DEFAULT 'default',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    expires_at BIGINT UNSIGNED NULL,
    last_used_at BIGINT UNSIGNED NULL,
    created_at BIGINT UNSIGNED NOT NULL,
    updated_at BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_api_tokens_token (token),
    KEY idx_api_tokens_user_id (user_id),
    KEY idx_api_tokens_header_name (header_name),
    KEY idx_api_tokens_is_active (is_active),
    CONSTRAINT fk_api_tokens_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE uploads (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    channel VARCHAR(100) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_name VARCHAR(255) NOT NULL,
    directory_name VARCHAR(255) NOT NULL,
    mime_type VARCHAR(150) DEFAULT NULL,
    extension VARCHAR(20) DEFAULT NULL,
    size BIGINT UNSIGNED NOT NULL DEFAULT 0,
    public_path VARCHAR(500) NOT NULL,
    created_at BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    KEY idx_uploads_channel (channel),
    KEY idx_uploads_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE mail_logs (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    channel VARCHAR(100) NOT NULL,
    recipient VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    template_name VARCHAR(255) DEFAULT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'queued',
    response_message TEXT DEFAULT NULL,
    created_at BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    KEY idx_mail_logs_channel (channel),
    KEY idx_mail_logs_status (status),
    KEY idx_mail_logs_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE log (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    method VARCHAR(20) NOT NULL,
    path VARCHAR(255) NOT NULL,
    query_params LONGTEXT NULL,
    request_body LONGTEXT NULL,
    request_headers LONGTEXT NULL,
    request_files LONGTEXT NULL,
    response_status INT NOT NULL DEFAULT 200,
    response_headers LONGTEXT NULL,
    response_body LONGTEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    error_message TEXT NULL,
    created_at BIGINT UNSIGNED NOT NULL,
    updated_at BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    KEY idx_log_method (method),
    KEY idx_log_path (path),
    KEY idx_log_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
