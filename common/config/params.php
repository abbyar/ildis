<?php
return [
    'adminEmail' => getenv('ADMIN_EMAIL') ?: 'admin@example.com',
    'supportEmail' => getenv('SUPPORT_EMAIL') ?: 'support@example.com',
    'user.passwordResetTokenExpire' => 3600,
    'recaptcha.siteKey' => getenv('RECAPTCHA_SITE_KEY'),
    'recaptcha.secretKey' => getenv('RECAPTCHA_SECRET_KEY'),
];
