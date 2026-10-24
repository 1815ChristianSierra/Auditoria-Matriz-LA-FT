<?php
namespace Tests\Feature\Auth;
use Tests\TestCase;
class PasswordResetTest extends TestCase {
    public function test_mail_reset_is_disabled_until_a_real_mail_transport_is_configured(): void {
        $this->get('/forgot-password')->assertNotFound();
        $this->post('/forgot-password', [])->assertNotFound();
        $this->get('/reset-password/unused')->assertNotFound();
        $this->post('/reset-password', [])->assertNotFound();
    }
}
