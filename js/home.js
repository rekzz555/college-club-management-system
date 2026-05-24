function loadContent(page) {
    const pages = ['clubs', 'home', 'events', 'workshops', 'contact'];
    if (pages.includes(page)) {
      window.location.href = `${page}.html`;
    }
  }
  
  function toggleMenu() {
    const sideMenu = document.getElementById('sideMenu');
    const menuToggle = document.querySelector('.menu-toggle');
  
    // Toggle the 'visible' class to show/hide the side menu
    sideMenu.classList.toggle('visible');
    menuToggle.classList.toggle('active'); // Optional: Add a class for animation or styling
  }
  
  // Attach the toggleMenu function to the hamburger menu button
  document.querySelector('.menu-toggle').addEventListener('click', toggleMenu);
  
  function logout() {
    window.location.href = "http://localhost/college_club/templates/login.html";
  }
  
  document.getElementById("logout-button").addEventListener("click", logout);
  
  // Safely attach event listener for profile button
  const profileBtn = document.getElementById("profile-button");
  if (profileBtn) {
    profileBtn.addEventListener("click", openProfileModal);
  }
  
  // Function to open the profile modal and fetch user details
  function openProfileModal() {
    const profileDetails = document.getElementById("profileDetails");
  
    // Show loading text while fetching data
    profileDetails.innerHTML = "<p>Loading...</p>";
  
    // Fetch user details from the server
    fetch("http://localhost/college_club/php/get_user_details.php", { credentials: 'include' })
      .then((response) => response.json())
      .then((data) => {
        console.log("Response data:", data);
        if (data.success) {
          // Populate the profile details with fetched data
          profileDetails.innerHTML = `
            <p><strong>Name:</strong> ${data.name}</p>
            <p><strong>Email:</strong> ${data.email}</p>
          `;
        } else {
          profileDetails.innerHTML = `<p>${data.message}</p>`;
          console.log("Debug info:", data.debug); // Log debug info
        }
      })
      .catch((error) => {
        console.error("Error fetching profile details:", error);
        profileDetails.innerHTML = "<p>An error occurred. Please try again later.</p>";
      });
  
    // Display the modal
    document.getElementById("profileModal").style.display = "flex";
  }
  
  // Function to close the profile modal
  function closeProfileModal() {
    document.getElementById("profileModal").style.display = "none";
  }

  document.addEventListener("DOMContentLoaded", () => {
    console.log("DOM fully loaded and parsed - carousel script running");

    const carousel = document.getElementById("cardCarousel");
    if (!carousel) {
        console.error("Carousel element not found");
        return;
    }

    let cards = document.querySelectorAll(".event-card");
    if (!cards || cards.length === 0) {
        console.error("No cards found for carousel");
        return;
    }

    const visibleCount = 3; // Number of cards visible at a time
    const cloneCount = visibleCount; // Number of cards to clone at each end

    // Clone last 3 cards to the beginning
    for (let i = 0; i < cloneCount; i++) {
        const clone = cards[cards.length - 1 - i].cloneNode(true);
        clone.id = `last-clone-${i + 1}`;
        carousel.insertBefore(clone, cards[0]);
    }

    // Clone first 3 cards to the end
    for (let i = 0; i < cloneCount; i++) {
        const clone = cards[i].cloneNode(true);
        clone.id = `first-clone-${i + 1}`;
        carousel.appendChild(clone);
    }

    // Re-select all cards after cloning
    cards = document.querySelectorAll(".event-card");

    // Calculate card width including margins
    const cardStyle = getComputedStyle(cards[cloneCount]);
    const cardMargin = parseInt(cardStyle.marginLeft) + parseInt(cardStyle.marginRight);
    const cardWidth = cards[cloneCount].offsetWidth + cardMargin;

    let index = cloneCount; // Start from the first real card (after clones)
    let isTransitioning = false;

    // Set initial position to ensure the first card is fully visible
    carousel.style.transition = "none";
    // Adjust initial position by subtracting full margin for better alignment
    const initialOffset = cardWidth * index - cardMargin;
    carousel.style.transform = `translateX(-${initialOffset}px)`;

    // Function to move the carousel
    const moveCarousel = () => {
        if (isTransitioning) return;
        isTransitioning = true;

        console.log("Moving carousel from index:", index);

        // Move by 1 card at a time
        index += 1;
        carousel.style.transition = "transform 0.5s ease-in-out";
        carousel.style.transform = `translateX(-${cardWidth * index}px)`;
    };

    carousel.addEventListener("transitionend", () => {
        isTransitioning = false;
        console.log("Transition ended at index:", index, "card id:", cards[index]?.id);

        if (cards[index]?.id?.startsWith("first-clone")) {
            carousel.style.transition = "none";
            index = cloneCount;
            carousel.style.transform = `translateX(-${cardWidth * index}px)`;
        }

        if (cards[index]?.id?.startsWith("last-clone")) {
            carousel.style.transition = "none";
            // Reset index to last real card (start of last visible set)
            index = cards.length - cloneCount - 1;
            carousel.style.transform = `translateX(-${cardWidth * index}px)`;
        }
    });

    // Auto-slide every 3.5 seconds
    setInterval(moveCarousel, 3500);

    // Handle window resizing
    window.addEventListener("resize", () => {
        const cardStyle = getComputedStyle(cards[cloneCount]);
        const cardMargin = parseInt(cardStyle.marginLeft) + parseInt(cardStyle.marginRight);
        const newCardWidth = cards[cloneCount].offsetWidth + cardMargin;
        carousel.style.transition = "none";
        // Adjust position with same offset as initial
        const newOffset = newCardWidth * index - cardMargin;
        carousel.style.transform = `translateX(-${newOffset}px)`;
    });

    const menuToggle = document.querySelector(".menu-toggle");
    const sideMenu = document.querySelector(".side-menu");

    menuToggle.addEventListener("click", () => {
        sideMenu.classList.toggle("visible"); // Toggle the 'visible' class
    });
});

