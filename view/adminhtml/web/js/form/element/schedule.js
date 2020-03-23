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
            var schedule_at = uiRegistry.get('index = schedule_at');
            if(value == 1) {
                schedule_at.show();
            }
            else {
                schedule_at.hide();
            }
            return this._super();
        },
    });
});