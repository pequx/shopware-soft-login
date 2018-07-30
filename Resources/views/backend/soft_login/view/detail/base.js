//{namespace name='backend/soft_login/view/detail'}
//{block name="backend/soft_login/view/detail/base"}
/**
 * Redundant class definition.
 */
Ext.define('Shopware.apps.SoftLogin.view.detail.Base', {
    /**
     * @string
     */
    extend:'Ext.form.FieldSet',

    /**
     * @string
     */
    alias: 'widget.soft-login-base-field-set',

    /**
     * @string
     */
    cls: Ext.baseCSSPrefix + 'base-field-set',

    /**
     * @string
     */
    layout: 'column',

    /**
     * @return void
     */
    initComponent:function () {
        var me = this;
        me.title = 'SoftLogin Box';

        me.items = me.createForm();
        me.callParent(arguments);
    },

    /**
     * @return [Array]
     */
    createForm:function () {
        var leftContainer, rightContainer, me = this;
        console.log('wurst');
        leftContainer = Ext.create('Ext.container.Container', {
            columnWidth:0.5,
            border:false,
            cls: Ext.baseCSSPrefix + 'field-set-container',
            layout:'anchor',
            items:me.createFormLeft()
        });

        rightContainer = Ext.create('Ext.container.Container', {
            columnWidth:0.5,
            border:false,
            cls: Ext.baseCSSPrefix + 'field-set-container',
            layout:'anchor',
            items:me.createFormRight()
        });

        return [ leftContainer, rightContainer ];
    },

    /**
     * @return [Array]
     */
    createFormLeft:function () {
        var me = this;

        me.loginHash = Ext.create('Ext.form.field.Text', {
            name:'soft_login_login_hash',
            labelWidth:150,
            size:25,
        });

        me.firstLogin = Ext.create('Ext.form.field.Text', {
            name:'soft_login_first_login',
            labelWidth:150,
            size:25,
        });

        me.lastLogin = Ext.create('Ext.form.field.Text', {
            name:'soft_login_last_login',
            labelWidth:150,
            size:25,
        });

        me.updatedAt = Ext.create('Ext.form.field.Text', {
            name:'soft_login_updated_at',
            labelWidth:150,
            size:25,
        });


        return [
            me.loginHash,
            me.firstLogin,
            me.lastLogin,
            me.updatedAt,
        ];
    },

    /**
     * @return array
     */
    createFormRight:function () {
        var me = this;

        me.isActive = Ext.create('Ext.form.field.Checkbox', {
            name:'soft_login_is_active',
            listeners: {
                scope: me,
                disable: function (el) {
                    // this.createTooltip(el, this.snippets.mustSelectDocument);
                },
                enable: function (el) {
                    // this.removeTooltip(el);
                }
            }
        });

        me.category = Ext.create('Ext.form.field.ComboBox', {
            name: 'soft_login_category',
            // store: me.dispatchesStore,
            // queryMode: 'local',
            // valueField: 'id',
            // displayField: 'name',
            // triggerAction: 'all',
            labelWidth: 155,
            listeners: {
                change: function(field, newValue) {
                    // me.fireEvent('changeDispatch', me, newValue);
                }
            }
        });

        me.generateHash = Ext.create('Ext.button.Button', {
            name: 'soft_login_generate_hash',
            // iconCls: 'sprite-license-key',
            // action: 'create-password',
            labelWidth: 155,
            width: 150,
            handler: function () {
                // me.fireEvent('generatePassword', me.passwordField, me.confirmField);
            }
        });

        return [
            me.isActive,
            me.category,
            me.generateHash
        ];
    },
});
//{/block}