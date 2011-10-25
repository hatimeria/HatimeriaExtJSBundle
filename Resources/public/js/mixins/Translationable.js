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
            var domain = '';
            
            if (typeof this.transDomain != 'undefined')
            {
                domain = this.transDomain + ':';
            }
            
            if (this.transNS)
            {
                var fullKey = domain + this.transNS + '.' + key;
                translated = __(fullKey, _placeholders);
                
                // if key not exists: return raw key (without domain and transNS)
                if (translated == fullKey) {
                    translated = __(key, _placeholders);
                } else {
                    return translated;
                }
                
                if (translated == key) {
                    return fullKey;
                } else {
                    return translated;
                }
            }
            
            return __(domain + key, _placeholders);
        }
    });
    
})();