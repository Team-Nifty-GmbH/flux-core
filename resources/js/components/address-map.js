import L from 'leaflet';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';
import 'leaflet.markercluster';

// remove default icon - take what vite generated
delete L.Icon.Default.prototype._getIconUrl;

L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow
});

export default function ($wire, propertyName, autoload = true, zoom = 13) {
    return {
        zoom: zoom,
        init() {
            // init map
            this.map = L.map('map');
            this.markers = L.markerClusterGroup();
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(this.map);

            // ensure that the property is wrapped in an object
            if (autoload) {
                this.$nextTick(() => {
                    this.addMarkers();
                });
            }

            if (typeof $wire[propertyName] !== 'function') {
                // side-effect -> update map on address change
                this.$watch('$wire.' + propertyName, this.onChange.bind(this));
            }
        },
        addMarkers(addresses = null) {
            let address = addresses ?? $wire[propertyName];
            if (propertyName.includes('.') && ! addresses) {
                const props = propertyName.split('.');
                address = $wire;
                props.forEach(prop => {
                    address = address[prop];
                });
            }

            if (typeof address === 'function') {
                address = address();
            }

            // check if async function
            if (address instanceof Promise) {
                address.then((address) => {
                    this.addMarkers(address);
                });
                return;
            }


            if (! address) {
                return;
            }

            if (! Array.isArray(address)) {
                address = [address];
            }

            this.markers.clearLayers();
            address.forEach((address) => {
                if (! address.latitude || ! address.longitude) {
                    return;
                }

                let icon = null;
                let options = {};
                if (address.hasOwnProperty('icon')) {
                    icon = L.divIcon({
                        className: 'custom-icon',
                        html: address.icon,
                    });
                    options.icon = icon;
                }

                let marker = L.marker([address.latitude, address.longitude], options);
                if (address.popup) marker.bindPopup(address.popup);
                if (address.tooltip) marker.bindTooltip(address.tooltip);

                this.markers.addLayer(marker);
            });

            if (this.markers.getLayers().length > 0) {
                this.markers.addTo(this.map);
                this.$nextTick(() => {
                    this.resizeMap();
                });
            }
        },
        resizeMap() {
            this.map.invalidateSize();
            // check if bounds are given#
            let boundZoom = 0;
            if (this.markers.getBounds().isValid()) {
                this.map.fitBounds(this.markers.getBounds(), {padding: [50, 50]});
                boundZoom = this.map.getBoundsZoom(this.markers.getBounds());
            }
            this.map.setZoom(boundZoom > this.zoom ? this.zoom : boundZoom);
        },
        onChange() {
            this.$nextTick(() => {
                // create view
                this.addMarkers();
            });
        },
        get showMap() {
            return this.markers.getLayers().length > 0;
        },
        map: null,
        markers: null,
        lat: null,
        long: null
    }
}
