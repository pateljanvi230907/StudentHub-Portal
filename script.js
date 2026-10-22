const faqQuestions = document.querySelectorAll(".faq-question");

faqQuestions.forEach(function(question) {
    question.addEventListener("click", function() {
        const faqItem = question.parentElement;
        const icon = question.querySelector("span");

        faqItem.classList.toggle("active");

        if (faqItem.classList.contains("active")) {
            icon.textContent = "-";
        } else {
            icon.textContent = "+";
        }
    });
});

const notificationBanner = document.getElementById("notificationBanner");
const closeNotification = document.getElementById("closeNotification");

if (closeNotification) {
    closeNotification.addEventListener("click", function() {
        notificationBanner.style.display = "none";
    });
}

const slides = document.querySelectorAll(".slide");
const previousSlide = document.getElementById("previousSlide");
const nextSlide = document.getElementById("nextSlide");

let currentSlide = 0;

function showSlide(index) {
    if (slides.length === 0) {
        return;
    }

    slides.forEach(function(slide) {
        slide.classList.remove("active");
    });

    slides[index].classList.add("active");
}

if (nextSlide) {
    nextSlide.addEventListener("click", function() {
        currentSlide++;

        if (currentSlide >= slides.length) {
            currentSlide = 0;
        }

        showSlide(currentSlide);
    });
}

if (previousSlide) {
    previousSlide.addEventListener("click", function() {
        currentSlide--;

        if (currentSlide < 0) {
            currentSlide = slides.length - 1;
        }

        showSlide(currentSlide);
    });
}

const welcomeModal = document.getElementById("welcomeModal");
const closeModal = document.getElementById("closeModal");
const modalOk = document.getElementById("modalOk");

if (welcomeModal) {
    window.addEventListener("load", function() {
        welcomeModal.classList.add("show");
    });
}

if (closeModal) {
    closeModal.addEventListener("click", function() {
        welcomeModal.classList.remove("show");
    });
}

if (modalOk) 
{
    modalOk.addEventListener("click", function() {
        welcomeModal.classList.remove("show");
    });
}

/* */
const lightTheme = document.getElementById("light");
const darkTheme = document.getElementById("dark");

function applyTheme(theme) {
    if (theme === "dark") {
        document.body.classList.add("dark-theme");

        if (darkTheme) {
            darkTheme.checked = true;
        }
    } else {
        document.body.classList.remove("dark-theme");

        if (lightTheme) {
            lightTheme.checked = true;
        }
    }
}

const savedTheme = localStorage.getItem("studentHubTheme");

if (savedTheme) {
    applyTheme(savedTheme);
} else {
    applyTheme("light");
}

if (lightTheme) {
    lightTheme.addEventListener("change", function() {
        if (lightTheme.checked) {
            applyTheme("light");
            localStorage.setItem("studentHubTheme", "light");
        }
    });
}

if (darkTheme) {
    darkTheme.addEventListener("change", function() {
        if (darkTheme.checked) {
            applyTheme("dark");
            localStorage.setItem("studentHubTheme", "dark");
        }
    });
}

const menuButton = document.querySelector(".menu-button");
const sideMenu = document.querySelector(".side-menu");

if (menuButton && sideMenu) {
    menuButton.addEventListener("click", function(event) {
        event.stopPropagation();
        sideMenu.classList.toggle("show");
    });

    sideMenu.addEventListener("click", function(event) {
        event.stopPropagation();
    });

    document.addEventListener("click", function() {
        sideMenu.classList.remove("show");
    });
}








