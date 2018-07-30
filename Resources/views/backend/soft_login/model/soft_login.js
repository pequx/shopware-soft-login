Ext.define('Shopware.apps.SoftLogin.model.SoftLogin', {
    /**
     * Extends the shopware extension of standard Ext Model.
     * @string
     */
    extend: 'Shopware.data.Model',

    configure: function() {
        return {
            controller: 'SoftLogin',
            listing: 'Shopware.apps.SoftLogin.view.list.SoftLogin',
            detail: 'Shopware.apps.SoftLogin.view.detail.SoftLogin'
        };
    },

    fields: [
        { name : 'id', type: 'int', useNull: true },
        { name : 'customerId', type: 'int' },
        { name : 'loginHash', type: 'string' },
        { name : 'firstLogin', type: 'date' },
        { name : 'lastLogin', type: 'date' },
        { name : 'updatedAt', type: 'date' },
        { name : 'isActive', type: 'bool' }
    ],

    associations: [
        {
            relation: 'OneToOne',
            field: 'customerId',
            type: 'hasMany',
            model: 'Shopware.apps.SoftLogin.model.Customer',
            name: 'getCustomer',
            associationKey: 'customer'
        }
    ]
});