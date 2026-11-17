<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Csrf;

class CsrfTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clear session before each test
        $_SESSION = [];
    }

    public function testGetTokenGeneratesValidHexTokenWhenEmpty(): void
    {
        $this->assertArrayNotHasKey('csrf_token', $_SESSION);

        $token = Csrf::getToken();

        // 32 random bytes converted to hex results in a 64-character string
        $this->assertNotEmpty($token);
        $this->assertSame(64, strlen($token));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $token);
        $this->assertSame($token, $_SESSION['csrf_token']);
    }

    public function testGetTokenReturnsExistingTokenWithoutRegenerating(): void
    {
        $existingToken = '11223344556677889900aabbccddeeff11223344556677889900aabbccddeeff';
        $_SESSION['csrf_token'] = $existingToken;

        $retrievedToken = Csrf::getToken();

        $this->assertSame($existingToken, $retrievedToken);
    }

    public function testValidatePassesSilentlyWhenRequestMethodIsGet(): void
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SESSION['csrf_token'] = 'valid-token-sample';
        $_POST = [];

        // Should not throw or die on GET request
        Csrf::validate();
        $this->assertTrue(true);
    }

    public function testValidatePassesWhenPostTokenMatchesSessionToken(): void
    {
        $token = 'test-token-1234567890abcdef';
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SESSION['csrf_token'] = $token;
        $_POST['csrf_token'] = $token;

        // Validation succeeds silently
        Csrf::validate();
        $this->assertSame($_POST['csrf_token'], $_SESSION['csrf_token']);
    }
}
