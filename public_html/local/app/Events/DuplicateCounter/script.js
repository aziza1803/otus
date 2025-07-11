(function() {
    'use strict';

    /**
     * @namespace BX.MPP
     */
    BX.namespace('BX.MPP');

    if (typeof (BX.MPP.DuplicateCounter) === 'undefined') {
        BX.MPP.DuplicateCounter = function (settings) {
            this._settings = settings;

            this.showCounter();
        };

        BX.MPP.DuplicateCounter.prototype = {
            showCounter: function () {
                console.log('settings');
                console.log(this._settings);
                let duplicateTabCounter = document.querySelector("[data-id=\"tab_products\"] .main-buttons-item-counter");
                if (duplicateTabCounter)
                {
                    duplicateTabCounter.innerText = this._settings['COUNTER'] + '-' + BX.message('BTN_TEXT');
                    if (this._settings['COUNTER'] < 1)
                        duplicateTabCounter.style.background = '#d3d7dc';
                }


                let addButtonBlock = document.querySelector("#crm-content-outer");
                console.log('addButtonBlock');
                console.log(addButtonBlock);
                if (addButtonBlock)
                {
                    let btn = BX.create('DIV', {
                        props: {className: 'ui-btn ui-btn-success'},
                        attrs: {
                            'id': 'main-deal-btn',
                        },
                        events: {
                            click: BX.proxy(this.mainDealBtnHandler, this)
                        },
                        text: BX.message('BTN_TEXT')
                    });
                    BX.insertBefore(btn, addButtonBlock);
                }
            },

            mainDealBtnHandler: function (event)
            {
                console.log(this);

                let clickedElement = event.target;
                console.log(clickedElement);
                console.log("ID:", clickedElement.id);
                console.log("Class:", clickedElement.className);
                console.log("Text:", clickedElement.textContent);
            }
        };
    }
})();