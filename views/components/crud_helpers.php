<?php
/**
 * CRUD Reusable Helpers
 * Include file ini di view CRUD untuk mengurangi duplikasi kode.
 *
 * Tersedia:
 *   - crud_table_card_start($title, $icon, $addUrl, $addLabel)
 *   - crud_search_bar($action, $keyword, $placeholder)
 *   - crud_pagination($totalPages, $currentPage, $baseUrl, $extra)
 *   - crud_delete_modal($id, $entityName, $deleteUrl, $csrfToken)
 *   - crud_form_card_start($backUrl)
 *   - crud_form_card_end($submitLabel)
 *   - crud_breadcrumb($items)
 */

if (!function_exists('crud_table_card_start')) {
    function crud_table_card_start(string $title, string $icon, string $addUrl = '', string $addLabel = 'Tambah Data'): void {
        echo '<div class="table-card">';
        echo '<div class="table-toolbar">';
        echo '<h5 class="fw-bold mb-0"><i class="bi ' . $icon . ' me-2 text-primary"></i>' . e($title) . '</h5>';
        if ($addUrl) {
            echo '<a href="' . e($addUrl) . '" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>' . e($addLabel) . '</a>';
        }
        echo '</div>';
    }

    function crud_table_card_end(): void {
        echo '</div>'; // .table-card
    }

    function crud_search_bar(string $action, string $keyword = '', string $placeholder = 'Cari...'): void {
        echo '<form action="' . e($action) . '" method="GET" class="mb-4">';
        echo '<div class="input-group" style="max-width:360px;">';
        echo '<input type="text" name="keyword" class="form-control" placeholder="' . e($placeholder) . '" value="' . e($keyword) . '">';
        echo '<button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>';
        if (!empty($keyword)) {
            echo '<a href="' . e($action) . '" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-x-lg"></i></a>';
        }
        echo '</div>';
        echo '</form>';
    }

    function crud_pagination(int $totalPages, int $currentPage, string $baseUrl, string $extra = ''): void {
        if ($totalPages <= 1) return;
        echo '<nav aria-label="Page navigation" class="mt-4">';
        echo '<ul class="pagination justify-content-center">';
        // Previous
        $prevDisabled = $currentPage <= 1 ? 'disabled' : '';
        echo '<li class="page-item ' . $prevDisabled . '">';
        echo '<a class="page-link" href="' . e($baseUrl) . '?page=' . ($currentPage - 1) . $extra . '"><i class="bi bi-chevron-left"></i></a>';
        echo '</li>';
        // Pages
        $start = max(1, $currentPage - 2);
        $end   = min($totalPages, $currentPage + 2);
        if ($start > 1) {
            echo '<li class="page-item"><a class="page-link" href="' . e($baseUrl) . '?page=1' . $extra . '">1</a></li>';
            if ($start > 2) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
        }
        for ($i = $start; $i <= $end; $i++) {
            $active = ($i === $currentPage) ? 'active' : '';
            echo '<li class="page-item ' . $active . '"><a class="page-link" href="' . e($baseUrl) . '?page=' . $i . $extra . '">' . $i . '</a></li>';
        }
        if ($end < $totalPages) {
            if ($end < $totalPages - 1) echo '<li class="page-item disabled"><span class="page-link">…</span></li>';
            echo '<li class="page-item"><a class="page-link" href="' . e($baseUrl) . '?page=' . $totalPages . $extra . '">' . $totalPages . '</a></li>';
        }
        // Next
        $nextDisabled = $currentPage >= $totalPages ? 'disabled' : '';
        echo '<li class="page-item ' . $nextDisabled . '">';
        echo '<a class="page-link" href="' . e($baseUrl) . '?page=' . ($currentPage + 1) . $extra . '"><i class="bi bi-chevron-right"></i></a>';
        echo '</li>';
        echo '</ul></nav>';
    }

    /**
     * Delete confirmation modal (triggered by data-bs-target="#deleteModal-{id}")
     */
    function crud_delete_modal(int $id, string $entityLabel, string $deleteUrl, string $csrfToken): void {
        echo '
        <div class="modal fade" id="deleteModal-' . $id . '" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <div class="mx-auto p-3 rounded-circle bg-danger bg-opacity-10 mt-3">
                            <i class="bi bi-trash3-fill text-danger fs-3"></i>
                        </div>
                    </div>
                    <div class="modal-body text-center pt-2 pb-0">
                        <h5 class="fw-bold">Hapus Data?</h5>
                        <p class="text-muted">Data <strong>' . e($entityLabel) . '</strong> akan dihapus secara permanen dan tidak dapat dipulihkan.</p>
                    </div>
                    <div class="modal-footer border-0 justify-content-center gap-2 pb-4">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Batal</button>
                        <form action="' . e($deleteUrl) . '" method="POST" class="d-inline">
                            <input type="hidden" name="csrf_token" value="' . e($csrfToken) . '">
                            <button type="submit" class="btn btn-danger px-4">
                                <i class="bi bi-trash3 me-1"></i>Ya, Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>';
    }

    function crud_form_card_start(string $backUrl, string $formAction, string $method = 'POST'): void {
        $csrf = e($_SESSION['csrf_token'] ?? '');
        echo '<div class="row justify-content-center">';
        echo '<div class="col-lg-7 col-xl-6">';
        // Back breadcrumb button
        echo '<div class="mb-4"><a href="' . e($backUrl) . '" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i>Kembali</a></div>';
        echo '<?php Flasher::flash(); ?>';
        echo '<div class="table-card">';
        echo '<form action="' . e($formAction) . '" method="POST">';
        echo '<input type="hidden" name="csrf_token" value="' . $csrf . '">';
    }

    function crud_form_card_end(string $submitLabel = 'Simpan', string $submitIcon = 'bi-check-lg'): void {
        echo '<div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">';
        echo '<button type="reset" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</button>';
        echo '<button type="submit" class="btn btn-primary px-4"><i class="bi ' . $submitIcon . ' me-1"></i>' . $submitLabel . '</button>';
        echo '</div>';
        echo '</form>';
        echo '</div>'; // .table-card
        echo '</div>'; // .col
        echo '</div>'; // .row
    }

    function crud_breadcrumb(array $items): void {
        echo '<nav aria-label="breadcrumb" class="mb-3">';
        echo '<ol class="breadcrumb">';
        $last = count($items);
        $i = 0;
        foreach ($items as $label => $url) {
            $i++;
            if ($i === $last) {
                echo '<li class="breadcrumb-item active" aria-current="page">' . e($label) . '</li>';
            } else {
                echo '<li class="breadcrumb-item"><a href="' . e($url) . '" class="text-decoration-none">' . e($label) . '</a></li>';
            }
        }
        echo '</ol></nav>';
    }
}
