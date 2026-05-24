// Go back button
function goBack() {
   window.history.back();
}

// Event Dates
const eventDates = {
   Meliora: new Date("2025-04-28T23:59:59"),
   Shilpkala: new Date("2025-05-25T23:59:59"),
   Nrityanjali: new Date("2025-05-10T23:59:59")
};

// Brochure download map
const brochureMap = {
    Meliora: "http://localhost/college_club/brochures/addyourbrochure.pdf", //Add the correct brochure link for Meliora
    Shilpkala: "http://localhost/college_club/brochures/addyourbrochure.pdf",//Add the correct brochure link for Shilpkala
    Adroit: "http://localhost/college_club/brochures/addyourbrochure.pdf"//Add the correct brochure link for Adroit
};

// Sub-event mapping
const subEventMap = {
   Meliora: [
       "The Write Move",
       "Rhyme Rendezvous",
       "Spoken Word Showdown",
       "The Great Meme Off",
       "Flip The Script",
   ],
   Shilpkala: [
       "On-Spot Painting",
       "Face Painting",
       "Poster making",
       "Cartooning",
       "Live Portrait",
       "Collage",
       "Installation",
       "Clay Modelling",
   ],
   Adroit: [
       "Corporate Titan",
       "Timeless Triumph",
       "Pioneers of Prestige",
       "Cent$ible Summit",
       "Zenith Circle",
       "Ascendara",
       "Alchemy Arcadia",
       "Bizards Vault",
       "Aurelius Exchange",
   ]
};

// Function to update countdowns
function updateCountdowns() {
   const now = new Date();

   Object.keys(eventDates).forEach(eventName => {
       const eventDate = eventDates[eventName];
       const countdownElement = document.getElementById(`countdown-${eventName.toLowerCase()}`);

       if (!countdownElement) return;

       const timeDifference = eventDate - now;

       if (timeDifference <= 0) {
           countdownElement.textContent = "Event has started!";
           return;
       }

       const days = Math.floor(timeDifference / (1000 * 60 * 60 * 24));
       const hours = Math.floor((timeDifference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
       const minutes = Math.floor((timeDifference % (1000 * 60 * 60)) / (1000 * 60));
       const seconds = Math.floor((timeDifference % (1000 * 60)) / 1000);

       countdownElement.textContent = `${days}d ${hours}h ${minutes}m ${seconds}s`;
   });
}

setInterval(updateCountdowns, 1000);
updateCountdowns(); // Initial call

// Show registration modal
function showEventDetails(eventName) {
   const modal = document.getElementById("registerModal");
   const modalTitle = document.getElementById("modalEventTitle");
   const checkboxList = document.getElementById("checkboxList");

   modal.style.display = "flex";
   modalTitle.innerText = `Register for ${eventName}`;
   checkboxList.innerHTML = "";

   // Populate checkboxes
   if (subEventMap[eventName]) {
       subEventMap[eventName].forEach(subEvent => {
           const label = document.createElement("label");
           const checkbox = document.createElement("input");
           checkbox.type = "checkbox";
           checkbox.name = "subEvents[]";
           checkbox.value = subEvent;
           checkbox.addEventListener("change", updateSelectedSubEvents);
           label.appendChild(checkbox);
           label.appendChild(document.createTextNode(" " + subEvent));
           checkboxList.appendChild(label);
       });
   } else {
       checkboxList.innerHTML = "<p>No sub-events available for this event.</p>";
   }

   // Clear selected sub-events input
   document.getElementById("selectedSubEvents").value = "";

   // Setup brochure download
   const downloadButton = document.getElementById("downloadBrochure");
   if (brochureMap[eventName]) {
       downloadButton.onclick = function () {
           const link = document.createElement("a");
           link.href = brochureMap[eventName];
           link.download = brochureMap[eventName].split("/").pop();
           document.body.appendChild(link);
           link.click();
           document.body.removeChild(link);
       };
   } else {
       downloadButton.onclick = null;
   }
}

// Close modal
function closeModal() {
   document.getElementById("registerModal").style.display = "none";
}

// Toggle sub-event dropdown
function toggleCheckboxDropdown() {
   const dropdown = document.getElementById("checkboxList");
   dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
}

// Update selected sub-events text input
function updateSelectedSubEvents() {
   const checkboxes = document.querySelectorAll("#checkboxList input[type='checkbox']");
   const selected = Array.from(checkboxes)
       .filter(checkbox => checkbox.checked)
       .map(checkbox => checkbox.value);

   document.getElementById("selectedSubEvents").value = selected.join(", ");
}

// Close dropdown if clicked outside
window.addEventListener("click", function (e) {
   const dropdown = document.getElementById("subEventDropdown");
   if (dropdown && !dropdown.contains(e.target)) {
       document.getElementById("checkboxList").style.display = "none";
   }
});

// Submit form and show response modal
document.getElementById('registerForm').addEventListener('submit', function (e) {
   e.preventDefault();

   const formData = new FormData(this);
   const eventName = document.getElementById("modalEventTitle").textContent.replace("Register for ", "");
   formData.append("event", eventName);

   fetch("http://localhost/college_club/php/events.php", {
       method: "POST",
       body: formData
   })
   .then(response => response.text())
   .then(data => {
       const trimmedData = data.trim();

       if (trimmedData === "You have already registered.") {
           showStatusModal("", "You have already registered.");
       } else if (trimmedData === "Registration successful!") {
           closeModal();
           showStatusModal("", "You have registered successfully!"); // Removed 'Success'
           document.getElementById("registerForm").reset();
       } else {
           showStatusModal("Error", trimmedData);
       }
   });
});

// Show status modal
function showStatusModal(title, message) {
   const titleElement = document.getElementById("statusTitle");
   if (title) {
       titleElement.innerText = title;
       titleElement.style.display = "block";
   } else {
       titleElement.style.display = "none";
   }

   document.getElementById("statusMessage").innerText = message;
   document.getElementById("statusModal").style.display = "flex";
}

// Close status modal and refresh page
function closeStatusModal() {
   document.getElementById("statusModal").style.display = "none";
   window.location.href = "http://localhost/college_club/templates/events.html";
}

const form = document.getElementById('registerForm');
const spinner = document.getElementById("spinnerOverlay");

form.addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = new FormData(form);

    // Show spinner
    if (spinner) {
        spinner.style.display = "flex";
    }

    try {
        const response = await fetch('http://localhost/college_club/php/events.php', {
            method: 'POST',
            body: formData,
        });

        const result = await response.text();

        // Hide spinner
        if (spinner) {
            spinner.style.display = "none";
        }

        // Handle response
        if (result.includes("success")) {
            showPopup("Registration Successful", true);
            form.reset();
        } else if (result.includes("already_registered")) {
            showPopup("You have already registered for this club.", false);
        } else if (result.includes("mail_error")) {
            showPopup("Email Failed to Send", false);
        } else {
            showPopup("Registration Failed. Try again.", false);
        }
    } catch (error) {
        // Hide spinner in case of an error
        if (spinner) {
            spinner.style.display = "none";
        }
        showPopup("Server Error. Please try again.", false);
    }
});