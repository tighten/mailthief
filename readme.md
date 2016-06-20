# MailThief

MailThief is a fake mailer for Laravel applications that makes it easy to test mail without actually sending any emails.

## Quickstart

Example route:

```php
Route::post('register', function () {
    // <snip> Validation, create account, etc. </snip>

    Mail::send('emails.welcome', [], function ($m) {
        $email = request('email');
        $m->to($email)
        $m->subject('Welcome to my app!');
        $m->from('noreply@example.com');
        $m->bcc('notifications@example.com');
    });

    // <snip> Return response </snip>
});
```

Example test:

```php
use MailThief\Facades\MailThief;

class RegistrationTest extends TestCase
{
    public function test_new_users_are_sent_a_welcome_email()
    {
        MailThief::hijack();

        $this->post('register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret',
        ]);

        $this->assertTrue(MailThief::hasMessageFor('john@example.com'));
        $this->assertTrue(MailThief::hasMessageFor('notifications@example.com'));
        $this->assertEquals('Welcome to my app!', MailThief::lastMessage()->subject);
        $this->assertEquals(['noreply@example.com'], MailThief::lastMessage()->from);
    }
}
```

MailThief supports just about everything you can do with the regular Laravel `Mailer` and `Message` classes. More detailed documentation is coming soon, but in the mean time, explore the [MailThief](https://github.com/tightenco/mailthief/blob/master/src/MailThief.php) and [Message](https://github.com/tightenco/mailthief/blob/master/src/Message.php) classes to get an idea of what's available.
