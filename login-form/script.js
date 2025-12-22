const form = document.getElementById('registrationForm');

form.addEventListener('submit', function(e) {
  e.preventDefault();

  document.querySelectorAll('.error').forEach(el => el.style.display = 'none');
  document.getElementById('successMessage').style.display = 'none';
  document.getElementById('errorMessage').style.display = 'none';

  let valid = true;

  const firstname = document.getElementById('firstname').value.trim();
  const lastname = document.getElementById('lastname').value.trim();
  const email = document.getElementById('email').value.trim();
  const phone = document.getElementById('phone').value.trim();
  const password = document.getElementById('password').value;
  const role = document.getElementById('role').value;

  if (!firstname) { 
    document.getElementById('firstNameError').textContent = 'First name is required';
    document.getElementById('firstNameError').style.display = 'block';
    valid = false;
  }
  if (!lastname) {
    document.getElementById('lastNameError').textContent = 'Last name is required';
    document.getElementById('lastNameError').style.display = 'block';
    valid = false;
  }
  const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailPattern.test(email)) {
    document.getElementById('emailError').textContent = 'Invalid email';
    document.getElementById('emailError').style.display = 'block';
    valid = false;
  }
  const phonePattern = /^[0-9]{10}$/;
  if (!phonePattern.test(phone)) {
    document.getElementById('phoneError').textContent = 'Phone must be 10 digits';
    document.getElementById('phoneError').style.display = 'block';
    valid = false;
  }
  if (password.length < 6) {
    document.getElementById('passwordError').textContent = 'Password must be at least 6 characters';
    document.getElementById('passwordError').style.display = 'block';
    valid = false;
  }
  if (!role) {
    document.getElementById('roleError').textContent = 'Please select a role';
    document.getElementById('roleError').style.display = 'block';
    valid = false;
  }

  if (!valid) return;

  fetch('register.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ firstname, lastname, email, phone, password, role })
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'success') {
      document.getElementById('successMessage').textContent = data.message;
      document.getElementById('successMessage').style.display = 'block';
      form.reset();
    } else {
      document.getElementById('errorMessage').textContent = data.message;
      document.getElementById('errorMessage').style.display = 'block';
    }
  })
  .catch(err => {
    document.getElementById('errorMessage').textContent = 'Server error. Try again later.';
    document.getElementById('errorMessage').style.display = 'block';
  });
});
