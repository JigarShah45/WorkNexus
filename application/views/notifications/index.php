<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->load->view('layouts/header');
$this->load->view('layouts/navbar');
?>

<style>
    .notification-filter-tabs {
        display: flex;
        gap: 4px;
        margin-bottom: 20px;
        background: var(--surface-soft);
        border-radius: var(--radius-sm);
        padding: 4px;
        width: fit-content;
    }
    .notification-filter-tabs .tab-btn {
        padding: 8px 20px;
        border: none;
        border-radius: var(--radius-sm);
        background: transparent;
        color: var(--muted);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        font-family: inherit;
    }
    .notification-filter-tabs .tab-btn:hover {
        color: var(--text);
    }
    .notification-filter-tabs .tab-btn.active {
        background: var(--surface);
        color: var(--primary);
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .notification-list {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .notification-item {
        display: flex;
        gap: 14px;
        padding: 16px 20px;
        background: var(--surface);
        border: 1px solid var(--border-light);
        border-radius: var(--radius);
        text-decoration: none;
        color: var(--text);
        transition: all 0.15s;
        cursor: pointer;
        align-items: flex-start;
    }
    .notification-item:hover {
        background: var(--primary-soft);
        border-color: var(--primary);
    }
    .notification-item.unread {
        background: var(--primary-soft);
        border-left: 3px solid var(--primary);
    }
    .notification-item.unread .notification-title {
        font-weight: 700;
    }
    .notification-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .notification-icon.leave {
        background: #FEF3C7;
        color: #D97706;
    }
    .notification-icon.meeting {
        background: #DBEAFE;
        color: #2563EB;
    }
    .notification-icon.salary {
        background: #D1FAE5;
        color: #059669;
    }
    .notification-icon.general {
        background: var(--surface-soft);
        color: var(--muted);
    }
    html[data-theme="dark"] .notification-icon.leave {
        background: rgba(217,119,6,0.15);
    }
    html[data-theme="dark"] .notification-icon.meeting {
        background: rgba(37,99,235,0.15);
    }
    html[data-theme="dark"] .notification-icon.salary {
        background: rgba(5,150,105,0.15);
    }
    .notification-body {
        flex: 1;
        min-width: 0;
    }
    .notification-title {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 2px;
        color: var(--text);
    }
    .notification-message {
        font-size: 13px;
        color: var(--muted);
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .notification-time {
        font-size: 11px;
        color: var(--text-light);
        margin-top: 4px;
    }
    .notification-empty {
        text-align: center;
        padding: 60px 20px;
        color: var(--muted);
    }
    .notification-empty i {
        font-size: 3rem;
        display: block;
        margin-bottom: 16px;
        opacity: 0.4;
    }
    .notification-empty p {
        font-size: 14px;
        margin: 0;
    }
    .notification-pagination {
        display: flex;
        justify-content: center;
        gap: 4px;
        margin-top: 24px;
    }
    .notification-pagination a,
    .notification-pagination span {
        padding: 8px 14px;
        border-radius: var(--radius-sm);
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        color: var(--text);
        background: var(--surface);
        border: 1px solid var(--border-light);
        transition: all 0.15s;
    }
    .notification-pagination a:hover {
        background: var(--primary-soft);
        border-color: var(--primary);
        color: var(--primary);
    }
    .notification-pagination span.current {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
    }
    .notification-top-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .mark-all-btn {
        border: none;
        background: none;
        color: var(--primary);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        padding: 6px 12px;
        border-radius: var(--radius-sm);
        transition: all 0.15s;
        font-family: inherit;
    }
    .mark-all-btn:hover {
        background: var(--primary-soft);
    }
</style>

<div class="container py-4"> 
    <?php $this->load->view('layouts/page_header', array(
        'title' => 'Notifications',
        'icon' => 'bi-bell-fill',
        'show_back' => TRUE,
        'back_url' => site_url('dashboard'),
        'back_label' => 'Back'
    )); ?>

    <div class="notification-top-bar">
        <div class="notification-filter-tabs">
            <a href="<?= site_url('notifications?filter=all'); ?>"
               class="tab-btn <?= $filter === 'all' ? 'active' : '' ?>">All</a>
            <a href="<?= site_url('notifications?filter=unread'); ?>"
               class="tab-btn <?= $filter === 'unread' ? 'active' : '' ?>">Unread <?= $unread_count > 0 ? '(' . $unread_count . ')' : '' ?></a>
        </div>
        <?php if ($unread_count > 0): ?>
            <button class="mark-all-btn" id="markAllReadBtn">
                <i class="bi bi-check-all"></i> Mark all read
            </button>
        <?php endif; ?>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="notification-empty">
            <i class="bi bi-bell"></i>
            <p>You're all caught up.<br>No new notifications.</p>
        </div>
    <?php else: ?>
        <div class="notification-list" id="notificationList">
            <?php foreach ($notifications as $n): ?>
                <a href="<?= $n->target_url ? site_url($n->target_url) : '#'; ?>"
                   class="notification-item <?= $n->is_read ? '' : 'unread' ?>"
                   data-id="<?= $n->notification_id ?>"
                   <?= $n->target_url ? '' : 'onclick="return false;"' ?>>
                    <div class="notification-icon <?= htmlspecialchars($n->type) ?>">
                        <?php
                        $icons = array(
                            'leave'   => 'bi-calendar-x',
                            'meeting' => 'bi-easel',
                            'salary'  => 'bi-graph-up-arrow',
                        );
                        $icon = isset($icons[$n->type]) ? $icons[$n->type] : 'bi-bell';
                        ?>
                        <i class="bi <?= $icon ?>"></i>
                    </div>
                    <div class="notification-body">
                        <div class="notification-title"><?= htmlspecialchars($n->title) ?></div>
                        <div class="notification-message"><?= htmlspecialchars($n->message) ?></div>
                        <div class="notification-time" data-time="<?= $n->created_at ?>"><?= notification_format_time($n->created_at) ?></div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="notification-pagination">
                <?php if ($current_page > 1): ?>
                    <a href="<?= site_url('notifications?filter=' . $filter . '&page=' . ($current_page - 1)) ?>">Prev</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if ($i == $current_page): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="<?= site_url('notifications?filter=' . $filter . '&page=' . $i) ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($current_page < $total_pages): ?>
                    <a href="<?= site_url('notifications?filter=' . $filter . '&page=' . ($current_page + 1)) ?>">Next</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateUnreadTab(count) {
        var $unreadTab = $('.notification-filter-tabs .tab-btn').filter(function() {
            return $(this).attr('href').indexOf('filter=unread') > -1;
        });
        if (count > 0) {
            $unreadTab.text('Unread (' + count + ')');
        } else {
            $unreadTab.text('Unread (0)');
        }
    }

    function handleEmptyUnreadView() {
        var $list = $('#notificationList');
        if ($list.length && $list.find('.notification-item').length === 0) {
            $list.replaceWith(
                '<div class="notification-empty" id="notificationEmpty">' +
                    '<i class="bi bi-bell"></i>' +
                    '<p>You\'re all caught up.<br>No new notifications.</p>' +
                '</div>'
            );
        }
    }

    $(document).on('click', '.notification-item.unread', function(e) {
        var $item = $(this);
        var id = $item.data('id');
        $.post('<?= site_url("notifications/mark_read") ?>/' + id, {
            '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
        }, function(resp) {
            if (resp.status) {
                $item.removeClass('unread');
                if (typeof setNotificationBadge === 'function') {
                    setNotificationBadge(resp.unread_count);
                }
                updateUnreadTab(resp.unread_count);
                if (!resp.unread_count) {
                    $('#markAllReadBtn').fadeOut();
                }
            }
        }, 'json').fail(function() {
            if (typeof setNotificationBadge === 'function') {
                $.getJSON('<?= site_url("notifications/unread_count") ?>', function(r) {
                    if (r.status) setNotificationBadge(r.count);
                });
            }
        });
    });

    $('#markAllReadBtn').on('click', function() {
        var $btn = $(this);
        $btn.prop('disabled', true);
        $.post('<?= site_url("notifications/mark_all_read") ?>', {
            '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
        }, function(resp) {
            if (resp.status) {
                $('.notification-item.unread').removeClass('unread');
                if (typeof setNotificationBadge === 'function') {
                    setNotificationBadge(resp.unread_count);
                }
                $btn.fadeOut();
                updateUnreadTab(resp.unread_count);
                if (window.location.search.indexOf('filter=unread') > -1) {
                    handleEmptyUnreadView();
                }
            } else {
                $btn.prop('disabled', false);
            }
        }, 'json').fail(function() {
            $btn.prop('disabled', false);
            if (typeof setNotificationBadge === 'function') {
                $.getJSON('<?= site_url("notifications/unread_count") ?>', function(r) {
                    if (r.status) setNotificationBadge(r.count);
                });
            }
        });
    });
});
</script>

<?php
if ( ! function_exists('notification_format_time'))
{
    function notification_format_time($datetime) {
        $dt = new DateTime($datetime, new DateTimeZone('Asia/Kolkata'));
        $now = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
        $diff = $now->getTimestamp() - $dt->getTimestamp();

        if ($diff < 60) return 'Just now';
        if ($diff < 3600) return floor($diff / 60) . ' min ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hour' . (floor($diff / 3600) > 1 ? 's' : '') . ' ago';
        if ($diff < 172800) return 'Yesterday';
        return $dt->format('d M Y, h:i A') . ' IST';
    }
}
$this->load->view('layouts/footer');
?>
