[![Codeship Status for tightenco/mailthief](https://codeship.com/projects/860d2030-1ae7-0134-a954-66ed86225da0/status?branch=master)](https://codeship.com/projects/159501)

![](https://raw.githubusercontent.com/tightenco/mailthief/master/mailthief-logo.png)

# MailThief

MailThief is a fake mailer for Laravel applications (5.0+) that makes it easy to test mail without actually sending any emails.

## Quickstart

Installation:

```bash
composer require tightenco/mailthief --dev
```

Example route:

```php
Route::post('register', function () {
    // <snip> Validation, create account, etc. </snip>

    Mail::send('emails.welcome', [], function ($m) {
        $email = request('email');
        $m->to($email);
        $m->subject('Welcome to my app!');
        $m->from('noreply@example.com');
        $m->bcc('notifications@example.com');
        $m->getHeaders()->addTextHeader('X-MailThief-Variables', 'mailthief');
    });

    // <snip> Return response </snip>
});
```

If you're copying this sample test, remember to create an email view at `resources/views/emails/welcome.blade.php`.

Example test:

```php
use MailThief\Testing\InteractsWithMail;

class RegistrationTest extends TestCase
{
    // Provides convenient testing traits and initializes MailThief
    use InteractsWithMail;

    public function test_new_users_are_sent_a_welcome_email()
    {
        $this->post('register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret',
        ]);

        // Check that an email was sent to this email address
        $this->seeMessageFor('john@example.com');

        // BCC addresses are included too
        $this->seeMessageFor('notifications@example.com');

        // Make sure the email has the correct subject
        $this->seeMessageWithSubject('Welcome to my app!');

        // Make sure the email was sent from the correct address
        $this->seeMessageFrom('noreply@example.com');

        // Make sure a given header is set on an email
        $this->seeHeaders('X-MailThief-Variables');

        // Make sure the header is set to a given value
        $this->seeHeaders('X-MailThief-Variables', 'mailthief');

        // Make sure the email contains text in the body of the message
        // Default is to search the html rendered view
        $this->assertTrue($this->lastMessage()->contains('Some text in the message'));
        // To search in the raw text
        $this->assertTrue($this->lastMessage()->contains('Some text in the message', 'raw'));
    }
}
```

MailThief supports just about everything you can do with the regular Laravel `Mailer` and `Message` classes. More detailed documentation is coming soon, but in the mean time, explore the [MailThief](https://github.com/tightenco/mailthief/blob/master/src/MailThief.php) and [Message](https://github.com/tightenco/mailthief/blob/master/src/Message.php) classes to get an idea of what's available.

If you’re using the new Mailables syntax in Laravel 5.3, you can use the [native mail assertions](https://laravel.com/docs/master/mocking#mail-fake). But if you’re using the classic mail syntax in any version of Laravel, MailThief is still your best option.


