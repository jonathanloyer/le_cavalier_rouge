// Vérification si l'utilisateur a déjà accepté les cookies
if (localStorage.getItem('cookies_accepted') !== 'true') {
    // Si non, affiche la bannière
    document.getElementById('cookie-banner').style.display = 'block';
} else {
    // Si déjà accepté, ne pas afficher la bannière
    document.getElementById('cookie-banner').style.display = 'none';
}

// Gère l'acceptation des cookies
document.getElementById('accept-cookies').addEventListener('click', function() {
    // Cache la bannière
    document.getElementById('cookie-banner').style.display = 'none';

    // Sauvegarde dans localStorage que l'utilisateur a accepté les cookies
    localStorage.setItem('cookies_accepted', 'true');
});

// Ouvre le modal lorsque l'utilisateur clique sur le bouton
document.getElementById('privacy-button').addEventListener('click', function() {
    console.log('Politique du site cliquée'); // Log pour vérifier l'événement
    document.getElementById('privacy-modal').style.display = 'block'; // Affiche le modal
});

// Ferme le modal lorsque l'utilisateur clique sur le bouton "Fermer"
document.getElementById('close-modal').addEventListener('click', function() {
    console.log('Modal fermé'); // Log pour vérifier que le bouton fermer fonctionne
    document.getElementById('privacy-modal').style.display = 'none'; // Cache le modal
});

