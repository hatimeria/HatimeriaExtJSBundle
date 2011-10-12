/**
 * Netto Brutto sync form
 */
(function() {
    
    Ext.define('HatimeriaCore.form.NettoBruttoForm', {
        extend: 'Ext.form.Panel',
        config: {
            vat: 23,
            nettoFieldName: 'netto',
            bruttoFieldName: 'brutto'
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
        },
        
        /**
         * Initialize components
         */
        initComponent: function()
        {
            var config = {
                layout: 'hbox',
                border: 0,
                defaults: {
                    xtype: 'textfield',
                    labelWidth: 40,
                    allowBlank: false,
                    size: 5
                },
                items: [
                    {
                        id: 'field-netto',
                        fieldLabel: 'netto',
                        name: this.getNettoFieldName(),
                        labelStyle: 'font-weight: bold',
                        margin: '0 10 0 0',
                        listeners: {
                            change: {
                                scope: this,
                                fn: this.onNettoChange
                            }
                        }
                    },
                    {
                        id: 'field-brutto',
                        fieldLabel: 'brutto',
                        name: this.getBruttoFieldName(),
                        labelStyle: 'font-weight: bold',
                        listeners: {
                            change: {
                                scope: this,
                                fn: this.onBruttoChange
                            }
                        }
                    }
                ]
            };
            Ext.apply(this, Ext.apply(config, this.initialConfig));
            this.callParent();
        },
        
        /**
         * Event: netto field changed
         * 
         * @pram Ext.form.field.Text
         * @param string value
         */
        onNettoChange: function(field, value)
        {
            value = parseFloat(value);
            var brutto;
            
            brutto = (isNaN(value)) ? '' : (value * (1+this.getPercent()).toFixed(2))
            
            this.getComponent('field-brutto').setRawValue(brutto);
        },
        
        /**
         * Event: netto field changed
         * 
         * @pram Ext.form.field.Text
         * @param string value
         */
        onBruttoChange: function(field, value)
        {
            value = parseFloat(value);
            var netto;
            
            /**
             *  value  -  123%
             *  x      -  100
             */
            
            netto = (isNaN(value)) ? '' : (((100 * value) / (100 + this.getVat())).toFixed(2));
            this.getComponent('field-netto').setRawValue(netto);
        },
        
        /**
         * Tax percentage
         * 
         * @return float
         */
        getPercent: function()
        {
            return this.getVat() / 100;
        }
        
        
    });
    
})();