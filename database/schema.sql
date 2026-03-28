PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    surname TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    role INTEGER NOT NULL DEFAULT 1,
    status TEXT NOT NULL DEFAULT 'active',
    created_at INTEGER NOT NULL,
    updated_at INTEGER NOT NULL,
    FOREIGN KEY (role) REFERENCES userRole(id) ON DELETE RESTRICT
);

CREATE TABLE IF NOT EXISTS userRole (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    auth TEXT NOT NULL,
    created_at INTEGER NOT NULL,
    updated_at INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS userProfile (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    userId INTEGER NOT NULL,
    phone TEXT,
    city TEXT,
    address TEXT,
    created_at INTEGER NOT NULL,
    updated_at INTEGER NOT NULL,
    FOREIGN KEY (userId) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS languages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code TEXT NOT NULL UNIQUE,
    name TEXT NOT NULL,
    is_active INTEGER NOT NULL DEFAULT 1,
    is_default INTEGER NOT NULL DEFAULT 0,
    created_at INTEGER NOT NULL,
    updated_at INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS language_translations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    language_id INTEGER NOT NULL,
    translation_key TEXT NOT NULL,
    translation_value TEXT NOT NULL,
    created_at INTEGER NOT NULL,
    updated_at INTEGER NOT NULL,
    FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS api_tokens (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER,
    name TEXT NOT NULL,
    token TEXT NOT NULL UNIQUE,
    header_name TEXT NOT NULL DEFAULT 'X-Api-Token',
    type TEXT NOT NULL DEFAULT 'mobile',
    is_active INTEGER NOT NULL DEFAULT 1,
    last_used_at INTEGER,
    expires_at INTEGER,
    created_at INTEGER NOT NULL,
    updated_at INTEGER NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS uploads (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    channel TEXT NOT NULL,
    original_name TEXT NOT NULL,
    stored_name TEXT NOT NULL,
    directory_name TEXT NOT NULL,
    mime_type TEXT,
    extension TEXT,
    size INTEGER NOT NULL DEFAULT 0,
    public_path TEXT NOT NULL,
    created_at INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS mail_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    channel TEXT NOT NULL,
    recipient TEXT NOT NULL,
    subject TEXT NOT NULL,
    template_name TEXT,
    status TEXT NOT NULL DEFAULT 'queued',
    response_message TEXT,
    created_at INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    method TEXT NOT NULL,
    path TEXT NOT NULL,
    query_params TEXT,
    request_body TEXT,
    request_headers TEXT,
    request_files TEXT,
    response_status INTEGER NOT NULL DEFAULT 200,
    response_headers TEXT,
    response_body TEXT,
    ip_address TEXT,
    user_agent TEXT,
    error_message TEXT,
    created_at INTEGER NOT NULL,
    updated_at INTEGER NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_user_role_name ON userRole(name);
CREATE INDEX IF NOT EXISTS idx_user_profile_user_id ON userProfile(userId);
CREATE UNIQUE INDEX IF NOT EXISTS uk_user_profile_user_id ON userProfile(userId);
CREATE INDEX IF NOT EXISTS idx_languages_code ON languages(code);
CREATE INDEX IF NOT EXISTS idx_language_translations_language_id ON language_translations(language_id);
CREATE UNIQUE INDEX IF NOT EXISTS uk_language_translations_key ON language_translations(language_id, translation_key);
CREATE INDEX IF NOT EXISTS idx_api_tokens_token ON api_tokens(token);
CREATE INDEX IF NOT EXISTS idx_api_tokens_user_id ON api_tokens(user_id);
CREATE INDEX IF NOT EXISTS idx_uploads_channel ON uploads(channel);
CREATE INDEX IF NOT EXISTS idx_mail_logs_channel ON mail_logs(channel);
CREATE INDEX IF NOT EXISTS idx_log_method ON log(method);
CREATE INDEX IF NOT EXISTS idx_log_path ON log(path);
CREATE INDEX IF NOT EXISTS idx_log_created_at ON log(created_at);
