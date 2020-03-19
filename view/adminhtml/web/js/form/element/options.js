define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Ui/js/modal/modal'
], function (_, uiRegistry, select, modal) {
    'use strict';
    return select.extend({

        /**
         * On value change handler.
         *
         * @param {String} value
         */
        onUpdate: function (value) {
            var send_at = uiRegistry.get('index = send_at');
            if(value == 2) {
                send_at.show();
            }
            else {
                send_at.hide();
            }
            return this._super();
        },
    });
});