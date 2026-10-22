const weatherForm = document.getElementById("weatherForm");

const cityInput = document.getElementById("city");
const errorMessage = document.getElementById("errorMessage");

const weatherResult = document.getElementById("weatherResult");
const cityName = document.getElementById("cityName");
const temperature = document.getElementById("temperature");
const description = document.getElementById("description");
const humidity = document.getElementById("humidity");
const wind = document.getElementById("wind");

const API_KEY = "b7e92ea0b3b8bff5c430d56d5a012903";

weatherForm.addEventListener("submit", async function (event) {
    event.preventDefault();

    const city = cityInput.value.trim();

    errorMessage.textContent = "";
    weatherResult.style.display = "none";

    if (city === "") {
        errorMessage.textContent = "Please enter a city name.";
        return;
    }

    try {
        const apiURL =
            `https://api.openweathermap.org/data/2.5/weather?q=${encodeURIComponent(city)}&appid=${API_KEY}&units=metric`;

        const response = await fetch(apiURL);

        if (!response.ok) {
            throw new Error("Weather data not found.");
        }

        const data = await response.json();

        cityName.textContent = `${data.name}, ${data.sys.country}`;
        temperature.textContent = `Temperature: ${data.main.temp} °C`;
        description.textContent =
            `Weather: ${data.weather[0].description}`;
        humidity.textContent =
            `Humidity: ${data.main.humidity}%`;
        wind.textContent =
            `Wind Speed: ${data.wind.speed} m/s`;

        weatherResult.style.display = "block";
    } catch (error) {
        errorMessage.textContent =
            "Unable to get weather data. Check the city name or API key.";
    }
});