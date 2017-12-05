$(document).ready(function () {
  var product_name_col = $('.dataTable th#product_name')[0];
  if (product_name_col) {
    var product_name_index = $('.dataTable th#product_name')[0].cellIndex + 1;
    $(product_name_col).closest('table').find('td:nth-child('+product_name_index+')').hide();
    $(product_name_col).hide();
  }
});
