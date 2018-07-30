//{block name="backend/soft_login/controller/main"}
Ext.define('Shopware.apps.SoftLogin.controller.Main', {
    extend: 'Enlight.app.Controller',
    refs: [
        { ref: 'grid', selector: 'soft-login-listing-grid' },
        { ref: 'detail', selector: 'soft-login-detail-window'}
    ],
    stores: [ 'SoftLogin' ],

    /**
     * @return void
     */
    init: function() {
        var me = this;
        me.control({
            'soft-login-detail-window': {
                regenerateHash: me.onRegenerateHash,
                save: me.onSave,
            }
        });
        me.mainWindow = me.getView('list.Window').create({ }).show();
    },


    /**
     * @event click
     * @param { button } button
     * @return void
     */
    onSave: function (button) {
        var me = this,
            detail = me.getDetail(),
            store = me.getSoftLoginStore(),
            formPanel = detail.formPanel,
            form = formPanel.form,
            record = form.getRecord(),
            gridPanel = me.mainWindow.gridPanel,
            values = form.getValues();

        if (!record) { return; }
        formPanel.setLoading(true);
        record.set('isActive', values.isActive);
        form.updateRecord(record);

        record.save({
            callback: function(self, operation) {
                if(operation.success){
                    var response = Ext.JSON.decode(operation.response.responseText),
                        data = response.data;
                    record.set(data);
                    Shopware.Notification.createGrowlMessage('Succees', 'trolo');

                    store.load();
                    formPanel.setLoading(false);
                    form.loadRecord(record);
                    detail.destroy();
                    gridPanel.getStore().reload();
                }else{
                    Shopware.Notification.createGrowlMessage('Error', 'no.');
                }
            }
        });
    },

    /**
     * @event regenerateHash
     * @param { Ext.button.Button } button
     * @return void
     */
    onRegenerateHash: function(button) {
        var me = this,
            detail = me.getDetail(),
            store = me.getSoftLoginStore(),
            grid = me.getGrid(),
            formPanel = detail.formPanel,
            form = formPanel.form,
            record = form.getRecord(),
            gridPanel = me.mainWindow.gridPanel;

        detail.setLoading(true);
        grid.setLoading(true);
        
        Ext.Ajax.request({
            url: '{url controller=SoftLogin action=regenerateHash}',
            params: {
                customerId: record.get('customerId')
            },
            //success of the ajax response
            success: function(response) {
                var result = Ext.JSON.decode(response.responseText);
                
                if (result.success === false) {
                    Shopware.Notification.createGrowlMessage('Error', result.message, 'SoftLogin');
                    return;
                }
                record.set('loginHash', result.data.loginHash);
                // detail.setLoading(true);
                record.save({
                    callback: function () {
                        record.set(result.data);
                        Shopware.Notification.createGrowlMessage('Succees', 'trolo');
                        form.loadRecord(record);
                        store.load({
                            callback: function() {
                                // gridPanel.reconfigure(store);
                                grid.setLoading(false);
                                detail.setLoading(false);
                                button.setDisabled(false);
                            }
                        });
                    }
                });
            },
            failure: function() {
                Shopware.Notification.createGrowlMessage('Error', 'Meh, ajax failure.', 'SoftLogin');
            }
        });
    }
});
//{/block}