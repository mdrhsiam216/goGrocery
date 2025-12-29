document.getElementById("loginForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const username = document.getElementById("username").value;
  const password = document.getElementById("password").value;
  const messageDiv = document.getElementById("message");

  messageDiv.textContent = "Logging in...";
  messageDiv.className = "";

  fetch("../../backend/api.php?action=login", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ username: username, password: password }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        messageDiv.textContent = data.message;
        messageDiv.className = "success";
        // Redirect based on role or to home
        setTimeout(() => {
          // Determine redirect based on role if needed, or just home
          // For now, let's say dashboard
          // window.location.href = '../dashboard.html';
          // Since I don't know the full structure, I'll alert or just show success
          alert("Login Successful! Welcome " + data.user.name);
        }, 1000);
      } else {
        messageDiv.textContent = data.message;
        messageDiv.className = "error";
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      messageDiv.textContent = "An error occurred. Please try again.";
      messageDiv.className = "error";
    });
});
