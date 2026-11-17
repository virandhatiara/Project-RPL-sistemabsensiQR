<?php

/**
 * AttendanceHelper
 * Menyediakan utilitas evaluasi logika absensi:
 * 1. Penentuan status kehadiran (Hadir vs Terlambat berdasarkan batas cutoff jam 07:00:00 WIB)
 * 2. Perhitungan jeda waktu (cooldown 10 menit untuk guru)
 */
class AttendanceHelper
{
    public const DEFAULT_CUTOFF = '07:00:00';
    public const DEFAULT_COOLDOWN_MINUTES = 10.0;

    /**
     * Tentukan status presensi berdasarkan waktu pemindaian dan jam batas cutoff.
     *
     * @param string $scanTime Format 'H:i:s' atau 'H:i'
     * @param string $cutoff   Format 'H:i:s' (default: '07:00:00')
     * @return string 'hadir' | 'terlambat'
     */
    public static function determineStatus(string $scanTime, string $cutoff = self::DEFAULT_CUTOFF): string
    {
        // Standarisasi perbandingan string waktu H:i:s
        $scanFormatted = date('H:i:s', strtotime($scanTime));
        $cutoffFormatted = date('H:i:s', strtotime($cutoff));

        return ($scanFormatted > $cutoffFormatted) ? 'terlambat' : 'hadir';
    }

    /**
     * Hitung selisih waktu dalam satuan menit antara jam masuk dan jam keluar.
     *
     * @param int $entryTimestamp Unix timestamp jam masuk
     * @param int $exitTimestamp  Unix timestamp jam keluar
     * @return float Selisih dalam menit
     */
    public static function calculateDifferenceMinutes(int $entryTimestamp, int $exitTimestamp): float
    {
        return round(abs($exitTimestamp - $entryTimestamp) / 60, 2);
    }

    /**
     * Periksa apakah aturan jeda cooldown guru masih aktif.
     *
     * @param int   $entryTimestamp    Unix timestamp jam masuk
     * @param int   $exitTimestamp     Unix timestamp jam kepulangan
     * @param float $thresholdMinutes  Batas jeda minimal (default 10 menit)
     * @return bool True jika masih dalam masa cooldown (< 10 menit)
     */
    public static function isTeacherCooldownActive(
        int $entryTimestamp,
        int $exitTimestamp,
        float $thresholdMinutes = self::DEFAULT_COOLDOWN_MINUTES
    ): bool {
        $diff = self::calculateDifferenceMinutes($entryTimestamp, $exitTimestamp);
        return $diff < $thresholdMinutes;
    }
}
