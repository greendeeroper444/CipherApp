const users = [
  { username: "user1", password: "password1" },
  { username: "user2", password: "password2" },
];

let currentUser = null;
let loginAttempts = 0;
const maxLoginAttempts = 3;

function toggleView(view) {
  document.getElementById("registration").style.display = view === "registration" ? "block" : "none";
  document.getElementById("login").style.display = view === "login" ? "block" : "none";
  document.getElementById("cipher").style.display = view === "cipher" ? "block" : "none";
}

function register() {
  const regUsername = document.getElementById("regUsername").value;
  const regPassword = document.getElementById("regPassword").value;

  if (regUsername.length < 6 || regPassword.length < 8) {
    alert("Username should be at least 6 characters and password should be at least 8 characters.");
    return;
  }

  if (users.some(user => user.username === regUsername)) {
    alert("Username is already taken.");
    return;
  }

  users.push({ username: regUsername, password: regPassword });

  alert("Registration successful! You can now login.");
  toggleView("login");
}

function login() {
  const loginUsername = document.getElementById("loginUsername").value;
  const loginPassword = document.getElementById("loginPassword").value;

  const user = users.find(user => user.username === loginUsername);

  if (!user || user.password !== loginPassword) {
    loginAttempts++;
    if (loginAttempts >= maxLoginAttempts) {
      alert("You have exceeded the maximum login attempts. You are blocked.");
      return;
    }
    alert("Invalid username or password. Please try again.");
    return;
  }

  currentUser = user;
  alert(`Welcome, ${currentUser.username}!`);
  toggleView("cipher");
}

function selectCipher() {
  const selectedCipher = document.getElementById("cipherSelect").value;
  alert(`You selected ${selectedCipher} cipher.`);
}

// Initial view setup
toggleView("registration");
