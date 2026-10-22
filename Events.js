let allEvents = [];
let filteredEvents = [];
let currentPage = 1;
const eventsPerPage = 4;

const eventContainer = document.getElementById("eventContainer");
const searchInput = document.getElementById("searchInput");
const categoryFilter = document.getElementById("categoryFilter");
const sortSelect = document.getElementById("sortSelect");
const paginationContainer = document.getElementById("pagination");

fetch("events.json")
    .then(function(response) {
        if (!response.ok) {
            throw new Error("Unable to load events.json");
        }
        return response.json();
    })
    .then(function(data) {
        allEvents = data;
        filteredEvents = data;
        createCategoryOptions();
        displayEvents();
    })
    .catch(function(error) {
        eventContainer.innerHTML = "<p>Unable to load event data.</p>";
        console.error(error);
    });

function createCategoryOptions() {
    const categories = [];

    allEvents.forEach(function(event) {
        if (!categories.includes(event.category)) {
            categories.push(event.category);
        }
    });

    categories.sort();

    categories.forEach(function(category) {
        const option = document.createElement("option");
        option.value = category;
        option.textContent = category;
        categoryFilter.appendChild(option);
    });
}

function displayEvents() {
    eventContainer.innerHTML = "";

    const startIndex = (currentPage - 1) * eventsPerPage;
    const endIndex = startIndex + eventsPerPage;

    const eventsToDisplay = filteredEvents.slice(startIndex, endIndex);

    if (eventsToDisplay.length === 0) {
        eventContainer.innerHTML = "<p>No events found.</p>";
        paginationContainer.innerHTML = "";
        return;
    }

    eventsToDisplay.forEach(function(event) {
        const eventCard = document.createElement("div");

        eventCard.className = "event-card";

        eventCard.innerHTML = `
            <h3>${event.name}</h3>
            <p><strong>Date:</strong> ${formatDate(event.date)}</p>
            <p><strong>Venue:</strong> ${event.venue}</p>
            <p><strong>Category:</strong> ${event.category}</p>
            <p>${event.description}</p>
            <form action="registrationForEvent.html">
                <input type="submit" value="Register">
            </form>
        `;

        eventContainer.appendChild(eventCard);
    });

    createPagination();
}

function formatDate(date) {
    const eventDate = new Date(date);

    return eventDate.toLocaleDateString("en-GB", {
        day: "2-digit",
        month: "short",
        year: "numeric"
    });
}

function applyFilters() {
    const searchText = searchInput.value.toLowerCase();
    const selectedCategory = categoryFilter.value;

    filteredEvents = allEvents.filter(function(event) {
        const matchesSearch =
            event.name.toLowerCase().includes(searchText) ||
            event.venue.toLowerCase().includes(searchText) ||
            event.category.toLowerCase().includes(searchText);

        const matchesCategory =
            selectedCategory === "all" ||
            event.category === selectedCategory;

        return matchesSearch && matchesCategory;
    });

    applySorting();

    currentPage = 1;

    displayEvents();
}

function applySorting() {
    const sortValue = sortSelect.value;

    if (sortValue === "nameAsc") {
        filteredEvents.sort(function(a, b) {
            return a.name.localeCompare(b.name);
        });
    }

    if (sortValue === "nameDesc") {
        filteredEvents.sort(function(a, b) {
            return b.name.localeCompare(a.name);
        });
    }

    if (sortValue === "dateAsc") {
        filteredEvents.sort(function(a, b) {
            return new Date(a.date) - new Date(b.date);
        });
    }

    if (sortValue === "dateDesc") {
        filteredEvents.sort(function(a, b) {
            return new Date(b.date) - new Date(a.date);
        });
    }
}

function createPagination() {
    paginationContainer.innerHTML = "";

    const totalPages = Math.ceil(filteredEvents.length / eventsPerPage);

    if (totalPages <= 1) {
        return;
    }

    const previousButton = document.createElement("button");

    previousButton.textContent = "Previous";
    previousButton.disabled = currentPage === 1;

    previousButton.addEventListener("click", function() {
        if (currentPage > 1) {
            currentPage--;
            displayEvents();
        }
    });

    paginationContainer.appendChild(previousButton);

    for (let page = 1; page <= totalPages; page++) {
        const pageButton = document.createElement("button");

        pageButton.textContent = page;

        if (page === currentPage) {
            pageButton.classList.add("active-page");
        }

        pageButton.addEventListener("click", function() {
            currentPage = page;
            displayEvents();
        });

        paginationContainer.appendChild(pageButton);
    }

    const nextButton = document.createElement("button");

    nextButton.textContent = "Next";
    nextButton.disabled = currentPage === totalPages;

    nextButton.addEventListener("click", function() {
        if (currentPage < totalPages) {
            currentPage++;
            displayEvents();
        }
    });

    paginationContainer.appendChild(nextButton);
}

searchInput.addEventListener("input", function() {
    applyFilters();
});

categoryFilter.addEventListener("change", function() {
    applyFilters();
});

sortSelect.addEventListener("change", function() {
    applySorting();
    currentPage = 1;
    displayEvents();
});