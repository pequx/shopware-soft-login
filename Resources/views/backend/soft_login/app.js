//{block name="backend/soft_login/app"}
Ext.define('Shopware.apps.SoftLogin', {
    extend: 'Enlight.app.SubApplication',
    name:'Shopware.apps.SoftLogin',
    loadPath: '{url action=load}',
    bulkLoad: true,

    controllers: [ 'Main' ],

    views: [
        'list.Window',
        'list.SoftLogin',

        'detail.SoftLogin',
        'detail.Window',

        'detail.Customer'
    ],

    models: [
        'SoftLogin',
        'Customer'
    ],

    stores: [
        'SoftLogin'
    ],

    launch: function() {
        return this.getController('Main').mainWindow;
    }
});
//{/block}