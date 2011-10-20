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
                        isFormField: false,
                        readOnly: true,
                        submitValue: false,
                        width: this.initialConfig.width || 350 ,
                        height: 100
                    },
                    {
                        xtype: 'checkbox',
                        fieldLabel: false,
                        boxLabel: this.getCheckboxLabel()+'<em class="ux-required">*</em>',
                        name: 'agreement'
                    }
                ]
            };
            Ext.apply(this, Ext.apply(config, this.initComponent || {}));
            
            this.callParent();
            
            this.on('afterrender', function() {
                //this.loadTerms();
                this.getComponent('agreement-field').setValue('Miller przyznał, że były trzy powody, dla których wystartował w wyborach na szefa klubu SLD. Pierwszym było "pragnienie, aby moje koleżanki i koledzy mieli prawdziwy wybór". Zdaniem byłego premiera rywalizacja jego i Ryszarda Kalisza "dawały szansę wyboru". - Po drugie mam zamiar razem z moimi koleżankami i kolegami uczynić z parlamentarnego klubu SLD liczącą się siłę, twardą opozycję, wrażliwą na racjonalne argumenty i partnera dla innych ugrupowań politycznych - przekonywał Miller. Jak zaznaczył celem Sojuszu jest to, aby "przestać być opozycją". - Chcielibyśmy być partnerem, który o nic nie prosi i przed nikim nie klęczy - zaznaczył. Trzeci powód to, jak wyznał Miller, "chęć pomocy dla formacji politycznej, z której się wywodzimy". yły prezes rady ministrów przyznał, że nie zamierza być szefem całego Sojuszu. - Trzeba poszerzać grupę liderów - przekonywał. - Mam nadzieję, że lider zostanie wybrany w głosowaniu powszechnym w prawyborach. Przy okazji dowiemy się ilu jest członków SLD i jest dobra okazja, żeby odbyć interesującą dyskusję programową. Dyskusja na ten temat jest bardzo potrzebna - stwierdził Miller.- Z Kaliszem różnimy się w niuansach. Musimy najpierw przeprowadzić proces ozdrowieńczy, żeby móc debatować z pozycji odpowiedzialnego partnera, który pokonał własne słabości. Potem kongres lewicy i proces jednoczenia. Trzeba trwać przy sztandarze, a nie go wyprowadzać. Można przeprowadzić okrągły stół lewicy, wracając do dobrych tradycji, i zobaczyć gdzie się różnimy, a gdzie są możliwości wspólnego działania - stwierdził Miller w TOK FM.');
                console.log(this.getComponent('agreement-field'))
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