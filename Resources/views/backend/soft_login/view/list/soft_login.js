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
            renderer: me.columnRenderer,
            sortable: true,
            dataIndex: 'email'
        };

        columns = Ext.Array.insert(columns, 2, [columnEmail]);

        return columns;
    },

    columnRenderer: function(value, metaData, record) {
        var store = record.getCustomerStore,
            result = store.data.items[0].data;
        // store.getProxy().extraParams = { customerId: record.get('customerId') };

        return this.defaultColumnRenderer(result.email);
    },

    /**
     * @param value
     * @returns string
     */
    defaultColumnRenderer: function (value) {
        return value;
    },
});
//{/block}