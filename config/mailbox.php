<?php

return [
    'host' => env('MAILBOX_HOST', 'imap.example.com'),
    'port' => env('MAILBOX_PORT', 993),
    'username' => env('MAILBOX_USERNAME', 'your-email@example.com'),
    'password' => env('MAILBOX_PASSWORD', 'your-password'),
    'encryption' => env('MAILBOX_ENCRYPTION', 'ssl'),
];
