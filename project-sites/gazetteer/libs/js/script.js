import { Map } from './map.js';

const myMap = new Map();

/* Preloader */
const popup = document.querySelector('.popup');
const preLoader = document.querySelector('.preloader');
const preLoaderContent = document.querySelector('.popup-content');

/* Header */
const countryListDropDown = document.getElementById('countryListDropdown');
const selectBorderCountries = document.getElementById('selectBorderCountries');
const selectRandomCountry = document.getElementById('selectRandomCountry');

/* Main */
const CountryInformationTextElements = document.querySelectorAll('.country-data');
const spinner = document.querySelector('.spinner');
const panelButtons = document.querySelector('.panel-buttons-container');


/* 

  Fetching data

*/

const postOption = (bodyData, contentType = 'text/html') => {
  return {
    method: 'POST',
    headers: {
      'Content-type': contentType,
    },
    body: bodyData,
  };
};

const json = {
  countryNames: async () => {
    try {
      const response = await fetch(
        './libs/php/controller/db/all_country_names.php',
      );

      if (!response.ok) throw new Error(response.status.description);

      const data = await response.json();
      return data;
    } catch (err) {
      console.error(err);
    }
  },

  getCountryData: async (userCountry) => {
    try {
      const response = await fetch(
        './libs/php/controller/db/get_country_name.php',
        postOption(JSON.stringify(userCountry), 'application/json'),
      );

      if (!response.ok) throw new Error(response.status.description);

      const countryName = await response.json();
      return countryName;
    } catch (err) {
      console.error(err);
    }
  },

  getCountryDataByIdx: async (index) => {
    try {
      const response = await fetch(
        './libs/php/controller/db/get_country_by_idx.php',
        postOption(index),
      );
      const countryName = await response.json();

      if (!response.ok) throw new Error(response.status.description);
      return countryName;
    } catch (err) {
      console.error(err);
    }
  },

  getBoundingBox: async (countryCode) => {
    try {
      const response = await fetch(
        './libs/php/controller/db/get_bounding_box.php',
        postOption(countryCode),
      );

      if (!response.ok) throw new Error(response.status.description);

      const responseBoundingBox = await response.json();
      return responseBoundingBox.data;
    } catch (err) {
      console.error(err);
    }
  },
};

const api = {
  getCountryData: async (country) => {

    const response = await fetch(
      './libs/php/controller/api/country_data.php',
      postOption(JSON.stringify(country), 'application/json'),
    );

    const data = await response.json();
    console.log(data);
    return data;

  },

  getUserCounty: async (coords) => {
    const response = await fetch(
      './libs/php/controller/api/user_coords.php',
      postOption(JSON.stringify(coords), 'application/json'),
    );

    const countryCode = response.json();
    return countryCode;
  },

  getPointsOfInterest: async (areaOfInterest) => {
    const response = await fetch(
      './libs/php/controller/api/points_of_interest.php',
      postOption(JSON.stringify(areaOfInterest), 'application/json'),
    );

    const pointsOfInterest = await response.json();
    return pointsOfInterest;
  },
};

/* Display country data and information */

// Create an array of keys and values for all api data. key[4] matches value[4]
// If HTML data attribute matches api keys, HTML element is equal to value.
// If api data is weather forcast, create div elements

