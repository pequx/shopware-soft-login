//{namespace name='backend/soft_login/view/list'}
//{block name="backend/soft_login/view/list/soft_login"}
Ext.define('Shopware.apps.SoftLogin.view.list.SoftLogin', {
    extend: 'Shopware.grid.Panel',
    alias:  'widget.soft-login-listing-grid',
    region: 'center',

    /**
     * Configures panel.
     *
     * @return Array
     */
    configure: function() {
        return {
            detailWindow: 'Shopware.apps.SoftLogin.view.detail.Window',
            addButton: false,
            deleteButton: false,
            deleteColumn: false,
        };
    },

    /**
     * Creates the columns for the grid panel.
     *
     * @return Array
     */
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

    /**
     * Renderer function of the customer group column.
     *
     * @param value
     * @param metaData
     * @param record Ext.data.Model
     */
    columnRenderer: function(value, metaData, record) {
        var customer = record.raw.customer;
        return this.defaultColumnRenderer(customer);
        /**
         * @todo: i am not able to get email from customer, in the browser console it's possible.
         */
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