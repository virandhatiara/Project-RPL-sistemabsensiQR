<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use AttendanceHelper;

class AttendanceRulesTest extends TestCase
{
    /**
     * Uji pemindaian sebelum atau tepat pada jam batas cutoff (07:00:00 WIB)
     * harus menghasilkan status 'hadir'.
     */
    public function testScanBeforeOrAtCutoffReturnsHadir(): void
    {
        // Scan jam 06:30 pagi
        $this->assertSame('hadir', AttendanceHelper::determineStatus('06:30:00'));

        // Scan jam 06:45 pagi
        $this->assertSame('hadir', AttendanceHelper::determineStatus('06:45:00'));

        // Scan jam 06:59:59 (1 detik sebelum cutoff)
        $this->assertSame('hadir', AttendanceHelper::determineStatus('06:59:59'));

        // Scan tepat jam 07:00:00
        $this->assertSame('hadir', AttendanceHelper::determineStatus('07:00:00'));
    }

    /**
     * Uji pemindaian setelah jam batas cutoff (> 07:00:00 WIB)
     * harus menghasilkan status 'terlambat'.
     */
    public function testScanAfterCutoffReturnsTerlambat(): void
    {
        // Scan jam 07:00:01 (lewat 1 detik)
        $this->assertSame('terlambat', AttendanceHelper::determineStatus('07:00:01'));

        // Scan jam 07:05:00
        $this->assertSame('terlambat', AttendanceHelper::determineStatus('07:05:00'));

        // Scan jam 07:15:30
        $this->assertSame('terlambat', AttendanceHelper::determineStatus('07:15:30'));

        // Scan jam 08:00:00
        $this->assertSame('terlambat', AttendanceHelper::determineStatus('08:00:00'));
    }

    /**
     * Uji perhitungan selisih menit antara jam masuk dan jam keluar.
     */
    public function testCalculateDifferenceMinutes(): void
    {
        $jamMasuk = strtotime('2026-11-17 06:45:00');
        $jamKeluarSatuJam = strtotime('2026-11-17 07:45:00');

        $selisih = AttendanceHelper::calculateDifferenceMinutes($jamMasuk, $jamKeluarSatuJam);
        $this->assertSame(60.0, $selisih);

        $jamKeluarLimaMenit = strtotime('2026-11-17 06:50:00');
        $this->assertSame(5.0, AttendanceHelper::calculateDifferenceMinutes($jamMasuk, $jamKeluarLimaMenit));
    }

    /**
     * Uji aturan jeda cooldown 10 menit untuk pemindaian guru.
     */
    public function testTeacherCooldownActiveUnder10Minutes(): void
    {
        $jamMasuk = strtotime('2026-11-17 06:30:00');

        // Scan pulang setelah 3 menit -> Cooldown masih aktif (True)
        $scanTigaMenit = strtotime('2026-11-17 06:33:00');
        $this->assertTrue(AttendanceHelper::isTeacherCooldownActive($jamMasuk, $scanTigaMenit));

        // Scan pulang setelah 9 menit 59 detik -> Cooldown masih aktif (True)
        $scanSembilanMenit = strtotime('2026-11-17 06:39:59');
        $this->assertTrue(AttendanceHelper::isTeacherCooldownActive($jamMasuk, $scanSembilanMenit));

        // Scan pulang tepat 10 menit -> Cooldown selesai (False)
        $scanSepuluhMenit = strtotime('2026-11-17 06:40:00');
        $this->assertFalse(AttendanceHelper::isTeacherCooldownActive($jamMasuk, $scanSepuluhMenit));

        // Scan pulang setelah 8 jam (jam pulang resmi 14:30) -> Cooldown selesai (False)
        $scanSore = strtotime('2026-11-17 14:30:00');
        $this->assertFalse(AttendanceHelper::isTeacherCooldownActive($jamMasuk, $scanSore));
    }
}