const displayCountryData = (countryApiData) => {
  const apiDataKeys = [];
  const apidDataValues = [];

  let weather_forcast_data;

  Object.keys(countryApiData).forEach((objectKey) => {

    if (objectKey === 'weather_forcast') {
      weather_forcast_data = countryApiData[objectKey];
    } else {
      const keys = Object.keys(countryApiData[objectKey]);
      const values = Object.values(countryApiData[objectKey]);
      apiDataKeys.push(keys);
      apidDataValues.push(values);
    }
  })

  const apiNames = apiDataKeys.flat();
  const apiValues = apidDataValues.flat();

  // match data key to html element name
  CountryInformationTextElements.forEach((htmlElement) => {
    htmlElement.innerText = '';

    const dataLabelForTextElement = htmlElement.getAttribute('data-info-header');

    // Add data to panels, if data is weather_forcast then 
    // create weather divs.

    if (dataLabelForTextElement === 'weather_forcast') {
      const weatherForcastContainer = htmlElement;

      // Create weather divs for weather panel
      weather_forcast_data.forEach(data => {  
        
        const weatherContainer = document.createElement('div')
        weatherContainer.classList.add('weather-container');

        const weatherText = document.createElement('div')
        weatherText.classList.add('weather-text');
        
        const h6 = document.createElement('h6');
        h6.innerText = data.Week_day;

        const description = document.createElement('p');
        description.innerText = data.Description;

        const temperature = document.createElement('p');
        temperature.innerText = `Temperature: ${data.Temp}°C`;

        const wind = document.createElement('p');
        wind.innerText = `Wind speed: ${data.Wind}mph`;

        weatherText.append(h6)
        weatherText.append(description)
        weatherText.append(temperature)
        weatherText.append(wind);

        const imgContainer = document.createElement('div');
        imgContainer.classList.add('weather-icon');

        const img = document.createElement('img');

        // If bellow weather types exists, display generic weather image
        switch (data.weather_icon) {
          case 'Mist':
          case 'Smoke':
          case 'Haze':
          case 'Dust':
          case 'Fog':
          case 'Sand':
          case 'Ash':
          case 'Squall':
          case 'Tornado':
            data.weather_icon = 'weather_all';
            break;
        }

        // weather images share data base weather icon name
        img.src = `./img/weather/${data.weather_icon}.svg`;

        imgContainer.append(img);
        
        weatherContainer.append(weatherText);
        weatherContainer.append(imgContainer);
        weatherForcastContainer.append(weatherContainer);
      })
    } else {
      const idx = apiNames
        .map((key, idx) => key === dataLabelForTextElement ? idx : '')
        .filter(idx => typeof idx === 'number')
        .join()

      // Create img
      if (dataLabelForTextElement === 'Flag') {
        htmlElement.src = apiValues[idx];

      // Create link
      } else if (dataLabelForTextElement === 'Link') {

        // Check if link exists
        if (apiValues[idx]) {
          htmlElement.setAttribute('href', apiValues[idx]);
          htmlElement.innerText = 'See more';
        }
        
      // Create text
      } else {
        htmlElement.innerText = apiValues[idx];
      }
    }

  });
};

/* Collect country data to display */

const countryDataAndLocation = async (countryData) => {
  const { geometry } = await json.getCountryData(countryData);
  const boundingBoxCoords = await json.getBoundingBox(countryData.iso_a2);
  const pointsOfInterest = await api.getPointsOfInterest({coords: boundingBoxCoords, iso_a2: countryData.iso_a2, country: countryData.name});
  const countryInfo = await api.getCountryData(countryData);

  // * Display points of interest
  myMap.addWikiMarkers(pointsOfInterest.wikipedia);
  myMap.addAttractionMarkers(pointsOfInterest.attractions);
  myMap.addWebcamMarkers(pointsOfInterest.webcam);
  myMap.addAirports(pointsOfInterest.airports);
  myMap.addWeatherMarkers(countryInfo.cities);

  // * Locate country after loading
  myMap.locateCountry(geometry.coordinates);

  // * Display in side panels
  displayCountryData(countryInfo);
  displayLoader(false);
};

/*

  Header Content

*/

/* Displaying Header Content */

(async function () {
  const allCountryName = await json.countryNames();
  for (let country of allCountryName) {
    const option = document.createElement('option');

    option.value = country.name;
    option.innerText = country.name;
    option.setAttribute('data-iso_a3', country.iso_a3);
    option.setAttribute('data-iso_a2', country.iso_a2);
  
    option.classList.add('country-to-select');
    countryListDropDown.appendChild(option);
  }
})();

/*

  Event listeners for header

*/

/* Select Country from list */
let selectedCountryInfo = {
  name: '',
  iso_a2: '',
  iso_a3: ''
};

countryListDropDown.addEventListener('change', async ({ target }) => {
  // Begin laoding
  displayLoader(true);
  const selected = target.options[target.selectedIndex];

  selectedCountryInfo.name = selected.value;
  selectedCountryInfo.iso_a2 = selected.getAttribute('data-iso_a2');
  selectedCountryInfo.iso_a3 = selected.getAttribute('data-iso_a3');

  // Display in dropdown
  countryDataAndLocation(selectedCountryInfo);
});

