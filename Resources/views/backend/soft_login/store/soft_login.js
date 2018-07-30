Ext.define('Shopware.apps.SoftLogin.store.SoftLogin', {
    extend:'Shopware.store.Listing',
    autoLoad: true,

    configure: function() {
        return {
            controller: 'SoftLogin',
        };
    },

    model: 'Shopware.apps.SoftLogin.model.SoftLogin'
});
