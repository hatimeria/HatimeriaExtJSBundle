/** 
 * ComboBox
 */
(function() {
    
    Ext.require('HatimeriaCore.direct.ResponseHandler');
    
    Ext.define('HatimeriaCore.form.field.TreePicker', {
        extend: 'Ext.tree.Panel',
        
        startWidth: undefined,
        
        initComponent: function()
        {
            var config = {
                floating: true,
                title: false,
                width: 200,
                height: 150
            };
            
            Ext.apply(this, Ext.apply(config, this.initialConfig || {}));
            this.callParent();
            
            this.on({
                itemcollapse: function() {
                    this.adjustTo(this.getEl().down('table'));
                },
                itemexpand: function() {
                    this.adjustTo(this.getEl().down('table'));
                },
                afterender: function() {
                    this.startWidth = this.ownerCt.getWidth();
                }
            });
        },
        
        /**
         * Selects current node
         * 
         * @param Ext.data.Store.ImplicitModel
         */
        getNode: function()
        {
            
        },
        
        /**
         * Adjust tree to dimmensions of table - best fit
         * 
         * @param Ext.Element table
         */
        adjustTo: function(table)
        {
            var _this = this;
            window.setTimeout(function() {
                _this.setHeight(table.getHeight()+5);
                
                if (table.getWidth() > _this.startWidth)
                {
                    _this.setWidth(table.getWidth()+5);
                }
            }, 600);
        }
        
    });
    
    Ext.define('HatimeriaCore.store.TreeStore', {
        extend: 'Ext.data.TreeStore',
        
        paramsAsHash: true,
        
        /**
         * Loads all nodes
         * 
         * @param {} nodes
         */
        loadNodes: function(nodes)
        {
            this.getRootNode().appendChild(nodes);
            this.fireEvent('load', this);
        },
        
        /**
         * Count of nodes (always one)
         * 
         * @return int
         */
        getCount: function()
        { 
            return 1; 
        },
        
        /**
         * Search node
         */
        findExact: function(field, value)
        {
            var find = function(nodes, field, value)
            {
                for (var i in nodes)
                {
                    if (nodes[i].get(field) == value)
                    {
                        return nodes[i];
                    }
                    else
                    {
                        return find(nodes[i].childNodes, field, value);
                    }
                }
            };
            
            var record = find(this.getRootNode().childNodes, field, value);
            
            return record;
        },
        
        /**
         * Get node from a pases position
         */
        getAt: function(record)
        {
            return record;
        }
    });
    
    
    Ext.define('HatimeriaCore.form.field.Category', {
        extend: 'HatimeriaCore.form.ComboBox',
        alias: 'widget.ux-category',
        config: {
            directFn: Ext.emptyFn
        },
        
        /**
         * Constructor
         */
        constructor: function(config)
        {
            this.initConfig(config);
            this.callParent([config]);
            
            return this;
        },
        
        /**
         * Initialize component
         */
        initComponent: function()
        {
            var _this = this;
            var config = {
                valueField: 'id',
                queryMode: 'local',
                displayField: 'text',
                store: Ext.create('HatimeriaCore.store.TreeStore', {
                    root: {
                        id: 'root',
                        expanded: true,
                        text: 'Wszystkie',
                        children: []
                    }
                })
            };
            
            Ext.apply(this, Ext.apply(config, this.initialConfig || {}));
            this.callParent();
            
            this.on('afterrender', function() {
                Ext.create('HatimeriaCore.direct.ResponseHandler', {
                   fn: this.getDirectFn(),
                   scope: this,
                   success: function(result) {
                       _this.store.loadNodes(result.record);
                   }
                });
            });
        },
        
        /**
         * Creates a picker
         * 
         * @return Ext.Component
         */
        createPicker: function()
        {
            var picker = Ext.create('HatimeriaCore.form.field.TreePicker', {
                ownerCt: this.ownerCt,
                store: this.store
            });
            
            this.mon(picker, {
                itemclick: this.onItemClick,
                scope: this
            });
            
            this.mon(picker.getSelectionModel(), {
                selectionChange: this.onListSelectionChange,
                scope: this
            });
            
            this.picker = picker;
            
            return picker;
        }
            
    });
    
})();