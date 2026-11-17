<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use SecurityHelper;

class SecurityHelperTest extends TestCase
{
    public function testEscapeConvertsHtmlSpecialCharacters(): void
    {
        $input = '<script>alert("XSS Attack!");</script>';
        $expected = '&lt;script&gt;alert(&quot;XSS Attack!&quot;);&lt;/script&gt;';
        
        $this->assertSame($expected, SecurityHelper::escape($input));
    }

    public function testGlobalEHelperFunctionWorksEqually(): void
    {
        $input = '<b>Nama Siswa & Gelar</b>';
        $expected = '&lt;b&gt;Nama Siswa &amp; Gelar&lt;/b&gt;';

        $this->assertSame($expected, e($input));
    }

    public function testEscapeHandlesSingleQuotesProperly(): void
    {
        $input = "O'Connor - Nama Guru";
        $expected = 'O&#039;Connor - Nama Guru';

        $this->assertSame($expected, SecurityHelper::escape($input));
    }

    public function testEscapeHandlesNullGracefully(): void
    {
        $this->assertSame('', SecurityHelper::escape(null));
        $this->assertSame('', e(null));
    }

    public function testEscapeHandlesNumericTypes(): void
    {
        $this->assertSame('12345', SecurityHelper::escape(12345));
        $this->assertSame('99.5', SecurityHelper::escape(99.5));
    }

    public function testCleanTextRemainsUnchanged(): void
    {
        $cleanText = 'SMK NU WARGABINANGUN - Jurusan RPL';
        $this->assertSame($cleanText, SecurityHelper::escape($cleanText));
    }
}
