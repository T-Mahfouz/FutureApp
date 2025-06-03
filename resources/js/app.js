import './bootstrap';

// Import jQuery and make it globally available
import $ from 'jquery';
window.$ = window.jQuery = $;

// Import jQuery plugins
import 'jquery-migrate';
import 'jquery-validation';
import 'jquery-mousewheel';

// Import Bootstrap
import 'bootstrap';

// Import Animate.css
import 'animate.css';

// Import Lodash and make it globally available
import _ from 'lodash';
window._ = _;

// Import and initialize Malihu Custom Scrollbar
import 'malihu-custom-scrollbar-plugin';
import 'malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.css';

// Initialize jQuery validation defaults (optional)
$(document).ready(function() {
    // Set default jQuery validation settings if needed
    if ($.validator) {
        $.validator.setDefaults({
            errorClass: 'is-invalid',
            validClass: 'is-valid',
            errorElement: 'div',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                if (element.prop('type') === 'checkbox') {
                    error.insertAfter(element.next('label'));
                } else {
                    error.insertAfter(element);
                }
            }
        });
    }
    
    // Initialize custom scrollbars if needed
    if ($.fn.mCustomScrollbar) {
        // Example: $(".content").mCustomScrollbar();
    }
});

// Vue 3 setup (if you plan to use Vue)
import { createApp } from 'vue';

// Uncomment and configure if you have Vue components
// const app = createApp({});
// app.mount('#app');