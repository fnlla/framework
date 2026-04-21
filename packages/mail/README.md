**FNLLA/MAIL**

Mail module using Symfony Mailer.

**INSTALLATION**
```bash
composer require fnlla/mail
```

**SERVICE PROVIDER**
Auto-discovered provider:
**-** `Finella\Mail\MailServiceProvider`

**CONFIGURATION**
`config/mail/mail.php`
**-** `dsn` or SMTP parts: `host`, `port`, `username`, `password`, `encryption`
**-** default `from` address and name

**USAGE**
```php
use Finella\Mail\Message;
use Finella\Mail\MailerInterface;

$mailer = $app->make(MailerInterface::class);
$msg = new Message(
    from: new \Finella\Mail\Address('noreply@example.com', 'fnlla (finella)'),
    to: [new \Finella\Mail\Address('user@example.com')],
    subject: 'Welcome',
    text: 'Hello'
);

$mailer->send($msg);
```

**NOTES**
**-** Use `MAIL_DSN` for advanced transports.
**-** For local testing, use a dev SMTP server (e.g. Mailpit).

**TESTING**
```bash
php tests/smoke.php
```
