$(function () {
  /* Share functions
  -------------------------------------------------------------- */
  $('[data-action="share"]').on('click', function() {
    Share.go(this);
  });
  Share = {
    go: function(_element, _options) {
      var self = Share,
          options = $.extend({
              type: 'vk',
              url: location.href,
              count_url: location.href,
              title: param('title'),
              image: param('image'),
              text: param('description'),
            },
            $(_element).data(),
            _options
          );

      if (self.popup(link = self[options.type](options)) === null) {
        if ($(_element).is('a')) {
          $(_element).prop('href', link);
          return true;
        } else {
          location.href = link;
          return false;
        }
      } else {
        return false;
      }

      function param(name) {
        return $('meta[property=og\\:' + name + ']').attr('content');
      }
    },

     // Vkontakte
    vk: function(_options) {
      var options = $.extend({
        url: location.href,
        title: document.title,
        image: '',
        text: '',
      }, _options);
      return 'http://vkontakte.ru/share.php?'
        + 'url=' + encodeURIComponent(options.url)
        + '&title=' + encodeURIComponent(options.title)
        + '&description=' + encodeURIComponent(options.text)
        + '&image=' + encodeURIComponent(options.image)
        + '&noparse=true';
    },
    // Odnoklassniki
    ok: function(_options) {
      var options = $.extend({
        url: location.href,
        text: '',
      }, _options);
      return 'http://www.odnoklassniki.ru/dk?st.cmd=addShare&st.s=1'
        + '&st.comments=' + encodeURIComponent(options.text)
        + '&st._surl=' + encodeURIComponent(options.url);
    },
    // Pinterest
    pt: function(_options) {
      var options = $.extend({
        url: location.href,
        title: document.title,
        image: '',
        text: '',
      }, _options);
      return 'http://pinterest.com/pin/create/button/'
        + '?url='+ encodeURIComponent(options.url)
        + '&media=' + encodeURIComponent(options.image);
        + '&description=' + encodeURIComponent(options.title)
    },
    // Reddit
    rd: function(_options) {
      var options = $.extend({
        url: location.href,
        title: document.title,
        image: '',
        text: '',
      }, _options);
      return 'http://reddit.com/submit'
        + '?url='+ encodeURIComponent(options.url)
        + '&title=' + encodeURIComponent(options.title)
    },

    // Facebook
    fb: function(_options) {
      var options = $.extend({
        url: location.href,
        title: document.title,
        image: '',
        text: '',
      }, _options);
      return 'http://www.facebook.com/sharer.php?s=100'
        + '&p[title]=' + encodeURIComponent(options.title)
        + '&p[summary]=' + encodeURIComponent(options.text)
        + '&p[url]='+ encodeURIComponent(options.url)
        + '&p[images][0]=' + encodeURIComponent(options.image);
    },
    // Livejournal
    lj: function(_options) {
      var options = $.extend({
        url: location.href,
        title: document.title,
        text: '',
      }, _options);
      return 'http://livejournal.com/update.bml?'
        + 'subject=' + encodeURIComponent(options.title)
        + '&event=' + encodeURIComponent(options.text + '<br/><a href="' + options.url + '">' + options.title + '</a>') + '&transform=1';
    },
    // Twitter
    tw: function(_options) {
      var options = $.extend({
        url: location.href,
        count_url: location.href,
        title: document.title,
      }, _options);
      return 'http://twitter.com/share?'
        + 'text=' + encodeURIComponent(options.title)
        + '&url=' + encodeURIComponent(options.url)
        + '&counturl=' + encodeURIComponent(options.count_url);
    },
    // Mail.Ru
    mr: function(_options) {
      var options = $.extend({
        url: location.href,
        title: document.title,
        image: '',
        text: '',
      }, _options);
      return 'http://connect.mail.ru/share?'
        + 'url=' + encodeURIComponent(options.url)
        + '&title=' + encodeURIComponent(options.title)
        + '&description=' + encodeURIComponent(options.text)
        + '&imageurl=' + encodeURIComponent(options.image);
    },
    // Google+
    gg: function(_options) {
      var options = $.extend({
        url: location.href
      }, _options);
      return 'https://plus.google.com/share?url='
        + encodeURIComponent(options.url);
    },
    // LinkedIn
    li: function(_options) {
      var options = $.extend({
        url: location.href,
        title: document.title,
        text: ''
      }, _options);
      return 'http://www.linkedin.com/shareArticle?mini=true'
        + '&url=' + encodeURIComponent(options.url)
        + '&title=' + encodeURIComponent(options.title)
        + '&summary=' + encodeURIComponent(options.text);
    },
    popup: function(url) {
      return window.open(url, '', 'toolbar=0,status=0,scrollbars=1,width=626,height=436');
    }
  }
});