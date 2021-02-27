/**
 * Magpleasure Ltd.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.magpleasure.com/LICENSE.txt
 *
 * @category   Magpleasure
 * @package    Magpleasure_Adminlogger
 * @copyright  Copyright (c) 2012 Magpleasure Ltd. (http://www.magpleasure.com)
 * @license    http://www.magpleasure.com/LICENSE.txt
 */

var MpAdminloggerActionTypeFilter = Class.create();
MpAdminloggerActionTypeFilter.prototype = {
    initialize: function (params) {
        for (key in params) {
            this[key] = params[key];
        }

        $(this.group_select).observe('change', (function(e){
            this.actionGroupChange();
        }).bind(this));

        $(this.type_select).style.display = $(this.group_select).value ? 'block' : 'none';

    },
    actionGroupChange: function(){
        var group = $(this.group_select);
        var type = $(this.type_select);

        type.style.display = group.value ? 'block' : 'none';

        while (type.length){
            type.remove(type.length - 1);
        }

        if (group.value){
            for (groupVal in this.data){
                if (groupVal == group.value){
                    var options = this.data[groupVal];

                    type.add(new Option('', ''));
                    for (typeId in options){
                        type.add(new Option(options[typeId], typeId));
                    }

                }
            }
        }
    }
};
