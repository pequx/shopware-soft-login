//{block name="backend/soft_login/model/customer"}
Ext.define('Shopware.apps.SoftLogin.model.Customer', {

    extend: 'Shopware.apps.Base.model.Customer',

    configure: function() {
        return {
            detail: 'Shopware.apps.SoftLogin.view.detail.Customer'
        }
    }
});
//{/block}