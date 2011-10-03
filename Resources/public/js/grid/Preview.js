/* 
 * Preview grid dynamically created with data returned by directFn
 *  
 */

Ext.define("HatimeriaCore.grid.Preview", {
    init: function() {
        var me = this;
        
        if(typeof me.config.directParams == 'undefined') {
            me.config.directParams = {};
        }
        
        me.config.directFn(me.config.directParams, function(response) {me.loadData(response)});
    },
    
    close: function() {
        this.grid.close();
    },
    loadData: function(response) {
        var me = this;
        var first = response.records[0];
        var keys = null;
        var headers = me.config.headers || null;
        var showAllData = headers == null;        
        
        if(showAllData) {
            if(typeof first != 'object') {
                alert('Empty recordset preview grid is not available');
                return;
            }
            
            keys = Object.keys(first);
            storeFields = keys;
        } else {
            keys = headers;
            storeFields = Object.keys(headers);
        }
        
        storeParams = {
            directFn: me.config.directFn,
            root: 'records',
            autoLoad: false,
            paramsAsHash: true,
            remoteSort: true,
            fields: storeFields
        };
        
        var store = Ext.create("Ext.data.DirectStore", storeParams);
        store.proxy.extraParams = me.config.directParams;
        store.load();
        
        var renderer = function(value) {
            if(typeof value == 'object') {
                var html = '';
                
                for(key in value) {
                    
                    var keyValue = value[key];
                    
                    if(typeof keyValue == 'object') {
                        keyValue = renderer(keyValue);
                    }
                    
                    html += key + ': ' + keyValue + '<br/>';
                }
                
                return html;
            }
            
            return value;
        };
        
        var columns = [];
        for(i in keys) {
            var key = keys[i];
            var column = {renderer: renderer, flex: 1, header: key}
            
            if(showAllData) {
                column.dataIndex = key;
            } else {
                column.dataIndex = i;
            }
            
            columns.push(column);
        }
        
        gridParams = {
            store: store,
            margin: '10px',
            width: 800,
            title: this.config.title || 'Preview grid',
            columns: columns,
            dockedItems: [{
                    xtype: 'pagingtoolbar',
                    store: store,
                    dock: 'bottom',
                    displayInfo: true
            }]
        };
        
        if(typeof this.config.renderTo != 'function') {
            gridParams.renderTo = this.config.renderTo || Ext.fly('body');
        }
        
        me.grid  = Ext.create("Ext.grid.Panel", gridParams);
        
        if(typeof this.config.renderTo == 'function') {
            this.config.renderTo(me.grid);
        }        
    },
    constructor: function(config){
        this.initConfig(config);
        
        return this;
    }
});