function togglePassword() {
    const password = document.getElementById("pw");
    const button = document.getElementById("password-toggle");
    if (!password || !button) return;

    const icon = button.querySelector("i");
    const isVisible = password.type === "password";
    password.type = isVisible ? "text" : "password";
    if (icon) icon.className = isVisible ? "bi bi-eye-slash" : "bi bi-eye";
    button.setAttribute("aria-label", isVisible ? "Hide password" : "Show password");
    button.setAttribute("aria-pressed", String(isVisible));
}