let currentIndex = 0;
let autoMoveInterval;

function moveCarousel(direction) {
  const track = document.querySelector('.carousel-track');
  const cards = document.querySelectorAll('.gallery-card');
  const visibleCount = 3; // Number of cards visible at a time
  const cardWidth = cards[0].offsetWidth + 20; // Include gap between cards

  // Calculate new index
  currentIndex += direction;

  // Prevent scrolling out of bounds
  if (currentIndex < 0) {
    currentIndex = 0;
  } else if (currentIndex > cards.length - visibleCount) { // Adjust for visible cards
    currentIndex = cards.length - visibleCount;
  }

  // Move the carousel
  track.style.transform = `translateX(-${currentIndex * cardWidth}px)`;
}

// Function to start auto-moving the carousel
function startAutoMove() {
  autoMoveInterval = setInterval(() => {
    moveCarousel(1); // Move to the next card
  }, 3000); // Move every 3 seconds
}

// Function to stop auto-moving the carousel
function stopAutoMove() {
  clearInterval(autoMoveInterval);
}

// Start auto-moving when the page loads
document.addEventListener('DOMContentLoaded', () => {
  startAutoMove();

  // Stop auto-moving when the user interacts with the carousel
  const track = document.querySelector('.carousel-track');
  track.addEventListener('mouseenter', stopAutoMove);
  track.addEventListener('mouseleave', startAutoMove);
});

function setFeedbackDetails(clubId, eventId) {
  // Set the hidden input fields for club_id and event_id
  document.getElementById("club_id").value = clubId;
  document.getElementById("event_id").value = eventId;
}

function setClubAndEventId() {
  // Map event names to club IDs and event IDs
  const eventDetailsMap = {
    "Meliora": { club_id: "CLB007", event_id: "EVN001" },
    "Shilpkala": { club_id: "CLB004", event_id: "EVN002" },
    "Adroit": { club_id: "CLB006", event_id: "EVN003" }
  };

  // Get the selected event name
  const selectedEvent = document.getElementById("event_name").value;

  // Set the club_id and event_id based on the selected event
  if (eventDetailsMap[selectedEvent]) {
    document.getElementById("club_id").value = eventDetailsMap[selectedEvent].club_id;
    document.getElementById("event_id").value = eventDetailsMap[selectedEvent].event_id;
  }
}

// Example: Call this function when a club or event is selected
// For example, if the student selects "Meliora" event:
setFeedbackDetails("CLB007", "EVN001");
