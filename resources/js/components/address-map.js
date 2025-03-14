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
    shadowUrl: markerShadow,
});

export default function (
    $wire,
    propertyName,
    autoload = true,
    userIcon = null,
    zoom = 13,
) {
    return {
        zoom: zoom,
        init() {
            // init map
            this.map = L.map('map');
            this.markers = L.markerClusterGroup();
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution:
                    '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
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
            if (propertyName.includes('.') && !addresses) {
                const props = propertyName.split('.');
                address = $wire;
                props.forEach((prop) => {
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

            if (!address) {
                return;
            }

            if (!Array.isArray(address)) {
                address = [address];
            }

            this.markers.clearLayers();
            address.forEach((address) => {
                if (!address.latitude || !address.longitude) {
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

                let marker = L.marker(
                    [address.latitude, address.longitude],
                    options,
                );
                if (address.popup) marker.bindPopup(address.popup);
                if (address.tooltip) marker.bindTooltip(address.tooltip);

                this.markers.addLayer(marker);
            });

            if (this.markers.getLayers().length > 0) {
                this.markers.addTo(this.map);
            }

            if (navigator.geolocation) {
                this.addUserMarker();
            }

            this.$nextTick(() => {
                this.resizeMap();
            });
        },
        resizeMap() {
            this.map.invalidateSize();

            let allMarkersBounds = null;
            let hasMarkerBounds = this.markers.getBounds().isValid();
            let userMarkerBounds = this.userMarker
                ? L.latLngBounds(
                      this.userMarker.getLatLng(),
                      this.userMarker.getLatLng(),
                  )
                : null;

            if (hasMarkerBounds) {
                allMarkersBounds = this.markers.getBounds();

                if (userMarkerBounds) {
                    allMarkersBounds.extend(userMarkerBounds);
                }
            } else if (userMarkerBounds) {
                allMarkersBounds = userMarkerBounds;
            }

            // Check if we have valid bounds to fit the map view
            if (allMarkersBounds && allMarkersBounds.isValid()) {
                this.map.fitBounds(allMarkersBounds, { padding: [50, 50] });
                let boundZoom = this.map.getBoundsZoom(allMarkersBounds);

                // Adjust the zoom level if necessary
                let finalZoom = boundZoom > this.zoom ? this.zoom : boundZoom;
                if (this.map.getZoom() !== finalZoom) {
                    this.map.setZoom(finalZoom);
                }
            } else {
                let defaultZoom = 2;
                this.map.setView([0, 0], defaultZoom);
            }
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
        addUserMarker() {
            navigator.geolocation.getCurrentPosition((position) => {
                let icon = null;
                let options = {};
                if (userIcon) {
                    options.icon = L.divIcon({
                        className: '', // Remove default class styling for full customization
                        html: `<div class="shrink-0 inline-flex items-center justify-center overflow-hidden rounded-full border border-gray-200 dark:border-secondary-500">
                                <img class="shrink-0 object-cover object-center rounded-full w-12 h-12 text-lg" src="${userIcon}">
                            </div>`,
                        iconSize: [48, 48], // Set to match overall size of the div
                        iconAnchor: [24, 24], // Center anchor
                    });
                }

                this.userMarker = L.marker(
                    [position.coords.latitude, position.coords.longitude],
                    options,
                ).addTo(this.map);

                this.$nextTick(() => {
                    this.resizeMap();
                });
            });
        },
        userMarker: null,
        map: null,
        markers: null,
        lat: null,
        long: null,
    };
}