/* Locate user position */
function geolocateUser() {
  // Begin laoding
  displayLoader(true);

  navigator.geolocation.getCurrentPosition(
    async (location) => {
      const latitude = location.coords.latitude;
      const longitude = location.coords.longitude;

      // return iso2('GBR') and iso3('GB')
      const countryCode = await api.getUserCounty({
        lat: latitude,
        lon: longitude,
      });

      const {properties} = await json.getCountryData(countryCode);

      // Display in dropdown
      for (const country of countryListDropDown.options) {
        if (country.innerText === properties.name) {
          country.setAttribute('selected', 'selected');
        }
      }      

      selectedCountryInfo.name = properties.name;
      selectedCountryInfo.iso_a2 = properties.iso_a2;
      selectedCountryInfo.iso_a3 = properties.iso_a3;
    
      countryDataAndLocation(selectedCountryInfo);
    },
    (error) => {
      console.error('Geolocation not allowed')
      window.alert('Allow location in browser to discover your country');
      randomCountry();
    },
    { timeout: 10000 },
  );
}

/* Find a random Country */
selectRandomCountry.addEventListener('click', randomCountry);

async function randomCountry() {
  // Begin laoding
  displayLoader(true);

  const numOfCountries = 174;
  const index = Math.floor(Math.random() * numOfCountries);
  const countryData = await json.getCountryDataByIdx(index);

  const countryProperties = countryData.properties;

  // Display in dropdown
  for (const country of countryListDropDown.options) {
    if (country.innerText === countryProperties.name) {
      country.setAttribute('selected', 'selected');
    }
  } 

  // Add to global object, mainly for finding borders
  selectedCountryInfo.name = countryProperties.name;
  selectedCountryInfo.iso_a2 = countryProperties.iso_a2;
  selectedCountryInfo.iso_a3 = countryProperties.iso_a3;

  countryDataAndLocation(countryProperties);
}

/* Locate countries borders */
selectBorderCountries.addEventListener('click', async () => {
  const selectedCountry = countryListDropDown.value;

  // Return if user hasn't selected a country
  if (selectedCountry === 'Select a country') return;

  // Prevent user adding multiple layers
  myMap.removeBorders();

  spinner.classList.toggle('spinner-border');

  const countryData = await api.getCountryData(selectedCountryInfo);
  const borders = countryData.general.Borders;
  borders.forEach(async (border) => {
    const country = await json.getCountryData({ iso_a3: border });
    if (country.status === '404') return;
    myMap.locateBorders(country);
  });

  spinner.classList.toggle('spinner-border');
});

/*

  Panel Information

*/

/* display panels that match the button */
const panels = document.querySelectorAll('.panel-info');
const panelContainer = document.querySelector('.panel-container')
const panelData = document.querySelector('.panel');

panelButtons.addEventListener('click', ({target}) => {

  if (target.classList.contains('show-panel')) {

    const hideBtn = panelButtons.lastElementChild;

    hideBtn.style.opacity = '1';
    panelData.style.display = 'block';
    panelContainer.classList.add('bottom-spacing-lg');

    const panelBtnName = target.id;

    panels.forEach((panel) => {
      
      if (panel.classList.contains(panelBtnName)) {
        panel.classList.remove('hidden');
      } else {
        panel.classList.add('hidden');
      }

    })

  }

  if (target.classList.contains('remove-panel')) {

    const hideBtn = target;
    hideBtn.style.opacity = '0';
    panelContainer.classList.remove('bottom-spacing-lg');
    panelData.style.display = 'none';

  }
})


/*

  Preloader

*/

popup.addEventListener('click', ({target}) => {
  if (target.nodeName === 'BUTTON') {
    preLoaderContent.style.marginTop = '-1000px';
    setTimeout(() => {
      popup.style.opacity = '0';
    }, 200)
    setTimeout(() => {
      popup.style.display = 'none';
    }, 600)
  }
})

let interval = 0;
const introBtnDone = document.getElementById('introBtnDone');

function displayLoader(value) {

  // Display spinner on btn in intro panel, if intro panel is visible
  if (window.getComputedStyle(popup).display === 'block') {

    if (value) {
      introBtnDone.classList.add('spinner-border');
      introBtnDone.classList.add('text-warning');
      introBtnDone.backgroundColor = 'transparent';
    } else {
      introBtnDone.classList.remove('spinner-border');
      introBtnDone.classList.remove('text-warning');
      introBtnDone.classList.add('btn-popup');
      introBtnDone.innerText = 'Done';
      popup.style.backgroundColor = 'transparent';
    }

  // Display "earth" loader if there is no intro panel
  } else {

    const moon = document.querySelector('.moon-container');
    preLoader.style.display = 'block';
  
    let checked = value;
    let counter = 0;
  
    if (checked) {
      interval = window.setInterval(() => {
        counter += 1;
        moon.style.transform = `rotate(${counter}deg)`;
      }, 8)
    } else {
      clearInterval(interval);
      preLoader.style.display = 'none';
    }
  }
}

geolocateUser();