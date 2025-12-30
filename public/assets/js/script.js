document.addEventListener('DOMContentLoaded', () => {
	const form = document.getElementById('loginForm');
	if (form) {
		form.addEventListener('submit', (e) => {
			e.preventDefault();
			window.location.href = 'dashboard.html';
		});
	}
});

