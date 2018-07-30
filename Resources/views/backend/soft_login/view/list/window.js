//{namespace name='backend/soft_login/view/list'}
//{block name="backend/soft_login/view/list/window"}
Ext.define('Shopware.apps.SoftLogin.view.list.Window', {
    extend: 'Shopware.window.Listing',
    alias: 'widget.soft-login-list-window',
    height: 450,
    title: 'SoftLogin',

    configure: function() {
        return {
            listingGrid: 'Shopware.apps.SoftLogin.view.list.SoftLogin',
            listingStore: 'Shopware.apps.SoftLogin.store.SoftLogin'
        };
    },
});
//{/block}