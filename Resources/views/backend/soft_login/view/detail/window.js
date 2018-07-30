//{namespace name='backend/soft_login/view/detail'}
//{block name="backend/soft_login/view/detail/window"}
Ext.define('Shopware.apps.SoftLogin.view.detail.Window', {
    /**
     * @string
     */
    extend: 'Shopware.window.Detail',
    /**
     * @string
     */
    alias: 'widget.soft-login-detail-window',
    /**
     * @string
     */
    title : 'Soft Login detail',
    /**
     * @integer
     */
    width: 680,

    /**
     * @return { Array }
     */
    configure: function() {
        var me = this;

        return {
            controller: 'SoftLogin'
        }
    },

    /**
     * @override
     * @return void
     */
    initComponent: function () {
        var me = this;
        me.callParent(arguments);
    },

    /**
     * @override
     * @return void
     */
    registerEvents: function() {
        var me = this;
        me.callParent(arguments);
        me.addEvents(
            /**
             * @event regenerateHash
             * @param { Ext.grid.Panel } view
             * @param { Ext.data.Model } record
             */
            'regenerateHash'
        );
    },

    /**
     * @override
     * @return { Array }
     */
    createDockedItems: function() {
        var me = this,
            items = me.callParent(arguments);
        console.log('test');
        items.push(me.createMenuBar());
        return items;
    },

    /**
     * @return { Ext.toolbar.Toolbar }
     */
    createMenuBar: function () {
        var me = this;
        return Ext.create('Ext.toolbar.Toolbar', {
            ui: 'shopware-ui',
            dock: 'top',
            items: [{
                text: 'Regenerate hash',
                iconCls: 'sprite-key-solid',
                itemId: 'test',
                // handler: me.onRegenerateHash()
                listeners: {
                    scope: me,
                    click: function(button, event) {
                        me.fireEvent('regenerateHash', button, event);
                    }
                }
            }]
        });
    },

    /**
     * @return { Ext.button.Button }
     */
    createSaveButton: function () {
        var me = this;

        me.saveButton = Ext.create('Ext.button.Button', {
            cls: 'primary',
            name: 'detail-save-button',
            listeners: {
                scope: me,
                click: function(button, event) {
                    me.fireEvent('save', button, event);
                }
            }
        });
        return me.saveButton;
    },
});
//{/block}