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

export default function ($wire){
    return {
        init(){
            const lat = parseFloat($wire.address.latitude) ?? 51.505;
            const long = parseFloat($wire.address.longitude) ?? -0.09;
            // init map
            this.map= L.map('map').setView([lat, long], 13);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(this.map);
            // set marker
            this.marker = L.marker([lat, long]).addTo(this.map);
            // update map on address change
            this.$watch('$wire.address',this.onChange.bind(this))
        },
        onChange(value){
            // remove old marker if exists
            if(this.marker){
                this.marker.remove();
            }
            const lat = parseFloat(value.latitude) ?? 51.505;
            const long = parseFloat(value.longitude) ?? -0.09;
            this.map.setView([lat, long], 13);
            this.marker = L.marker([lat, long]).addTo(this.map);
        },
        map:null,
        marker:null,
    }
}
