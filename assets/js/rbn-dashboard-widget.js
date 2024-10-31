jQuery(document).ready(function($) {
    // Toggle Mark as Read/Unread
    $('.rbn-mark-as-read-link').on('click', function(e) {
        e.preventDefault();

        var $link = $(this);
        var $noticeBlock = $link.closest('blockquote.notice-message');
        var postId = $link.data('post-id');
        var isRead = $noticeBlock.hasClass('read');
        var action = isRead ? 'role_dash_mark_as_unread' : 'role_dash_mark_as_read';

        $.ajax({
            url: rbnAjax.ajaxurl,
            type: 'POST',
            data: {
                action: action,
                post_id: postId,
                _ajax_nonce: rbnAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    if (isRead) {
                        $noticeBlock.removeClass('read');
                        $link.text('Mark as Read');
                    } else {
                        $noticeBlock.addClass('read');
                        $link.text('Mark as Unread');
                    }
                } else {
                    console.error('Error toggling read status:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    });

    // Archive Notice
    $('.rbn-archive-link').on('click', function(e) {
        e.preventDefault();
    
        var $link = $(this);
        var postId = $link.data('post-id');
    
        $.ajax({
            url: rbnAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'role_dash_archive_notice',
                post_id: postId,
                _ajax_nonce: rbnAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $link.closest('.notice-item').remove(); // Remove notice from the widget
                } else {
                    console.error('Error archiving:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    });
    
    // Unarchive Notice
    $('.rbn-unarchive-link').on('click', function(e) {
        e.preventDefault();

        var $link = $(this);
        var postId = $link.data('post-id');

        $.ajax({
            url: rbnAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'role_dash_unarchive_notice',
                post_id: postId,
                _ajax_nonce: rbnAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $link.closest('.notice-item').fadeOut(function() {
                        $(this).remove();
                    });
                } else {
                    console.error('Error unarchiving notice:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    });

    // Delete Notice
    $('.rbn-delete-link').on('click', function(e) {
        e.preventDefault();
    
        var $link = $(this);
        var postId = $link.data('post-id');
    
        $.ajax({
            url: rbnAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'role_dash_delete_notice',
                post_id: postId,
                _ajax_nonce: rbnAjax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $link.closest('.notice-item').fadeOut(function() {
                        $(this).remove();
                    });
                } else {
                    console.error('Error deleting notice:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    });
});
