// Get current theme
const currentTheme = localStorage.getItem('theme') || 'dark';

function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    updateToggleButton(theme);
}

function updateToggleButton(theme) {
    const toggle = document.getElementById('theme-toggle');
    if (toggle) {
        const icon = toggle.querySelector('i');
        const text = toggle.querySelector('span');
        
        if (theme === 'dark') {
            icon.className = 'fas fa-moon';
            text.textContent = 'Dark';
        } else {
            icon.className = 'fas fa-sun';
            text.textContent = 'Light';
        }
    }
}

function toggleTheme() {
    const currentTheme = localStorage.getItem('theme') || 'dark';
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    setTheme(newTheme);
}

// Initialize theme toggle after DOM loads
document.addEventListener('DOMContentLoaded', () => {
    updateToggleButton(currentTheme);
    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', toggleTheme);
    }
});
