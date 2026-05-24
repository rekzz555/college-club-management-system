document.addEventListener('DOMContentLoaded', function() {
  const loginForm = document.querySelector('#loginForm');

  if (loginForm) {
    loginForm.addEventListener('submit', function(event) {
      event.preventDefault();
      const formData = new FormData(loginForm);
      const data = {
        username: formData.get('username'),
        password: formData.get('password'),
      };

      // Use fetch to send login data to backend
      fetch('http://localhost/college_club/php/login.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
      })
      .then(response => response.json())
      .then(data => {
        if (data.message === 'Login successful') {
          showLoginPopup('Login successful!', true);
        } else {
          showLoginPopup(data.message, false);
        }
      })
      .catch(error => {
        console.error('Login error:', error);
        showLoginPopup('An error occurred. Please try again.', false);
      });
    });
  }

  // ✅ These should be OUTSIDE the event listener!
  window.showLoginPopup = function(message, success) {
    const popup = document.createElement('div');
    popup.className = 'popup-modal';
    popup.innerHTML = `
      <div class="popup-content ${success ? 'success' : 'error'}">
        <p>${message}</p>
        <button onclick="closeLoginPopup(${success})">OK</button>
      </div>
    `;
    document.body.appendChild(popup);
  }

  window.closeLoginPopup = function(success) {
    const popup = document.querySelector('.popup-modal');
    if (popup) popup.remove();

    if (success) {
      window.location.href = 'http://localhost/college_club/templates/home.html';
    }
  }
});

