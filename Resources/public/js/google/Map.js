Ext.define("HatimeriaCore.google.Map", {
    config: {
        address: null,
        renderTo: null
    },
    /**
     * Constructor
     * 
     * @param {} config
     */
    constructor: function(config)
    {
        this.initConfig(config);
        this.geocoder = new google.maps.Geocoder();

        return this;
    },
    
    render: function()
    {
        this.geocoder.geocode( {
            'address': this.config.address
            }, Ext.bind(this.handleGoogleResponse, this));
    },
    
    getContainer: function()
    {
        return document.getElementById(this.config.renderTo);
    },
    
    handleGoogleResponse: function(results, status)
    {
        if (status == google.maps.GeocoderStatus.OK) {
            var latlng = new google.maps.LatLng(-34.397, 150.644);
            var myOptions = {
                zoom: 9,
                center: latlng,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            }                  
            var map = new google.maps.Map(this.getContainer(), myOptions);
            map.setCenter(results[0].geometry.location);
            var marker = new google.maps.Marker({
                map: map,
                position: results[0].geometry.location
            });
        } else {
            alert("Geocode was not successful for the following reason: " + status);
        }
    }
})