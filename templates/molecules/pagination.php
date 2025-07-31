<?php
/**
 * Molecule: Advanced Pagination
 *
 * Expects:
 * - $context['total']        → total number of items
 * - $context['per_page']     → items per page
 * - $context['current_page'] → current page number
 * - $context['base_url']     → base URL (should already include `page` and `s` if needed)
 */

$total        = $context['total'];
$per_page     = $context['per_page'];
$current_page = $context['current_page'];
$base_url     = $context['base_url'];

// ✅ If total <= per_page, no pagination needed
if (empty($total) || $total <= $per_page) {
    return;
}

$total_pages  = max(1, ceil($total / $per_page));
$current_page = max(1, min($current_page, $total_pages));

// ✅ Config for visible pages
$max_visible_pages = 10; // max window length
$always_visible    = 2;  // always show first & last 2

// ✅ Calculate middle window
$start = max(1, $current_page - floor($max_visible_pages / 2));
$end   = min($total_pages, $start + $max_visible_pages - 1);

// ✅ Adjust start if near end
if (($end - $start + 1) < $max_visible_pages) {
    $start = max(1, $end - $max_visible_pages + 1);
}
?>
<div class="tablenav">
    <div class="tablenav-pages">
        <span class="displaying-num">
            <?php echo sprintf(
                _n('%d item', '%d items', $total, 'learnwpdata'),
                $total
            ); ?>
        </span>

        <span class="pagination-links">
            <?php
            // ✅ FIRST + PREV
            if ($current_page > 1) {
                $first_url = add_query_arg('paged', 1, $base_url);
                $prev_url  = add_query_arg('paged', $current_page - 1, $base_url);
                echo '<a class="first-page button" href="' . esc_url($first_url) . '">&laquo;</a>';
                echo '<a class="prev-page button" href="' . esc_url($prev_url) . '">&lsaquo;</a>';
            }

            // ✅ Always visible first 2 pages
            for ($i = 1; $i <= $always_visible && $i <= $total_pages; $i++) {
                $class = $i === $current_page ? 'button button-primary current-page' : 'button';
                $url = add_query_arg('paged', $i, $base_url);
                echo '<a class="' . $class . '" href="' . esc_url($url) . '">' . $i . '</a>';
            }

            // ✅ Ellipses before middle window if needed
            if ($start > ($always_visible + 1)) {
                echo '<span class="pagination-ellipsis">…</span>';
            }

            // ✅ Middle window
            for ($i = $start; $i <= $end; $i++) {
                // Skip pages already shown in always-visible
                if ($i <= $always_visible || $i > $total_pages - $always_visible) continue;

                $class = $i === $current_page ? 'button button-primary current-page' : 'button';
                $url = add_query_arg('paged', $i, $base_url);
                echo '<a class="' . $class . '" href="' . esc_url($url) . '">' . $i . '</a>';
            }

            // ✅ Ellipses before last two pages if needed
            if ($end < ($total_pages - $always_visible)) {
                echo '<span class="pagination-ellipsis">…</span>';
            }

            // ✅ Always visible last 2 pages
            for ($i = max($total_pages - $always_visible + 1, $always_visible + 1); $i <= $total_pages; $i++) {
                $class = $i === $current_page ? 'button button-primary current-page' : 'button';
                $url = add_query_arg('paged', $i, $base_url);
                echo '<a class="' . $class . '" href="' . esc_url($url) . '">' . $i . '</a>';
            }

            // ✅ NEXT + LAST
            if ($current_page < $total_pages) {
                $next_url = add_query_arg('paged', $current_page + 1, $base_url);
                $last_url = add_query_arg('paged', $total_pages, $base_url);
                echo '<a class="next-page button" href="' . esc_url($next_url) . '">&rsaquo;</a>';
                echo '<a class="last-page button" href="' . esc_url($last_url) . '">&raquo;</a>';
            }
            ?>
        </span>
    </div>
</div>
