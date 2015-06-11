(function() {
  jQuery(document).ready(function() {
    var filter,
        redirect;
    filter = document.getElementById( "professionals-filter" );
    redirect = function() {
      var is_first_option,
          new_url;
      is_first_option = filter.selectedIndex === 0;
      new_url = php.wp_site_url + '/?cat=' + filter.options[ filter.selectedIndex ].value;
      if (filter && redirect && !is_first_option) {
        location.href = new_url;
      }
    }
    filter.onchange = redirect;
  });
}).call(this);
