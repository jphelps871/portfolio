export class Map {
  constructor() {
    /* Create map */
    this.map = L.map('mapid', {
      zoomControl: false,
      minZoom: 2,
      tap: false,
      fullscreenControl: true,
    }).setView([0, 0], 2);
    this.layers = L.control.layers(null, null, {
      position: 'topleft',
    }).addTo(this.map);

    this.addTile();

    /* Styles for border country */
    this.borderColor = 'green';

    this.countryLayer = '';
    this.borderLayers = [];

    /* Layers */
    this.wikiLayer = L.layerGroup().addTo(this.map);
    this.weatherLayer = L.layerGroup().addTo(this.map);
    this.attractionLayer = L.layerGroup().addTo(this.map);
    this.webcamLayer = L.layerGroup().addTo(this.map);
    this.airportLayer = L.layerGroup().addTo(this.map);

    /* map access to certain features  */
    this.allowBorders = false;
  }

  /**
   *
   * @public
   * @param {string} tile - To display the map
   * @param {string} attribution - meta data
   *
   */

  addTile() {
    const stadiaAttr =
      '&copy; <a href="https://stadiamaps.com/">Stadia Maps</a>, &copy; <a href="https://openmaptiles.org/">OpenMapTiles</a> &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors';
    const openStreetAttr = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';
    const stamenAttr =
      'Map tiles by <a href="http://stamen.com">Stamen Design</a>, <a href="http://creativecommons.org/licenses/by/3.0">CC BY 3.0</a> &mdash; Map data &copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors';

    const classicUrl = 'https://tiles.stadiamaps.com/tiles/alidade_smooth/{z}/{x}/{y}{r}.png';
    const darkUrl = 'https://tiles.stadiamaps.com/tiles/alidade_smooth_dark/{z}/{x}/{y}{r}.png';
    const traditionalUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
    const basicUrl = 'https://stamen-tiles-{s}.a.ssl.fastly.net/toner-background/{z}/{x}/{y}{r}.png';

    const classicTile = L.tileLayer(classicUrl, { attribution: stadiaAttr });
    const darkTile = L.tileLayer(darkUrl, { attribution: stadiaAttr });
    const traditionalTile = L.tileLayer(traditionalUrl, {
      attribution: openStreetAttr,
    });
    const basicTile = L.tileLayer(basicUrl, { attribution: stamenAttr });

    this.layers.addBaseLayer(classicTile, 'Base map');
    this.layers.addBaseLayer(darkTile, 'Dark map');
    this.layers.addBaseLayer(traditionalTile, 'Traditional map');
    this.layers.addBaseLayer(basicTile, 'Basic map');
  
    classicTile.addTo(this.map);
  }

  /**
   *
   * @public
   * @param {array} data - GeoJson coordinates
   * @param {string} type - Type of GeoJson data
   *
   */

  locateCountry(data, type = 'MultiPolygon') {
    if (this.countryLayer) this.resetAllLayers();

    try {
      const location = this.createPolygon(data, type, {
        color: this.borderColor,
        lineJoin: 'round',
      });

      this.countryLayer = location;
      /* Pan to country */
      this.map.flyToBounds(this.countryLayer.getBounds());

      location.addTo(this.map);
    } catch (e) {
      /* if coordinates are Polygon rather than MultiPolygon */
      if (e.name === 'Error') this.locateCountry(data, 'Polygon');
    }
  }

  /**
   *
   * @public
   * @param {array} data - GeoJson coordinates
   * @param {string} type - Type of GeoJson data
   *
   */

  locateBorders({geometry, properties}, type = 'MultiPolygon') {
    try {
      const borderLocation = this.createPolygon(geometry.coordinates, type, {
        color: '#ffc107',
        dashArray: '10,20',
        lineJoin: 'round',
      });

      borderLocation.bindTooltip(properties.name, {
        sticky: true,
      });

      this.borderLayers.push(borderLocation);
      borderLocation.addTo(this.map);
    } catch (e) {
      // if coordinates are Polygon rather than MultiPolygon
      if (e.name === 'Error') this.locateBorders({geometry, properties}, 'Polygon');
    }
  }

  /**
   *
   * @public
   * @param {object} - data and location for marker and popup
   *
   */

  addWikiMarkers(pointsOfInterest) {
    this.wikiLayer.clearLayers();
    this.layers.removeLayer(this.wikiLayer);

    const clusterGroup = L.markerClusterGroup();

    pointsOfInterest.forEach(city => {
      if (!city.location.lat || !city.location.lon) return;
      
      const marker = this.markersPopups(city, {
        icon: 'fa-info',
        markerColor: 'red',
        shape: 'square',
      })

      marker.bindPopup(`
        <h5> ${city.name} </h5>
        <p> ${city.intro} </p>
        <img style="width: 100%; height: 100%" src="${city.img}"/>
      `);

      clusterGroup.addLayer(marker)

      this.wikiLayer.addLayer(clusterGroup);
    });

    this.layers.addOverlay(this.wikiLayer, "Wikipedia");
  }

  addWeatherMarkers(pointsOfInterest) {
    this.weatherLayer.clearLayers();
    this.layers.removeLayer(this.weatherLayer);

    const clusterGroup = L.markerClusterGroup();

    pointsOfInterest.forEach(city => {
        if (!city.location.lat || !city.location.lon) return;

        const marker = this.markersPopups(city, {
          icon: 'fa-cloud-sun',
          color: 'blue',
          shape: 'circle',
        });
  
        marker.bindPopup(`
          <h5> ${city.name} </h5>
          <h6> ${city.weather.text} </h6>
          <p><b>Current temperature:</b> ${city.weather.temp}° </p>
          <p><b>Feels like:</b> ${city.weather.feels_like}° </p>
          <p><b>Wind speed:</b> ${city.weather.wind_speed}mph </p>
        `);

        clusterGroup.addLayer(marker);
  
        // markers.push(marker);
        this.weatherLayer.addLayer(clusterGroup);
    })

    this.layers.addOverlay(this.weatherLayer, "Weather");
  }

  addAttractionMarkers(pointsOfInterest) {
    this.attractionLayer.clearLayers();
    this.layers.removeLayer(this.attractionLayer);

    const clusterGroup = L.markerClusterGroup();

    pointsOfInterest.forEach(attraction => {
      if (!attraction.location.lat || !attraction.location.lon) return;

      const marker = this.markersPopups(attraction, {
        icon: 'fa-compass',
        color: 'yellow',
        shape: 'penta',
      });

      marker.bindPopup(`
        <h5> ${attraction.name} </h5>
        <p> Rating: ${attraction.rating} / 5</p>
        <img style="width: 100%; height: 100%" src="${attraction.img}"/>
        <p> ${attraction.description} </p>
        <a href="${attraction.link}" target="_blank">See more</a>
      `);

      clusterGroup.addLayer(marker)
      this.attractionLayer.addLayer(clusterGroup);
    })

    this.layers.addOverlay(this.attractionLayer, "Attractions");

  }

  addWebcamMarkers(pointsOfInterest) {
    this.webcamLayer.clearLayers();
    this.layers.removeLayer(this.webcamLayer);

    const clusterGroup = L.markerClusterGroup();

    pointsOfInterest.forEach(webcam => {
      if (!webcam.location.lat || !webcam.location.lon) return;

      const marker = this.markersPopups(webcam, {
        icon: 'fa-camera',
        color: 'blue-dark',
        shape: 'star',
      });

      marker.bindPopup(`
        <h5> ${webcam.name} </h5>
        <p> ${webcam.city} </p>
        <img style="width: 100%; height: 100%" src="${webcam.img}"/>
      `);

      clusterGroup.addLayer(marker);
      this.webcamLayer.addLayer(clusterGroup);
    })

    this.layers.addOverlay(this.webcamLayer, "Webcams");

  }

  addAirports(pointsOfInterest) {
    this.airportLayer.clearLayers();
    this.layers.removeLayer(this.airportLayer);

    const clusterGroup = L.markerClusterGroup();

    pointsOfInterest.forEach(airport => {
      if (!airport.location.lat || !airport.location.lon) return;

      const marker = this.markersPopups(airport, {
        icon: 'fa-plane-departure',
        color: 'purple',
        shape: 'square',
      });

      clusterGroup.addLayer(marker);
      this.airportLayer.addLayer(clusterGroup);
    })

    this.layers.addOverlay(this.airportLayer, "Airports");
  }

  markersPopups({name, location}, { icon, color, shape }) {
    const iconStyle = L.ExtraMarkers.icon({
      icon: icon,
      markerColor: color,
      shape: shape,
      prefix: 'fa',
    });

    const marker = L.marker([location.lat, location.lon], {
      icon: iconStyle,
      riseOnHover: true,
    })

    marker.bindTooltip(name, {
      offset: L.point(5, -21),
    });

    return marker;
  }

  /**
   *
   * @public
   *
   */

  resetAllLayers() {
    /* Removing neighbouring border countries */
    this.removeBorders();

    /* Remove country border */
    if (this.countryLayer) this.map.removeLayer(this.countryLayer);

    /* Remove points of interest */
  }

  /**
   *
   * @public
   *
   */

  removeBorders() {
    if (this.countryLayer) {
      this.borderLayers.forEach(border => this.map.removeLayer(border));
    }
  }

  

  /**
   *
   * @private
   * @param {array} data - GeoJson coordinates
   * @param {sting} type - GeoJson type of data
   * @param {object} style - how to style the borders
   *
   */

  createPolygon(data, type, style) {
    return L.geoJSON(
      {
        type: 'Feature',
        geometry: {
          type: type,
          coordinates: data,
        },
      },
      {
        style: style,
      },
    );
  }
}
