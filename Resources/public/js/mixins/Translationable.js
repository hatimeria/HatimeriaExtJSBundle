/** 
 * Tranlation layer
 * 
 * Component should be mixex to each other components
 */
(function() {
    
    Ext.define('HatimeriaCore.mixins.Translationable', {
        extend: 'Ext.Base',
        
        __: function(key, placeholders)
        {
            var _placeholders = placeholders || {};
            var translated = '';
            
            if(this.transNS) {
                var fullKey = this.transNS + '.' + key;
                translated = __(fullKey, _placeholders);
                
                if(translated == fullKey) {
                    translated = __(key, _placeholders);
                } else {
                    return translated;
                }
                
                if(translated == key) {
                    return fullKey;
                } else {
                    return translated;
                }
            } 
            
            return __(key, _placeholders);
        }
    });
    
})();