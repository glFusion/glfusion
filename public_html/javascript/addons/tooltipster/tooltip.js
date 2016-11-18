$(document).ready(function() {
  $('.tooltip').tooltipster({
    contentAsHTML: true,
    animation: 'fade',
    delay: 100,
    functionInit: function(origin, content) {
      tipParts = content.split('::');
      if ( typeof(tipParts[1]) === 'undefined' ) {
          return content;
      }
      tipTitle = tipParts[0];
      tipData = tipParts[1];
      formatted = '<span class="tool-title">' + tipTitle + '</span><br/>' + tipData;
      return formatted;
    }
  });
});