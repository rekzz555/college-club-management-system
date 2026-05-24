document.addEventListener('DOMContentLoaded', () => {
    // Go Back Button
    window.goBack = function () {
        window.history.back();
    };

    const form = document.getElementById('registerForm');
    const spinner = document.getElementById("spinnerOverlay");

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(form);
        if (spinner) spinner.style.display = "flex"; // Show spinner only on submit

        try {
            const response = await fetch('http://localhost/college_club/php/clubs.php', {
                method: 'POST',
                body: formData,
            });

            const result = await response.text();
            if (spinner) spinner.style.display = "none"; // Hide spinner when done
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
            if (spinner) spinner.style.display = "none";
            showPopup("Server Error. Please try again.", false);
        }
    });
});

// Register Modal logic with club ID
function openRegisterModal(clubName, clubId) {
    const modal = document.getElementById('registerModal');
    const modalTitle = document.getElementById('registerModalTitle');
    const dropdownContainer = document.getElementById('dropdownContainer');
    const optionsDropdown = document.getElementById('options');
    const registerForm = document.getElementById('registerForm');

    registerForm.reset();

    modalTitle.textContent = `Register for ${clubName}`;
    document.getElementById('club').value = clubName;
    document.getElementById('club_id').value = clubId; // New: Set club ID

    optionsDropdown.innerHTML = ''; // Clear dropdown

    if (['Dance Club', 'Music Club', 'Sports Club'].includes(clubName)) {
        dropdownContainer.style.display = 'block';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.disabled = true;
        placeholder.selected = true;
        placeholder.hidden = true;
        placeholder.textContent = 'Select an option';
        optionsDropdown.appendChild(placeholder);

        let options = [];

        if (clubName === 'Dance Club') {
            options = ['Bharatanatyam', ' Contemporary dance', 'Folk dance', 'Hip-hop dance'];
        } else if (clubName === 'Music Club') {
            options = ['Vocals', 'Instrumental', 'Fusion', 'Classical'];
        } else if (clubName === 'Sports Club') {
            options = ['Football', 'Cricket', 'Tennis', 'Hockey', 'Boxing', 'Swimming', 'Karate'];
        }

        options.forEach(opt => {
            const option = document.createElement('option');
            option.value = opt;
            option.textContent = opt;
            optionsDropdown.appendChild(option);
        });
    } else {
        dropdownContainer.style.display = 'none';
    }

    modal.style.display = 'flex';
}

function closeRegisterModal() {
    document.getElementById('registerModal').style.display = 'none';

    const spinner = document.getElementById("spinnerOverlay");
    if (spinner) spinner.style.display = "none";
}

// Popup modal
function showPopup(message, success) {
    const popup = document.createElement('div');
    popup.className = 'popup-modal';
    popup.innerHTML = `
        <div class="popup-content ${success ? 'success' : 'error'}">
            <p>${message}</p>
            <button onclick="closePopup()">OK</button>
        </div>
    `;
    document.body.appendChild(popup);
}

function closePopup() {
    const popup = document.querySelector('.popup-modal');
    if (popup) popup.remove();

    const isSuccess = document.querySelector('.popup-content.success');
    if (isSuccess) closeRegisterModal();
}
