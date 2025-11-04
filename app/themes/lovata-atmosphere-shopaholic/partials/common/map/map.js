import loadGoogleMapsApi from 'load-google-maps-api';

class Map {
  constructor() {
    this.mapSelector = '.map';
    this.mapWrapper = null;
    this.apiKey = null;
    this.lat = null;
    this.lng = null;

    this.markerPath = null;
    this.maxWidth = 280;

    this.obMap = null;
  }

  init() {
    this.mapWrapper = document.querySelector(this.mapSelector);
    this.apiKey = this.mapWrapper ? this.mapWrapper.dataset.apiKey : null;
    if (!this.mapWrapper || !this.apiKey) {
      return;
    }

    this.lat = parseFloat(this.mapWrapper.dataset.lat);
    this.lng = parseFloat(this.mapWrapper.dataset.lng);
    this.markerPath = this.mapWrapper.dataset.markerPath;
    if (!this.lat || !this.lng || !this.markerPath) {
      return;
    }

    this.drawMap();
  }

  async drawMap() {
    const obThis = this;
    loadGoogleMapsApi({ key: this.apiKey }).then(async (googleMaps) => {
      const { Map, Marker, InfoWindow } = googleMaps;

      const position = {lat: obThis.lat, lng: obThis.lng};
      obThis.obMap = new Map(obThis.mapWrapper, {
        center: position,
        zoom: 14,
      });

      const marker = new Marker({
        map: obThis.obMap,
        position: position,
        icon: obThis.markerPath,
      });

      oc.ajax('onAjax', {
        update: { 'common/map/popup': obThis.mapSelector },
        success: (response) => {
          const content = response['common/map/popup'];
          const infoWindow = new InfoWindow({ content, maxWidth: obThis.maxWidth });

          infoWindow.open(obThis.obMap, marker);
          marker.addListener('click', () => {
            infoWindow.open(obThis.obMap, marker);
          });
        },
      });
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const obMap = new Map();
  obMap.init();
});
