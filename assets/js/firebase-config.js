// Import the functions you need from the SDKs you need
import { initializeApp } from "https://www.gstatic.com/firebasejs/9.6.10/firebase-app.js";
import { getAuth, onAuthStateChanged } from "https://www.gstatic.com/firebasejs/9.6.10/firebase-auth.js";

// Your web app's Firebase configuration
const firebaseConfig = {
    apiKey: "AIzaSyAp6D2r9uDhV1g9nEjYI7hJZg2xSPQZQnM",
    authDomain: "resep-nusantara-55826.firebaseapp.com",
    projectId: "resep-nusantara-55826",
    storageBucket: "resep-nusantara-55826.firebasestorage.app",
    messagingSenderId: "22308405304",
    appId: "1:22308405304:web:1bb52fbf17c0400ca3a6cd",
    measurementId: "G-6X2R7VVLVL"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

// Export auth for use in other files
export { auth, onAuthStateChanged };