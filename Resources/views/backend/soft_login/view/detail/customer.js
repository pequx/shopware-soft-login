//{namespace name='backend/soft_login/view/detail'}
//{block name="backend/soft_login/view/detail/customer"}
Ext.define('Shopware.apps.SoftLogin.view.detail.Customer', {
    extend: 'Shopware.model.Container',
    alias: 'widget.soft-login-view-detail-customer',

    /**
     * Configures model container.
     *
     * @return Array
     */
    configure: function() {
        return {
            fieldAlias: 'customer',
            controller: 'SoftLogin',
            fieldSets: [
                {
                    title: 'Customer data',
                    fields: {
                        email: {},
                        firstname: {},
                        lastname: {},

                        active: {
                            xtype: 'displayfield'
                        },
                        customernumber: {
                            xtype: 'displayfield'
                        },
                    }
                },
                {
                    title: 'Customer account',
                    fields: {
                        accountMode: {
                            xtype: 'displayfield'
                        },
                        failedLogins: {
                            xtype: 'displayfield'
                        },
                        shopId: {
                            xtype: 'displayfield'
                        },
                        priceGroupId: {
                            xtype: 'displayfield'
                        }
                    }
                }
            ]
        };
    }
});
//{/block}