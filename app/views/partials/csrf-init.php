<meta name="csrf-token" content="<?= \App\Core\Csrf::token() ?>">
<script>
window.REFIXEL_CSRF_TOKEN = '<?= \App\Core\Csrf::token() ?>';
(function() {
  var csrfToken = '<?= \App\Core\Csrf::token() ?>';
  if (!csrfToken) return;

  // 1. Intercept native window.fetch to automatically inject X-CSRF-TOKEN on all mutations
  var origFetch = window.fetch;
  if (origFetch) {
    window.fetch = function(url, options) {
      options = options || {};
      var method = (options.method || 'GET').toUpperCase();
      if (['POST', 'PUT', 'DELETE', 'PATCH'].indexOf(method) !== -1) {
        options.headers = options.headers || {};
        if (typeof Headers !== 'undefined' && options.headers instanceof Headers) {
          if (!options.headers.has('X-CSRF-TOKEN')) {
            options.headers.append('X-CSRF-TOKEN', csrfToken);
          }
        } else if (Array.isArray(options.headers)) {
          options.headers.push(['X-CSRF-TOKEN', csrfToken]);
        } else {
          if (!options.headers['X-CSRF-TOKEN'] && !options.headers['x-csrf-token']) {
            options.headers['X-CSRF-TOKEN'] = csrfToken;
          }
        }
      }
      return origFetch.call(this, url, options);
    };
  }

  // 2. Intercept jQuery AJAX if jQuery is loaded
  if (typeof window.jQuery !== 'undefined' && window.jQuery.ajaxSetup) {
    window.jQuery.ajaxSetup({
      beforeSend: function(xhr, settings) {
        if (!/^(GET|HEAD|OPTIONS|TRACE)$/i.test(settings.type) && !this.crossDomain) {
          xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        }
      }
    });
  }
})();
</script>
