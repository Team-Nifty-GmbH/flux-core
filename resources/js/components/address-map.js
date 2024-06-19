import L from 'leaflet';
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

// remove default icon - take what vite generated
delete L.Icon.Default.prototype._getIconUrl;

L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow
});

export default function ($wire) {
    return {
        init() {
            // init map
            this.map = L.map('map');
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(this.map);

            this.lat = $wire.address.latitude !== null ? parseFloat($wire.address.latitude) : null;
            this.long = $wire.address.longitude !== null ? parseFloat($wire.address.longitude) : null;

            if (this.lat !== null && this.long !== null) {
                // x-show - racing condition with leaflet
                // Dom is not ready yet - hence next event loop run
                this.$nextTick(() => {
                    // create view
                    this.map.setView([this.lat, this.long], 13);
                    // set marker
                    this.marker = L.marker([this.lat, this.long]).addTo(this.map);
                });
            }
            // side-effect -> update map on address change
            this.$watch('$wire.address', this.onChange.bind(this));
        },
        onChange(value) {
            // remove old marker if exists
            if (this.marker) {
                this.marker.remove();
            }
            // update new lat and long
            this.lat = $wire.address.latitude !== null ? parseFloat($wire.address.latitude) : null;
            this.long = $wire.address.longitude !== null ? parseFloat($wire.address.longitude) : null;

            if (this.lat === null || this.long === null) {
                return;
            }
            this.$nextTick(() => {
                // create view
                this.map.setView([this.lat, this.long], 13);
                // set marker
                this.marker = L.marker([this.lat, this.long]).addTo(this.map);
            });
        },
        get showMap() {
            return this.lat && this.long;
        },
        map: null,
        marker: null,
        lat: null,
        long: null
    }
}
