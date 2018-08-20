//{namespace name='backend/soft_login/view/list'}
//{block name="backend/soft_login/view/list/soft_login"}
Ext.define('Shopware.apps.SoftLogin.view.list.SoftLogin', {
    extend: 'Shopware.grid.Panel',
    alias:  'widget.soft-login-listing-grid',
    region: 'center',

    configure: function() {
        return {
            detailWindow: 'Shopware.apps.SoftLogin.view.detail.Window',
            addButton: false,
            deleteButton: false,
            deleteColumn: false,
        };
    },

    createColumns: function() {
        var me = this,
            columns = me.callParent(arguments);

        var columnEmail = {
            xtype: 'gridcolumn',
            header: 'E-mail',
            renderer: me.emailColumnRenderer,
            sortable: true,
            dataIndex: 'email'
        };

        columns = Ext.Array.insert(columns, 2, [columnEmail]);

        return columns;
    },

    // columnRenderer: function(value, metaData, record) {
    //     var store = record.getCustomerStore && record.getCustomerStore.first();
    //     var result = store.get('email');
    //     // store.getProxy().extraParams = { customerId: record.get('customerId') };
    //
    //     return this.defaultColumnRenderer(result);
    // },

    // /**
    //  * @param value
    //  * @returns string
    //  */
    // defaultColumnRenderer: function (value) {
    //     return value;
    // },

    emailColumnRenderer: function (value, meta, record) {
        var customer = record.getCustomerStore && record.getCustomerStore.first();
        console.log(customer);
        return customer.get('email');
    }
});
//{/block}