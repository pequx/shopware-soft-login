//{namespace name='backend/soft_login/view/detail'}
//{block name="backend/soft_login/view/detail/soft_login"}
Ext.define('Shopware.apps.SoftLogin.view.detail.SoftLogin', {
    extend: 'Shopware.model.Container',
    alias: 'widget.soft-login-detail-container',
    record: 'Shopware.apps.SoftLogin.model.SoftLogin',
    padding: 10,

    /**
     * Configures model container.
     *
     * @return Array
     */
    configure: function() {
        return {
            controller: 'SoftLogin',
            associations: [ 'customer' ],
            fieldSets: [
                {
                    title: 'Soft Login',
                    fields: {
                        loginHash: {
                            xtype: 'textarea'
                        },
                        isActive: {
                            xtype: 'checkbox'
                        },
                    }
                },
                {
                    title: 'Additional data',
                    fields: {
                        firstLogin: {
                            xtype: 'displayfield'
                        },
                        lastLogin: {
                            xtype: 'displayfield'
                        },
                        updatedAt: {
                            xtype: 'displayfield'
                        }
                    }
                }
            ]
        };
    }
});
//{/block}