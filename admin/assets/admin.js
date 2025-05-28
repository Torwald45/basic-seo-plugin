// Basic SEO Plugin Torwald45 - Admin JavaScript

(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Settings form handling
        $('.basicseo-settings-form').on('submit', function() {
            var $button = $(this).find('[type="submit"]');
            $button.prop('disabled', true).text(basicSeoTorwald45Admin.strings.saving);
        });
        
        // Character counter for meta description
        $('.basicseo-desc-input').on('input', function() {
            updateCharCounter($(this));
        });
        
        function updateCharCounter($textarea) {
            var length = $textarea.val().length;
            var $counter = $textarea.closest('.form-field, .inline-edit-col').find('.basicseo-desc-count');
            
            if ($counter.length) {
                $counter.text(length);
                $counter.closest('.basicseo-counter').toggleClass('warning', length > 160);
            }
        }
    });
    
})(jQuery);
