INSERT INTO userRole (id, name, auth, created_at, updated_at)
VALUES
    (1, 'Admin', 'all', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'User', 'dashboard.view,users.view,roles.view,logs.view,translations.view,uploads.view,docs.view,profile,account', strftime('%s', 'now'), strftime('%s', 'now'));

INSERT INTO users (name, surname, email, password, role, status, created_at, updated_at)
VALUES
    ('System', 'Admin', 'admin@example.com', '$2y$12$YVgEIsqR1YIKYqKsUf28q.eEMeGox71Jswxa3grlV739KFMSwXsFG', 1, 'active', strftime('%s', 'now'), strftime('%s', 'now')),
    ('Demo', 'User', 'user@example.com', '$2y$12$YVgEIsqR1YIKYqKsUf28q.eEMeGox71Jswxa3grlV739KFMSwXsFG', 2, 'active', strftime('%s', 'now'), strftime('%s', 'now'));

INSERT INTO userProfile (userId, phone, city, address, created_at, updated_at)
VALUES
    (1, '05550000001', 'Istanbul', 'Admin adres', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, '05550000000', 'Istanbul', 'Demo adres', strftime('%s', 'now'), strftime('%s', 'now'));

INSERT INTO api_tokens (user_id, name, token, header_name, type, is_active, created_at, updated_at)
VALUES (1, 'Default Mobile App', 'change-this-api-token', 'X-Api-Token', 'mobile', 1, strftime('%s', 'now'), strftime('%s', 'now'));

INSERT INTO languages (id, code, name, is_active, is_default, created_at, updated_at)
VALUES
    (1, 'tr', 'Turkce', 1, 1, strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'en', 'English', 1, 0, strftime('%s', 'now'), strftime('%s', 'now'));

INSERT INTO language_translations (language_id, translation_key, translation_value, created_at, updated_at)
VALUES
    (1, 'site_title', 'Mini Framework', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'site_badge', 'Site Template', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'welcome_text', 'Bu sayfa secili dilde veritabanindan gelen ceviri metinlerini kullanir.', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'site_template_title', 'Template sistemi', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'site_template_description', 'HTML dosyalari icindeki placeholder alanlar aktif dildeki degerlerle otomatik parse edilir.', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'site_area_title', 'Site alani', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'site_area_description', 'Bu alan sadece site tarafina aittir ve admin tarafindan bagimsiz ilerler.', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'shared_service_title', 'Ortak servis', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'shared_service_description', 'ile upload ve mail gibi islemleri tek merkezden yonetir. Upload hedefi:', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'api_ready_title', 'API hazir', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'api_ready_description', 'Mobil uygulamalar icin JSON endpointleri kullanima hazirdir. Ornekler: /api/v1/status ve /api/v1/secure-status', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'path_label', 'Path', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'page_generated_at_label', 'Sayfa olusturma zamani', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'admin_panel_title', 'Admin Panel', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'admin_menu_dashboard', 'Dashboard', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'admin_menu_return_site', 'Siteye Don', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'admin_template_badge', 'Admin Template', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'dashboard_title', 'Yonetim Paneli', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'admin_welcome', 'Hos geldiniz,', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'admin_intro', 'Bu alan sadece admin tarafina aittir. Site controller ve site template kokunden bagimsiz calisir.', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'stat_users', 'Kullanicilar', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'stat_orders', 'Siparisler', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'stat_tickets', 'Destek Talepleri', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'admin_shared_service_description', 'ile admin ve site ayni upload/mail metodlarini kullanabilir.', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'upload_target_label', 'Upload hedefi', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'logout_button', 'Cikis Yap', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'admin_login_badge', 'Admin Login', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'admin_login_title', 'Yonetici Girisi', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'admin_login_description', 'Yonetim paneline erismek icin giris yapin.', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'email_label', 'E-posta', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'password_label', 'Sifre', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'login_button', 'Giris Yap', strftime('%s', 'now'), strftime('%s', 'now')),
    (1, 'demo_account_label', 'Demo hesap', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'site_title', 'Mini Framework', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'site_badge', 'Site Template', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'welcome_text', 'Welcome', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'site_template_title', 'Template system', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'site_template_description', 'Placeholder fields inside HTML files are automatically parsed with values from the active language.', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'site_area_title', 'Site area', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'site_area_description', 'This area belongs only to the site side and stays independent from the admin side.', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'shared_service_title', 'Shared service', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'shared_service_description', 'manages upload and mail operations from a single point. Upload target:', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'api_ready_title', 'API ready', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'api_ready_description', 'JSON endpoints for mobile applications are ready to use. Examples: /api/v1/status and /api/v1/secure-status', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'path_label', 'Path', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'page_generated_at_label', 'Generated at', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'admin_panel_title', 'Admin Panel', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'admin_menu_dashboard', 'Dashboard', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'admin_menu_return_site', 'Return to Site', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'admin_template_badge', 'Admin Template', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'dashboard_title', 'Dashboard', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'admin_welcome', 'Welcome,', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'admin_intro', 'This area belongs only to the admin side. It works independently from the site controller and template tree.', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'stat_users', 'Users', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'stat_orders', 'Orders', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'stat_tickets', 'Support Tickets', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'admin_shared_service_description', 'lets admin and site use the same upload and mail methods.', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'upload_target_label', 'Upload target', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'logout_button', 'Logout', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'admin_login_badge', 'Admin Login', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'admin_login_title', 'Administrator Login', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'admin_login_description', 'Sign in to access the administration panel.', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'email_label', 'Email', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'password_label', 'Password', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'login_button', 'Sign In', strftime('%s', 'now'), strftime('%s', 'now')),
    (2, 'demo_account_label', 'Demo account', strftime('%s', 'now'), strftime('%s', 'now'));
