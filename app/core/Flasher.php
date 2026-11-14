<?php
class Flasher {
    public static function setFlash($pesan, $aksi, $tipe) {
        $_SESSION['flash'] = [
            'pesan' => $pesan,
            'aksi'  => $aksi,
            'tipe'  => $tipe
        ];
    }

    public static function flash() {
        if (isset($_SESSION['flash'])) {
            $tipe = $_SESSION['flash']['tipe'];
            $icon = 'bi-info-circle';
            
            if ($tipe === 'success') $icon = 'bi-check-circle';
            if ($tipe === 'danger')  $icon = 'bi-exclamation-triangle';
            if ($tipe === 'warning') $icon = 'bi-exclamation-circle';

            echo '<div class="alert alert-' . $tipe . ' alert-custom alert-dismissible fade show mb-4" role="alert">
                    <i class="bi ' . $icon . '"></i>
                    <div>
                        ' . $_SESSION['flash']['pesan'] . ' <strong>' . $_SESSION['flash']['aksi'] . '</strong>
                    </div>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
            unset($_SESSION['flash']);
        }
    }
}
