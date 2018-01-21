$(document).ready(function () {
  var product_name_col = $('.dataTable th#product_name')[0];
  if (product_name_col) {
    var product_name_index = $('.dataTable th#product_name')[0].cellIndex + 1;
    $(product_name_col).closest('table').find('td:nth-child('+product_name_index+')').hide();
    $(product_name_col).hide();
  }
});

(function ($, require) {

  var exports = require('piwik/UI'),
      DataTable = exports.DataTable,
      dataTablePrototype = DataTable.prototype;

   var UIControl = exports.UIControl;

   function getValueFromEvent(event)
   {
       return event.target.value ? event.target.value : $(event.target).attr('value');
   }

  /**
   * UI control that handles extra functionality for Actions datatables.
   *
   * @constructor
   */
  exports.RetailerUniqueAction = function (element) {
      DataTable.call(this, element);
  };

  $.extend(exports.RetailerUniqueAction.prototype, dataTablePrototype, {

      _init: function (domElem) {
          var self = this;
          //dataTablePrototype.init.call(this);
          domElem.find('.action_list_select').on('change', function() {
              self.param.unique_action_id = Number(this.value);
              self.reloadAjaxDataTable();
          });
      }
  });

})(jQuery, require);
