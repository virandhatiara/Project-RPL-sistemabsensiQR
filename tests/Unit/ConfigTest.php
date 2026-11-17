<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testEnvReturnsDefaultWhenNotSet(): void
    {
        $nonExistentKey = 'NON_EXISTENT_KEY_' . uniqid();
        $this->assertNull(env($nonExistentKey));
        $this->assertSame('custom_default', env($nonExistentKey, 'custom_default'));
    }

    public function testEnvReturnsValueWhenPresent(): void
    {
        $key = 'TEST_ENV_VAR_' . uniqid();
        putenv("{$key}=AntigravityCI");

        $this->assertSame('AntigravityCI', env($key));

        // Clean up
        putenv($key);
    }

    public function testCoreConstantsAreDefined(): void
    {
        $this->assertTrue(defined('BASEURL'), 'BASEURL should be defined');
        $this->assertTrue(defined('DB_HOST'), 'DB_HOST should be defined');
        $this->assertTrue(defined('DB_USER'), 'DB_USER should be defined');
        $this->assertTrue(defined('DB_PASS'), 'DB_PASS should be defined');
        $this->assertTrue(defined('DB_NAME'), 'DB_NAME should be defined');
        $this->assertTrue(defined('AI_API_KEY'), 'AI_API_KEY should be defined');
        $this->assertTrue(defined('FONNTE_TOKEN'), 'FONNTE_TOKEN should be defined');
    }

    public function testBaseUrlHasValidUrlFormat(): void
    {
        $this->assertIsString(BASEURL);
        $this->assertMatchesRegularExpression('/^https?:\/\//i', BASEURL);
    }
}
