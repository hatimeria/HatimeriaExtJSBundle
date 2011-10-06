/**
 * Agreement Form
 */
(function() {
    
    Ext.require('HatimeriaCore.direct.ResponseHandler');
    
    Ext.define('HatimeriaCore.form.AgreementForm', {
        extend: 'Ext.form.Panel',
        config: {
            /**
             * Direct function to content of terms
             */
            directFn: function() {},
            
            /**
             * Optional request parameters
             */
            params: {},
            
            /**
             * Label of form
             */
            label: 'Regulamin',
            
            /**
             * Label behind checkbox
             */
            checkboxLabel: 'Akceptuję requlamin'
        },
        
        /**
         * Constructor
         * 
         * @param {} config
         */
        constructor: function(config)
        {
            this.initConfig(config);
            this.callParent([config]);
            
            return this;
        },
        
        /**
         * Initializes form
         */
        initComponent: function()
        {
            var config = {
                border: false,
                items: [
                    {
                        id: 'agreement-field',
                        fieldLabel: this.getLabel(),
                        labelAlign: 'top',
                        xtype: 'textarea',
                        width: 350,
                        height: 100
                    },
                    {
                        xtype: 'checkbox',
                        fieldLabel: false,
                        boxLabel: this.getCheckboxLabel(),
                        name: 'agreement'
                    }
                ]
            };
            Ext.apply(this, Ext.apply(config, this.initComponent || {}));
            
            this.callParent();
            
            this.on('afterrender', function() {
                this.loadTerms();
            });
        },
        
        /**
         * Load terms of Agreement
         */
        loadTerms: function()
        {
            Ext.create('HatimeriaCore.direct.ResponseHandler', {
                fn: this.getDirectFn(),
                params: this.getParams(),
                scope: this,
                success: function(result) {
                    this.updateTerms(result.record);
                }
            });
        },
        
        /**
         * Updates a textarea field
         * 
         * @param string value
         */
        updateTerms: function(value)
        {
            this.getComponent('agreement-field').setValue(value);
        }
    });
    
})();