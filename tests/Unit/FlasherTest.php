<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Flasher;

class FlasherTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset($_SESSION['flash']);
    }

    protected function tearDown(): void
    {
        unset($_SESSION['flash']);
    }

    public function testSetFlashStoresDataInSession(): void
    {
        Flasher::setFlash('Data siswa berhasil', 'ditambahkan', 'success');

        $this->assertArrayHasKey('flash', $_SESSION);
        $this->assertSame('Data siswa berhasil', $_SESSION['flash']['pesan']);
        $this->assertSame('ditambahkan', $_SESSION['flash']['aksi']);
        $this->assertSame('success', $_SESSION['flash']['tipe']);
    }

    public function testFlashRendersSuccessAlertAndClearsSession(): void
    {
        Flasher::setFlash('Presensi berhasil', 'disimpan', 'success');

        ob_start();
        Flasher::flash();
        $output = ob_get_clean();

        $this->assertStringContainsString('alert-success', $output);
        $this->assertStringContainsString('bi-check-circle', $output);
        $this->assertStringContainsString('Presensi berhasil', $output);
        $this->assertStringContainsString('disimpan', $output);
        $this->assertArrayNotHasKey('flash', $_SESSION, 'Session flash must be cleared after flash() is called');
    }

    public function testFlashRendersDangerAlert(): void
    {
        Flasher::setFlash('QR Code tidak valid', 'dibatalkan', 'danger');

        ob_start();
        Flasher::flash();
        $output = ob_get_clean();

        $this->assertStringContainsString('alert-danger', $output);
        $this->assertStringContainsString('bi-exclamation-triangle', $output);
        $this->assertArrayNotHasKey('flash', $_SESSION);
    }

    public function testFlashRendersWarningAlert(): void
    {
        Flasher::setFlash('Waktu cooldown belum habis', 'peringatan', 'warning');

        ob_start();
        Flasher::flash();
        $output = ob_get_clean();

        $this->assertStringContainsString('alert-warning', $output);
        $this->assertStringContainsString('bi-exclamation-circle', $output);
        $this->assertArrayNotHasKey('flash', $_SESSION);
    }

    public function testFlashProducesNoOutputWhenNoFlashSet(): void
    {
        ob_start();
        Flasher::flash();
        $output = ob_get_clean();

        $this->assertSame('', $output);
    }
}
